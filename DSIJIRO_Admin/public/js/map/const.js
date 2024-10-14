// Fonction pour charger les icônes et mettre à jour la légende
async function load_json_icon() {
    if (!json_icons) {
        try {
            const response = await fetch('/json/icon.json');
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            json_icons = await response.json();
        } catch (error) {
            console.error('Error loading icon.json:', error);
            return null; // Retourner null si une erreur survient
        }
    }
}

async function loadIconsAndLegend() {
    await load_json_icon();
    try {
        // Sélectionner le conteneur de légende
        const legendContainer = document.getElementById('map-legend');

        // Supprimer les éléments existants dans la légende (si nécessaire)
        while (legendContainer.firstChild) {
            legendContainer.removeChild(legendContainer.firstChild);
        }

        // Ajouter le titre de la légende
        const legendTitle = document.createElement('h5');
        legendTitle.textContent = 'Légende';
        legendContainer.appendChild(legendTitle);

        // Parcourir les icônes et ajouter des éléments de légende
        /*for (const key in json_icons) {
            if (json_icons.hasOwnProperty(key)) {
                const icon = json_icons[key];

                const legendItem = document.createElement('div');
                legendItem.className = 'legend-item';

                const legendBox = document.createElement('div');
                legendBox.className = 'legend-box';
                legendBox.style.backgroundImage = `url(${icon.iconUrl})`;
                legendBox.style.backgroundSize = 'contain';
                legendBox.style.width = (icon.iconSize[0] - 5) + 'px';
                legendBox.style.height = (icon.iconSize[1] - 5) + 'px';
                legendBox.style.opacity = 0.9;

                const legendText = document.createElement('span');
                legendText.textContent = icon.legend;

                legendItem.appendChild(legendBox);
                legendItem.appendChild(legendText);

                legendContainer.appendChild(legendItem);
            }
        }*/
    } catch (error) {
        console.error('Error loading icon.json:', error);
    }
}

async function get_icon(key) {
    await load_json_icon();
    if (json_icons && json_icons[key]) {
        // console.log(json_icons[key]);
        return L.icon({
            iconUrl: json_icons[key].iconUrl,
            iconSize: json_icons[key].iconSize
        });
    } else {
        console.error('Icon key not found in json_icons:', key);
        return null;
    }
}