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

      
            // Dibujar la ruta completa
            const fullRoute = <?= json_encode($fullRoute) ?>;
            if (fullRoute.length > 0) {
                L.polyline(
                    fullRoute.map(coord => [coord.lat, coord.lng]),
                    {color: '#0066ff', weight: 4, opacity: 0.8}
                ).addTo(map);
            }

            // Mostrar solo los puntos de destino
            const waypoints = <?= json_encode($waypoints) ?>;
            waypoints.forEach((point, index) => {
                L.marker([point.lat, point.lng], {
                    icon: L.divIcon({
                        className: 'waypoint-marker',
                        html: `<div class="marker-label">${index + 1}</div>`,
                        iconSize: [30, 30]
                    })
                })
                .bindPopup(`Punto ${index + 1}<br>Lat: ${point.lat.toFixed(4)}<br>Lng: ${point.lng.toFixed(4)}`)
                .addTo(map);
            });

            // Ajustar el zoom para mostrar toda la ruta
            if (fullRoute.length > 0) {
                const routeLine = L.polyline(fullRoute.map(coord => [coord.lat, coord.lng]));
                map.fitBounds(routeLine.getBounds());
            }


        // Manejar el botón de limpiar
        document.getElementById('clearMap').addEventListener('click', () => {
            // Recargar la página para borrar todo
            window.location.href = 'index.php';
        });
    </script>
    <div class="stats-container">
        <h3>Estadísticas de Optimización</h3>
        
        <div class="stat-row">
            <span class="stat-label">Distancia original:</span>
            <span class="stat-value"><?= round($model->totalDistanceOriginal / 1000, 2) ?> km</span>
        </div>
        
        <div class="stat-row">
            <span class="stat-label">Distancia optimizada:</span>
            <span class="stat-value"><?= round($model->totalDistanceOptimized / 1000, 2) ?> km</span>
        </div>
        
        <div class="stat-row highlight">
            <span class="stat-label">Reducción de distancia:</span>
            <span class="stat-value">
                <?= round(($model->totalDistanceOriginal - $model->totalDistanceOptimized) / 1000, 2) ?> km
                (<?= $model->getPorcentajeMejora() ?>%)
            </span>
        </div>
        
        <div class="stat-row">
            <span class="stat-label">Huella de carbono reducida:</span>
            <span class="stat-value">
                ~<?= round(($model->totalDistanceOriginal - $model->totalDistanceOptimized) / 1000 * 0.2, 2) ?> kg CO₂
            </span>
        </div>
    </div>

    <div class="time-estimate">
        <h3>Tiempo Estimado de Viaje</h3>
        
        <?php 
        $tiempoViaje = $model->getTiempoViaje($_POST['vehicle'] ?? 'car');
        $vehiculo = $model->getVehicleProfiles()[$_POST['vehicle'] ?? 'car'];
        ?>
        
        <div class="vehicle-time">
            <strong><?= $vehiculo->getNombre() ?>:</strong>
            <?= $tiempoViaje['formateado'] ?>
            <small>(Velocidad promedio: <?= $vehiculo->getVelocidadPromedio() ?> km/h)</small>
        </div>
        
        <!-- Comparativa con otros transportes -->
        <div class="other-vehicles">
            <h4>Comparativa con otros transportes:</h4>
            <ul>
                <?php foreach ($model->getVehicleProfiles() as $tipo => $v): ?>
                    <?php if ($tipo != ($_POST['vehicle'] ?? 'car')): ?>
                        <?php $tiempo = $v->calcularTiempoViaje($model->totalDistanceOptimized / 1000); ?>
                        <li>
                            <?= $v->getNombre() ?>: <?= $tiempo['formateado'] ?>
                            <small>(<?= $v->getVelocidadPromedio() ?> km/h)</small>
                        </li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>

    <!-- Sección de condiciones meteorológicas -->
    <?php if ($incluirClima && !empty($weatherData)): ?>
        <div class="weather-section">
            <h3>Condiciones Meteorológicas</h3>
            
            <div class="weather-cards">
                <?php foreach ($weatherData as $index => $weather): ?>
                    <div class="weather-card">
                        <h4>Punto <?= $index + 1 ?>: <?= $weather['city'] ?></h4>
                        <div class="weather-main">
                            <img src="https://openweathermap.org/img/wn/<?= $weather['icon'] ?>.png" 
                                alt="<?= $weather['conditions'] ?>">
                            <span class="temperature"><?= $weather['temperature'] ?>°C</span>
                        </div>
                        <div class="weather-details">
                            <p>Condiciones: <?= ucfirst($weather['conditions']) ?></p>
                            <p>Humedad: <?= $weather['humidity'] ?>%</p>
                            <p>Viento: <?= $weather['wind_speed'] ?> m/s</p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php elseif ($incluirClima): ?>
            <div class="weather-error">No se pudieron cargar los datos meteorológicos.</div>
            <?php endif; ?>

    
    
   
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
        <?php foreach ($model->getVehicleProfiles() as $vehiculo): ?>
        <tr>
            <td><?= $vehiculo->getNombre() ?></td>
            <td><?= $vehiculo->getCo2PorKm() ?> kg</td>
            <td><?= $vehiculo->getCombustiblePorKm() ?> L</td>
            <td>
                <?= $vehiculo->calcularEmisiones($model->totalDistanceOptimized / 1000) ?> kg
            </td>
            <td>
                <?= $vehiculo->calcularCombustible($model->totalDistanceOptimized / 1000) ?> L
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
    </div>
   
   

</body>
</html>
