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

INSERT INTO products (category_id, products_name, products_description, brand, model, products_image, price, stock)
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

-- Fotografías locales: 145 productos con WebP y un caso sin foto (Ryzen 5 5600).
-- También actualiza las imágenes de productos ya existentes; conserva precio y stock.
UPDATE products AS p
INNER JOIN categories AS c ON c.category_id = p.category_id
INNER JOIN (
    SELECT 'Procesadores' AS category_name, 'AMD' AS brand, 'Ryzen 5 5600' AS model, NULL AS products_image
    UNION ALL SELECT 'Procesadores', 'AMD', 'Ryzen 5 7600', 'assets/products/amd-ryzen-5-7600.webp'
    UNION ALL SELECT 'Procesadores', 'AMD', 'Ryzen 5 9600X', 'assets/products/amd-ryzen-5-9600x.webp'
    UNION ALL SELECT 'Procesadores', 'AMD', 'Ryzen 7 5700X', 'assets/products/amd-ryzen-7-5700x.webp'
    UNION ALL SELECT 'Procesadores', 'AMD', 'Ryzen 7 7700X', 'assets/products/amd-ryzen-7-7700x.webp'
    UNION ALL SELECT 'Procesadores', 'AMD', 'Ryzen 7 7800X3D', 'assets/products/amd-ryzen-7-7800x3d.webp'
    UNION ALL SELECT 'Procesadores', 'AMD', 'Ryzen 7 9700X', 'assets/products/amd-ryzen-7-9700x.webp'
    UNION ALL SELECT 'Procesadores', 'AMD', 'Ryzen 7 9800X3D', 'assets/products/amd-ryzen-7-9800x3d.webp'
    UNION ALL SELECT 'Procesadores', 'AMD', 'Ryzen 9 9900X', 'assets/products/amd-ryzen-9-9900x.webp'
    UNION ALL SELECT 'Procesadores', 'AMD', 'Ryzen 9 9950X', 'assets/products/amd-ryzen-9-9950x.webp'
    UNION ALL SELECT 'Procesadores', 'Intel', 'Core i5-14400F', 'assets/products/intel-core-i5-14400f.webp'
    UNION ALL SELECT 'Procesadores', 'Intel', 'Core i7-14700K', 'assets/products/intel-core-i7-14700k.webp'
    UNION ALL SELECT 'Procesadores', 'Intel', 'Core Ultra 5 245K', 'assets/products/intel-core-ultra-5-245k.webp'
    UNION ALL SELECT 'Procesadores', 'Intel', 'Core Ultra 7 265K', 'assets/products/intel-core-ultra-7-265k.webp'
    UNION ALL SELECT 'Tarjetas gráficas', 'MSI', 'GeForce RTX 5060 Ti 16G VENTUS 2X OC PLUS', 'assets/products/msi-geforce-rtx-5060-ti-16g-ventus-2x-oc-plus.webp'
    UNION ALL SELECT 'Tarjetas gráficas', 'MSI', 'GeForce RTX 5070 12G VENTUS 2X OC', 'assets/products/msi-geforce-rtx-5070-12g-ventus-2x-oc.webp'
    UNION ALL SELECT 'Tarjetas gráficas', 'NVIDIA', 'GeForce RTX 4060', 'assets/products/nvidia-geforce-rtx-4060.webp'
    UNION ALL SELECT 'Tarjetas gráficas', 'NVIDIA', 'GeForce RTX 4060 Ti 8GB', 'assets/products/nvidia-geforce-rtx-4060-ti-8gb.webp'
    UNION ALL SELECT 'Tarjetas gráficas', 'NVIDIA', 'GeForce RTX 4070 SUPER', 'assets/products/nvidia-geforce-rtx-4070-super.webp'
    UNION ALL SELECT 'Tarjetas gráficas', 'NVIDIA', 'GeForce RTX 4080 SUPER', 'assets/products/nvidia-geforce-rtx-4080-super.webp'
    UNION ALL SELECT 'Tarjetas gráficas', 'NVIDIA', 'GeForce RTX 5060', 'assets/products/nvidia-geforce-rtx-5060.webp'
    UNION ALL SELECT 'Tarjetas gráficas', 'NVIDIA', 'GeForce RTX 5070 Ti', 'assets/products/nvidia-geforce-rtx-5070-ti.webp'
    UNION ALL SELECT 'Tarjetas gráficas', 'NVIDIA', 'GeForce RTX 5080', 'assets/products/nvidia-geforce-rtx-5080.webp'
    UNION ALL SELECT 'Tarjetas gráficas', 'AMD', 'Radeon RX 9060 XT 16GB', 'assets/products/amd-radeon-rx-9060-xt-16gb.webp'
    UNION ALL SELECT 'Tarjetas gráficas', 'AMD', 'Radeon RX 9070', 'assets/products/amd-radeon-rx-9070.webp'
    UNION ALL SELECT 'Tarjetas gráficas', 'AMD', 'Radeon RX 9070 XT', 'assets/products/amd-radeon-rx-9070-xt.webp'
    UNION ALL SELECT 'Placas base', 'ASUS', 'TUF GAMING B650-PLUS WIFI', 'assets/products/asus-tuf-gaming-b650-plus-wifi.webp'
    UNION ALL SELECT 'Placas base', 'ASUS', 'PRIME B650-PLUS', 'assets/products/asus-prime-b650-plus.webp'
    UNION ALL SELECT 'Placas base', 'ASUS', 'ROG STRIX B650E-F GAMING WIFI', 'assets/products/asus-rog-strix-b650e-f-gaming-wifi.webp'
    UNION ALL SELECT 'Placas base', 'ASUS', 'TUF GAMING X670E-PLUS WIFI', 'assets/products/asus-tuf-gaming-x670e-plus-wifi.webp'
    UNION ALL SELECT 'Placas base', 'ASUS', 'ROG STRIX X870-F GAMING WIFI', 'assets/products/asus-rog-strix-x870-f-gaming-wifi.webp'
    UNION ALL SELECT 'Placas base', 'MSI', 'MAG B650 TOMAHAWK WIFI', 'assets/products/msi-mag-b650-tomahawk-wifi.webp'
    UNION ALL SELECT 'Placas base', 'MSI', 'PRO B650-S WIFI', 'assets/products/msi-pro-b650-s-wifi.webp'
    UNION ALL SELECT 'Placas base', 'MSI', 'B760 GAMING PLUS WIFI', 'assets/products/msi-b760-gaming-plus-wifi.webp'
    UNION ALL SELECT 'Placas base', 'MSI', 'MAG Z790 TOMAHAWK WIFI', 'assets/products/msi-mag-z790-tomahawk-wifi.webp'
    UNION ALL SELECT 'Placas base', 'Gigabyte', 'B650 AORUS ELITE AX', 'assets/products/gigabyte-b650-aorus-elite-ax.webp'
    UNION ALL SELECT 'Placas base', 'Gigabyte', 'Z790 AORUS ELITE AX', 'assets/products/gigabyte-z790-aorus-elite-ax.webp'
    UNION ALL SELECT 'Placas base', 'ASRock', 'B650M Pro RS WiFi', 'assets/products/asrock-b650m-pro-rs-wifi.webp'
    UNION ALL SELECT 'Memoria RAM', 'Kingston', 'FURY Beast DDR5 32GB (2x16GB) 5600MT/s', 'assets/products/kingston-fury-beast-ddr5-32gb-2x16gb-5600mt-s.webp'
    UNION ALL SELECT 'Memoria RAM', 'Kingston', 'FURY Beast RGB DDR5 32GB (2x16GB) 6000MT/s', 'assets/products/kingston-fury-beast-rgb-ddr5-32gb-2x16gb-6000mt-s.webp'
    UNION ALL SELECT 'Memoria RAM', 'Kingston', 'FURY Renegade DDR5 32GB (2x16GB) 6400MT/s', 'assets/products/kingston-fury-renegade-ddr5-32gb-2x16gb-6400mt-s.webp'
    UNION ALL SELECT 'Memoria RAM', 'Kingston', 'FURY Beast DDR4 16GB (2x8GB) 3200MT/s', 'assets/products/kingston-fury-beast-ddr4-16gb-2x8gb-3200mt-s.webp'
    UNION ALL SELECT 'Memoria RAM', 'Corsair', 'Vengeance DDR5 32GB (2x16GB) 5600MHz', 'assets/products/corsair-vengeance-ddr5-32gb-2x16gb-5600mhz.webp'
    UNION ALL SELECT 'Memoria RAM', 'Corsair', 'Vengeance RGB DDR5 32GB (2x16GB) 6000MHz', 'assets/products/corsair-vengeance-rgb-ddr5-32gb-2x16gb-6000mhz.webp'
    UNION ALL SELECT 'Memoria RAM', 'Corsair', 'Dominator Titanium DDR5 32GB (2x16GB) 6000MHz', 'assets/products/corsair-dominator-titanium-ddr5-32gb-2x16gb-6000mhz.webp'
    UNION ALL SELECT 'Memoria RAM', 'Corsair', 'Vengeance LPX DDR4 32GB (2x16GB) 3200MHz', 'assets/products/corsair-vengeance-lpx-ddr4-32gb-2x16gb-3200mhz.webp'
    UNION ALL SELECT 'Memoria RAM', 'G.SKILL', 'Flare X5 DDR5 32GB (2x16GB) 6000MT/s', 'assets/products/g-skill-flare-x5-ddr5-32gb-2x16gb-6000mt-s.webp'
    UNION ALL SELECT 'Memoria RAM', 'G.SKILL', 'Trident Z5 Neo RGB DDR5 32GB (2x16GB) 6000MT/s', 'assets/products/g-skill-trident-z5-neo-rgb-ddr5-32gb-2x16gb-6000mt-s.webp'
    UNION ALL SELECT 'Memoria RAM', 'G.SKILL', 'Ripjaws S5 DDR5 32GB (2x16GB) 6000MT/s', 'assets/products/g-skill-ripjaws-s5-ddr5-32gb-2x16gb-6000mt-s.webp'
    UNION ALL SELECT 'Memoria RAM', 'Crucial', 'Pro DDR5 32GB (2x16GB) 5600MT/s', 'assets/products/crucial-pro-ddr5-32gb-2x16gb-5600mt-s.webp'
    UNION ALL SELECT 'Almacenamiento SSD', 'Samsung', '990 PRO 1TB', 'assets/products/samsung-990-pro-1tb.webp'
    UNION ALL SELECT 'Almacenamiento SSD', 'Samsung', '990 PRO 2TB', 'assets/products/samsung-990-pro-2tb.webp'
    UNION ALL SELECT 'Almacenamiento SSD', 'Samsung', '990 EVO Plus 1TB', 'assets/products/samsung-990-evo-plus-1tb.webp'
    UNION ALL SELECT 'Almacenamiento SSD', 'Samsung', '990 EVO Plus 2TB', 'assets/products/samsung-990-evo-plus-2tb.webp'
    UNION ALL SELECT 'Almacenamiento SSD', 'Samsung', '9100 PRO 2TB', 'assets/products/samsung-9100-pro-2tb.webp'
    UNION ALL SELECT 'Almacenamiento SSD', 'Samsung', '870 EVO 1TB', 'assets/products/samsung-870-evo-1tb.webp'
    UNION ALL SELECT 'Almacenamiento SSD', 'WD_BLACK', 'SN850X 1TB', 'assets/products/wd-black-sn850x-1tb.webp'
    UNION ALL SELECT 'Almacenamiento SSD', 'WD_BLACK', 'SN850X 2TB', 'assets/products/wd-black-sn850x-2tb.webp'
    UNION ALL SELECT 'Almacenamiento SSD', 'WD Blue', 'SN580 1TB', 'assets/products/wd-blue-sn580-1tb.webp'
    UNION ALL SELECT 'Almacenamiento SSD', 'Crucial', 'T500 1TB', 'assets/products/crucial-t500-1tb.webp'
    UNION ALL SELECT 'Almacenamiento SSD', 'Crucial', 'P3 Plus 2TB', 'assets/products/crucial-p3-plus-2tb.webp'
    UNION ALL SELECT 'Almacenamiento SSD', 'Kingston', 'NV3 1TB', 'assets/products/kingston-nv3-1tb.webp'
    UNION ALL SELECT 'Fuentes de alimentación', 'Corsair', 'RM650e 650W', 'assets/products/corsair-rm650e-650w.webp'
    UNION ALL SELECT 'Fuentes de alimentación', 'Corsair', 'RM750e 750W', 'assets/products/corsair-rm750e-750w.webp'
    UNION ALL SELECT 'Fuentes de alimentación', 'Corsair', 'RM850e 850W', 'assets/products/corsair-rm850e-850w.webp'
    UNION ALL SELECT 'Fuentes de alimentación', 'Corsair', 'RM1000e 1000W', 'assets/products/corsair-rm1000e-1000w.webp'
    UNION ALL SELECT 'Fuentes de alimentación', 'Corsair', 'RM850x 850W', 'assets/products/corsair-rm850x-850w.webp'
    UNION ALL SELECT 'Fuentes de alimentación', 'Corsair', 'SF750 750W', 'assets/products/corsair-sf750-750w.webp'
    UNION ALL SELECT 'Fuentes de alimentación', 'Seasonic', 'FOCUS GX-650 650W', 'assets/products/seasonic-focus-gx-650-650w.webp'
    UNION ALL SELECT 'Fuentes de alimentación', 'Seasonic', 'FOCUS GX-750 750W', 'assets/products/seasonic-focus-gx-750-750w.webp'
    UNION ALL SELECT 'Fuentes de alimentación', 'Seasonic', 'FOCUS GX-850 850W', 'assets/products/seasonic-focus-gx-850-850w.webp'
    UNION ALL SELECT 'Fuentes de alimentación', 'Seasonic', 'VERTEX GX-1000 1000W', 'assets/products/seasonic-vertex-gx-1000-1000w.webp'
    UNION ALL SELECT 'Fuentes de alimentación', 'be quiet!', 'Pure Power 12 M 750W', 'assets/products/be-quiet-pure-power-12-m-750w.webp'
    UNION ALL SELECT 'Fuentes de alimentación', 'be quiet!', 'Straight Power 12 1000W', 'assets/products/be-quiet-straight-power-12-1000w.webp'
    UNION ALL SELECT 'Refrigeración', 'Noctua', 'NH-D15 G2', 'assets/products/noctua-nh-d15-g2.webp'
    UNION ALL SELECT 'Refrigeración', 'Noctua', 'NH-D15 chromax.black', 'assets/products/noctua-nh-d15-chromax-black.webp'
    UNION ALL SELECT 'Refrigeración', 'Noctua', 'NH-U12S redux', 'assets/products/noctua-nh-u12s-redux.webp'
    UNION ALL SELECT 'Refrigeración', 'Noctua', 'NH-U12A chromax.black', 'assets/products/noctua-nh-u12a-chromax-black.webp'
    UNION ALL SELECT 'Refrigeración', 'Noctua', 'NH-L9i-17xx', 'assets/products/noctua-nh-l9i-17xx.webp'
    UNION ALL SELECT 'Refrigeración', 'DeepCool', 'AK400', 'assets/products/deepcool-ak400.webp'
    UNION ALL SELECT 'Refrigeración', 'DeepCool', 'AK620', 'assets/products/deepcool-ak620.webp'
    UNION ALL SELECT 'Refrigeración', 'be quiet!', 'Dark Rock Pro 5', 'assets/products/be-quiet-dark-rock-pro-5.webp'
    UNION ALL SELECT 'Refrigeración', 'be quiet!', 'Pure Rock 2', 'assets/products/be-quiet-pure-rock-2.webp'
    UNION ALL SELECT 'Refrigeración', 'Arctic', 'Freezer 36', 'assets/products/arctic-freezer-36.webp'
    UNION ALL SELECT 'Refrigeración', 'Arctic', 'Liquid Freezer III 240', 'assets/products/arctic-liquid-freezer-iii-240.webp'
    UNION ALL SELECT 'Refrigeración', 'Arctic', 'Liquid Freezer III 360', 'assets/products/arctic-liquid-freezer-iii-360.webp'
    UNION ALL SELECT 'Cajas de PC', 'Fractal Design', 'North Charcoal Black TG Dark', 'assets/products/fractal-design-north-charcoal-black-tg-dark.webp'
    UNION ALL SELECT 'Cajas de PC', 'Fractal Design', 'North Chalk White TG Clear', 'assets/products/fractal-design-north-chalk-white-tg-clear.webp'
    UNION ALL SELECT 'Cajas de PC', 'Fractal Design', 'North XL Charcoal Black TG Dark', 'assets/products/fractal-design-north-xl-charcoal-black-tg-dark.webp'
    UNION ALL SELECT 'Cajas de PC', 'Fractal Design', 'Pop Air RGB Black TG', 'assets/products/fractal-design-pop-air-rgb-black-tg.webp'
    UNION ALL SELECT 'Cajas de PC', 'Fractal Design', 'Meshify 2 Black TG', 'assets/products/fractal-design-meshify-2-black-tg.webp'
    UNION ALL SELECT 'Cajas de PC', 'Fractal Design', 'Torrent Black TG Light Tint', 'assets/products/fractal-design-torrent-black-tg-light-tint.webp'
    UNION ALL SELECT 'Cajas de PC', 'NZXT', 'H5 Flow Black', 'assets/products/nzxt-h5-flow-black.webp'
    UNION ALL SELECT 'Cajas de PC', 'NZXT', 'H6 Flow Black', 'assets/products/nzxt-h6-flow-black.webp'
    UNION ALL SELECT 'Cajas de PC', 'NZXT', 'H9 Flow Black', 'assets/products/nzxt-h9-flow-black.webp'
    UNION ALL SELECT 'Cajas de PC', 'Corsair', '4000D Airflow Black', 'assets/products/corsair-4000d-airflow-black.webp'
    UNION ALL SELECT 'Cajas de PC', 'Corsair', '5000D Airflow Black', 'assets/products/corsair-5000d-airflow-black.webp'
    UNION ALL SELECT 'Cajas de PC', 'Lian Li', 'LANCOOL 216 Black', 'assets/products/lian-li-lancool-216-black.webp'
    UNION ALL SELECT 'Teclados', 'Logitech G', 'G413 SE', 'assets/products/logitech-g-g413-se.webp'
    UNION ALL SELECT 'Teclados', 'Logitech G', 'G512 Carbon', 'assets/products/logitech-g-g512-carbon.webp'
    UNION ALL SELECT 'Teclados', 'Logitech G', 'G915 LIGHTSPEED', 'assets/products/logitech-g-g915-lightspeed.webp'
    UNION ALL SELECT 'Teclados', 'Logitech G', 'PRO X TKL LIGHTSPEED', 'assets/products/logitech-g-pro-x-tkl-lightspeed.webp'
    UNION ALL SELECT 'Teclados', 'Corsair', 'K55 RGB PRO', 'assets/products/corsair-k55-rgb-pro.webp'
    UNION ALL SELECT 'Teclados', 'Corsair', 'K70 CORE RGB', 'assets/products/corsair-k70-core-rgb.webp'
    UNION ALL SELECT 'Teclados', 'Corsair', 'K70 RGB PRO', 'assets/products/corsair-k70-rgb-pro.webp'
    UNION ALL SELECT 'Teclados', 'Corsair', 'K65 PLUS WIRELESS', 'assets/products/corsair-k65-plus-wireless.webp'
    UNION ALL SELECT 'Teclados', 'Razer', 'BlackWidow V4', 'assets/products/razer-blackwidow-v4.webp'
    UNION ALL SELECT 'Teclados', 'Razer', 'Huntsman V3 Pro TKL', 'assets/products/razer-huntsman-v3-pro-tkl.webp'
    UNION ALL SELECT 'Teclados', 'Keychron', 'K2 Pro', 'assets/products/keychron-k2-pro.webp'
    UNION ALL SELECT 'Teclados', 'SteelSeries', 'Apex Pro TKL (2023)', 'assets/products/steelseries-apex-pro-tkl-2023.webp'
    UNION ALL SELECT 'Ratones', 'Logitech G', 'G305 LIGHTSPEED', 'assets/products/logitech-g-g305-lightspeed.webp'
    UNION ALL SELECT 'Ratones', 'Logitech G', 'G502 X', 'assets/products/logitech-g-g502-x.webp'
    UNION ALL SELECT 'Ratones', 'Logitech G', 'G502 X LIGHTSPEED', 'assets/products/logitech-g-g502-x-lightspeed.webp'
    UNION ALL SELECT 'Ratones', 'Logitech G', 'G502 X PLUS', 'assets/products/logitech-g-g502-x-plus.webp'
    UNION ALL SELECT 'Ratones', 'Logitech G', 'PRO X SUPERLIGHT 2', 'assets/products/logitech-g-pro-x-superlight-2.webp'
    UNION ALL SELECT 'Ratones', 'Logitech G', 'G309 LIGHTSPEED', 'assets/products/logitech-g-g309-lightspeed.webp'
    UNION ALL SELECT 'Ratones', 'Razer', 'DeathAdder V3', 'assets/products/razer-deathadder-v3.webp'
    UNION ALL SELECT 'Ratones', 'Razer', 'DeathAdder V3 Pro', 'assets/products/razer-deathadder-v3-pro.webp'
    UNION ALL SELECT 'Ratones', 'Razer', 'Basilisk V3', 'assets/products/razer-basilisk-v3.webp'
    UNION ALL SELECT 'Ratones', 'Razer', 'Viper V3 Pro', 'assets/products/razer-viper-v3-pro.webp'
    UNION ALL SELECT 'Ratones', 'SteelSeries', 'Aerox 3 Wireless', 'assets/products/steelseries-aerox-3-wireless.webp'
    UNION ALL SELECT 'Ratones', 'Corsair', 'M65 RGB ULTRA', 'assets/products/corsair-m65-rgb-ultra.webp'
    UNION ALL SELECT 'Monitores', 'MSI', 'G274QPF E2', 'assets/products/msi-g274qpf-e2.webp'
    UNION ALL SELECT 'Monitores', 'MSI', 'MAG 274QRF QD E2', 'assets/products/msi-mag-274qrf-qd-e2.webp'
    UNION ALL SELECT 'Monitores', 'MSI', 'G255F', 'assets/products/msi-g255f.webp'
    UNION ALL SELECT 'Monitores', 'LG', '27GP850-B', 'assets/products/lg-27gp850-b.webp'
    UNION ALL SELECT 'Monitores', 'LG', '27GR75Q-B', 'assets/products/lg-27gr75q-b.webp'
    UNION ALL SELECT 'Monitores', 'LG', '27GS95QE-B', 'assets/products/lg-27gs95qe-b.webp'
    UNION ALL SELECT 'Monitores', 'Dell', 'G2724D', 'assets/products/dell-g2724d.webp'
    UNION ALL SELECT 'Monitores', 'Dell', 'P2422H', 'assets/products/dell-p2422h.webp'
    UNION ALL SELECT 'Monitores', 'Dell', 'U2723QE', 'assets/products/dell-u2723qe.webp'
    UNION ALL SELECT 'Monitores', 'ASUS', 'TUF Gaming VG27AQ1A', 'assets/products/asus-tuf-gaming-vg27aq1a.webp'
    UNION ALL SELECT 'Monitores', 'ASUS', 'ROG Swift PG27AQDM', 'assets/products/asus-rog-swift-pg27aqdm.webp'
    UNION ALL SELECT 'Monitores', 'Samsung', 'Odyssey G5 C27G55T', 'assets/products/samsung-odyssey-g5-c27g55t.webp'
    UNION ALL SELECT 'Auriculares', 'Logitech G', 'G435 LIGHTSPEED', 'assets/products/logitech-g-g435-lightspeed.webp'
    UNION ALL SELECT 'Auriculares', 'Logitech G', 'G733 LIGHTSPEED', 'assets/products/logitech-g-g733-lightspeed.webp'
    UNION ALL SELECT 'Auriculares', 'Logitech G', 'PRO X 2 LIGHTSPEED', 'assets/products/logitech-g-pro-x-2-lightspeed.webp'
    UNION ALL SELECT 'Auriculares', 'HyperX', 'Cloud II', 'assets/products/hyperx-cloud-ii.webp'
    UNION ALL SELECT 'Auriculares', 'HyperX', 'Cloud III', 'assets/products/hyperx-cloud-iii.webp'
    UNION ALL SELECT 'Auriculares', 'HyperX', 'Cloud III Wireless', 'assets/products/hyperx-cloud-iii-wireless.webp'
    UNION ALL SELECT 'Auriculares', 'SteelSeries', 'Arctis Nova 1', 'assets/products/steelseries-arctis-nova-1.webp'
    UNION ALL SELECT 'Auriculares', 'SteelSeries', 'Arctis Nova 5 Wireless', 'assets/products/steelseries-arctis-nova-5-wireless.webp'
    UNION ALL SELECT 'Auriculares', 'SteelSeries', 'Arctis Nova 7 Wireless', 'assets/products/steelseries-arctis-nova-7-wireless.webp'
    UNION ALL SELECT 'Auriculares', 'SteelSeries', 'Arctis Nova Pro Wireless', 'assets/products/steelseries-arctis-nova-pro-wireless.webp'
    UNION ALL SELECT 'Auriculares', 'Razer', 'BlackShark V2 X', 'assets/products/razer-blackshark-v2-x.webp'
    UNION ALL SELECT 'Auriculares', 'Corsair', 'HS80 RGB Wireless', 'assets/products/corsair-hs80-rgb-wireless.webp'
) AS images ON images.category_name = c.category_name AND images.brand = p.brand AND images.model = p.model
SET p.products_image = images.products_image;

COMMIT;