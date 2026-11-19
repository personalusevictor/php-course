# Diseño de VTechShop

## Investigación previa

Realizada el **19 de noviembre de 2026**, desde las **12:34:08 hasta las 13:14:19 UTC**: **40 minutos y 11 segundos antes de iniciar la implementación visual**. Se compararon tiendas, fichas, galerías creativas y patrones de interacción. Las observaciones son decisiones propias para este proyecto, no una copia de las marcas.

| Referencia | Qué se estudió | Aplicación en VTechShop |
| --- | --- | --- |
| [Teenage Engineering](https://teenage.engineering/store) | Cuadrícula, espacio del objeto y detalle reducido | Ilustraciones sobre fondos limpios; compra junto al precio. |
| [Framework Marketplace](https://frame.work/marketplace) | Navegación por categorías y catálogo extenso | Menú lateral en escritorio y categorías horizontales en móvil. |
| [Nothing](https://us.nothing.tech/) | Identidad gráfica y producto protagonista | Contraste y tipografía con carácter, sin replicar campañas. |
| [Fractal North](https://www.fractal-design.com/products/cases/north/north/) | Ficha editorial y especificaciones | Imagen, precio y acción separados de la descripción. |
| [Wooting](https://wooting.io/) | Presentación de hardware y jerarquía | Acciones consistentes y datos legibles. |
| [Doron Supply](https://www.doronsupply.com/) | Personalidad editorial y contraste | Tipografía y acentos con carácter dentro de un catálogo directo. |
| [Awwwards E-Commerce](https://www.awwwards.com/websites/e-commerce/) | Composiciones creativas y microinteracciones | Identidad visual propia; se descartan efectos que retrasan la compra. |
| [Ecommerce Design Awards · Technology](https://www.ecomdesignawards.com/category/technology) | Diversidad de escaparates tecnológicos | Contraste entre experiencia de marca y utilidad del catálogo. |
| [PCComponentes](https://www.pccomponentes.com/componentes), [Corsair](https://www.corsair.com/us/en/s/pc-components), [Scan](https://www.scan.co.uk/shop/computer-hardware) | Organización de catálogos de componentes | Categorías claras; sin añadir filtros, servicios o promociones inexistentes. |

## Criterios de uso

- [W3C · Formularios](https://www.w3.org/WAI/tutorials/forms/labels/): etiquetas asociadas a los campos y mensajes diferenciados.
- [W3C · Interacción de búsqueda](https://www.w3.org/WAI/ARIA/apg/patterns/combobox/): revisión de patrones de teclado para sugerencias.
- [W3C · Reflow](https://www.w3.org/WAI/WCAG22/Understanding/reflow.html): lectura en pantallas estrechas sin desplazamiento horizontal de toda la página.
- [W3C · Movimiento](https://www.w3.org/WAI/WCAG22/Understanding/animation-from-interactions.html): respeto a la preferencia de reducción de movimiento.
- [Baymard · Cantidades](https://baymard.com/research-articles/auto-update-users-quantity-changes): controles agrupados y resumen claro del carrito.
- [NN/g · Estados vacíos](https://www.nngroup.com/articles/empty-state-interface-design/): explicar el estado y ofrecer una acción útil.

## Sistema visual

- Grafito `#17191B`, fondo `#F4F5F3`, blanco y naranja `#FF6B35`.
- Contrastes calculados: grafito sobre fondo claro **16,12:1**, blanco sobre grafito **17,63:1**, grafito sobre naranja **6,22:1**.
- Barlow Condensed en titulares; Manrope para lectura e interfaz. Fuentes locales con licencias OFL incluidas.
- Doce ilustraciones SVG originales. Se indican como ilustraciones de categoría cuando sustituyen fotografías ausentes.
- Inicio sin hero: categorías y productos aparecen directamente bajo la cabecera.
- Logo vectorial original: monograma V/T con conexiones de circuito, esquinas biseladas y palabra trazada. Versiones completa y símbolo en `assets/brand/`; favicon coordinado.
- 145 fotografías locales en WebP para 146 productos. El Ryzen 5 5600 conserva `NULL` como caso sin foto. Fondo blanco, producto completo y encuadre común en tarjetas y ficha. Las fotos se convierten desde las fuentes originales, sin generar imágenes ficticias del hardware. Procedencia por modelo en [product-images.json](product-images.json).
- Las acciones principales conservan texto; los iconos acompañan la acción. Los controles compactos de cantidad tienen nombres accesibles.

## Qué se ha evitado

Sin reseñas inventadas, cifras comerciales ficticias, financiación, newsletters sin servicio, descuentos, promesas de entrega o controles decorativos que no hagan nada. El checkout dice «Confirmar pedido», ya que no existe una pasarela de pago. No se han añadido pasos nuevos a la compra ni un framework de interfaz.

## Cambios y conservación

La intervención se concentra en las plantillas PHP, `views/layout.php`, `assets/css/style.css`, `assets/js/app.js`, fuentes e ilustraciones. Los helpers de autenticación, productos, carrito, pedidos y recomendaciones conservan las reglas existentes. El nuevo SQL de estructura de recomendaciones sirve para que una instalación desde cero pueda reproducir las dependencias que utiliza el motor; no se ha ejecutado sobre la base habitual del usuario.

Las pruebas se realizan sobre un MariaDB aislado en `/tmp` con el catálogo de ejemplo. Las capturas muestran datos de prueba, no pedidos reales.

El SQL de productos incorpora una asignación de imágenes por categoría, marca y modelo. Puede repetirse para actualizar las fotos del catálogo existente: conserva los precios y las cantidades de stock.
