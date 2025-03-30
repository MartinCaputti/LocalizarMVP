<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Optimización de Rutas</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />
</head>
<body>
    <h1>Optimización de Rutas para Pallets</h1>
    <form method="POST">
        <input type="hidden" id="coordinates" name="coordinates">
        <div id="map" style="height: 400px;"></div>
        <button type="submit">Calcular Ruta</button>
    </form>

    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>
    <script>
        const map = L.map('map').setView([-34.60, -58.38], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

        const geocoder = L.Control.Geocoder.nominatim();
        const control = L.Control.geocoder({
            geocoder: geocoder,
            position: 'topright'
        }).addTo(map);

        const markers = [];
        map.on('click', (e) => {
            const marker = L.marker(e.latlng).addTo(map);
            markers.push(e.latlng);
            document.getElementById('coordinates').value = JSON.stringify(markers);
        });
    </script>
</body>
</html>
