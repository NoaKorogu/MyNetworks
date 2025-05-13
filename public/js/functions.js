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
                            <div>
                                <b>Nom :</b> ${properties.name}<br>
                                <b>Type :</b> ${properties.type_name || 'Inconnu'}<br>
                        `;
    
                        // Ajouter les champs supplémentaires en fonction du type
                        if (properties.type === 'bus_stop') {
                            popupContent += `
                                <b>Numéro de ligne :</b> ${properties.line_number || 'Non défini'}<br>
                            `;
                        }
    
                        // Vérifier les permissions de l'utilisateur
                        const userHasPermission = app && app.user && (app.user.roles.includes('ROLE_ADMIN') || app.user.roles.includes('ROLE_FILIBUS'));
                        if (userHasPermission) {
                            popupContent += `
                                <button onclick="modifyStructure(${properties.id})">Modifier</button><br>
                                <button onclick="deleteStructure(${properties.id})">Supprimer</button>
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

    window.deleteStructure = function (id) {
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
                    toggleBusStructures(); // Rafraîchir les structures
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

    // Exemple pour afficher les structures électriques avec les données supplémentaires
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
                        let popupContent = `<b>${properties.name}</b><br>Type: ${properties.type_name || 'Inconnu'}`;
    
                        // Ajouter la capacité si elle est disponible
                        if (properties.capacity) {
                            popupContent += `<br>Capacité: ${properties.capacity}`;
                        }
    
                        marker.bindPopup(popupContent);
                        return marker;
                    });
    
                    electricalStructuresLayer = L.layerGroup(electricalStructures).addTo(macarte);
                })
                .catch(error => console.error('Error fetching electrical structures:', error));
        }
    };

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
                        let popupContent = `<b>${properties.name}</b><br>Type: ${properties.type_name || 'Inconnu'}`;
    
                        // Ajouter les propriétés spécifiques si elles sont disponibles
                        if (properties.water_pressure) {
                            popupContent += `<br>Pression de l'eau: ${properties.water_pressure}`;
                        }
                        if (properties.is_open !== null && properties.is_open !== undefined) {
                            popupContent += `<br>Ouvert: ${properties.is_open ? 'Oui' : 'Non'}`;
                        }
    
                        marker.bindPopup(popupContent);
                        return marker;
                    });
    
                    waterStructuresLayer = L.layerGroup(waterStructures).addTo(macarte);
                })
                .catch(error => console.error('Error fetching water structures:', error));
        }
    };

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
    

    window.saveStructureChanges = function (id) {
        const name = document.getElementById(`structure-name-${id}`).value;
        const lineNumber = document.getElementById(`line-number-${id}`)?.value;
    
        const additionalData = {};
        if (lineNumber !== undefined) {
            additionalData.line_number = lineNumber;
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