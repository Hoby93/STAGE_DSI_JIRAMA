function doesAddSite(site) {
    if(site.typeInfra === 1 && add_shop) {
        return true;
    }
    if(site.typeInfra === 2 && add_agence) {
        return true;
    }
    return false;
}

function addSiteOnMap(site, icon) {
    if(!doesAddSite(site)) {
        return;
    }
    // Ajouter le shop sur la carte
    var marker = L.marker([site.latitude, site.longitude], { icon: icon}).addTo(map);

    marker.on('click', function(e) {
        map.eachLayer(function(layer) {
            if (layer instanceof L.Marker) {
                layer._icon.style.animation = ''; // Retirez l'animation
            }
        });

        map.setView(e.latlng, 16); // Centre la carte sur le marqueur
        e.target._icon.style.animation = 'jumpMarker 0.5s infinite alternate'; // Appliquez l'animation au marqueur cliqué

        show_hidde_sidebar(1); // Appeler la fonction show_hide_sidebar avec l'argument 1
        set_sidebar_content_by_site(site);
    });

    marker.on('mouseover', function(e) {
        e.target._icon.style.opacity = '0.6';
    });

    marker.on('mouseout', function(e) {
        if (e.target._icon.style.animation !== 'jumpMarker 0.5s infinite alternate') {
            e.target._icon.style.opacity = '1'; // Retire l'animation de survol si le marqueur n'est pas cliqué
        }
    });

    // site_loaded.push(marker);
}

function removeSiteOnMap() {
    // Supprimer tous les marqueurs
    map.eachLayer(function(layer) {
        if (layer instanceof L.Marker) {
            if(!layer.isPolygonCenter && layer !== window.userPulse) {
                map.removeLayer(layer);
            }
        }
    });
}

async function loadSitesOnMap() {
    document.getElementById('loading-on-map').style.display = "block";

    // Supprimer tous les marqueurs
    // removeSiteOnMap();

    for (const site of site_loaded) {
        var icon = await get_icon(`${site.typeInfra}`);
        var user_coord = await getUserPosition();

        site.distance = calculateDistance(site.latitude, site.longitude, user_coord.latitude, user_coord.longitude);
        addSiteOnMap(site, icon); // Ajouter au map
    }

    document.getElementById('loading-on-map').style.display = "none";
}

function loadSiteOnView(api) {
    var boundsview = getBoundsView();
    if(boundsview) {
        //console.log(JSON.stringify({ bounds: rectangle.getLatLngs() }));

        // Mettre à jour les limites de la carte avec les nouvelles limites
        // map.fitBounds(newBounds, { padding: [50, 50] }); // Le padding peut être ajusté selon vos besoins
        // console.log(boundsview);

        $.ajax({
            url: api,
            method: 'POST',
            contentType: 'application/json',  // Ajout du type de contenu JSON
            data: JSON.stringify({coord: boundsview}),  // Conversion en JSON
            success: function(response) {
                //console.log(response);
                // Supprimer tous les sites existants sur la carte
                //site_loaded.forEach(shop => map.removeLayer(shop));
                site_loaded = [];

                // Convertissez la chaîne JSON en objet JavaScript
                let data = JSON.parse(response);

                site_loaded = data.sites;
                // console.log(site_loaded);

                loadSitesOnMap();
            },
            error: function() {
                // Masquer le spinner en cas d'erreur de la requête AJAX
                document.getElementById('loading-on-map').style.display = "none";
            }
        });
        //document.getElementById('loading-on-map').style.display = "none";
    }
}


async function fetchSiteNearestOfUser(api, user_coord) {
    $.ajax({
        url: api,
        method: 'POST',
        contentType: 'application/json',  // Ajout du type de contenu JSON
        data: JSON.stringify(user_coord),  // Conversion en JSON
        success: function(response) {
            let data = JSON.parse(response);

            let views = data.views;
            site_nearest = views;
            // console.log(views);
        },
        error: function() {
            console.error('Erreur lors du chargement du site_nearest sur le serveur');
        }
    });
}

function setViewToNearestSite(type) {
    var nsite = site_nearest[type];
    map.flyTo([nsite.latitude, nsite.longitude], 16, {
        duration: 1.0 // Durée de l'animation en secondes
    });
}