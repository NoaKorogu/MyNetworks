function initializeStructureFeatures() {
    if (!macarte) {
        console.error('Map is not initialized. Ensure initMap() is called before this script.');
        return;
    }

    let structureMode = false;
    let currentMarker = null;

    const structureButton = document.getElementById('placeStructureButton');

    // Activer le mode de placement des structures
    function enableStructureMode() {
        structureMode = true;
        alert('Cliquez sur la carte pour placer une structure.');
    }

    // Désactiver le mode de placement des structures
    function disableStructureMode() {
        structureMode = false;
        if (currentMarker) {
            macarte.removeLayer(currentMarker);
            currentMarker = null;
        }
    }

    // Gérer le clic sur la carte pour placer une structure
    macarte.on('click', function (e) {
        if (!structureMode) return;

        const { lat, lng } = e.latlng;

        // Supprimer l'ancien marqueur s'il existe
        if (currentMarker) {
            macarte.removeLayer(currentMarker);
        }

        // Ajouter un nouveau marqueur
        currentMarker = L.marker([lat, lng], { icon: customIcon }).addTo(macarte);

        // Récupérer les options de type depuis le backend
        fetch('/api/types')
            .then(response => response.json())
            .then(types => {
                const options = types.map(type => `<option value="${type.id}" data-name="${type.name}">${type.name}</option>`).join('');
                currentMarker.bindPopup(`
                    <div>
                        <label for="structure-name">Nom :</label>
                        <input id="structure-name" type="text" value="Nouvelle Structure"><br>
                        <label for="structure-type">Type :</label>
                        <select id="structure-type" onchange="updateAdditionalFields()">
                            ${options}
                        </select><br>
                        <div id="additional-fields"></div>
                        <button onclick="saveStructure(${lat}, ${lng})">Sauvegarder</button>
                        <button onclick="cancelStructure()">Annuler</button>
                    </div>
                `).openPopup();
                updateAdditionalFields();
            })
            .catch(error => {
                console.error('Erreur lors de la récupération des types :', error);
                alert('Impossible de charger les types. Veuillez réessayer.');
            });
    });

    // Mettre à jour les champs supplémentaires en fonction du type sélectionné
    window.updateAdditionalFields = function () {
        const typeSelect = document.getElementById('structure-type');
        const selectedTypeName = typeSelect.options[typeSelect.selectedIndex].dataset.name;
        const additionalFields = document.getElementById('additional-fields');

        let fieldsHtml = '';
        if (selectedTypeName === 'Arrêt de bus') {
            fieldsHtml = `
                <label for="line-number">Numéro de ligne :</label>
                <input id="line-number" type="text" placeholder="Ex: Ligne 42"><br>
            `;
        } else if (selectedTypeName === `Château d'eau`) {
            fieldsHtml = `
                <label for="water-pressure">Pression de l'eau :</label>
                <input id="water-pressure" type="text" placeholder="Ex: 3 bars"><br>
                <label for="is-open">Ouvert :</label>
                <input id="is-open" type="checkbox"><br>
            `;
        } else if (selectedTypeName === 'Poste électrique') {
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

    // Annuler la création de la structure
    window.cancelStructure = function () {
        disableStructureMode();
        alert('Création de la structure annulée.');
    };

    window.disableStructureMode = disableStructureMode;
    window.enableStructureMode = enableStructureMode;
    window.initializeStructureFeatures = initializeStructureFeatures;
}
