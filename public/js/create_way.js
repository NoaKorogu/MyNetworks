function initializeDrawingFeatures() {
    if (!macarte) {
        console.error('Map is not initialized. Ensure initMap() is called before this script.');
        return;
    }

    let drawnCoordinates = [];
    let drawingMode = false;
    let drawnPolyline = null;

    const endPathButton = document.getElementById('endPathButton');

    // Enable drawing mode
    function enableDrawingMode() {
        drawingMode = true;
        drawnCoordinates = [];
        if (drawnPolyline) {
            macarte.removeLayer(drawnPolyline);
            drawnPolyline = null;
        }
        endPathButton.disabled = false; 
        alert('Cliquez sur la carte pour créer un point.');
    }

    // Disable drawing mode
    function disableDrawingMode(error) {
        drawingMode = false;
        if (drawnPolyline) {
            macarte.removeLayer(drawnPolyline);
            drawnPolyline = null;
        }
        endPathButton.disabled = true;
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

        showColorPicker()
        .then(color => {
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
                    disableDrawingMode('Chemin crée avec succes !');
                    
                    // Check if the checkbox to show paths is checked
                    const checkbox = document.getElementById('toggle-paths');
                    if (checkbox.checked) {
                        // Fetch the most recently created path
                        fetch('/api/paths/last')
                            .then(response => response.json())
                            .then(pathData => {
                                const coordinates = pathData.geometry.coordinates.map(coord => [coord[1], coord[0]]);
                                const polyline = L.polyline(coordinates, {
                                    color: pathData.properties.color || 'blue',
                                    weight: 4,
                                });

                                // Bind a popup with buttons for modifying or deleting the path
                                polyline.bindPopup(`
                                    <div id="button-container">
                                        <b>${pathData.properties.name || 'Unnamed Path'}</b><br>
                                        <button onclick="modifyPathName(${pathData.properties.id})">Modifier le nom</button><br>
                                        <button onclick="modifyPathColor(${pathData.properties.id})">Modifier la couleur</button><br>
                                        <button onclick="deletePath(${pathData.properties.id})">Supprimer</button>
                                    </div>
                                `);

                                // Add the new path to the busLinesLayer
                                if (!busLinesLayer) {
                                    busLinesLayer = L.layerGroup().addTo(macarte);
                                }
                                busLinesLayer.addLayer(polyline);
                            })
                            .catch(error => {
                                console.error('Error fetching the last path:', error);
                                alert('Erreur lors de l\'ajout du nouveau chemin sur la carte.');
                            });
                    }
                })
                .catch(error => {
                    console.error('Error saving path:', error);
                    alert('Erreur lors de la création du chemin.');
                });
        })
        .catch(error => {
            console.log(error);
            disableDrawingMode('Création du chemin annulé.');
        });
    }

    // Toggle paths with modification and deletion buttons
    function togglePaths() {
        if (pathsLayer) {
            macarte.removeLayer(pathsLayer);
            pathsLayer = null;
        } else {
            fetch('/api/paths')
                .then(response => response.json())
                .then(data => {
                    if (!data.features || !Array.isArray(data.features)) {
                        throw new Error('Invalid GeoJSON data');
                    }

                    const paths = data.features.map(feature => {
                        const coordinates = feature.geometry.coordinates.map(coord => [coord[1], coord[0]]);
                        const polyline = L.polyline(coordinates, {
                            color: feature.properties.color || 'blue',
                            weight: 4,
                        });

                        // Bind a popup with buttons for modifying or deleting the path
                        polyline.bindPopup(`
                            <div id="button-container">
                                <b>${feature.properties.name || 'Unnamed Path'}</b><br>
                                <button onclick="modifyPathName(${feature.properties.id})">Modifier le nom</button><br>
                                <button onclick="modifyPathColor(${feature.properties.id})">Modifier la couleur</button><br>
                                <button onclick="deletePath(${feature.properties.id})">Supprimer</button>
                            </div>
                        `);

                        return polyline;
                    });

                    pathsLayer = L.layerGroup(paths).addTo(macarte);
                })
                .catch(error => console.error('Error fetching paths:', error));
        }
    }

    // Expose functions globally
    window.enableDrawingMode = enableDrawingMode;
    window.finishDrawing = finishDrawing;
    window.togglePaths = togglePaths;
    window.initializeDrawingFeatures = initializeDrawingFeatures;
}