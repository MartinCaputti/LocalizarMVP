<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ruta Optimizada</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
</head>
<body>
    <h1>Resultado: Ruta Optimizada</h1>
    <div id="map" style="height: 500px;"></div>

    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script>
        const map = L.map('map').setView([<?= $optimizedRoute[0]['lat'] ?>, <?= $optimizedRoute[0]['lng'] ?>], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

        // Dibuja la ruta optimizada
        const routeCoords = <?= json_encode($optimizedRoute) ?>;
        L.polyline(routeCoords.map(coord => [coord.lat, coord.lng]), {color: 'blue'}).addTo(map);

        // Añade marcadores
        routeCoords.forEach((coord, index) => {
            L.marker([coord.lat, coord.lng]).bindTooltip(`Punto ${index + 1}`, {permanent: true}).addTo(map);
        });
    </script>
</body>
</html>
