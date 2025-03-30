<?php
    // models/LocationModel.php
    // Clase para manejar la optimización de rutas
    // y la interacción con la API de OpenRouteService
    class LocationModel {
        // Método para calcular la ruta optimizada
        public function getOptimizedRoute($locations) {
            // Obtener la matriz de distancias usando [lng, lat]
            $distances = $this->getDistancesMatrix($locations);
            
            // Obtener el orden optimizado de índices (no los objetos)
            $optimizedOrder = $this->greedyTSP($distances);
            
            // Mapear los índices a las ubicaciones reales
            $optimizedRoute = [];
            foreach ($optimizedOrder as $index) {
                $optimizedRoute[] = $locations[$index];
            }
            
            // Calcular distancias
            $this->totalDistanceOriginal = $this->calculateTotalDistanceOriginal($locations, $distances);
            $this->totalDistanceOptimized = $this->calculateTotalDistanceOptimized($optimizedOrder, $distances);
            
            return $optimizedRoute;
        }
        
        // Calcular distancia del orden original
        private function calculateTotalDistanceOriginal($locations, $distances) {
            $total = 0;
            for ($i = 0; $i < count($locations) - 1; $i++) {
                $total += $distances[$i][$i + 1] ?? 0;
            }
            return $total;
        }

        // Calcular distancia del orden optimizado
        private function calculateTotalDistanceOptimized($optimizedOrder, $distances) {
            $total = 0;
            for ($i = 0; $i < count($optimizedOrder) - 1; $i++) {
                $from = $optimizedOrder[$i];
                $to = $optimizedOrder[$i + 1];
                $total += $distances[$from][$to] ?? 0;
            }
            return $total;
        }

        // Método para obtener la matriz de distancias desde OpenRouteService
        private function getDistancesMatrix($locations) {
            require_once 'config/api_keys.php'; // Incluye el archivo con la clave
            $apiUrl = "https://api.openrouteservice.org/v2/matrix/driving-car";
            
            // Reestructura las ubicaciones en el formato necesario
            $coords = array_map(function($loc) {
                return [(float)$loc['lng'], (float)$loc['lat']]; // Convertir explícitamente a float
            }, $locations);

            // Cuerpo de la solicitud en formato JSON
            $body = json_encode([
                'locations' => $coords,
                'metrics' => ['distance'] // Puedes usar también 'duration' si lo prefieres
            ]);

            // Configuración de la solicitud cURL
            $ch = curl_init($apiUrl);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: ' . ORS_API_KEY,
                'Content-Type: application/json'
            ]);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $response = curl_exec($ch);
            curl_close($ch);

            // Manejo de errores en la respuesta
            $data = json_decode($response, true);
            if (isset($data['distances'])) {
                return $data['distances']; // Matriz de distancias entre ubicaciones
            } else {
                // Error: Retorna una matriz vacía o un valor por defecto
                return [];
            }
        }

        // Algoritmo TSP con matriz de distancias
        private function greedyTSP($distances) {
            $n = count($distances);
            if ($n == 0) return [];
            
            $visited = array_fill(0, $n, false);
            $current = 0;
            $route = [$current];
            $visited[$current] = true;
            
            for ($i = 1; $i < $n; $i++) {
                $nearest = null;
                $minDist = PHP_INT_MAX;
                
                for ($j = 0; $j < $n; $j++) {
                    if (!$visited[$j] && $distances[$current][$j] < $minDist) {
                        $minDist = $distances[$current][$j];
                        $nearest = $j;
                    }
                }
                
                if ($nearest === null) break;
                $route[] = $nearest;
                $visited[$nearest] = true;
                $current = $nearest;
            }
            
            return $route;
        }
        

        /*private function calculateTotalDistance($route, $distances) {
            $total = 0;
            for ($i = 0; $i < count($route) - 1; $i++) {
                $total += $distances[$i][$i + 1] ?? 0;
            }
            return $total;
        }*/
    }

  
?>