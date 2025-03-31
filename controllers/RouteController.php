<?php
    // controllers/RouteController.php
    // Controlador para manejar la lógica de optimización de rutas
    // y la interacción con el modelo

    require_once 'models/LocationModel.php';

    class RouteController {
        private $locationModel;
    
        public function __construct(LocationModel $locationModel) {
            $this->locationModel = $locationModel;
        }
    
        public function handleRequest() {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $coordinates = $_POST['coordinates'] ? json_decode($_POST['coordinates'], true) : [];
                
                if (empty($coordinates)) {
                    $coordinates = [
                        ['lat' => -34.60, 'lng' => -58.38],
                        ['lat' => -34.58, 'lng' => -58.40],
                        ['lat' => -34.62, 'lng' => -58.42]
                    ];
                }
    
                $vehicleType = $_POST['vehicle'] ?? 'car';
                
                // Usamos $this->locationModel en lugar de $model
                $routeData = $this->locationModel->getOptimizedRoute($coordinates, $vehicleType);
                
                // Consultar clima solo si está marcado el checkbox
                $weatherData = isset($_POST['incluir_clima']) ? 
                    $this->locationModel->getWeatherForRoute($routeData['waypoints']) : 
                    null;
    
                extract([
                    'model' => $this->locationModel, // Pasamos la instancia ya inyectada
                    'fullRoute' => $routeData['full_route'],
                    'waypoints' => $routeData['waypoints'],
                    'weatherData' => $weatherData,
                    'incluirClima' => isset($_POST['incluir_clima'])
                ]);
                
                include 'views/map.php';
            } else {
                include 'views/form.php';
            }
        }
    }
    ?>