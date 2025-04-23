function initializeDrawingFeatures() {
    if (!macarte) {
        console.error('Map is not initialized. Ensure initMap() is called before this script.');
        return;
    }

    let drawnCoordinates = [];
    let drawingMode = false;
    let drawnPolyline = null;

    // Enable drawing mode
    function enableDrawingMode() {
        drawingMode = true;
        drawnCoordinates = [];
        if (drawnPolyline) {
            macarte.removeLayer(drawnPolyline);
            drawnPolyline = null;
        }
        alert('Cliquez sur la carte pour créer un point.');
    }

    // Disable drawing mode
    function disableDrawingMode(error) {
        drawingMode = false;
        if (drawnPolyline) {
            macarte.removeLayer(drawnPolyline);
            drawnPolyline = null;
        }
        alert(error);
    }

    // Handle map clicks to collect coordinates
    macarte.on('click', function (e) {
        if (!drawingMode) return;

        const { lat, lng } = e.latlng;
        drawnCoordinates.push([lng, lat]); // Store coordinates in GeoJSON format (lng, lat)

        // Draw the polyline on the map
        if (drawnPolyline) {
            macarte.removeLayer(drawnPolyline);
        }
        drawnPolyline = L.polyline(drawnCoordinates.map(coord => [coord[1], coord[0]]), {
            color: 'blue',
            weight: 4,
        }).addTo(macarte);
    });

    // Ask the user for path details and submit the drawn path to the backend
    function finishDrawing() {
        if (drawnCoordinates.length === 0) {
            disableDrawingMode("Aucun point dessiné. Fin de la création de chemin.");
            return;
        }

        if (drawnCoordinates.length < 2) {
            disableDrawingMode("Un chemin doit avoir au moins 2 points.");
            return;
        }

        // Prompt the user for the path name and color
        const name = prompt('Entrer le nom du chemin:', 'Mon chemin');
        if (!name) {
            disableDrawingMode('Création du chemin annulé.');
            return;
        }

        const color = prompt('Choisir la couleur du chemin (e.g., #FF5733):', '#0000FF');
        if (!color) {
            disableDrawingMode('Création du chemin annulé.');
            return;
        }

        const pathData = {
            name: name,
            color: color,
            coordinates: drawnCoordinates,
        };

        // Send the path data to the backend
        fetch('/api/paths', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(pathData),
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Failed to save path.');
                }
                return response.json();
            })
            .then(data => {
                alert('Chemin crée avec succes !');
                disableDrawingMode();
            })
            .catch(error => {
                console.error('Error saving path:', error);
                alert('Erreur lors de la création du chemin.');
            });
    }

    // Expose functions globally
    window.enableDrawingMode = enableDrawingMode;
    window.finishDrawing = finishDrawing;
}

// Initialize drawing features after the map is loaded
window.onload = function () {
    initMap(); // Ensure the map is initialized
    initializeDrawingFeatures(); // Initialize drawing features
};
