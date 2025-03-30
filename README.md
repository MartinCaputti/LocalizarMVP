# LocalizarMVP

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
│ └── api_keys.php # Clave de OpenRouteService  
└── index.php

Este MVP refleja cómo tecnología simple (PHP + APIs gratuitas) puede resolver problemas complejos como la optimización logística, que es el core de Red Pallet Swap. Con más tiempo, escalaría la lógica con algoritmos como VRP

Qué mostrar en la entrevista (aunque sea un MVP mínimo):
Código organizado en MVC (aunque sea básico).

Demo funcional:

"Ingresé 5 ubicaciones aleatorias de pallets en CABA y el sistema devolvió un recorrido un 15% más corto".

Próximos pasos:

"Con más tiempo, integraría la API de direcciones para trazar la ruta en el mapa, no solo marcadores".
