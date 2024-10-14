function getBoundsView() {
    var bounds = map.getBounds();
    var visibleBounds = {
        north: bounds.getNorth(),
        south: bounds.getSouth(),
        east: bounds.getEast(),
        west: bounds.getWest()
    };

    // Appeler la fonction pour mettre à jour les limites stockées
    var movedoutside = updateStoredBounds(visibleBounds.north, visibleBounds.south, visibleBounds.east, visibleBounds.west);

    // Vérifier si storedBounds est vide ou si la vue de la carte va à l'extérieur du rectangle tracé
    if (movedoutside || true) {
        var north = visibleBounds.north;
        var south = visibleBounds.south;
        var east = visibleBounds.east;
        var west = visibleBounds.west;

        var newBounds = [
            [south, west], // Coin sud-ouest
            [north, east]  // Coin nord-est
        ];

        // Tracer un rectangle avec les nouvelles limites pour visualiser l'aire englobante
        var rectangleOptions = {
            color: 'blue', // Couleur du contour
            weight: 2, // Épaisseur du contour
            fillColor: 'rgba(0, 0, 255, 0.05)', // Couleur de remplissage transparente pour visualisation
            fillOpacity: 0.2 // Opacité du remplissage
        };

        //console.log("MySQL Query to get zones within the rectangle:", coord_str_wkt);
        // console.log("** We load data **");

        // Supprimer l'ancien rectangle s'il existe
        if (window.rectangle) {
            map.removeLayer(window.rectangle);
        }

        // Ajouter le nouveau rectangle à la carte
    // window.rectangle = L.rectangle(newBounds, rectangleOptions).addTo(map);

        // return window.rectangle.getLatLngs();
        return true;
    }

    return null;
}

// Fonction pour comparer et mettre à jour les limites stockées
function updateStoredBounds(north, south, east, west) {
    // Si storedBounds est null, cela signifie que les limites n'ont jamais été définies
    if (!stored_bounds || (north > stored_bounds.north) || (south < stored_bounds.south) || (east > stored_bounds.east) || (west < stored_bounds.west)) {
        // Mettre à jour les limites stockées
        stored_bounds = { north, south, east, west };
        return true;
    }
    return false;
}