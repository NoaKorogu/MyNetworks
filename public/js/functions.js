// Fonction principale pour initialiser toutes les fonctionnalités
function initializeApp() {
    // Initialiser les fonctionnalités de création de structures
    if (typeof initializeStructureFeatures === 'function') {
        initializeStructureFeatures();
    } else {
        console.error('initializeStructureFeatures is not defined.');
    }

    // Initialiser les fonctionnalités de création de chemins
    if (typeof initializeDrawingFeatures === 'function') {
        initializeDrawingFeatures();
    } else {
        console.error('initializeDrawingFeatures is not defined.');
    }

    // Initialiser les fonctionnalités d'affichage des structures et des chemins
    initializeToggleFeatures();
}

// Fonction pour initialiser les fonctionnalités d'affichage des structures et des chemins
function initializeToggleFeatures() {
    let busStructuresLayer = null;
    let electricalStructuresLayer = null;
    let waterStructuresLayer = null;
    let pathsLayer = null;

    window.toggleBusStructures = function () {
        if (busStructuresLayer) {
            macarte.removeLayer(busStructuresLayer);
            busStructuresLayer = null;
        } else {
            fetch('/api/structures/type/bus_stop')
                .then(response => response.json())
                .then(data => {
                    if (!data.features || !Array.isArray(data.features)) {
                        throw new Error('Invalid GeoJSON data');
                    }
    
                    const busStructures = data.features.map(feature => {
                        const coordinates = feature.geometry.coordinates;
                        const properties = feature.properties;
    
                        const marker = L.marker([coordinates[1], coordinates[0]], { icon: customIcon });
                        let popupContent = `
                            <div id="popup-content-${properties.id}">
                                <b>Nom :</b> ${properties.name}<br>
                                <b>Type :</b> ${properties.type_name || 'Inconnu'}<br>
                        `;
    
                        // Ajouter les champs supplémentaires en fonction du type
                        if (properties.type === 'bus_stop') {
                            if (properties.type_name === 'Arrêt de bus') {
                                popupContent += `
                                    <b>Numéro de ligne :</b> ${properties.line_number}<br>
                                `;
                            }
                        }
    
                        // Vérifier les permissions de l'utilisateur
                        const userHasPermission = app && app.user && (
                            app.user.roles.includes('ROLE_ADMIN') || app.user.roles.includes('ROLE_FILIBUS')
                        );
                        if (userHasPermission) {
                            popupContent += `
                                <button onclick="enterEditMode(${properties.id}, '${properties.name}', '${properties.line_number || ''}', '${properties.type}', '${properties.type_name}')">Modifier</button><br>
                                <button onclick="deleteStructure(${properties.id}, '${properties.type}')">Supprimer</button>
                            `;
                        }
    
                        popupContent += `</div>`;
                        marker.bindPopup(popupContent);
                        return marker;
                    });
    
                    busStructuresLayer = L.layerGroup(busStructures).addTo(macarte);
                })
                .catch(error => console.error('Error fetching bus structures:', error));
        }
    };

    // Exemple pour afficher les structures électriques avec les données supplémentaires
    window.toggleWaterStructures = function () {
        if (waterStructuresLayer) {
            macarte.removeLayer(waterStructuresLayer);
            waterStructuresLayer = null;
        } else {
            fetch('/api/structures/type/water')
                .then(response => response.json())
                .then(data => {
                    if (!data.features || !Array.isArray(data.features)) {
                        throw new Error('Invalid GeoJSON data');
                    }
    
                    const waterStructures = data.features.map(feature => {
                        const coordinates = feature.geometry.coordinates;
                        const properties = feature.properties;
    
                        const marker = L.marker([coordinates[1], coordinates[0]], { icon: customIcon });
                        let popupContent = `
                            <div id="popup-content-${properties.id}">
                                <b>Nom :</b> ${properties.name}<br>
                                <b>Type :</b> ${properties.type_name || 'Inconnu'}<br>
                        `;
    
                        // Ajouter les champs spécifiques "Pression de l'eau" et "Ouvert"
                        if (properties.water_pressure) {
                            popupContent += `
                                <b>Pression de l'eau :</b> ${properties.water_pressure}<br>
                            `;
                        }
                        if (properties.is_open !== null && properties.is_open !== undefined) {
                            popupContent += `
                                <b>Ouvert :</b> ${properties.is_open ? 'Oui' : 'Non'}<br>
                            `;
                        }
    
                        // Vérifier les permissions de l'utilisateur
                        const userHasPermission = app && app.user && (
                            app.user.roles.includes('ROLE_ADMIN') || app.user.roles.includes('ROLE_WATER')
                        );
                        if (userHasPermission) {
                            popupContent += `
                                <button onclick="enterEditMode(${properties.id}, '${properties.name}', '${properties.water_pressure || ''}', '${properties.type}', '${properties.type_name}', ${properties.is_open})">Modifier</button><br>
                                <button onclick="deleteStructure(${properties.id}, '${properties.type}')">Supprimer</button>
                            `;
                        }
    
                        popupContent += `</div>`;
                        marker.bindPopup(popupContent);
                        return marker;
                    });
    
                    waterStructuresLayer = L.layerGroup(waterStructures).addTo(macarte);
                })
                .catch(error => console.error('Error fetching water structures:', error));
        }
    };

    
    window.toggleElectricalStructures = function () {
        if (electricalStructuresLayer) {
            macarte.removeLayer(electricalStructuresLayer);
            electricalStructuresLayer = null;
        } else {
            fetch('/api/structures/type/electrical')
                .then(response => response.json())
                .then(data => {
                    if (!data.features || !Array.isArray(data.features)) {
                        throw new Error('Invalid GeoJSON data');
                    }
    
                    const electricalStructures = data.features.map(feature => {
                        const coordinates = feature.geometry.coordinates;
                        const properties = feature.properties;
    
                        const marker = L.marker([coordinates[1], coordinates[0]], { icon: customIcon });
                        let popupContent = `
                            <div id="popup-content-${properties.id}">
                                <b>Nom :</b> ${properties.name}<br>
                                <b>Type :</b> ${properties.type_name || 'Inconnu'}<br>
                        `;
    
                        // Ajouter le champ spécifique "Capacité"
                        if (properties.capacity) {
                            popupContent += `
                                <b>Capacité :</b> ${properties.capacity}<br>
                            `;
                        }
    
                        // Vérifier les permissions de l'utilisateur
                        const userHasPermission = app && app.user && (
                            app.user.roles.includes('ROLE_ADMIN') || app.user.roles.includes('ROLE_EDF')
                        );
                        if (userHasPermission) {
                            popupContent += `
                                <button onclick="enterEditMode(${properties.id}, '${properties.name}', '${properties.capacity || ''}', '${properties.type}', '${properties.type_name}')">Modifier</button><br>
                                <button onclick="deleteStructure(${properties.id}, '${properties.type}')">Supprimer</button>
                            `;
                        }
    
                        popupContent += `</div>`;
                        marker.bindPopup(popupContent);
                        return marker;
                    });
    
                    electricalStructuresLayer = L.layerGroup(electricalStructures).addTo(macarte);
                })
                .catch(error => console.error('Error fetching electrical structures:', error));
        }
    };

    window.enterEditMode = function (id, name, additionalField, type, typeName, isOpen = null) {
        const popupContent = document.getElementById(`popup-content-${id}`);
    
        let editContent = `
            <div>
                <label for="structure-name-${id}">Nom :</label>
                <input id="structure-name-${id}" type="text" value="${name}"><br>
        `;
    
        // Ajouter les champs spécifiques en fonction du type
        if (typeName === 'Arrêt de bus') {
            editContent += `
                <label for="line-number-${id}">Numéro de ligne :</label>
                <input id="line-number-${id}" type="text" value="${additionalField || ''}"><br>
            `;
        } else if (typeName === 'Poste électrique') {
            editContent += `
                <label for="capacity-${id}">Capacité :</label>
                <input id="capacity-${id}" type="text" value="${additionalField || ''}"><br>
            `;
        } else if (typeName === "Château d'eau") {
            editContent += `
                <label for="water-pressure-${id}">Pression de l'eau :</label>
                <input id="water-pressure-${id}" type="text" value="${additionalField || ''}"><br>
                <label for="is-open-${id}">Ouvert :</label>
                <input id="is-open-${id}" type="checkbox" ${isOpen ? 'checked' : ''}><br>
            `;
        }
    
        editContent += `
                <button onclick="saveStructureChanges(${id}, '${type}')">Sauvegarder</button>
                <button onclick="cancelEditMode(${id}, '${name}', '${additionalField}', '${type}', '${typeName}', ${isOpen})">Annuler</button>
            </div>
        `;
    
        popupContent.innerHTML = editContent;
    };

    window.cancelEditMode = function (id, name, lineNumber, type) {
        const popupContent = document.getElementById(`popup-content-${id}`);
    
        let originalContent = `
            <div>
                <b>Nom :</b> ${name}<br>
                <b>Type :</b> ${type}<br>
        `;
    
        // Ajouter les champs supplémentaires en fonction du type
        if (type === 'bus_stop') {
            originalContent += `
                <b>Numéro de ligne :</b> ${lineNumber || 'Non défini'}<br>
            `;
        }
    
        originalContent += `
                <button onclick="enterEditMode(${id}, '${name}', '${lineNumber || ''}', '${type}')">Modifier</button><br>
                <button onclick="deleteStructure(${properties.id}, '${properties.type}')">Supprimer</button>
            </div>
        `;
    
        popupContent.innerHTML = originalContent;
    };

    window.modifyStructure = function (id) {
        const newName = prompt('Entrez le nouveau nom de la structure :');
        if (!newName) {
            alert('Modification annulée.');
            return;
        }

        fetch(`/api/structures/${id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ name: newName }),
        })
            .then(response => response.json())
            .then(data => {
                if (data.message) {
                    alert('Structure modifiée avec succès !');
                    toggleBusStructures(); // Rafraîchir les structures
                } else {
                    alert('Erreur lors de la modification : ' + (data.error || 'Inconnue'));
                }
            })
            .catch(error => {
                console.error('Erreur lors de la modification :', error);
                alert('Erreur lors de la modification. Veuillez réessayer.');
            });
    };

    window.deleteStructure = function (id, type) {
        if (!confirm('Êtes-vous sûr de vouloir supprimer cette structure ?')) {
            return;
        }
    
        fetch(`/api/structures/${id}`, {
            method: 'DELETE',
        })
            .then(response => response.json())
            .then(data => {
                if (data.message) {
                    alert('Structure supprimée avec succès !');
                    // Rafraîchir les structures en fonction du type
                    if (type === 'electrical') {
                        toggleElectricalStructures();
                    } else if (type === 'water') {
                        toggleWaterStructures();
                    } else if (type === 'bus_stop') {
                        toggleBusStructures();
                    }
                } else {
                    alert('Erreur lors de la suppression : ' + (data.error || 'Inconnue'));
                }
            })
            .catch(error => {
                console.error('Erreur lors de la suppression :', error);
                alert('Erreur lors de la suppression. Veuillez réessayer.');
            });
        };
    }

    // // Fonction pour afficher les chemins
    // window.togglePaths = function () {
    //     if (pathsLayer) {
    //         macarte.removeLayer(pathsLayer);
    //         pathsLayer = null;
    //     } else {
    //         fetch('/api/paths')
    //             .then(response => response.json())
    //             .then(data => {
    //                 if (!data.features || !Array.isArray(data.features)) {
    //                     throw new Error('Invalid GeoJSON data');
    //                 }

    //                 const paths = data.features.map(feature => {
    //                     const coordinates = feature.geometry.coordinates.map(coord => [coord[1], coord[0]]);
    //                     const polyline = L.polyline(coordinates, {
    //                         color: feature.properties.color || 'blue',
    //                         weight: 4
    //                     });

    //                     polyline.bindPopup(`<b>${feature.properties.name}</b>`);
    //                     return polyline;
    //                 });

    //                 pathsLayer = L.layerGroup(paths).addTo(macarte);
    //             })
    //             .catch(error => console.error('Error fetching paths:', error));
    //     }
    // };

    // Mettre à jour les champs supplémentaires en fonction du type sélectionné
    window.updateAdditionalFields = function () {
        const typeSelect = document.getElementById('structure-type');
        const selectedTypeName = typeSelect.options[typeSelect.selectedIndex].dataset.name; // Nom du type sélectionné
        const additionalFields = document.getElementById('additional-fields');

        let fieldsHtml = '';

        // Ajoutez des champs spécifiques en fonction du type sélectionné
        if (selectedTypeName === 'Arrêt de bus') {
            fieldsHtml = `
                <label for="line-number">Numéro de ligne :</label>
                <input id="line-number" type="text" placeholder="Ex: Ligne 42"><br>
            `;
        } else if (selectedTypeName === "Château d'eau") {
            fieldsHtml = `
                <label for="water-pressure">Pression de l'eau :</label>
                <input id="water-pressure" type="text" placeholder="Ex: 3 bars"><br>
                <label for="is-open">Ouvert :</label>
                <input id="is-open" type="checkbox"><br>
            `;
        } else if (selectedTypeName === 'Poste electrique') {
            fieldsHtml = `
                <label for="capacity">Capacité :</label>
                <input id="capacity" type="text" placeholder="Ex: 100 MW"><br>
            `;
        }

        additionalFields.innerHTML = fieldsHtml;
    };

    // Sauvegarder la structure
    window.saveStructure = function (lat, lng) {
        const name = document.getElementById('structure-name').value;
        const typeId = document.getElementById('structure-type').value;
        const additionalData = {};

        // Récupérer les champs supplémentaires
        if (document.getElementById('line-number')) {
            additionalData.line_number = document.getElementById('line-number').value;
        }
        if (document.getElementById('water-pressure')) {
            additionalData.water_pressure = document.getElementById('water-pressure').value;
        }
        if (document.getElementById('is-open')) {
            additionalData.is_open = document.getElementById('is-open').checked;
        }
        if (document.getElementById('capacity')) {
            additionalData.capacity = document.getElementById('capacity').value;
        }

        if (!name || !typeId) {
            alert('Veuillez remplir tous les champs.');
            return;
        }

        // Envoyer les données au backend
        fetch('/api/structures', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                lat: lat,
                lon: lng,
                name: name,
                typeId: typeId,
                additionalData: additionalData
            }),
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Structure sauvegardée avec succès !');
                    disableStructureMode();
                } else {
                    alert('Erreur lors de la sauvegarde : ' + (data.error || 'Inconnue'));
                }
            })
            .catch(error => {
                console.error('Erreur lors de la sauvegarde :', error);
                alert('Erreur lors de la sauvegarde. Veuillez réessayer.');
            });
        };
    

        window.saveStructureChanges = function (id, type) {
            const name = document.getElementById(`structure-name-${id}`).value;
            const additionalData = {};
        
            // Récupérer les champs supplémentaires en fonction du type
            if (type === 'bus_stop' && document.getElementById(`line-number-${id}`)) {
                additionalData.line_number = document.getElementById(`line-number-${id}`).value;
            } else if (type === 'electrical' && document.getElementById(`capacity-${id}`)) {
                additionalData.capacity = document.getElementById(`capacity-${id}`).value;
            } else if (type === 'water') {
                if (document.getElementById(`water-pressure-${id}`)) {
                    additionalData.water_pressure = document.getElementById(`water-pressure-${id}`).value;
                }
                if (document.getElementById(`is-open-${id}`)) {
                    additionalData.is_open = document.getElementById(`is-open-${id}`).checked;
                }
            }
        
            fetch(`/api/structures/${id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    name: name,
                    additionalData: additionalData,
                }),
            })
                .then(response => response.json())
                .then(data => {
                    if (data.message) {
                        alert('Structure modifiée avec succès !');
                        toggleBusStructures(); // Rafraîchir les structures
                    } else {
                        alert('Erreur lors de la modification : ' + (data.error || 'Inconnue'));
                    }
                })
                .catch(error => {
                    console.error('Erreur lors de la modification :', error);
                    alert('Erreur lors de la modification. Veuillez réessayer.');
                });
        };

// Appeler la fonction principale lorsque la carte est chargée
window.onload = function () {
    initMap(); // Assurez-vous que la carte est initialisée
    setTimeout(() => {
        initializeApp(); // Initialiser toutes les fonctionnalités après un léger délai
    }, 100); // Délai pour s'assurer que `macarte` est prêt
};