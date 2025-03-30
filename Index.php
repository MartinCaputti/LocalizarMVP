<?php
//index.php
// Archivo principal que inicia la aplicación y carga el controlador
// Carga de configuraciones y claves API

// Carga del controlador principal
require_once 'controllers/RouteController.php';

// Instancia del controlador y manejo de la solicitud
$controller = new RouteController();
$controller->handleRequest();
?>
