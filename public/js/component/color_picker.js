function showColorPicker(defaultColor = '#0000FF') {
    return new Promise((resolve, reject) => {
        const colorPrompt = document.createElement('div');
        colorPrompt.style.position = 'fixed';
        colorPrompt.style.top = '50%';
        colorPrompt.style.left = '50%';
        colorPrompt.style.transform = 'translate(-50%, -50%)';
        colorPrompt.style.padding = '20px';
        colorPrompt.style.backgroundColor = 'white';
        colorPrompt.style.border = '1px solid #ccc';
        colorPrompt.style.boxShadow = '0 4px 8px rgba(0, 0, 0, 0.2)';
        colorPrompt.style.zIndex = '1000';
        colorPrompt.style.textAlign = 'center';

        const colorPickerLabel = document.createElement('label');
        colorPickerLabel.textContent = 'Choisir la couleur :';
        colorPickerLabel.style.display = 'block';
        colorPickerLabel.style.marginBottom = '10px';

        const colorPicker = document.createElement('input');
        colorPicker.type = 'color';
        colorPicker.value = defaultColor;
        colorPicker.style.marginBottom = '10px';

        const confirmButton = document.createElement('button');
        confirmButton.textContent = 'Confirmer';
        confirmButton.style.marginRight = '10px';

        const cancelButton = document.createElement('button');
        cancelButton.textContent = 'Annuler';

        colorPrompt.appendChild(colorPickerLabel);
        colorPrompt.appendChild(colorPicker);
        colorPrompt.appendChild(confirmButton);
        colorPrompt.appendChild(cancelButton);
        document.body.appendChild(colorPrompt);

        confirmButton.addEventListener('click', () => {
            const selectedColor = colorPicker.value;
            document.body.removeChild(colorPrompt);
            resolve(selectedColor);
        });

        cancelButton.addEventListener('click', () => {
            document.body.removeChild(colorPrompt);
            reject('Color selection canceled');
        });
    });
}

// Attach to the global window object
window.showColorPicker = showColorPicker;