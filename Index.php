<?php
// index.php
require_once 'config/api_keys.php';
require_once 'models/WeatherModel.php';
require_once 'models/LocationModel.php';
require_once 'controllers/RouteController.php';

// Configuración de dependencias
$weatherModel = new WeatherModel(OWM_API_KEY);
$locationModel = new LocationModel($weatherModel);

// Instancia del controlador con las dependencias inyectadas
$controller = new RouteController($locationModel);
$controller->handleRequest();
?>