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
        alert('Drawing mode enabled. Click on the map to add points.');
    }

    // Disable drawing mode
    function disableDrawingMode() {
        drawingMode = false;
        if (drawnPolyline) {
            macarte.removeLayer(drawnPolyline);
            drawnPolyline = null;
        }
        alert('Drawing mode disabled.');
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
        if (drawnCoordinates.length < 2) {
            alert('A path must have at least two points.');
            return;
        }

        // Prompt the user for the path name and color
        const name = prompt('Enter the name of the path:', 'Unnamed Path');
        if (!name) {
            alert('Path creation canceled.');
            return;
        }

        const color = prompt('Enter the color of the path (e.g., #FF5733):', '#0000FF');
        if (!color) {
            alert('Path creation canceled.');
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
                alert('Path saved successfully!');
                disableDrawingMode();
            })
            .catch(error => {
                console.error('Error saving path:', error);
                alert('Error saving path.');
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
