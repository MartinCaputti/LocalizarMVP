<?php
// Carga del controlador principal
require_once 'controllers/RouteController.php';

// Instancia del controlador y manejo de la solicitud
$controller = new RouteController();
$controller->handleRequest();
?>
