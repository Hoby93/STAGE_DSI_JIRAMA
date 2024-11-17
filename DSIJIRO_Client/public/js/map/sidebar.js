function setSideBarLoading(isLoading) {
    const sidebarInfo = document.getElementById('sidebar-info');
    if (isLoading) {
        sidebarInfo.classList.add('loading');
    } else {
        sidebarInfo.classList.remove('loading');
    }
}

function switch_sidebar_content(show, hidde) {
    document.getElementById(show).style.display = "block";
    document.getElementById(hidde).style.display = "none";
}

function set_sidebar_content_by_coupure(zone_coupee) {
    setSideBarLoading(true); // ajouter le spinner sur le side-bar
    switch_sidebar_content("info-coupure", "info-site"); // masquer l'info-site et afficher l'info-coupure
    setTimeout(() => {
        setSideBarLoading(false);
    }, 500);
    // Obtenir l'élément contenant les tags
    var tagCloudContainer = document.getElementById('secteur-fkt');

    // S'assurer que l'élément existe
    if (!tagCloudContainer) {
        console.error('L\'élément avec l\'id "secteur-fkt" est introuvable.');
        return;
    }

    // Nettoyer le conteneur avant d'ajouter de nouveaux éléments
    tagCloudContainer.innerHTML = '';

    // Diviser la chaîne de fokontany par le point-virgule
    var lieux = zone_coupee.zone.lieux.split('-');

    // Parcourir chaque fokontany
    lieux.forEach(function(lieu) {
        // Créer un lien pour chaque fokontany
        var tagLink = document.createElement('a');
        tagLink.href = "#";
        tagLink.className = "tag-cloud-link";
        tagLink.textContent = lieu.trim(); // Utiliser textContent et enlever les espaces inutiles

        // Ajouter le lien au conteneur
        tagCloudContainer.appendChild(tagLink);
    });

    // Obtenir l'élément contenant les événements
    var eventsContainer = document.getElementById('events');

    // S'assurer que l'élément existe
    if (!eventsContainer) {
        console.error('L\'élément avec l\'id "events" est introuvable.');
        return;
    }

    // Nettoyer le conteneur avant d'ajouter de nouveaux éléments
    eventsContainer.innerHTML = '';

    // console.log(zone_coupee.zone.specificite);

    // Diviser la chaîne en paires clé-valeur
    var specs = zone_coupee.zone.specificite.split(';');

    specs.forEach(function(spec) {
        // Extraire la clé et la valeur de chaque spece
        var parts = spec.split(':');
        var key = parts[0];
        var value = parseInt(parts[1], 10); // Convertir la valeur en nombre

        // Trouver l'élément HTML correspondant à la clé
        var elementId = 'count-' + key;
        var element = document.getElementById(elementId);

        // Vérifier si l'élément existe
        if (element) {
            // Mettre à jour la valeur de l'élément HTML
            element.dataset.number = value.toString();
            element.textContent = value;
        }
    });

    // Diviser la chaîne en valeurs
    var postes = zone_coupee.zone.postes.split('-');

    // Ajouter les badges à l'élément div
    // var postesCoupee = document.getElementById('postes-coupee');
    // postesCoupee.innerHTML = "";
    // postes.forEach(function(post) {
    //     // Créer un nœud DOM à partir de la chaîne HTML
    //     var badgeNode = document.createElement('span');
    //     badgeNode.className = 'badge btn btn-outline-primary mr-1';
    //     badgeNode.textContent = post;

    //     // Ajouter le nœud DOM à l'élément parent
    //     postesCoupee.appendChild(badgeNode);
    // });

    function createEvent(coupure) {
        // Convertir les timestamps Unix en objets Date
        var dateDebut = new Date(coupure.dateDebut.timestamp * 1000); // Convertir les secondes en millisecondes
        var dateFin = new Date(coupure.dateFin.timestamp * 1000); // Convertir les secondes en millisecondes

        // console.log(dateDebut + '-' + dateFin);

        var day = dateDebut.getUTCDate();
        var month = dateDebut.toLocaleString('default', { month: 'short' }).toUpperCase();
        var durationInHours = (dateFin - dateDebut) / (1000 * 60 * 60); // Durée en heures

        // Convertir la durée en heures et minutes
        var hours = Math.floor(durationInHours);
        var minutes = Math.round((durationInHours - hours) * 60);
        
        let ctn_event = `
            <div id="event">
                <div class="row row-striped">
                    <div class="col-3 text-center">
                        <h5 class=""><span class="badge bg-gray" style="font-size: unset;" id="date-number-coupure">${day}</span></h5>
                        <dt id="date-month-coupure">${month.slice(0, -1)}</dt>
                    </div>
                    <div class="col-9">
                        <h6 class="text-uppercase text-muted">
                            <strong style="font-size: 0.8em">Coupure de courant</strong>
                            <a class="text-white ml-1" data-toggle="collapse" href="#multiCollapseExample${coupure.id}" role="button" aria-expanded="false" aria-controls="multiCollapseExample2"><i class="fa fa-question-circle"></i></a>
                        </h6>
                        <ul class="list-inline" style="font-size: 12px;">
                            <li class="list-inline-item"><i class="fa fa-time" aria-hidden="true"></i><span id="horaire-coupure"> ${dateDebut.getUTCHours()}h${dateDebut.getUTCMinutes().toString().padStart(2, '00')} - ${dateFin.getUTCHours()}h${dateFin.getUTCMinutes().toString().padStart(2, '0')}</span></li>
                            <li class="list-inline-item" id="duree-coupure"> (${hours}h${minutes})</li>
                        </ul>
                    </div>
                </div>
                <div class="collapse multi-collapse mt-2" id="multiCollapseExample${coupure.id}">
                    <div class="divider"></div>
                    <p class="motif-coupure" id="motif-coupure">
                        ${coupure.motif}
                    </p>
                </div>
            </div>
            <div class="divider-lg"></div>
        `;
        return ctn_event;
    }

    var ctn_events = "";
    zone_coupee.coupures.forEach(function(item) {
        // Créer un nœud DOM à partir de la chaîne HTML
        var event = createEvent(item.coupure);
        // console.log(item.coupure);

        // Ajouter le nœud DOM à l'élément parent
        ctn_events += event;
    });
    eventsContainer.innerHTML = ctn_events;
}


function set_sidebar_content_by_site(site) {
    setSideBarLoading(true); // ajouter le spinner sur le side-bar
    switch_sidebar_content("info-site", "info-coupure"); // masquer l'info-coupure et afficher l'info-site
    setTimeout(() => {
        setSideBarLoading(false);
    }, 500);

    function createHours(site) {
        const days = ['lun', 'mar', 'mer', 'jeu', 'ven', 'sam'];
        const hoursRegex = /(\w+):(\d{2}):(\d{2})-(\d{2}):(\d{2})/;

        let html = '';
        site.horaire.split(';').forEach(dayTime => {
            const match = dayTime.match(hoursRegex);
            if (match) {
                const [, day, startHour, startMin, endHour, endMin] = match;
                html += `
                    <div class="col-md-12 card-desc d-flex justify-content-between">
                        <span class="text-left">${days[days.indexOf(day)]}.</span>
                        <span class="text-right" id="sidebar-info-adress">${startHour}:${startMin} - ${endHour}:${endMin}</span>
                    </div>
                `;
            }
        });
        
        return html;
    }
    
    document.getElementById("site-libelle").innerHTML = site.libelle;
    document.getElementById("site-descr").innerHTML = site.descr;
    document.getElementById("site-adresse").innerHTML = site.adresse;
    document.getElementById("site-type").innerHTML = getTypeSiteTitle(site.typeInfra);
    document.getElementById("site-distance").innerHTML = site.distance + " km";
    document.getElementById("site-contact").innerHTML = site.contact;

    document.getElementById("site-horaire").innerHTML = createHours(site);
}

function show_hidde_sidebar(opacity) {
    // sidebar.classList.add('hidden2');
    var info = document.getElementById("sidebar-info");
    //info.style.transform = "translateX(80%)";
    // info.style.opacity = opacity;

    if(opacity == 0) {
        info.style.transform = "translateX(110%)";
        map.eachLayer(function(layer) {
            if (layer instanceof L.Marker) {
                layer._icon.style.animation = ''; // Retirez l'animation
            }
        });
    } else {
        info.style.transform = "translateX(0%)";
    }
}