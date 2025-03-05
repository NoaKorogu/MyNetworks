let lat = 48.852969; // Default latitude (Paris)
let lon = 2.349903;  // Default longitude (Paris)
let macarte = null;
let waypointMode = false;
let waypoints = [];
let waterLinesLayer, gasLinesLayer;

// Créer un nouvel objet d'icône pour ajuster l'ancre
let customIcon = L.icon({
    iconUrl: 'https://unpkg.com/leaflet@1.7.1/dist/images/marker-icon.png', // Icône par défaut
    iconSize: [25, 41],       // Taille de l'icône (largeur, hauteur)
    iconAnchor: [12, 41],     // Point d'ancrage de l'icône (centre bas)
    popupAnchor: [0, -41]     // Position de la bulle par rapport à l'icône
});

// Initialisation de la carte
function initMap() {
    macarte = L.map('map').setView([lat, lon], 11);
    L.tileLayer('https://{s}.tile.openstreetmap.fr/osmfr/{z}/{x}/{y}.png', {
        attribution: 'données © <a href="//osm.org/copyright">OpenStreetMap</a>/ODbL - rendu <a href="//openstreetmap.fr">OSM France</a>',
        minZoom: 1,
        maxZoom: 20
    }).addTo(macarte);

    waterLinesLayer = L.layerGroup().addTo(macarte);
    gasLinesLayer = L.layerGroup().addTo(macarte);

    // Demande la localisation de l'utilisateur
    centerMapOnUserLocation();

    // Ajoute un événement pour les clics sur la carte
    macarte.on('click', function(e) {
        if (waypointMode) {
            createWaypoint(e.latlng);
        }
    });
}

// Activer le mode waypoint
function enableWaypointMode() {
    waypointMode = true;
    document.getElementById('map').classList.add('cursor-waypoint');  // Ajoute la classe pour le curseur
    alert("Cliquez sur la carte pour ajouter un waypoint.");
}

// Créer un nouveau waypoint
function createWaypoint(latlng) {
    waypointMode = false;

    // Retirer la classe pour revenir au curseur normal
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

// Modifier le nom d'un waypoint
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

// Supprimer un waypoint
function deleteWaypoint(index) {
    let confirmDelete = confirm("Voulez-vous vraiment supprimer ce waypoint ?");
    if (confirmDelete) {
        macarte.removeLayer(waypoints[index].marker);
        waypoints.splice(index, 1);
    }
}

// Recherche de lieu avec l'API Nominatim
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

                let marker = L.marker([lat, lon]).addTo(macarte)
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

// Centrer la carte sur la position de l'utilisateur
function centerMapOnUserLocation() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            function(position) {
                let userLat = position.coords.latitude;
                let userLon = position.coords.longitude;

                // Centrer la carte et ajouter un marqueur pour la position de l'utilisateur
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
            layer.clearLayers(); // Nettoyer la couche avant d'ajouter de nouvelles données

            let geojson = window.osmtogeojson(data); // Utilisez la méthode ici
            L.geoJSON(geojson, {
                style: { color: layer === waterLinesLayer ? "blue" : "red", weight: 2 }
            }).addTo(layer);
        })
        .catch(error => console.error("Erreur lors du chargement des données OSM :", error));
}

function toggleWaterLines() {
    if (document.getElementById("toggle-water").checked) {
        let waterQuery = `
            [out:json];
            way["man_made"="pipeline"]["substance"="water"];
            out geom;`;
        fetchOSMData(waterQuery, waterLinesLayer);
    } else {
        waterLinesLayer.clearLayers();
    }
}

function toggleGasLines() {
    if (document.getElementById("toggle-gas").checked) {
        let gasQuery = `
            [out:json];
            way["man_made"="pipeline"]["substance"="gas"];
            out geom;`;
        fetchOSMData(gasQuery, gasLinesLayer);
    } else {
        gasLinesLayer.clearLayers();
    }
}

// Charger la carte au chargement de la fenêtre
window.onload = function() {
    initMap();
};