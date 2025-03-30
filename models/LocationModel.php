<?php

    class LocationModel {
        // Método para calcular la ruta optimizada
        public function getOptimizedRoute($locations) {
            $distances = $this->getDistancesMatrix($locations);
            $optimized = $this->greedyTSP($locations, $distances);
            return $optimized;
        }

        // Método para obtener la matriz de distancias desde OpenRouteService
        private function getDistancesMatrix($locations) {
            require_once 'config/api_keys.php'; // Incluye el archivo con la clave
            $apiUrl = "https://api.openrouteservice.org/v2/matrix/driving-car";
            
            // Reestructura las ubicaciones en el formato necesario
            $coords = array_map(function($loc) {
                return [$loc['lng'], $loc['lat']];
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
        private function greedyTSP($locations, $distances) {
            $n = count($locations);
            if ($n == 0) return [];

            $visited = array_fill(0, $n, false);
            $route = [];
            $current = 0;
            $route[] = $locations[$current];
            $visited[$current] = true;

            for ($i = 1; $i < $n; $i++) {
                $nearest = null;
                $minDist = PHP_FLOAT_MAX;

                for ($j = 0; $j < $n; $j++) {
                    if (!$visited[$j] && isset($distances[$current][$j])) {
                        $dist = $distances[$current][$j];
                        if ($dist < $minDist) {
                            $minDist = $dist;
                            $nearest = $j;
                        }
                    }
                }

                if ($nearest !== null) {
                    $visited[$nearest] = true;
                    $route[] = $locations[$nearest];
                    $current = $nearest;
                }
            }

            return $route;
        }
    }

?>