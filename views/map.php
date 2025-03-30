<?Php
    // view/map.php
    // Muestra el mapa con la ruta optimizada
    // y los puntos seleccionados por el usuario
 ?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ruta Optimizada</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <h1>Resultado: Ruta Optimizada</h1>
    <div id="map" style="height: 500px;"></div>

    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <button id="clearMap" style="margin-top: 10px; padding: 8px 15px; background: #ff4444; color: white; border: none; cursor: pointer;">
        Limpiar y Volver a Empezar
    </button>
    <script>
        // Inicializar mapa una sola vez
        const map = L.map('map').setView([<?= $optimizedRoute[0]['lat'] ?? -34.60 ?>, <?= $optimizedRoute[0]['lng'] ?? -58.38 ?>], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

        // Dibujar ruta optimizada
        const routeCoords = <?= json_encode($optimizedRoute) ?>;
        if (routeCoords.length > 0) {
            L.polyline(
                routeCoords.map(coord => [coord.lat, coord.lng]),
                {color: 'red', dashArray: '5, 5'}
            ).addTo(map);
        }

        // Añadir marcadores
        routeCoords.forEach((coord, index) => {
            L.marker([coord.lat, coord.lng])
                .bindPopup(`Punto ${index + 1}<br>Lat: ${coord.lat}<br>Lng: ${coord.lng}`)
                .addTo(map);
        });

        // Manejar el botón de limpiar
        document.getElementById('clearMap').addEventListener('click', () => {
            // Recargar la página para borrar todo
            window.location.href = 'index.php';
        });
    </script>
    <div style="margin: 15px 0; padding: 10px; background: #f0f0f0;">
        <strong>Distancia total:</strong><br>
        - Original: <?= round($model->totalDistanceOriginal / 1000, 2) ?> km<br>
        - Optimizada: <?= round($model->totalDistanceOptimized / 1000, 2) ?> km<br>
        - Ahorro: <?= round(($model->totalDistanceOriginal - $model->totalDistanceOptimized) / 1000, 2) ?> km
    </div>
    <div style="margin: 10px 0; color: #2e7d32;">
        <strong>Huella de carbono reducida:</strong> 
        ~<?= round(($model->totalDistanceOriginal - $model->totalDistanceOptimized) / 1000 * 0.2, 2) ?> kg CO₂
    </div>
    <div style="margin: 20px 0; padding: 15px; background: #f5f5f5;">
    <h3>Comparación de Emisiones por Transporte</h3>
    <table border="1" style="width: 100%; border-collapse: collapse;">
            <tr>
                <th>Vehículo</th>
                <th>CO₂ por km</th>
                <th>Combustible por km</th>
                <th>Total CO₂ (ruta)</th>
                <th>Total Combustible (ruta)</th>
            </tr>
            <?php foreach ($model->getVehicleProfiles() as $key => $vehicle): ?>
            <tr>
                <td><?= $vehicle['name'] ?></td>
                <td><?= $vehicle['co2_per_km'] ?> kg</td>
                <td><?= $vehicle['fuel_per_km'] ?> L</td>
                <td>
                    <?= round(($model->totalDistanceOptimized / 1000) * $vehicle['co2_per_km'], 2) ?> kg
                </td>
                <td>
                    <?= round(($model->totalDistanceOptimized / 1000) * $vehicle['fuel_per_km'], 2) ?> L
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>

</body>
</html>