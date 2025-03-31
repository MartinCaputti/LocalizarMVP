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
            
            // 1. Obtener orden optimizado
            $optimizedOrder = $this->solveTSP($this->getDistancesMatrix($locations));
            
            // 2. Obtener ruta detallada para el orden optimizado
            return $this->getDetailedRoute($locations, $optimizedOrder, $vehicleType);
        }


        private function getDetailedRoute(array $locations, array $order, string $vehicleType): array {
            $profile = [
                'car' => 'driving-car',
                'truck' => 'driving-hgv',
                'bike' => 'cycling-regular'
            ];
            
            // Construir lista de coordenadas en orden optimizado
            $coords = [];
            foreach ($order as $index) {
                $coords[] = [(float)$locations[$index]['lng'], (float)$locations[$index]['lat']];
            }
            
            $body = json_encode([
                'coordinates' => $coords,
                'geometry' => true,
                'instructions' => false
            ]);
    
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => "https://api.openrouteservice.org/v2/directions/" . $profile[$vehicleType] . "/geojson",
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => [
                    'Authorization: ' . ORS_API_KEY,
                    'Content-Type: application/json'
                ],
                CURLOPT_POSTFIELDS => $body,
                CURLOPT_RETURNTRANSFER => true
            ]);
    
            $response = curl_exec($ch);
            curl_close($ch);
    
            $data = json_decode($response, true);
    
            if (!isset($data['features'][0]['geometry']['coordinates'])) {
                throw new RuntimeException("Error al obtener ruta detallada");
            }
    
            // Convertir coordenadas [lng, lat] a [lat, lng] para Leaflet
            $optimizedRoute = array_map(function($point) {
                return ['lat' => $point[1], 'lng' => $point[0]];
            }, $data['features'][0]['geometry']['coordinates']);
    
            // Almacenar métricas
            $this->totalDistanceOptimized = $data['features'][0]['properties']['summary']['distance'];
            $this->totalDistanceOriginal = $this->calculateOriginalDistance($locations);
    
            // Guardamos ambos conjuntos de coordenadas:
            return [
                'full_route' => array_map(function($point) {
                    return ['lat' => $point[1], 'lng' => $point[0]];
                }, $data['features'][0]['geometry']['coordinates']),
                'waypoints' => array_map(function($index) use ($locations) {
                    return $locations[$index];
                }, $order)
            ];
        }

         /**
     * Calcula distancia original (orden de entrada)
        */
        private function calculateOriginalDistance(array $locations): float {
            $total = 0;
            for ($i = 0; $i < count($locations) - 1; $i++) {
                $total += $this->calculateDistanceBetweenPoints(
                    $locations[$i],
                    $locations[$i + 1]
                );
            }
            return $total;
        }

        
    /**
     * Calcula distancia entre dos puntos (fórmula Haversine)
     */
    private function calculateDistanceBetweenPoints(array $point1, array $point2): float {
        $lat1 = deg2rad($point1['lat']);
        $lon1 = deg2rad($point1['lng']);
        $lat2 = deg2rad($point2['lat']);
        $lon2 = deg2rad($point2['lng']);

        $dlat = $lat2 - $lat1;
        $dlon = $lon2 - $lon1;

        $a = sin($dlat / 2) ** 2 + cos($lat1) * cos($lat2) * sin($dlon / 2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return 6371000 * $c; // Distancia en metros
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
            array $graph, 
            array $locations,
            array $optimizedOrder,
            string $vehicleType
        ): void {
            $this->totalDistanceOriginal = $this->calculateRouteDistance(
                $this->getSequentialIndexes(count($locations)), 
                $graph
            );
        
            $this->totalDistanceOptimized = $this->calculateRouteDistance(
                $optimizedOrder, 
                $graph
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

        // Metodo dijkstra para encontrar el camino más corto
            private function dijkstraShortestPath(array $graph, int $start, int $end): array {
            $distances = array_fill(0, count($graph), PHP_INT_MAX);
            $distances[$start] = 0;
            $previous = array_fill(0, count($graph), null);
            $queue = array_keys($graph);

            while (!empty($queue)) {
                // Encontrar el nodo con la distancia mínima
                $min = PHP_INT_MAX;
                $current = null;
                foreach ($queue as $node) {
                    if ($distances[$node] < $min) {
                        $min = $distances[$node];
                        $current = $node;
                    }
                }

                if ($current === null || $current === $end) {
                    break;
                }

                $queue = array_diff($queue, [$current]);

                foreach ($graph[$current] as $neighbor => $distance) {
                    if ($distance > 0) {
                        $alt = $distances[$current] + $distance;
                        if ($alt < $distances[$neighbor]) {
                            $distances[$neighbor] = $alt;
                            $previous[$neighbor] = $current;
                        }
                    }
                }
            }

            // Reconstruir el camino
            $path = [];
            $current = $end;
            while ($current !== null) {
                array_unshift($path, $current);
                $current = $previous[$current];
            }

            return $path;
        }


        // Método para obtener el grafo de distancias de calles

        private function getStreetDistanceGraph(array $locations, string $vehicleType): array {
            $profile = [
                'car' => 'driving-car',
                'truck' => 'driving-hgv',
                'bike' => 'cycling-regular'
            ];
            
            $coords = array_map(function($loc) {
                return [(float)$loc['lng'], (float)$loc['lat']];
            }, $locations);
        
            $body = json_encode([
                'locations' => $coords,
                'metrics' => ['distance'],
                'sources' => ['all'],
                'destinations' => ['all']
            ]);
        
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => "https://api.openrouteservice.org/v2/matrix/" . $profile[$vehicleType],
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => [
                    'Authorization: ' . ORS_API_KEY,
                    'Content-Type: application/json'
                ],
                CURLOPT_POSTFIELDS => $body,
                CURLOPT_RETURNTRANSFER => true
            ]);
        
            $response = curl_exec($ch);
            curl_close($ch);
        
            $data = json_decode($response, true);

            // Veriverificar que la API retorna datos válidos
            if (!isset($data['distances'])) {
                throw new RuntimeException("Error en respuesta de la API: " . json_encode($data));
            }


            return $data['distances'] ?? [];
        }

        /**
         * Calcula la ruta óptima usando el algoritmo de Dijkstra
         */

         private function calculateOptimalRoute(array $graph): array {
            $n = count($graph);
            if ($n <= 1) return [0];
            
            $route = [0];
            $visited = [0 => true];
            
            for ($i = 1; $i < $n; $i++) {
                $last = end($route);
                $next = $this->findNearestNode($last, $graph, $visited);
                
                if ($next === null) break;
                
                $route[] = $next;
                $visited[$next] = true;
            }
            
            return $route;
        }


        /**
         * Encuentra el nodo más cercano no visitado
         */

         private function findNearestNode(int $current, array $graph, array $visited): ?int {
            $nearest = null;
            $minDist = PHP_INT_MAX;
    
            foreach ($graph[$current] as $node => $distance) {
                if (!isset($visited[$node])) {
                    if ($distance < $minDist) {
                        $minDist = $distance;
                        $nearest = $node;
                    }
                }
            }
    
            return $nearest;
        }


    }
?>
