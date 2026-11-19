-- Preparación del motor de recomendaciones para una instalación nueva.
-- Ejecutar una sola vez, después de vtechshop_database.sql y antes de
-- vtechshop_recommendations.sql. No modifica precios, stock ni pedidos.
USE vtechshop;

ALTER TABLE products
    ADD COLUMN socket VARCHAR(32) NULL,
    ADD COLUMN memory_type VARCHAR(32) NULL;

CREATE TABLE category_affinity (
    category_id INT NOT NULL,
    related_category_id INT NOT NULL,
    weight DECIMAL(5, 3) NOT NULL DEFAULT 1.000,
    PRIMARY KEY (category_id, related_category_id),
    CONSTRAINT fk_affinity_source FOREIGN KEY (category_id)
        REFERENCES categories(category_id) ON DELETE CASCADE,
    CONSTRAINT fk_affinity_target FOREIGN KEY (related_category_id)
        REFERENCES categories(category_id) ON DELETE CASCADE
) ENGINE=InnoDB;
