let lat = 48.852969; let lon = 2.349903;  // Map center by default on Paris
let macarte = null;
let waterLinesLayer, gasLinesLayer, pathsLayer;
const userRole = document.getElementById('map').dataset.role;

const roleToNetwork = {
    'ROLE_EDF': 1,      // Network ID 1 pour EDF
    'ROLE_WATER': 2,    // Network ID 2 pour Water
    'ROLE_FILIBUS': 3   // Network ID 3 pour Filibus
};

// Define structure appearance
let customIcon = L.icon({
    iconUrl: 'https://unpkg.com/leaflet@1.7.1/dist/images/marker-icon.png',
    iconSize: [25, 41],     
    iconAnchor: [12, 41],   
    popupAnchor: [0, -41]    
});

// Map init
function initMap() {
    macarte = L.map('map').setView([lat, lon], 11);
    L.tileLayer('https://{s}.tile.openstreetmap.fr/osmfr/{z}/{x}/{y}.png', {
        attribution: 'données © <a href="//osm.org/copyright">OpenStreetMap</a>/ODbL - rendu <a href="//openstreetmap.fr">OSM France</a>',
        minZoom: 1,
        maxZoom: 20
    }).addTo(macarte);

    waterLinesLayer = L.layerGroup().addTo(macarte);
    gasLinesLayer = L.layerGroup().addTo(macarte);

    // Ask for user location
    centerMapOnUserLocation();
}

function saveStructure(index) {
    const waypoint = waypoints[index];
    const selectElement = document.getElementById(`structure-select-${index}`);
    const selectedType = selectElement.value;

    if (!selectedType) {
        alert("Veuillez sélectionner une structure.");
        return;
    }

    // Envoyer les données au backend
    fetch('/api/structures', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            lat: waypoint.lat,
            lon: waypoint.lon,
            name: waypoint.name,
            networkId: waypoint.networkId,
            type: selectedType
        }),
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert("Structure sauvegardée avec succès !");
            } else {
                alert("Erreur lors de la sauvegarde : " + (data.error || "Inconnue"));
            }
        })
        .catch(error => {
            console.error("Erreur lors de la sauvegarde :", error);
            alert("Erreur lors de la sauvegarde. Veuillez réessayer.");
        });
}

// Permit to search the location when pressing 'Enter' key
let searchBar = document.getElementById('search-bar');

searchBar.addEventListener('keydown', function(event) {
    if (event.key === 'Enter') {
        searchLocation();
    }
});

// Search Bar
function searchLocation() {
    let query = document.getElementById('search-bar').value;
    if (!query) {
        alert("Veuillez entrer un lieu à rechercher.");
        return;
    }
    
    fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(data => {
            if (data && data.length > 0) {
                let result = data[0];
                let lat = parseFloat(result.lat);
                let lon = parseFloat(result.lon);

                macarte.setView([lat, lon], 14);

                L.marker([lat, lon]).addTo(macarte)
                    .bindPopup(`<b>${result.display_name}</b>`)
                    .openPopup();
            } else {
                alert("Aucun résultat trouvé pour ce lieu.");
            }
        })
        .catch(err => {
            console.error(err);
            alert("Erreur lors de la recherche. Veuillez réessayer.");
        });
}

// Center map on user location
function centerMapOnUserLocation() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            function(position) {
                let userLat = position.coords.latitude;
                let userLon = position.coords.longitude;

                // Center map and add a waypoint on user location
                macarte.setView([userLat, userLon], 17);
            },
            function(error) {
                alert("Impossible d'obtenir votre localisation. Veuillez vérifier vos permissions.");
                console.error("Erreur de géolocalisation : ", error);
            }
        );
    } else {
        alert("La géolocalisation n'est pas prise en charge par votre navigateur.");
    }
}

function fetchOSMData(query, layer) {
    const OVERPASS_URL = "https://overpass-api.de/api/interpreter";
    fetch(`${OVERPASS_URL}?data=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(data => {
            layer.clearLayers();

            let geojson = window.osmtogeojson(data);
            L.geoJSON(geojson, {
                style: { color: layer === waterLinesLayer ? "blue" : "red", weight: 2 }
            }).addTo(layer);
        })
        .catch(error => console.error("Erreur lors du chargement des données OSM :", error));
}

function updatePathOnMap(pathId, updatedPathData) {
    // Find and remove the existing path from the map
    if (pathsLayer) {
        pathsLayer.eachLayer(layer => {
            if (layer.feature && layer.feature.properties.id === pathId) {
                pathsLayer.removeLayer(layer);
            }
        });
    }

    // Add the updated path to the map
    const coordinates = updatedPathData.geometry.coordinates.map(coord => [coord[1], coord[0]]);
    const polyline = L.polyline(coordinates, {
        color: updatedPathData.properties.color || 'blue',
        weight: 4
    });

    // Bind a popup with buttons for modifying or deleting the path
    polyline.bindPopup(`
        <div id="button-container">
            <b>${updatedPathData.properties.name || 'Unnamed Path'}</b><br>
            <button onclick="modifyPathName(${updatedPathData.properties.id})">Modifier le nom</button><br>
            <button onclick="modifyPathColor(${updatedPathData.properties.id})">Modifier la couleur</button><br>
            <button onclick="deletePath(${updatedPathData.properties.id})">Supprimer</button>
        </div>
    `);

    // Add the updated path to the pathsLayer
    if (!pathsLayer) {
        pathsLayer = L.layerGroup().addTo(macarte);
    }
    pathsLayer.addLayer(polyline);

    // Open the popup after adding the updated path
    polyline.openPopup();
}

let busLinesLayer = null;

function toggleBusLines() {
    if (busLinesLayer) {
        // If bus lines are already displayed, remove them from the map
        macarte.removeLayer(busLinesLayer);
        busLinesLayer = null;
    } else {
        // Fetch bus lines from the API
        fetch('/api/paths')
            .then(response => response.json())
            .then(data => {
                if (!data.features || !Array.isArray(data.features)) {
                    throw new Error('Invalid GeoJSON data');
                }

                // Create polylines for each feature
                const busLines = data.features.map(feature => {
                    const coordinates = feature.geometry.coordinates.map(coord => [coord[1], coord[0]]);
                    const polyline = L.polyline(coordinates, {
                        color: feature.properties.color || 'blue', // Default color if not provided
                        weight: 4
                    });
                    
                    // Attach the feature object to the polyline
                    polyline.feature = feature;
                    
                    // Bind a popup with buttons for modifying or deleting the path
                    polyline.bindPopup(`
                        <div id="button-container">
                            <b>${feature.properties.name || 'Unnamed Bus Line'}</b><br>
                            <button onclick="modifyPathName(${feature.properties.id})">Modifier le nom</button><br>
                            <button onclick="modifyPathColor(${feature.properties.id})">Modifier la couleur</button><br>
                            <button onclick="deletePath(${feature.properties.id})">Supprimer</button>
                        </div>
                    `);

                    return polyline;
                });

                // Create a LayerGroup and add it to the map
                busLinesLayer = L.layerGroup(busLines);
                macarte.addLayer(busLinesLayer); // Add the LayerGroup to the map
            })
            .catch(error => console.error('Error fetching bus lines:', error));
    }
}

function refreshBusLines() {
    const checkbox = document.getElementById('toggle-bus'); // Reference the checkbox
    if (!checkbox.checked) {
        // If the checkbox is not checked, do nothing
        return;
    }

    if (busLinesLayer) {
        // Remove the existing layer
        macarte.removeLayer(busLinesLayer);
        busLinesLayer = null;
    }

    // Fetch and display the bus lines again
    fetch('/api/paths')
        .then(response => response.json())
        .then(data => {
            if (!data.features || !Array.isArray(data.features)) {
                throw new Error('Invalid GeoJSON data');
            }

            // Create polylines for each feature
            const busLines = data.features.map(feature => {
                const coordinates = feature.geometry.coordinates.map(coord => [coord[1], coord[0]]);
                const polyline = L.polyline(coordinates, {
                    color: feature.properties.color || 'blue', // Default color if not provided
                    weight: 4
                });
                
                // Attach the feature object to the polyline
                polyline.feature = feature;
                
                // Bind a popup with buttons for modifying or deleting the path
                polyline.bindPopup(`
                    <div id="button-container">
                        <b>${feature.properties.name || 'Unnamed Bus Line'}</b><br>
                        <button onclick="modifyPathName(${feature.properties.id})">Modifier le nom</button><br>
                        <button onclick="modifyPathColor(${feature.properties.id})">Modifier la couleur</button><br>
                        <button onclick="deletePath(${feature.properties.id})">Supprimer</button>
                    </div>
                `);

                return polyline;
            });

            // Create a LayerGroup and add it to the map
            busLinesLayer = L.layerGroup(busLines);
            macarte.addLayer(busLinesLayer); // Add the LayerGroup to the map
        })
        .catch(error => console.error('Error refreshing bus lines:', error));


    // Fonction pour afficher les structures liées au bus
    function toggleBusStructures() {
        if (busStructuresLayer) {
            macarte.removeLayer(busStructuresLayer);
            busStructuresLayer = null;
        } else {
            fetch('/api/structures?type=bus_stop')
                .then(response => response.json())
                .then(data => {
                    if (!data.features || !Array.isArray(data.features)) {
                        throw new Error('Invalid GeoJSON data');
                    }

                    const busStructures = data.features.map(feature => {
                        const coordinates = feature.geometry.coordinates;
                        const marker = L.marker([coordinates[1], coordinates[0]], { icon: customIcon });
                        marker.bindPopup(`<b>${feature.properties.name}</b>`);
                        return marker;
                    });

                    busStructuresLayer = L.layerGroup(busStructures).addTo(macarte);
                })
                .catch(error => console.error('Error fetching bus structures:', error));
        }
    }

    // Fonction pour afficher les structures liées à l'électricité
    function toggleElectricalStructures() {
        if (electricalStructuresLayer) {
            macarte.removeLayer(electricalStructuresLayer);
            electricalStructuresLayer = null;
        } else {
            fetch('/api/structures?type=electrical')
                .then(response => response.json())
                .then(data => {
                    if (!data.features || !Array.isArray(data.features)) {
                        throw new Error('Invalid GeoJSON data');
                    }

                    const electricalStructures = data.features.map(feature => {
                        const coordinates = feature.geometry.coordinates;
                        const marker = L.marker([coordinates[1], coordinates[0]], { icon: customIcon });
                        marker.bindPopup(`<b>${feature.properties.name}</b>`);
                        return marker;
                    });

                    electricalStructuresLayer = L.layerGroup(electricalStructures).addTo(macarte);
                })
                .catch(error => console.error('Error fetching electrical structures:', error));
        }
    }

    // Fonction pour afficher les structures liées à l'eau
    function toggleWaterStructures() {
        if (waterStructuresLayer) {
            macarte.removeLayer(waterStructuresLayer);
            waterStructuresLayer = null;
        } else {
            fetch('/api/structures?type=water')
                .then(response => response.json())
                .then(data => {
                    if (!data.features || !Array.isArray(data.features)) {
                        throw new Error('Invalid GeoJSON data');
                    }

                    const waterStructures = data.features.map(feature => {
                        const coordinates = feature.geometry.coordinates;
                        const marker = L.marker([coordinates[1], coordinates[0]], { icon: customIcon });
                        marker.bindPopup(`<b>${feature.properties.name}</b>`);
                        return marker;
                    });

                    waterStructuresLayer = L.layerGroup(waterStructures).addTo(macarte);
                })
                .catch(error => console.error('Error fetching water structures:', error));
        }
    }

    window.toggleElectricalStructures = toggleElectricalStructures;
    window.pathsLayer = pathsLayer;

}

// Load map 
window.onload = function() {
    initMap();
};