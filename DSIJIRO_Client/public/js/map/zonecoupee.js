function flatpickrInstanceToDate(flatpickrInstance) {
    let selectedDate = flatpickrInstance.selectedDates[0] || new Date();

    return new Date(selectedDate);
}

function addPolygonOnMap(zone_coupee) {

    // Combiner les valeurs pour créer des objets Date
    const startTime = flatpickrInstanceToDate(flatpickrInstance1);
    const endTime = flatpickrInstanceToDate(flatpickrInstance2);

    // Ajuster manuellement pour GMT+3
    startTime.setHours(startTime.getHours() + 3);
    endTime.setHours(endTime.getHours() + 3);

    // console.log(startTime + '-' + endTime);

    // Déterminer le style du polygone basé sur les coupures
    const polygon_style = [
        {'color': 'black', 'icon': 'images/spinner/light-off2.gif'},
        {'color': 'black', 'icon': 'images/spinner/light-off2.gif'},
        {'color': 'red', 'icon': 'images/icons/light-off4.png'}
    ];
    
    function getStyleBasedOnCoupures(startTime, endTime) {
        const coupureStart = new Date(zone_coupee.coupures[0].coupure.dateDebut.timestamp * 1000);
        const coupureEnd = new Date(zone_coupee.coupures[0].coupure.dateFin.timestamp * 1000);

        // console.log('[' + startTime.toISOString() + ' - ' + endTime.toISOString() + '] ? ' + '[' + coupureStart.toISOString() + ' - ' + coupureEnd.toISOString() + ']');

        // intervalle compris entierement entre la coupure
        if (startTime >= coupureStart && endTime <= coupureEnd) {
            return 2;
        }

        const cas1 = (startTime < coupureStart && endTime < coupureStart);
        const cas2 = (startTime > coupureEnd && endTime > coupureEnd);
        const cas = cas1 || cas2;

        // une partie de l'intervalle compris entre la coupure
        if (!cas) {
            return 1;
        }

        return 0;
    }


    // Choisir le style en fonction des coupures
    const styleId = getStyleBasedOnCoupures(startTime, endTime);

    // Extraire les coordonnées de la zone
    const coords = zone_coupee.zone.coord.map(coord => [coord.longitude, coord.latitude]);
    
    // Créer un polygone avec les coordonnées extraites
    const polygon = L.polygon(coords, {
        fillOpacity: 0.2,
        smoothFactor: 1,
        weight: 2,
        dashArray: "7, 7",
        stroke: true,
        color: polygon_style[styleId].color,
    }).addTo(map); // Ajouter le polygone à la carte

    // Obtenir le centre du polygone
    const center = polygon.getBounds().getCenter();

    // Variable pour stocker le marqueur GIF
    let gifMarker = null;

    // Ajouter un écouteur d'événement pour le clic sur le polygone
    polygon.on('click', function() {
        map.setView(center, 14);
        show_hidde_sidebar(1); // Appeler la fonction show_hide_sidebar avec l'argument 1
        set_sidebar_content_by_coupure(zone_coupee);
    });

    // Ajouter des écouteurs d'événements pour changer le style sur hover
    polygon.on('mouseover', function() {
        this.setStyle({
            fillOpacity: 0.4,
            dashArray: "7, 7",
        });

        // Créer une icône personnalisée
        const lightOnIcon = L.icon({
            iconUrl: polygon_style[styleId].icon,
            iconSize: [25, 25], // Taille de l'icône
            iconAnchor: [12.5, 12.5] // Point de l'icône correspondant à sa position
        });

        // Ajouter un marqueur avec l'icône personnalisée au centre du polygone si il n'existe pas déjà
        if (!gifMarker) {
            // gifMarker = L.marker(center, { icon: lightOnIcon }).addTo(map);
        }
    });

    // Créer une icône personnalisée
    const lightIcon = L.icon({
        iconUrl: polygon_style[styleId].icon,
        iconSize: [25, 25], // Taille de l'icône
        iconAnchor: [12.5, 12.5] // Point de l'icône correspondant à sa position
    });

    gifMarker = L.marker(center, { icon: lightIcon }).addTo(map);
    gifMarker.isPolygonCenter = true;

    // Associer le marqueur au polygone
    // polygon.associatedMarker = gifMarker;

    polygon.on('mouseout', function() {
        this.setStyle({
            fillOpacity: 0.2,
            dashArray: "7, 7",
        });

        // Supprimer le marqueur GIF du centre du polygone
        //if (gifMarker) {
            //map.removeLayer(gifMarker);
            //gifMarker = null; // Réinitialiser le marqueur
        //}
    });

    // add_result_tag_content(zone_coupee.zone.lieux.split('-'), center);
}

// Regrouper les memes zones
function adjustZoneCoupee() {
    let groupedZones = [];

    // Un objet temporaire pour suivre les zones par idZone
    let tempZones = {};

    // Parcours chaque élément dans zones_coupee_loaded
    zones_coupee_loaded.forEach(item => {
        let idZone = item.zone.id;
        
        // Si la zone n'existe pas encore dans tempZones, initialise-la
        if (!tempZones[idZone]) {
            tempZones[idZone] = {
                zone: item.zone,
                coupures: [] // Crée un tableau de coupures
            };
        }
        
        // Ajoute la coupure dans le tableau des coupures de la zone correspondante
        tempZones[idZone].coupures.push({ coupure: item.coupure });
    });

    // Convertit tempZones en un tableau regroupé
    groupedZones = Object.values(tempZones);

    zones_coupee = groupedZones;
}

function removeZoneCoupeeOnMap() {
    // Supprimer tous les polygones de la carte
    map.eachLayer(function(layer) {
        if (layer instanceof L.Polygon) {
            map.removeLayer(layer);
        } else if (layer.isPolygonCenter) {
            map.removeLayer(layer);
        }
    });
}

async function loadZoneCoupeeOnMap() {
    document.getElementById('loading-on-map').style.display = "block";

    // Supprimer tout les zones sur la carte
    removeZoneCoupeeOnMap();

    // const userCoords = await getUserPosition();

    // Parcourir chaque shop dans le tableau shop_loaded
    zones_coupee.forEach(zone_coupee => {
        // console.log(zone_coupee.secteur);
        addPolygonOnMap(zone_coupee); // Ajouter au map
    });

    document.getElementById('loading-on-map').style.display = "none";
}

function loadZoneCoupeeOnView(api) {
    var boundsview = getBoundsView();
    if(boundsview) {
        const startTime = flatpickrInstance1.formatDate(flatpickrInstanceToDate(flatpickrInstance1), 'Y-m-d H:i:ss');
        const endTime = flatpickrInstance2.formatDate(flatpickrInstanceToDate(flatpickrInstance2), 'Y-m-d H:i:ss');

        //console.log(`Debut: ${startTime} - Fin: ${endTime}`);
        //console.log(JSON.stringify({ bounds: rectangle.getLatLngs() }));

        // Mettre à jour les limites de la carte avec les nouvelles limites
        // map.fitBounds(newBounds, { padding: [50, 50] }); // Le padding peut être ajusté selon vos besoins
        // console.log(boundsview);
        var data = {
            coord: boundsview,
            // date: document.getElementById('date-search-info').value // devrait etre de la forme -> yyyy-mm-dd
            date: "2024-08-16",
            debut: startTime,
            fin: endTime
        };

        $.ajax({
            url: api,
            method: 'POST',
            contentType: 'application/json',  // Ajout du type de contenu JSON
            data: JSON.stringify(data),  // Conversion en JSON
            beforeSend: function() {
                document.getElementById('loading-on-map').style.display = "block";
            },
            success: function(response) {
                //console.log(response);
                // Supprimer tous les sites existants sur la carte
                //zones_coupee_loaded.forEach(shop => map.removeLayer(shop));

                // Convertissez la chaîne JSON en objet JavaScript
                let data = JSON.parse(response);

                zones_coupee_loaded = data.zonesCoupee;

                adjustZoneCoupee();
                // console.log(zones_coupee_loaded);

                loadZoneCoupeeOnMap();
            },
            error: function() {
                // Masquer le spinner en cas d'erreur de la requête AJAX
                document.getElementById('loading-on-map').style.display = "none";
            }
        });
        //document.getElementById('loading-on-map').style.display = "none";
    }
}