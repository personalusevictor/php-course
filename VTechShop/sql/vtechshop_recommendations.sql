USE vtechshop;

UPDATE products SET socket = 'AM4',     memory_type = 'DDR4'
 WHERE category_id = (SELECT category_id FROM categories WHERE category_name = 'Procesadores')
   AND model IN ('Ryzen 5 5600', 'Ryzen 7 5700X');

UPDATE products SET socket = 'AM5',     memory_type = 'DDR5'
 WHERE category_id = (SELECT category_id FROM categories WHERE category_name = 'Procesadores')
   AND brand = 'AMD' AND socket IS NULL;

UPDATE products SET socket = 'LGA1700'
 WHERE category_id = (SELECT category_id FROM categories WHERE category_name = 'Procesadores')
   AND model IN ('Core i5-14400F', 'Core i7-14700K');

UPDATE products SET socket = 'LGA1851', memory_type = 'DDR5'
 WHERE category_id = (SELECT category_id FROM categories WHERE category_name = 'Procesadores')
   AND model LIKE 'Core Ultra%';

UPDATE products SET socket = 'AM5',     memory_type = 'DDR5'
 WHERE category_id = (SELECT category_id FROM categories WHERE category_name = 'Placas base')
   AND products_description LIKE '%AM5%';

UPDATE products SET socket = 'LGA1700', memory_type = 'DDR5'
 WHERE category_id = (SELECT category_id FROM categories WHERE category_name = 'Placas base')
   AND products_description LIKE '%LGA1700%';

UPDATE products SET memory_type = 'DDR5'
 WHERE category_id = (SELECT category_id FROM categories WHERE category_name = 'Memoria RAM')
   AND model LIKE '%DDR5%';

UPDATE products SET memory_type = 'DDR4'
 WHERE category_id = (SELECT category_id FROM categories WHERE category_name = 'Memoria RAM')
   AND model LIKE '%DDR4%';

UPDATE products SET socket = 'LGA1700'
 WHERE category_id = (SELECT category_id FROM categories WHERE category_name = 'Refrigeración')
   AND model = 'NH-L9i-17xx';

INSERT IGNORE INTO category_affinity (category_id, related_category_id, weight)
SELECT c1.category_id, c2.category_id, a.weight
FROM (
    SELECT 'Procesadores' AS src, 'Placas base' AS dst, 1.00 AS weight
    UNION ALL SELECT 'Procesadores', 'Refrigeración', 0.90
    UNION ALL SELECT 'Procesadores', 'Memoria RAM', 0.80
    UNION ALL SELECT 'Procesadores', 'Almacenamiento SSD', 0.40

    UNION ALL SELECT 'Placas base', 'Procesadores', 1.00
    UNION ALL SELECT 'Placas base', 'Memoria RAM', 1.00
    UNION ALL SELECT 'Placas base', 'Almacenamiento SSD', 0.60
    UNION ALL SELECT 'Placas base', 'Refrigeración', 0.50
    UNION ALL SELECT 'Placas base', 'Cajas de PC', 0.40

    UNION ALL SELECT 'Memoria RAM', 'Placas base', 0.90
    UNION ALL SELECT 'Memoria RAM', 'Procesadores', 0.70
    UNION ALL SELECT 'Memoria RAM', 'Almacenamiento SSD', 0.50

    UNION ALL SELECT 'Tarjetas gráficas', 'Fuentes de alimentación', 1.00
    UNION ALL SELECT 'Tarjetas gráficas', 'Monitores', 0.70
    UNION ALL SELECT 'Tarjetas gráficas', 'Cajas de PC', 0.50
    UNION ALL SELECT 'Tarjetas gráficas', 'Procesadores', 0.50

    UNION ALL SELECT 'Almacenamiento SSD', 'Placas base', 0.60
    UNION ALL SELECT 'Almacenamiento SSD', 'Memoria RAM', 0.50

    UNION ALL SELECT 'Fuentes de alimentación', 'Tarjetas gráficas', 0.80
    UNION ALL SELECT 'Fuentes de alimentación', 'Cajas de PC', 0.70
    UNION ALL SELECT 'Fuentes de alimentación', 'Placas base', 0.40

    UNION ALL SELECT 'Refrigeración', 'Procesadores', 0.90
    UNION ALL SELECT 'Refrigeración', 'Cajas de PC', 0.60
    UNION ALL SELECT 'Refrigeración', 'Placas base', 0.40

    UNION ALL SELECT 'Cajas de PC', 'Fuentes de alimentación', 0.80
    UNION ALL SELECT 'Cajas de PC', 'Refrigeración', 0.70
    UNION ALL SELECT 'Cajas de PC', 'Placas base', 0.60

    UNION ALL SELECT 'Teclados', 'Ratones', 1.00
    UNION ALL SELECT 'Teclados', 'Auriculares', 0.70
    UNION ALL SELECT 'Teclados', 'Monitores', 0.40

    UNION ALL SELECT 'Ratones', 'Teclados', 1.00
    UNION ALL SELECT 'Ratones', 'Auriculares', 0.70
    UNION ALL SELECT 'Ratones', 'Monitores', 0.40

    UNION ALL SELECT 'Monitores', 'Teclados', 0.70
    UNION ALL SELECT 'Monitores', 'Ratones', 0.70
    UNION ALL SELECT 'Monitores', 'Tarjetas gráficas', 0.60
    UNION ALL SELECT 'Monitores', 'Auriculares', 0.40

    UNION ALL SELECT 'Auriculares', 'Ratones', 0.80
    UNION ALL SELECT 'Auriculares', 'Teclados', 0.80
    UNION ALL SELECT 'Auriculares', 'Monitores', 0.30
) AS a
INNER JOIN categories c1 ON c1.category_name = a.src
INNER JOIN categories c2 ON c2.category_name = a.dst;
