<?php
// models/Vehiculo.php
//  Clase para manejar la información de los vehículos
//  y sus características de emisiones y consumo de combustible
//  Esta clase es utilizada por el controlador para calcular las emisiones y el consumo de combustible
class Vehiculo {
    private string $tipo;
    private string $nombre;
    private float $co2_por_km;
    private float $combustible_por_km;
    private float $velocidad_promedio;

    public function __construct(
        string $tipo,
        string $nombre,
        float $co2_por_km,
        float $combustible_por_km,
        float $velocidad_promedio
    ) {
        $this->tipo = $tipo;
        $this->nombre = $nombre;
        $this->co2_por_km = $co2_por_km;
        $this->combustible_por_km = $combustible_por_km;
        $this->velocidad_promedio = $velocidad_promedio;
    }

    // Getters
    public function getTipo(): string {
        return $this->tipo;
    }

    public function getNombre(): string {
        return $this->nombre;
    }

    public function getCo2PorKm(): float {
        return $this->co2_por_km;
    }

    public function getCombustiblePorKm(): float {
        return $this->combustible_por_km;
    }

    public function getVelocidadPromedio(): float {
        return $this->velocidad_promedio;
    }

    // Métodos de negocio
    public function calcularEmisiones(float $distancia_km): float {
        return round($distancia_km * $this->co2_por_km, 2);
    }

    public function calcularCombustible(float $distancia_km): float {
        return round($distancia_km * $this->combustible_por_km, 2);
    }
    
   // Método para calcular el tiempo de viaje
    public function calcularTiempoViaje(float $distancia_km): array {
        $horas = $distancia_km / $this->velocidad_promedio;
        
        return [
            'horas' => floor($horas),
            'minutos' => round(($horas - floor($horas)) * 60),
            'formateado' => $this->formatearTiempo($horas)
        ];
    }
    // Método para calcular el tiempo de viaje en horas y minutos
    private function formatearTiempo(float $horas): string {
        $horasEnteras = floor($horas);
        $minutos = round(($horas - $horasEnteras) * 60);
        
        if ($horasEnteras == 0) {
            return "{$minutos} minutos";
        } elseif ($minutos == 0) {
            return "{$horasEnteras} horas";
        } else {
            return "{$horasEnteras}h {$minutos}m";
        }
    }

    
}
?>
