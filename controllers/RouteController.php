<?php
    // controllers/RouteController.php
    // Controlador para manejar la lógica de optimización de rutas
    // y la interacción con el modelo


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
            $vehicleType = $_POST['vehicle'] ?? 'car';
            $optimizedRoute = $model->getOptimizedRoute($coordinates, $vehicleType);
            
            // Cálculo de la distancia total original y optimizada
            $vehicle = $_POST['vehicle'] ?? 'car';
            $profile = [
                'car' => 'driving-car',
                'truck' => 'driving-hgv',
                'bike' => 'cycling-regular'
            ];
            $apiUrl = "https://api.openrouteservice.org/v2/matrix/" . $profile[$vehicle];

            // Incluye la vista que mostrará los resultados y paso explícitamente el modelo
            extract(['model' => $model, 'optimizedRoute' => $optimizedRoute]);
            include 'views/map.php';
        } else {
            // Carga del formulario inicial
            include 'views/form.php';
        }
    }
}
?>
