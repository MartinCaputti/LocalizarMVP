<?php
    // view/form.php
    // Formulario para la optimización de rutas
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Optimización de Rutas</title>
    
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    
    <!-- Leaflet Geocoder CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />

    <!-- Estilos propios -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <h1>Optimización de Rutas para Pallets</h1>
    <p>Seleccione las ubicaciones en el mapa y haga clic en "Calcular Ruta".</p>

    <form method="POST">
        <input type="hidden" id="coordinates" name="coordinates">
        <div id="map" style="height: 400px;"></div>
        
        <div class="form-group">
            <label for="vehicle">Tipo de vehículo:</label>
            <select name="vehicle" id="vehicle">
                <option value="car">Auto</option>
                <option value="truck">Camión</option>
                <option value="bike">Moto/Bicicleta</option>
            </select>
        </div>
        
        <div class="form-group">
            <input type="checkbox" id="incluir_clima" name="incluir_clima" checked>
            <label for="incluir_clima">Incluir información meteorológica</label>
        </div>
        
        <button type="submit">Calcular Ruta</button>
    </form>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

    <!-- Leaflet Geocoder JS -->
    <script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>

    <!-- Script de inicialización -->
    <script>
        window.addEventListener('load', () => {
            const map = L.map('map').setView([-34.60, -58.38], 13);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

            // Verificar si el geocoder está disponible
            if (L.Control.Geocoder && L.Control.Geocoder.nominatim) {
                const geocoder = L.Control.Geocoder.nominatim();
                const control = L.Control.geocoder({
                    geocoder: geocoder,
                    position: 'topright'
                }).addTo(map);
            } else {
                console.warn("El geocoder no está disponible. Puede que no se haya cargado correctamente.");
            }

            const markers = [];
            map.on('click', (e) => {
                const marker = L.marker(e.latlng).addTo(map);
                markers.push(e.latlng);
                document.getElementById('coordinates').value = JSON.stringify(markers);
            });
        });
    </script>
</body>
</html>
