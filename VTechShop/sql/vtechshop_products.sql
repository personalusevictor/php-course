USE vtechshop;
START TRANSACTION;

INSERT IGNORE INTO categories (category_name, category_description) VALUES
    ('Procesadores', 'CPU para equipos de sobremesa Intel y AMD, de gama de entrada a alto rendimiento.'),
    ('Tarjetas gráficas', 'GPU dedicadas para videojuegos, renderizado, productividad y creación de contenido.'),
    ('Placas base', 'Placas base ATX y micro-ATX para plataformas AMD e Intel.'),
    ('Memoria RAM', 'Kits de memoria RAM DDR4 y DDR5 para ordenadores de sobremesa.'),
    ('Almacenamiento SSD', 'Unidades de estado sólido SATA y NVMe de distintas capacidades.'),
    ('Fuentes de alimentación', 'Fuentes ATX y SFX para PC con distintas potencias y certificaciones.'),
    ('Refrigeración', 'Disipadores por aire y refrigeración líquida para procesadores.'),
    ('Cajas de PC', 'Chasis para equipos de sobremesa con diferentes formatos y ventilación.'),
    ('Teclados', 'Teclados de productividad y gaming, mecánicos o de membrana.'),
    ('Ratones', 'Ratones con cable e inalámbricos para trabajo y gaming.'),
    ('Monitores', 'Pantallas para PC, productividad, diseño y videojuegos.'),
    ('Auriculares', 'Auriculares con micrófono para juegos, videollamadas y uso multimedia.');

INSERT INTO products (category_id, products_name, products_description, brand, model, product_image, price, stock)
SELECT c.category_id, s.products_name, s.products_description, s.brand, s.model, NULL, s.price, s.stock
FROM (
    SELECT 'Procesadores' AS category_name, 'Procesador AMD Ryzen 5 5600' AS products_name, 'CPU de escritorio AM4 de 6 núcleos; adecuada para montajes DDR4 económicos.' AS products_description, 'AMD' AS brand, 'Ryzen 5 5600' AS model, 108.90 AS price, 0 AS stock
    UNION ALL SELECT 'Procesadores', 'Procesador AMD Ryzen 5 7600', 'CPU de escritorio AM5 de 6 núcleos; plataforma DDR5 para gaming.', 'AMD', 'Ryzen 5 7600', 189.90, 5
    UNION ALL SELECT 'Procesadores', 'Procesador AMD Ryzen 5 9600X', 'CPU Ryzen 9000 AM5 de 6 núcleos orientada a equipos modernos.', 'AMD', 'Ryzen 5 9600X', 239.90, 7
    UNION ALL SELECT 'Procesadores', 'Procesador AMD Ryzen 7 5700X', 'CPU AM4 de 8 núcleos para actualizar equipos DDR4.', 'AMD', 'Ryzen 7 5700X', 159.90, 9
    UNION ALL SELECT 'Procesadores', 'Procesador AMD Ryzen 7 7700X', 'CPU Ryzen 7000 AM5 de 8 núcleos para multitarea y juegos.', 'AMD', 'Ryzen 7 7700X', 279.90, 12
    UNION ALL SELECT 'Procesadores', 'Procesador AMD Ryzen 7 7800X3D', 'Procesador AM5 con tecnología 3D V-Cache para gaming.', 'AMD', 'Ryzen 7 7800X3D', 369.90, 16
    UNION ALL SELECT 'Procesadores', 'Procesador AMD Ryzen 7 9700X', 'CPU Ryzen 9000 AM5 de 8 núcleos con arquitectura Zen 5.', 'AMD', 'Ryzen 7 9700X', 319.90, 22
    UNION ALL SELECT 'Procesadores', 'Procesador AMD Ryzen 7 9800X3D', 'Procesador de alto rendimiento gaming con memoria 3D V-Cache.', 'AMD', 'Ryzen 7 9800X3D', 469.90, 27
    UNION ALL SELECT 'Procesadores', 'Procesador AMD Ryzen 9 9900X', 'Procesador AM5 de 12 núcleos para creación y productividad.', 'AMD', 'Ryzen 9 9900X', 429.90, 3
    UNION ALL SELECT 'Procesadores', 'Procesador AMD Ryzen 9 9950X', 'Procesador AM5 de 16 núcleos para tareas exigentes.', 'AMD', 'Ryzen 9 9950X', 599.90, 5
    UNION ALL SELECT 'Procesadores', 'Procesador Intel Core i5-14400F', 'Procesador de escritorio LGA1700; requiere tarjeta gráfica dedicada.', 'Intel', 'Core i5-14400F', 174.90, 7
    UNION ALL SELECT 'Procesadores', 'Procesador Intel Core i7-14700K', 'Procesador Intel de escritorio desbloqueado para altas cargas.', 'Intel', 'Core i7-14700K', 359.90, 0
    UNION ALL SELECT 'Procesadores', 'Procesador Intel Core Ultra 5 245K', 'Procesador Intel Core Ultra Serie 2 de escritorio; socket LGA1851.', 'Intel', 'Core Ultra 5 245K', 279.90, 12
    UNION ALL SELECT 'Procesadores', 'Procesador Intel Core Ultra 7 265K', 'Procesador Intel Core Ultra Serie 2 de 20 núcleos; socket LGA1851.', 'Intel', 'Core Ultra 7 265K', 409.90, 16
    UNION ALL SELECT 'Tarjetas gráficas', 'Tarjeta gráfica MSI GeForce RTX 5060 Ti 16G VENTUS 2X OC PLUS', 'Tarjeta NVIDIA GeForce RTX 5060 Ti con 16 GB GDDR7 y doble ventilador.', 'MSI', 'GeForce RTX 5060 Ti 16G VENTUS 2X OC PLUS', 479.90, 7
    UNION ALL SELECT 'Tarjetas gráficas', 'Tarjeta gráfica MSI GeForce RTX 5070 12G VENTUS 2X OC', 'Tarjeta NVIDIA GeForce RTX 5070 con 12 GB GDDR7 y refrigeración dual.', 'MSI', 'GeForce RTX 5070 12G VENTUS 2X OC', 629.90, 9
    UNION ALL SELECT 'Tarjetas gráficas', 'Tarjeta gráfica NVIDIA GeForce RTX 4060', 'GPU de gama media con tecnologías RTX y DLSS; referencia de familia.', 'NVIDIA', 'GeForce RTX 4060', 289.90, 12
    UNION ALL SELECT 'Tarjetas gráficas', 'Tarjeta gráfica NVIDIA GeForce RTX 4060 Ti 8GB', 'GPU GeForce RTX 4060 Ti de 8 GB; referencia de familia.', 'NVIDIA', 'GeForce RTX 4060 Ti 8GB', 379.90, 16
    UNION ALL SELECT 'Tarjetas gráficas', 'Tarjeta gráfica NVIDIA GeForce RTX 4070 SUPER', 'GPU RTX 4070 SUPER para gaming de alta resolución; referencia de familia.', 'NVIDIA', 'GeForce RTX 4070 SUPER', 579.90, 22
    UNION ALL SELECT 'Tarjetas gráficas', 'Tarjeta gráfica NVIDIA GeForce RTX 4080 SUPER', 'GPU de gama alta NVIDIA RTX 4080 SUPER; referencia de familia.', 'NVIDIA', 'GeForce RTX 4080 SUPER', 1099.90, 27
    UNION ALL SELECT 'Tarjetas gráficas', 'Tarjeta gráfica NVIDIA GeForce RTX 5060', 'GPU GeForce RTX 5060 orientada a juegos; referencia de familia.', 'NVIDIA', 'GeForce RTX 5060', 369.90, 3
    UNION ALL SELECT 'Tarjetas gráficas', 'Tarjeta gráfica NVIDIA GeForce RTX 5070 Ti', 'GPU de gama alta NVIDIA RTX 5070 Ti; referencia de familia.', 'NVIDIA', 'GeForce RTX 5070 Ti', 849.90, 5
    UNION ALL SELECT 'Tarjetas gráficas', 'Tarjeta gráfica NVIDIA GeForce RTX 5080', 'GPU NVIDIA RTX 5080 para sistemas exigentes; referencia de familia.', 'NVIDIA', 'GeForce RTX 5080', 1259.90, 7
    UNION ALL SELECT 'Tarjetas gráficas', 'Tarjeta gráfica AMD Radeon RX 9060 XT 16GB', 'Familia Radeon RX 9060 XT, variante con 16 GB GDDR6.', 'AMD', 'Radeon RX 9060 XT 16GB', 399.90, 0
    UNION ALL SELECT 'Tarjetas gráficas', 'Tarjeta gráfica AMD Radeon RX 9070', 'Familia Radeon RX 9070 con 16 GB GDDR6.', 'AMD', 'Radeon RX 9070', 629.90, 12
    UNION ALL SELECT 'Tarjetas gráficas', 'Tarjeta gráfica AMD Radeon RX 9070 XT', 'Familia Radeon RX 9070 XT con 16 GB GDDR6 para gaming.', 'AMD', 'Radeon RX 9070 XT', 729.90, 16
    UNION ALL SELECT 'Placas base', 'Placa base ASUS TUF GAMING B650-PLUS WIFI', 'Placa ATX AM5 con DDR5 y conectividad Wi-Fi para procesadores AMD Ryzen.', 'ASUS', 'TUF GAMING B650-PLUS WIFI', 219.90, 12
    UNION ALL SELECT 'Placas base', 'Placa base ASUS PRIME B650-PLUS', 'Placa ATX AMD AM5 con memoria DDR5 y expansión M.2.', 'ASUS', 'PRIME B650-PLUS', 169.90, 16
    UNION ALL SELECT 'Placas base', 'Placa base ASUS ROG STRIX B650E-F GAMING WIFI', 'Placa AM5 para gaming con chipset B650E y Wi-Fi.', 'ASUS', 'ROG STRIX B650E-F GAMING WIFI', 289.90, 22
    UNION ALL SELECT 'Placas base', 'Placa base ASUS TUF GAMING X670E-PLUS WIFI', 'Placa base ATX AM5 de gama alta con chipset X670E.', 'ASUS', 'TUF GAMING X670E-PLUS WIFI', 309.90, 27
    UNION ALL SELECT 'Placas base', 'Placa base ASUS ROG STRIX X870-F GAMING WIFI', 'Placa base AM5 premium con chipset X870 para plataforma DDR5.', 'ASUS', 'ROG STRIX X870-F GAMING WIFI', 419.90, 3
    UNION ALL SELECT 'Placas base', 'Placa base MSI MAG B650 TOMAHAWK WIFI', 'Placa ATX AM5 DDR5 para gaming con conectividad inalámbrica.', 'MSI', 'MAG B650 TOMAHAWK WIFI', 199.90, 5
    UNION ALL SELECT 'Placas base', 'Placa base MSI PRO B650-S WIFI', 'Placa ATX AM5 de uso general para ordenadores de sobremesa.', 'MSI', 'PRO B650-S WIFI', 159.90, 7
    UNION ALL SELECT 'Placas base', 'Placa base MSI B760 GAMING PLUS WIFI', 'Placa base Intel LGA1700 con chipset B760 y conectividad Wi-Fi.', 'MSI', 'B760 GAMING PLUS WIFI', 164.90, 0
    UNION ALL SELECT 'Placas base', 'Placa base MSI MAG Z790 TOMAHAWK WIFI', 'Placa base LGA1700 con chipset Z790 para procesadores Intel.', 'MSI', 'MAG Z790 TOMAHAWK WIFI', 259.90, 12
    UNION ALL SELECT 'Placas base', 'Placa base Gigabyte B650 AORUS ELITE AX', 'Placa ATX AMD AM5 B650 con conectividad inalámbrica.', 'Gigabyte', 'B650 AORUS ELITE AX', 199.90, 16
    UNION ALL SELECT 'Placas base', 'Placa base Gigabyte Z790 AORUS ELITE AX', 'Placa ATX Intel LGA1700 con chipset Z790 y expansión M.2.', 'Gigabyte', 'Z790 AORUS ELITE AX', 239.90, 22
    UNION ALL SELECT 'Placas base', 'Placa base ASRock B650M Pro RS WiFi', 'Placa micro-ATX AM5 con DDR5 y conectividad Wi-Fi.', 'ASRock', 'B650M Pro RS WiFi', 149.90, 27
    UNION ALL SELECT 'Memoria RAM', 'Memoria RAM Kingston FURY Beast DDR5 32GB (2x16GB) 5600MT/s', 'Kit DDR5 de 32 GB en dos módulos, serie FURY Beast.', 'Kingston', 'FURY Beast DDR5 32GB (2x16GB) 5600MT/s', 104.90, 22
    UNION ALL SELECT 'Memoria RAM', 'Memoria RAM Kingston FURY Beast RGB DDR5 32GB (2x16GB) 6000MT/s', 'Kit DDR5 32 GB con iluminación RGB para sobremesa.', 'Kingston', 'FURY Beast RGB DDR5 32GB (2x16GB) 6000MT/s', 139.90, 27
    UNION ALL SELECT 'Memoria RAM', 'Memoria RAM Kingston FURY Renegade DDR5 32GB (2x16GB) 6400MT/s', 'Kit DDR5 32 GB de alto rendimiento, serie Renegade.', 'Kingston', 'FURY Renegade DDR5 32GB (2x16GB) 6400MT/s', 159.90, 3
    UNION ALL SELECT 'Memoria RAM', 'Memoria RAM Kingston FURY Beast DDR4 16GB (2x8GB) 3200MT/s', 'Kit DDR4 de 16 GB para placas compatibles con DDR4.', 'Kingston', 'FURY Beast DDR4 16GB (2x8GB) 3200MT/s', 49.90, 5
    UNION ALL SELECT 'Memoria RAM', 'Memoria RAM Corsair Vengeance DDR5 32GB (2x16GB) 5600MHz', 'Kit de 32 GB DDR5 para plataformas de sobremesa.', 'Corsair', 'Vengeance DDR5 32GB (2x16GB) 5600MHz', 109.90, 7
    UNION ALL SELECT 'Memoria RAM', 'Memoria RAM Corsair Vengeance RGB DDR5 32GB (2x16GB) 6000MHz', 'Kit DDR5 de 32 GB con iluminación RGB.', 'Corsair', 'Vengeance RGB DDR5 32GB (2x16GB) 6000MHz', 149.90, 0
    UNION ALL SELECT 'Memoria RAM', 'Memoria RAM Corsair Dominator Titanium DDR5 32GB (2x16GB) 6000MHz', 'Kit de memoria DDR5 premium con estética y disipación avanzadas.', 'Corsair', 'Dominator Titanium DDR5 32GB (2x16GB) 6000MHz', 209.90, 12
    UNION ALL SELECT 'Memoria RAM', 'Memoria RAM Corsair Vengeance LPX DDR4 32GB (2x16GB) 3200MHz', 'Kit DDR4 de 32 GB de perfil bajo para sobremesa.', 'Corsair', 'Vengeance LPX DDR4 32GB (2x16GB) 3200MHz', 69.90, 16
    UNION ALL SELECT 'Memoria RAM', 'Memoria RAM G.SKILL Flare X5 DDR5 32GB (2x16GB) 6000MT/s', 'Kit DDR5 de 32 GB orientado a equipos AMD compatibles.', 'G.SKILL', 'Flare X5 DDR5 32GB (2x16GB) 6000MT/s', 119.90, 22
    UNION ALL SELECT 'Memoria RAM', 'Memoria RAM G.SKILL Trident Z5 Neo RGB DDR5 32GB (2x16GB) 6000MT/s', 'Kit DDR5 32 GB con iluminación RGB y perfiles AMD EXPO.', 'G.SKILL', 'Trident Z5 Neo RGB DDR5 32GB (2x16GB) 6000MT/s', 159.90, 27
    UNION ALL SELECT 'Memoria RAM', 'Memoria RAM G.SKILL Ripjaws S5 DDR5 32GB (2x16GB) 6000MT/s', 'Kit DDR5 de 32 GB para equipos de sobremesa.', 'G.SKILL', 'Ripjaws S5 DDR5 32GB (2x16GB) 6000MT/s', 129.90, 3
    UNION ALL SELECT 'Memoria RAM', 'Memoria RAM Crucial Pro DDR5 32GB (2x16GB) 5600MT/s', 'Kit DDR5 de 32 GB orientado a fiabilidad y productividad.', 'Crucial', 'Pro DDR5 32GB (2x16GB) 5600MT/s', 99.90, 5
    UNION ALL SELECT 'Almacenamiento SSD', 'SSD Samsung 990 PRO 1TB', 'SSD interno NVMe M.2 PCIe 4.0 de 1 TB para alto rendimiento.', 'Samsung', '990 PRO 1TB', 119.90, 3
    UNION ALL SELECT 'Almacenamiento SSD', 'SSD Samsung 990 PRO 2TB', 'SSD interno NVMe M.2 PCIe 4.0 de 2 TB.', 'Samsung', '990 PRO 2TB', 199.90, 5
    UNION ALL SELECT 'Almacenamiento SSD', 'SSD Samsung 990 EVO Plus 1TB', 'Unidad SSD NVMe M.2 de 1 TB para equipos modernos.', 'Samsung', '990 EVO Plus 1TB', 99.90, 7
    UNION ALL SELECT 'Almacenamiento SSD', 'SSD Samsung 990 EVO Plus 2TB', 'Unidad SSD NVMe M.2 de 2 TB para almacenamiento rápido.', 'Samsung', '990 EVO Plus 2TB', 159.90, 0
    UNION ALL SELECT 'Almacenamiento SSD', 'SSD Samsung 9100 PRO 2TB', 'SSD NVMe M.2 PCIe 5.0 de 2 TB para altas cargas.', 'Samsung', '9100 PRO 2TB', 299.90, 12
    UNION ALL SELECT 'Almacenamiento SSD', 'SSD Samsung 870 EVO 1TB', 'SSD SATA de 2,5 pulgadas y 1 TB para actualizaciones.', 'Samsung', '870 EVO 1TB', 109.90, 16
    UNION ALL SELECT 'Almacenamiento SSD', 'SSD WD_BLACK SN850X 1TB', 'SSD NVMe M.2 PCIe 4.0 de 1 TB orientado a gaming.', 'WD_BLACK', 'SN850X 1TB', 109.90, 22
    UNION ALL SELECT 'Almacenamiento SSD', 'SSD WD_BLACK SN850X 2TB', 'SSD NVMe M.2 PCIe 4.0 de 2 TB para juegos y aplicaciones.', 'WD_BLACK', 'SN850X 2TB', 179.90, 27
    UNION ALL SELECT 'Almacenamiento SSD', 'SSD WD Blue SN580 1TB', 'SSD NVMe M.2 de 1 TB para uso general.', 'WD Blue', 'SN580 1TB', 79.90, 3
    UNION ALL SELECT 'Almacenamiento SSD', 'SSD Crucial T500 1TB', 'SSD NVMe M.2 PCIe 4.0 de 1 TB para equipos de alto rendimiento.', 'Crucial', 'T500 1TB', 104.90, 5
    UNION ALL SELECT 'Almacenamiento SSD', 'SSD Crucial P3 Plus 2TB', 'SSD NVMe M.2 PCIe 4.0 de 2 TB para capacidad y velocidad.', 'Crucial', 'P3 Plus 2TB', 119.90, 7
    UNION ALL SELECT 'Almacenamiento SSD', 'SSD Kingston NV3 1TB', 'Unidad SSD NVMe M.2 PCIe 4.0 de 1 TB.', 'Kingston', 'NV3 1TB', 69.90, 9
    UNION ALL SELECT 'Fuentes de alimentación', 'Fuente de alimentación Corsair RM650e 650W', 'Fuente ATX modular de 650 W para PC de sobremesa.', 'Corsair', 'RM650e 650W', 94.90, 7
    UNION ALL SELECT 'Fuentes de alimentación', 'Fuente de alimentación Corsair RM750e 750W', 'Fuente ATX modular de 750 W para equipos de gama media.', 'Corsair', 'RM750e 750W', 109.90, 0
    UNION ALL SELECT 'Fuentes de alimentación', 'Fuente de alimentación Corsair RM850e 850W', 'Fuente ATX modular de 850 W para equipos gaming.', 'Corsair', 'RM850e 850W', 129.90, 12
    UNION ALL SELECT 'Fuentes de alimentación', 'Fuente de alimentación Corsair RM1000e 1000W', 'Fuente ATX de 1000 W para equipos exigentes.', 'Corsair', 'RM1000e 1000W', 169.90, 16
    UNION ALL SELECT 'Fuentes de alimentación', 'Fuente de alimentación Corsair RM850x 850W', 'Fuente ATX modular de 850 W de la serie RMx.', 'Corsair', 'RM850x 850W', 149.90, 22
    UNION ALL SELECT 'Fuentes de alimentación', 'Fuente de alimentación Corsair SF750 750W', 'Fuente SFX de 750 W para cajas compactas compatibles.', 'Corsair', 'SF750 750W', 179.90, 27
    UNION ALL SELECT 'Fuentes de alimentación', 'Fuente de alimentación Seasonic FOCUS GX-650 650W', 'Fuente ATX totalmente modular, certificación 80 PLUS Gold.', 'Seasonic', 'FOCUS GX-650 650W', 109.90, 3
    UNION ALL SELECT 'Fuentes de alimentación', 'Fuente de alimentación Seasonic FOCUS GX-750 750W', 'Fuente ATX totalmente modular, certificación 80 PLUS Gold.', 'Seasonic', 'FOCUS GX-750 750W', 124.90, 5
    UNION ALL SELECT 'Fuentes de alimentación', 'Fuente de alimentación Seasonic FOCUS GX-850 850W', 'Fuente ATX totalmente modular para configuraciones potentes.', 'Seasonic', 'FOCUS GX-850 850W', 144.90, 7
    UNION ALL SELECT 'Fuentes de alimentación', 'Fuente de alimentación Seasonic VERTEX GX-1000 1000W', 'Fuente ATX de 1000 W de la serie VERTEX GX.', 'Seasonic', 'VERTEX GX-1000 1000W', 209.90, 9
    UNION ALL SELECT 'Fuentes de alimentación', 'Fuente de alimentación be quiet! Pure Power 12 M 750W', 'Fuente de alimentación modular para PC de sobremesa.', 'be quiet!', 'Pure Power 12 M 750W', 129.90, 12
    UNION ALL SELECT 'Fuentes de alimentación', 'Fuente de alimentación be quiet! Straight Power 12 1000W', 'Fuente de alimentación de 1000 W de gama alta.', 'be quiet!', 'Straight Power 12 1000W', 219.90, 16
    UNION ALL SELECT 'Refrigeración', 'Refrigeración CPU Noctua NH-D15 G2', 'Disipador de doble torre premium para CPU; revisar espacio disponible.', 'Noctua', 'NH-D15 G2', 149.90, 12
    UNION ALL SELECT 'Refrigeración', 'Refrigeración CPU Noctua NH-D15 chromax.black', 'Disipador por aire de doble torre con acabado negro.', 'Noctua', 'NH-D15 chromax.black', 119.90, 16
    UNION ALL SELECT 'Refrigeración', 'Refrigeración CPU Noctua NH-U12S redux', 'Disipador por aire de torre única para procesadores compatibles.', 'Noctua', 'NH-U12S redux', 54.90, 22
    UNION ALL SELECT 'Refrigeración', 'Refrigeración CPU Noctua NH-U12A chromax.black', 'Disipador premium por aire con ventiladores de 120 mm.', 'Noctua', 'NH-U12A chromax.black', 129.90, 27
    UNION ALL SELECT 'Refrigeración', 'Refrigeración CPU Noctua NH-L9i-17xx', 'Disipador compacto de bajo perfil para Intel LGA1700.', 'Noctua', 'NH-L9i-17xx', 49.90, 3
    UNION ALL SELECT 'Refrigeración', 'Refrigeración CPU DeepCool AK400', 'Disipador por aire compacto de torre para CPU.', 'DeepCool', 'AK400', 39.90, 5
    UNION ALL SELECT 'Refrigeración', 'Refrigeración CPU DeepCool AK620', 'Disipador por aire de doble torre para CPU.', 'DeepCool', 'AK620', 69.90, 7
    UNION ALL SELECT 'Refrigeración', 'Refrigeración CPU be quiet! Dark Rock Pro 5', 'Disipador por aire de alto rendimiento con doble torre.', 'be quiet!', 'Dark Rock Pro 5', 99.90, 9
    UNION ALL SELECT 'Refrigeración', 'Refrigeración CPU be quiet! Pure Rock 2', 'Disipador por aire para PC de sobremesa.', 'be quiet!', 'Pure Rock 2', 44.90, 12
    UNION ALL SELECT 'Refrigeración', 'Refrigeración CPU Arctic Freezer 36', 'Disipador por aire para procesadores de escritorio.', 'Arctic', 'Freezer 36', 34.90, 16
    UNION ALL SELECT 'Refrigeración', 'Refrigeración CPU Arctic Liquid Freezer III 240', 'Sistema de refrigeración líquida AIO con radiador de 240 mm.', 'Arctic', 'Liquid Freezer III 240', 89.90, 0
    UNION ALL SELECT 'Refrigeración', 'Refrigeración CPU Arctic Liquid Freezer III 360', 'Sistema AIO de refrigeración líquida con radiador de 360 mm.', 'Arctic', 'Liquid Freezer III 360', 109.90, 27
    UNION ALL SELECT 'Cajas de PC', 'Caja PC Fractal Design North Charcoal Black TG Dark', 'Caja ATX con frontal de madera y panel de vidrio templado.', 'Fractal Design', 'North Charcoal Black TG Dark', 149.90, 22
    UNION ALL SELECT 'Cajas de PC', 'Caja PC Fractal Design North Chalk White TG Clear', 'Caja ATX blanca con detalles de madera y panel de vidrio.', 'Fractal Design', 'North Chalk White TG Clear', 149.90, 27
    UNION ALL SELECT 'Cajas de PC', 'Caja PC Fractal Design North XL Charcoal Black TG Dark', 'Caja ATX de gran formato con estética North.', 'Fractal Design', 'North XL Charcoal Black TG Dark', 189.90, 3
    UNION ALL SELECT 'Cajas de PC', 'Caja PC Fractal Design Pop Air RGB Black TG', 'Caja ATX con frontal ventilado e iluminación RGB.', 'Fractal Design', 'Pop Air RGB Black TG', 109.90, 5
    UNION ALL SELECT 'Cajas de PC', 'Caja PC Fractal Design Meshify 2 Black TG', 'Caja ATX con frontal de malla y diseño orientado al flujo de aire.', 'Fractal Design', 'Meshify 2 Black TG', 169.90, 7
    UNION ALL SELECT 'Cajas de PC', 'Caja PC Fractal Design Torrent Black TG Light Tint', 'Caja de PC de alto flujo de aire con panel lateral transparente.', 'Fractal Design', 'Torrent Black TG Light Tint', 199.90, 9
    UNION ALL SELECT 'Cajas de PC', 'Caja PC NZXT H5 Flow Black', 'Caja ATX con frontal orientado a flujo de aire.', 'NZXT', 'H5 Flow Black', 99.90, 12
    UNION ALL SELECT 'Cajas de PC', 'Caja PC NZXT H6 Flow Black', 'Caja ATX de doble cámara con paneles de cristal.', 'NZXT', 'H6 Flow Black', 124.90, 16
    UNION ALL SELECT 'Cajas de PC', 'Caja PC NZXT H9 Flow Black', 'Chasis grande de doble cámara con amplio espacio interior.', 'NZXT', 'H9 Flow Black', 169.90, 0
    UNION ALL SELECT 'Cajas de PC', 'Caja PC Corsair 4000D Airflow Black', 'Caja ATX con frontal ventilado para montaje de escritorio.', 'Corsair', '4000D Airflow Black', 109.90, 27
    UNION ALL SELECT 'Cajas de PC', 'Caja PC Corsair 5000D Airflow Black', 'Caja ATX espaciosa con paneles de alto flujo de aire.', 'Corsair', '5000D Airflow Black', 159.90, 3
    UNION ALL SELECT 'Cajas de PC', 'Caja PC Lian Li LANCOOL 216 Black', 'Caja de sobremesa con ventilación frontal y diseño gaming.', 'Lian Li', 'LANCOOL 216 Black', 109.90, 5
    UNION ALL SELECT 'Teclados', 'Teclado Logitech G G413 SE', 'Teclado mecánico gaming de tamaño completo.', 'Logitech G', 'G413 SE', 69.90, 3
    UNION ALL SELECT 'Teclados', 'Teclado Logitech G G512 Carbon', 'Teclado mecánico gaming con iluminación RGB.', 'Logitech G', 'G512 Carbon', 109.90, 5
    UNION ALL SELECT 'Teclados', 'Teclado Logitech G G915 LIGHTSPEED', 'Teclado gaming inalámbrico de perfil bajo.', 'Logitech G', 'G915 LIGHTSPEED', 189.90, 7
    UNION ALL SELECT 'Teclados', 'Teclado Logitech G PRO X TKL LIGHTSPEED', 'Teclado gaming inalámbrico de formato tenkeyless.', 'Logitech G', 'PRO X TKL LIGHTSPEED', 179.90, 9
    UNION ALL SELECT 'Teclados', 'Teclado Corsair K55 RGB PRO', 'Teclado gaming de membrana con iluminación RGB.', 'Corsair', 'K55 RGB PRO', 59.90, 12
    UNION ALL SELECT 'Teclados', 'Teclado Corsair K70 CORE RGB', 'Teclado gaming mecánico con retroiluminación RGB.', 'Corsair', 'K70 CORE RGB', 99.90, 16
    UNION ALL SELECT 'Teclados', 'Teclado Corsair K70 RGB PRO', 'Teclado mecánico premium gaming con RGB.', 'Corsair', 'K70 RGB PRO', 159.90, 0
    UNION ALL SELECT 'Teclados', 'Teclado Corsair K65 PLUS WIRELESS', 'Teclado mecánico inalámbrico de formato compacto.', 'Corsair', 'K65 PLUS WIRELESS', 169.90, 27
    UNION ALL SELECT 'Teclados', 'Teclado Razer BlackWidow V4', 'Teclado mecánico gaming con iluminación configurable.', 'Razer', 'BlackWidow V4', 169.90, 3
    UNION ALL SELECT 'Teclados', 'Teclado Razer Huntsman V3 Pro TKL', 'Teclado gaming tenkeyless con interruptores ópticos.', 'Razer', 'Huntsman V3 Pro TKL', 229.90, 5
    UNION ALL SELECT 'Teclados', 'Teclado Keychron K2 Pro', 'Teclado mecánico compacto inalámbrico para trabajo y ocio.', 'Keychron', 'K2 Pro', 119.90, 7
    UNION ALL SELECT 'Teclados', 'Teclado SteelSeries Apex Pro TKL (2023)', 'Teclado gaming compacto con interruptores ajustables.', 'SteelSeries', 'Apex Pro TKL (2023)', 219.90, 9
    UNION ALL SELECT 'Ratones', 'Ratón Logitech G G305 LIGHTSPEED', 'Ratón gaming inalámbrico ligero con tecnología LIGHTSPEED.', 'Logitech G', 'G305 LIGHTSPEED', 45.90, 7
    UNION ALL SELECT 'Ratones', 'Ratón Logitech G G502 X', 'Ratón gaming con cable y botones programables.', 'Logitech G', 'G502 X', 69.90, 9
    UNION ALL SELECT 'Ratones', 'Ratón Logitech G G502 X LIGHTSPEED', 'Ratón gaming inalámbrico con interruptores LIGHTFORCE.', 'Logitech G', 'G502 X LIGHTSPEED', 109.90, 12
    UNION ALL SELECT 'Ratones', 'Ratón Logitech G G502 X PLUS', 'Ratón gaming inalámbrico con iluminación RGB.', 'Logitech G', 'G502 X PLUS', 139.90, 16
    UNION ALL SELECT 'Ratones', 'Ratón Logitech G PRO X SUPERLIGHT 2', 'Ratón gaming inalámbrico ultraligero para eSports.', 'Logitech G', 'PRO X SUPERLIGHT 2', 149.90, 0
    UNION ALL SELECT 'Ratones', 'Ratón Logitech G G309 LIGHTSPEED', 'Ratón gaming inalámbrico con interruptores LIGHTFORCE.', 'Logitech G', 'G309 LIGHTSPEED', 69.90, 27
    UNION ALL SELECT 'Ratones', 'Ratón Razer DeathAdder V3', 'Ratón gaming ergonómico con cable.', 'Razer', 'DeathAdder V3', 69.90, 3
    UNION ALL SELECT 'Ratones', 'Ratón Razer DeathAdder V3 Pro', 'Ratón gaming inalámbrico ergonómico para competición.', 'Razer', 'DeathAdder V3 Pro', 149.90, 5
    UNION ALL SELECT 'Ratones', 'Ratón Razer Basilisk V3', 'Ratón gaming ergonómico con numerosos controles.', 'Razer', 'Basilisk V3', 59.90, 7
    UNION ALL SELECT 'Ratones', 'Ratón Razer Viper V3 Pro', 'Ratón gaming inalámbrico ligero para competición.', 'Razer', 'Viper V3 Pro', 169.90, 9
    UNION ALL SELECT 'Ratones', 'Ratón SteelSeries Aerox 3 Wireless', 'Ratón gaming inalámbrico de diseño ligero.', 'SteelSeries', 'Aerox 3 Wireless', 89.90, 12
    UNION ALL SELECT 'Ratones', 'Ratón Corsair M65 RGB ULTRA', 'Ratón gaming con cable y sensor óptico.', 'Corsair', 'M65 RGB ULTRA', 79.90, 16
    UNION ALL SELECT 'Monitores', 'Monitor MSI G274QPF E2', 'Monitor gaming de 27 pulgadas con panel de alta frecuencia.', 'MSI', 'G274QPF E2', 219.90, 12
    UNION ALL SELECT 'Monitores', 'Monitor MSI MAG 274QRF QD E2', 'Monitor gaming QHD con tecnología de color Quantum Dot.', 'MSI', 'MAG 274QRF QD E2', 289.90, 16
    UNION ALL SELECT 'Monitores', 'Monitor MSI G255F', 'Monitor gaming de 24,5 pulgadas para uso general.', 'MSI', 'G255F', 119.90, 0
    UNION ALL SELECT 'Monitores', 'Monitor LG 27GP850-B', 'Monitor gaming de 27 pulgadas QHD con panel IPS.', 'LG', '27GP850-B', 279.90, 27
    UNION ALL SELECT 'Monitores', 'Monitor LG 27GR75Q-B', 'Monitor gaming de 27 pulgadas QHD para escritorio.', 'LG', '27GR75Q-B', 209.90, 3
    UNION ALL SELECT 'Monitores', 'Monitor LG 27GS95QE-B', 'Monitor OLED gaming de 27 pulgadas.', 'LG', '27GS95QE-B', 699.90, 5
    UNION ALL SELECT 'Monitores', 'Monitor Dell G2724D', 'Monitor gaming QHD de 27 pulgadas.', 'Dell', 'G2724D', 219.90, 7
    UNION ALL SELECT 'Monitores', 'Monitor Dell P2422H', 'Monitor de oficina Full HD de 23,8 pulgadas.', 'Dell', 'P2422H', 179.90, 9
    UNION ALL SELECT 'Monitores', 'Monitor Dell U2723QE', 'Monitor profesional 4K de 27 pulgadas.', 'Dell', 'U2723QE', 549.90, 12
    UNION ALL SELECT 'Monitores', 'Monitor ASUS TUF Gaming VG27AQ1A', 'Monitor gaming QHD de 27 pulgadas.', 'ASUS', 'TUF Gaming VG27AQ1A', 229.90, 16
    UNION ALL SELECT 'Monitores', 'Monitor ASUS ROG Swift PG27AQDM', 'Monitor gaming OLED de 27 pulgadas.', 'ASUS', 'ROG Swift PG27AQDM', 749.90, 22
    UNION ALL SELECT 'Monitores', 'Monitor Samsung Odyssey G5 C27G55T', 'Monitor gaming curvo QHD de 27 pulgadas.', 'Samsung', 'Odyssey G5 C27G55T', 199.90, 27
    UNION ALL SELECT 'Auriculares', 'Auriculares Logitech G G435 LIGHTSPEED', 'Auriculares gaming inalámbricos con micrófono.', 'Logitech G', 'G435 LIGHTSPEED', 64.90, 0
    UNION ALL SELECT 'Auriculares', 'Auriculares Logitech G G733 LIGHTSPEED', 'Auriculares gaming inalámbricos con iluminación y micrófono.', 'Logitech G', 'G733 LIGHTSPEED', 129.90, 27
    UNION ALL SELECT 'Auriculares', 'Auriculares Logitech G PRO X 2 LIGHTSPEED', 'Auriculares gaming inalámbricos premium con micrófono.', 'Logitech G', 'PRO X 2 LIGHTSPEED', 229.90, 3
    UNION ALL SELECT 'Auriculares', 'Auriculares HyperX Cloud II', 'Auriculares gaming cerrados con micrófono desmontable.', 'HyperX', 'Cloud II', 74.90, 5
    UNION ALL SELECT 'Auriculares', 'Auriculares HyperX Cloud III', 'Auriculares gaming con micrófono para PC y consolas compatibles.', 'HyperX', 'Cloud III', 99.90, 7
    UNION ALL SELECT 'Auriculares', 'Auriculares HyperX Cloud III Wireless', 'Auriculares gaming inalámbricos con micrófono.', 'HyperX', 'Cloud III Wireless', 159.90, 9
    UNION ALL SELECT 'Auriculares', 'Auriculares SteelSeries Arctis Nova 1', 'Auriculares gaming con cable y micrófono.', 'SteelSeries', 'Arctis Nova 1', 59.90, 12
    UNION ALL SELECT 'Auriculares', 'Auriculares SteelSeries Arctis Nova 5 Wireless', 'Auriculares gaming inalámbricos con micrófono.', 'SteelSeries', 'Arctis Nova 5 Wireless', 129.90, 16
    UNION ALL SELECT 'Auriculares', 'Auriculares SteelSeries Arctis Nova 7 Wireless', 'Auriculares gaming inalámbricos multiplataforma.', 'SteelSeries', 'Arctis Nova 7 Wireless', 159.90, 22
    UNION ALL SELECT 'Auriculares', 'Auriculares SteelSeries Arctis Nova Pro Wireless', 'Auriculares gaming premium inalámbricos con estación base.', 'SteelSeries', 'Arctis Nova Pro Wireless', 319.90, 27
    UNION ALL SELECT 'Auriculares', 'Auriculares Razer BlackShark V2 X', 'Auriculares gaming con cable y micrófono.', 'Razer', 'BlackShark V2 X', 49.90, 3
    UNION ALL SELECT 'Auriculares', 'Auriculares Corsair HS80 RGB Wireless', 'Auriculares gaming inalámbricos con sonido envolvente virtual.', 'Corsair', 'HS80 RGB Wireless', 139.90, 0
) AS s
INNER JOIN categories AS c ON c.category_name = s.category_name
LEFT JOIN products AS p ON p.category_id = c.category_id AND p.brand = s.brand AND p.model = s.model
WHERE p.products_id IS NULL;
COMMIT;