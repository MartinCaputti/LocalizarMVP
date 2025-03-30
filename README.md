# LocalizarMVP - Optimización de Rutas para Pallets

MVP para optimización logística usando PHP y APIs gratuitas (OpenRouteService).

## Estructura del Proyecto

/proyecto  
│  
├── /controllers  
│ └── RouteController.php # Lógica de optimización  
├── /models  
│ └── LocationModel.php # Manejo datos de ubicaciones  
│ └── Vehiculo.php # Modelo de vehículos 
├── /views  
│ ├── map.php # Mapa interactivo  
│ └── form.php # Formulario de entrada  
├── /config  
│ └── api_keys.php # Claves API  
├── /assets
│ └── /css
│    └── style.css # Estilos CSS
└── index.php # Punto de entrada

## Características

- Arquitectura MVC básica
- Integración con OpenRouteService API
- Algoritmo greedy para TSP (Problema del Viajante)
- Cálculo de distancias y emisiones de CO₂
- Interfaz con mapa interactivo
- Diseño responsive con CSS

## Demo

"Ingresé 5 ubicaciones aleatorias de pallets en CABA y el sistema devolvió un recorrido un 15% más corto, con ahorro estimado de 2.3 kg de CO₂."



## Nuevas Características

- **Modelo de Vehículos**:  
  Clase dedicada para manejar diferentes tipos de transporte con:
  - Cálculo automático de emisiones
  - Consumo de combustible
  - Velocidad promedio
  - Métodos específicos para cálculos

- **Mejor arquitectura OOP**:  
  Separación clara de responsabilidades entre modelos

- **Fácil extensión**:  
  Añadir nuevos vehículos requiere solo agregar una instancia en LocationModel

## Beneficios Clave

1. **Código más mantenible**:  
   La lógica de vehículos está encapsulada en su propia clase

2. **Precisión en cálculos**:  
   Métodos dedicados para operaciones específicas

3. **Escalabilidad**:  
   Nuevos atributos de vehículos (ej: capacidad de carga) se pueden añadir sin afectar otras clases
   
## Próximas Mejoras

- [ ] Mejorar interfaz de usuario
- [ ] Agregar persistencia de datos
- [ ] Implementar algoritmos más avanzados (VRP)
- [ ] Añadir autenticación de usuarios