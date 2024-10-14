// Fonction pour calculer la distance entre deux points
function calculateDistance(lat1, lon1, lat2, lon2) {
    const R = 6371; // Rayon de la Terre en kilomètres
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLon = (lon2 - lon1) * Math.PI / 180;
    const a =
        Math.sin(dLat / 2) * Math.sin(dLat / 2) +
        Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
        Math.sin(dLon / 2) * Math.sin(dLon / 2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    const distance = R * c;
    return distance;
}

async function getLocationCoordinates(location) {
    const encodedLocation = encodeURIComponent(location); // Encoder le texte pour l'URL
    const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodedLocation}&bounded=1&addressdetails=1&extratags=1`;

    try {
        const response = await fetch(url);
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        const data = await response.json();
        if (data.length > 0) {
            const result = data[0];
            const lat = result.lat;
            const lon = result.lon;
            const address = result.display_name;
            const adress_nv = address.split(", ");

            console.log(`Location: ${location}`);
            console.log(`Lat: ${lat}, Lng: ${lon}`);
            console.log(`Address: ${address}`);
            console.log(`Niveau: ${adress_nv.length}`);

            return {
                lat: lat,
                lng: lon,
                zoom: 8 + adress_nv.length
            };
        } else {
            console.log('No results found for the given location.');
            return null;
        }
    } catch (error) {
        console.error('Error:', error);
        return null;
    }
}

async function searchLocation() {
    var location = document.getElementById('location').value;
    document.getElementById('search-map-icon').classList.add('d-none');
    document.getElementById('search-map-spinner').classList.remove('d-none');
    var coord = await getLocationCoordinates(location);
    if (coord) {
        console.log(coord);
        // Utilisez les coordonnées et le niveau de zoom pour centrer la carte ici
        map.flyTo([coord.lat, coord.lng], coord.zoom, {
            duration: 1.0 // Durée de l'animation en secondes
        });
    }

    document.getElementById('search-map-icon').classList.remove('d-none');
    document.getElementById('search-map-spinner').classList.add('d-none');
}