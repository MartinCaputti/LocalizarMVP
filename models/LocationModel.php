<?php
    // models/LocationModel.php
    // Clase para manejar la optimización de rutas
    // y la interacción con la API de OpenRouteService
  
    
    require_once 'Vehiculo.php'; // Importar el modelo Vehiculo
    require_once 'WeatherModel.php';

    class LocationModel {

        private $weatherModel;
        public $totalDistanceOriginal;
        public $totalDistanceOptimized;
        public $totalCO2;
        public $totalFuel;
        private $vehicleProfiles;
       

        public function __construct(WeatherModel $weatherModel) {
            $this->weatherModel = $weatherModel;
            $this->vehicleProfiles = [
                'car' => new Vehiculo("car", "Automóvil", 0.15, 0.08, 60),
                'truck' => new Vehiculo("truck", "Camión", 0.25, 0.3, 40),
                'bike' => new Vehiculo("bike", "Bicicleta/Moto Eléctrica", 0.0, 0.0, 20),
            ];
        }

        /**
         * Obtiene los perfiles de vehículos disponibles
         */
        public function getVehicleProfiles(): array {
            return $this->vehicleProfiles;
        }

        /**
         * Método principal para obtener una ruta optimizada
         */
        public function getOptimizedRoute(array $locations, string $vehicleType): array {
            $this->validateInput($locations, $vehicleType);

            $distances = $this->getDistancesMatrix($locations);
            $optimizedOrder = $this->solveTSP($distances);
            $optimizedRoute = $this->mapToLocations($optimizedOrder, $locations);

            $this->calculateMetrics($distances, $locations, $optimizedOrder, $vehicleType);

            return $optimizedRoute;
        }

        /**
         * Validación de datos de entrada
         */
        private function validateInput(array $locations, string $vehicleType): void {
            if (empty($locations)) {
                throw new InvalidArgumentException("Debe proporcionar al menos una ubicación");
            }

            if (!isset($this->vehicleProfiles[$vehicleType])) {
                throw new InvalidArgumentException("Tipo de vehículo no válido");
            }
        }

        /**
         * Mapea índices a ubicaciones reales
         */
        private function mapToLocations(array $indices, array $locations): array {
            return array_map(function($index) use ($locations) {
                return $locations[$index];
            }, $indices);
        }

        /**
         * Calcula todas las métricas de la ruta
         */
        private function calculateMetrics(
            array $distances, 
            array $locations, 
            array $optimizedOrder,
            string $vehicleType
        ): void {
            $this->totalDistanceOriginal = $this->calculateRouteDistance(
                $this->getSequentialIndexes(count($locations)), 
                $distances
            );

            $this->totalDistanceOptimized = $this->calculateRouteDistance(
                $optimizedOrder, 
                $distances
            );

            $vehiculo = $this->vehicleProfiles[$vehicleType];
            $distanceKm = $this->totalDistanceOptimized / 1000;

            $this->totalCO2 = $vehiculo->calcularEmisiones($distanceKm);
            $this->totalFuel = $vehiculo->calcularCombustible($distanceKm);
        }

        /**
         * Genera índices secuenciales para la ruta original
         */
        private function getSequentialIndexes(int $count): array {
            return range(0, $count - 1);
        }

        /**
         * Calcula distancia total para un orden específico
         */
        private function calculateRouteDistance(array $order, array $distances): float {
            $total = 0.0;
            $count = count($order);

            for ($i = 0; $i < $count - 1; $i++) {
                $from = $order[$i];
                $to = $order[$i + 1];
                $total += $distances[$from][$to] ?? 0;
            }

            return $total;
        }

        /**
         * Obtiene matriz de distancias desde la API
         */
        private function getDistancesMatrix(array $locations): array {
            $apiResponse = $this->callDistanceMatrixAPI($locations);

            if (!isset($apiResponse['distances'])) {
                throw new RuntimeException("Error al obtener distancias de la API");
            }

            return $apiResponse['distances'];
        }

        /**
         * Llama a la API de OpenRouteService
         */
        private function callDistanceMatrixAPI(array $locations): array {
            require_once 'config/api_keys.php';

            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => "https://api.openrouteservice.org/v2/matrix/driving-car",
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => [
                    'Authorization: ' . ORS_API_KEY,
                    'Content-Type: application/json'
                ],
                CURLOPT_POSTFIELDS => json_encode([
                    'locations' => $this->formatCoordinates($locations),
                    'metrics' => ['distance']
                ]),
                CURLOPT_RETURNTRANSFER => true
            ]);

            $response = curl_exec($ch);
            $error = curl_error($ch);
            curl_close($ch);

            if ($error) {
                throw new RuntimeException("Error en la conexión: " . $error);
            }

            return json_decode($response, true) ?? [];
        }

        /**
         * Formatea coordenadas para la API
         */
        private function formatCoordinates(array $locations): array {
            return array_map(function($loc) {
                return [(float)$loc['lng'], (float)$loc['lat']];
            }, $locations);
        }

        /**
         * Algoritmo para resolver el problema del viajante (TSP)
         */
        private function solveTSP(array $distances): array {
            $count = count($distances);
            if ($count === 0) return [];

            $visited = array_fill(0, $count, false);
            $route = [0];
            $visited[0] = true;

            for ($i = 1; $i < $count; $i++) {
                $next = $this->findNearestUnvisited($route[count($route) - 1], $distances, $visited);
                if ($next === null) break;

                $route[] = $next;
                $visited[$next] = true;
            }

            return $route;
        }

        /**
         * Encuentra el nodo más cercano no visitado
         */
        private function findNearestUnvisited(int $current, array $distances, array $visited): ?int {
            $nearest = null;
            $minDist = PHP_INT_MAX;

            foreach ($distances[$current] as $node => $distance) {
                if (!$visited[$node] && $distance < $minDist) {
                    $minDist = $distance;
                    $nearest = $node;
                }
            }

            return $nearest;
        }

        //Metodo para calcular el porcentaje de mejora 
        public function getPorcentajeMejora(): float {
            if ($this->totalDistanceOriginal == 0) {
                return 0.0; // Evitar división por cero
            }
            
            $diferencia = $this->totalDistanceOriginal - $this->totalDistanceOptimized;
            return round(($diferencia / $this->totalDistanceOriginal) * 100, 2);
        }

        // Método para calcular el tiempo de viaje
        public function getTiempoViaje(string $vehicleType): array {
            $vehiculo = $this->vehicleProfiles[$vehicleType];
            $distanciaKm = $this->totalDistanceOptimized / 1000;
            
            return $vehiculo->calcularTiempoViaje($distanciaKm);
        }

        // Método para obtener el clima en una ubicación
        public function getWeatherForRoute(array $locations): array {
            $weatherData = [];
            foreach ($locations as $location) {
                $data = $this->weatherModel->getWeatherData($location['lat'], $location['lng']);
                if ($data) {
                    $weatherData[] = $this->weatherModel->formatWeatherData($data);
                }
            }
            return $weatherData;
        }
    }
?>
