<?Php
    // view/form.php
    // Formulario para la optimización de rutas
 ?>
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
<p>Seleccione las ubicaciones en el mapa y haga clic en "Calcular Ruta".</p>

    <form method="POST">
        <input type="hidden" id="coordinates" name="coordinates">
        <div id="map" style="height: 400px;"></div>
        <button type="submit">Calcular Ruta</button>
        <label for="vehicle">Tipo de vehículo:</label>
        <select name="vehicle" id="vehicle" style="margin: 10px 0;">
            <option value="car">Auto</option>
            <option value="truck">Camión</option>
            <option value="bike">Moto/Bicicleta</option>
        </select>
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
