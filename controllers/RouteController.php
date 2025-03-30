<?php
require_once 'models/LocationModel.php';

class RouteController {
    public function handleRequest() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Obtención de las coordenadas enviadas por el formulario
            $coordinates = isset($_POST['coordinates']) ? json_decode($_POST['coordinates'], true) : [];
            
            // Si no hay coordenadas, se incluyen datos de prueba
            if (empty($coordinates)) {
                $coordinates = [
                    ['lat' => -34.60, 'lng' => -58.38], // Ejemplo: Buenos Aires
                    ['lat' => -34.58, 'lng' => -58.40],
                    ['lat' => -34.62, 'lng' => -58.42]
                ];
            }

            // Instancia del modelo para procesar datos
            $model = new LocationModel();
            $optimizedRoute = $model->getOptimizedRoute($coordinates);

            // Incluye la vista que mostrará los resultados
            include 'views/map.php';
        } else {
            // Carga del formulario inicial
            include 'views/form.php';
        }
    }
}
?>
