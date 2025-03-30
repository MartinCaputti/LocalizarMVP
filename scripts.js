// scripts.js

// Inicialización del mapa
function initializeMap(center, zoomLevel) {
  const map = L.map("map").setView(center, zoomLevel);
  L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png").addTo(map);
  return map;
}

// Añadir un marcador al mapa
function addMarker(map, latLng, markersArray) {
  const marker = L.marker(latLng).addTo(map);
  markersArray.push(marker);
  return marker;
}

// Actualizar el input oculto con coordenadas
function updateCoordinatesInput(inputId, markersArray) {
  const coordinates = markersArray.map((marker) => marker.getLatLng());
  document.getElementById(inputId).value = JSON.stringify(coordinates);
}

// Limpiar todos los marcadores del mapa
function clearMarkers(map, markersArray, inputId) {
  markersArray.forEach((marker) => map.removeLayer(marker));
  markersArray.length = 0; // Limpia el array de marcadores
  updateCoordinatesInput(inputId, markersArray);
}

// Dibujar una ruta en el mapa
function drawRoute(map, routeCoords) {
  const polyline = L.polyline(
    routeCoords.map((coord) => [coord.lat, coord.lng]),
    { color: "red", dashArray: "5, 5" }
  );
  polyline.addTo(map);
  return polyline;
}

// Añadir marcadores con etiquetas al mapa
function addMarkersWithLabels(map, routeCoords) {
  routeCoords.forEach((coord, index) => {
    L.marker([coord.lat, coord.lng])
      .bindTooltip(`Punto ${index + 1}`, { permanent: true })
      .addTo(map);
  });
}

// Evento de envío del formulario
function handleFormSubmit(
  formId,
  endpoint,
  map,
  markersArray,
  inputId,
  updateMetricsCallback
) {
  const form = document.getElementById(formId);

  form.addEventListener("submit", function (e) {
    e.preventDefault(); // Prevenir recarga de la página
    const formData = new FormData(form);

    fetch(endpoint, {
      method: "POST",
      body: formData,
    })
      .then((response) => response.json())
      .then((data) => {
        // Limpia el mapa y agrega nueva información
        clearMarkers(map, markersArray, inputId);
        drawRoute(map, data.optimizedRoute);
        addMarkersWithLabels(map, data.optimizedRoute);
        // Actualiza las métricas
        updateMetricsCallback(data.metrics);
      })
      .catch((error) => console.error("Error:", error));
  });
}

// Actualizar métricas en el frontend
function updateMetrics(metrics) {
  const metricsContainer = document.getElementById("metrics");
  metricsContainer.innerHTML = `
        <p><strong>Distancia Original:</strong> ${metrics.totalDistanceOriginal} km</p>
        <p><strong>Distancia Optimizada:</strong> ${metrics.totalDistanceOptimized} km</p>
        <p><strong>Diferencia:</strong> ${metrics.distanceDifference} km</p>
        <p><strong>Mejora:</strong> ${metrics.percentageImprovement} %</p>
        <p><strong>Emisiones de CO2:</strong> ${metrics.totalCO2} kg</p>
        <p><strong>Consumo de Combustible:</strong> ${metrics.totalFuel} litros</p>
        <p><strong>Tiempo Estimado (Original):</strong> ${metrics.estimatedTimeOriginal} minutos</p>
        <p><strong>Tiempo Estimado (Optimizado):</strong> ${metrics.estimatedTimeOptimized} minutos</p>
        <p><strong>Factor Climático:</strong> ${metrics.weatherFactor}</p>
    `;
  metricsContainer.style.display = "block";
}
