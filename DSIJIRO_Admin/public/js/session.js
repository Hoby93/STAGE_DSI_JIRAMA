function saveFilterOnSession(gpniv, niv, gpdate, date) {
    sessionStorage.setItem('filterData', JSON.stringify({
        gpniv: gpniv,
        niv: niv,
        gpdate: gpdate,
        date: date
    }));
    
    console.log('Données filtrées ont été sauvegardées dans sessionStorage');
}


function getFilterFromSession() {
    const storedData = sessionStorage.getItem('filterData');
    
    if (storedData) {
        try {
            const filterData = JSON.parse(storedData);
            
            return {
                gpniv: filterData.gpniv,
                niv: filterData.niv,
                gpdate: filterData.gpdate,
                date: filterData.date
            };
        } catch (error) {
            console.error('Erreur lors de la lecture des données de filtre:', error);
            return null;
        }
    }
    
    console.log('Aucune donnée de filtre n\'a été trouvée dans sessionStorage');
    return null;
}

function setLastZoneAdded(last_zone_added) {
    if (sessionStorage.getItem('last_zone') === null) {
        sessionStorage.setItem('last_zone', JSON.stringify([JSON.parse(last_zone_added)]));
    } else {
        let zones = JSON.parse(sessionStorage.getItem('last_zone'));
        zones.push(JSON.parse(last_zone_added));
        sessionStorage.setItem('last_zone', JSON.stringify(zones));
    }
}

function getLastZoneAdded() {
    return  JSON.parse(sessionStorage.getItem('last_zone'));
}

function savePreferenceOnSession(paginated, nbelt, legend, filter) {
    sessionStorage.setItem('preferenceData', JSON.stringify({
        paginated: paginated,
        nbelt: nbelt,
        legend: legend,
        filter: filter
    }));
    
    console.log('Données de préférence ont été sauvegardées dans sessionStorage');
}

function getPreferenceFromSession() {
    const storedData = sessionStorage.getItem('preferenceData');
    
    if (storedData) {
        try {
            const filterData = JSON.parse(storedData);
            
            return {
                paginated: filterData.paginated,
                nbelt: filterData.nbelt,
                legend: filterData.legend,
                filter: filterData.filter
            };
        } catch (error) {
            console.error('Erreur lors de la lecture des données de preference:', error);
            return null;
        }
    }
    
    console.log('Aucune donnée de preference n\'a été trouvée dans sessionStorage');
    return null;
}