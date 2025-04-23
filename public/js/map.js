let lat = 48.852969; let lon = 2.349903;  // Map center by default on Paris
let macarte = null;
let waypointMode = false;
let waypoints = [];
let waterLinesLayer, gasLinesLayer;

// Define waypoint appearance
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

    macarte.on('click', function(e) {
        if (waypointMode) {
            createWaypoint(e.latlng);
        }
    });
}

function enableWaypointMode() {
    waypointMode = true;
    document.getElementById('map').classList.add('cursor-waypoint'); 
    alert("Cliquez sur la carte pour ajouter un waypoint.");
}

// Create new waypoint
function createWaypoint(latlng) {
    waypointMode = false;

    // waypoint cursor -> normal cursor
    document.getElementById('map').classList.remove('cursor-waypoint');

    let marker = L.marker([latlng.lat, latlng.lng], { icon: customIcon }).addTo(macarte);
    let waypoint = {
        lat: latlng.lat,
        lon: latlng.lng,
        marker: marker,
        name: "Nouveau Waypoint"
    };
    waypoints.push(waypoint);

    marker.bindPopup(`
        <div>
            <b>${waypoint.name}</b><br>
            <button onclick="editWaypoint(${waypoints.length - 1})">Modifier le nom</button><br>
            <button onclick="deleteWaypoint(${waypoints.length - 1})">Supprimer</button>
        </div>
    `).openPopup();
}

// Edit waypoint name
function editWaypoint(index) {
    let newName = prompt("Entrez un nouveau nom pour ce waypoint :", waypoints[index].name);
    if (newName !== null && newName.trim() !== "") {
        waypoints[index].name = newName;
        waypoints[index].marker.bindPopup(`
            <div>
                <b>${newName}</b><br>
                <button onclick="editWaypoint(${index})">Modifier le nom</button><br>
                <button onclick="deleteWaypoint(${index})">Supprimer</button>
            </div>
        `).openPopup();
    }
}

// Delete waypoint
function deleteWaypoint(index) {
    let confirmDelete = confirm("Voulez-vous vraiment supprimer ce waypoint ?");
    if (confirmDelete) {
        macarte.removeLayer(waypoints[index].marker);
        waypoints.splice(index, 1);
    }
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

// "Refresh" the path after an update
function updatePathOnMap(pathId) {
    fetch(`/api/paths/${pathId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Failed to fetch the updated path.');
            }
            return response.json();
        })
        .then(data => {
            // Find and remove the existing path from the map
            if (busLinesLayer) {
                busLinesLayer.eachLayer(layer => {
                    if (layer.feature && layer.feature.properties.id === pathId) {
                        busLinesLayer.removeLayer(layer);
                    }
                });
            }

            // Add the updated path to the map
            const coordinates = data.geometry.coordinates.map(coord => [coord[1], coord[0]]);
            const polyline = L.polyline(coordinates, {
                color: data.properties.color || 'blue',
                weight: 4
            });

            // Bind a popup with buttons for modifying or deleting the path
            polyline.bindPopup(`
                <div id="button-container">
                    <b>${data.properties.name || 'Unnamed Path'}</b><br>
                    <button onclick="modifyPathName(${data.properties.id})">Modifier le nom</button><br>
                    <button onclick="modifyPathColor(${data.properties.id})">Modifier la couleur</button><br>
                    <button onclick="deletePath(${data.properties.id})">Supprimer</button>
                </div>
            `);

            // Add the updated path to the busLinesLayer
            if (!busLinesLayer) {
                busLinesLayer = L.layerGroup().addTo(macarte);
            }
            busLinesLayer.addLayer(polyline);
        })
        .catch(error => {
            console.error('Error updating path on map:', error);
            alert('Erreur lors de la mise à jour du chemin sur la carte.');
        });
}

function modifyPathName(pathId) {
    const newName = prompt('Entrez le nouveau nom du chemin :');
    if (!newName) {
        alert('Modification annulée.');
        return;
    }

    fetch(`/api/paths/${pathId}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ name: newName }),
    })
        .then(response => {
            if (!response.ok) {
                throw new Error('Failed to modify path name.');
            }
            return response.json();
        })
        .then(data => {
            alert('Nom du chemin modifié avec succès !');
            updatePathOnMap(pathId); // Refresh the path on the map
        })
        .catch(error => {
            console.error('Error modifying path name:', error);
            alert('Erreur lors de la modification du nom du chemin.');
        });
}

function modifyPathColor(pathId) {
    const newColor = prompt('Entrez la nouvelle couleur du chemin (e.g., #FF5733) :');
    if (!newColor) {
        alert('Modification annulée.');
        return;
    }

    fetch(`/api/paths/${pathId}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ color: newColor }),
    })
        .then(response => {
            if (!response.ok) {
                throw new Error('Failed to modify path color.');
            }
            return response.json();
        })
        .then(data => {
            alert('Couleur du chemin modifiée avec succès !');
            updatePathOnMap(pathId);
        })
        .catch(error => {
            console.error('Error modifying path color:', error);
            alert('Erreur lors de la modification de la couleur du chemin.');
        });
}

function deletePath(pathId) {
    const confirmDelete = confirm('Voulez-vous vraiment supprimer ce chemin ?');
    if (!confirmDelete) {
        return;
    }

    fetch(`/api/paths/${pathId}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
        },
    })
        .then(response => {
            if (!response.ok) {
                throw new Error('Failed to delete path.');
            }
            return response.json();
        })
        .then(data => {
            alert('Chemin supprimé avec succès !');
            toggleBusLines(); // Refresh the bus lines layer
        })
        .catch(error => {
            console.error('Error deleting path:', error);
            alert('Erreur lors de la suppression du chemin.');
        });
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

// Load map 
window.onload = function() {
    initMap();
};