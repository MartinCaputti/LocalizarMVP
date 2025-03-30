# LocalizarMVP - Optimización de Rutas para Pallets

MVP para optimización logística usando PHP y APIs gratuitas (OpenRouteService).

## Estructura del Proyecto

/proyecto  
│  
├── /controllers  
│ └── RouteController.php # Lógica de optimización  
├── /models  
│ └── LocationModel.php # Manejo datos de ubicaciones  
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

## Próximas Mejoras

- [ ] Mejorar interfaz de usuario
- [ ] Agregar persistencia de datos
- [ ] Implementar algoritmos más avanzados (VRP)
- [ ] Añadir autenticación de usuarios