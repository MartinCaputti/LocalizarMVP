<?php
    // models/WeatherModel.php
    class WeatherModel {
        private $apiKey;

        public function __construct(string $apiKey) {
            $this->apiKey = $apiKey;
        }

        public function getWeatherData(float $lat, float $lon): ?array {
            $url = "https://api.openweathermap.org/data/2.5/weather?lat={$lat}&lon={$lon}&appid={$this->apiKey}&units=metric&lang=es";
            
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FAILONERROR => true
            ]);
            
            $response = curl_exec($ch);
            if (curl_errno($ch)) {
                error_log("Error en API de clima: " . curl_error($ch));
                return null;
            }
            curl_close($ch);
            
            return json_decode($response, true);
        }

        public function formatWeatherData(array $weatherData): array {
            return [
                'temperature' => $weatherData['main']['temp'] ?? 0,
                'conditions' => $weatherData['weather'][0]['description'] ?? 'Desconocido',
                'icon' => $weatherData['weather'][0]['icon'] ?? '',
                'humidity' => $weatherData['main']['humidity'] ?? 0,
                'wind_speed' => $weatherData['wind']['speed'] ?? 0,
                'city' => $weatherData['name'] ?? 'Ubicación desconocida'
            ];
        }
    }


?>