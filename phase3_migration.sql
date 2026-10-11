USE kasuwalink;


-- Phase 3: inventory movement audit ledger. Run once on an existing database.
CREATE TABLE IF NOT EXISTS inventory_movements (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 user_id BIGINT UNSIGNED NOT NULL,
 product_id BIGINT UNSIGNED NULL,
 product_name VARCHAR(160) NOT NULL,
 movement_type VARCHAR(32) NOT NULL,
 quantity_change INT NOT NULL,
 quantity_before INT NOT NULL,
 quantity_after INT NOT NULL,
 reference_type VARCHAR(32) NULL,
 reference_id BIGINT UNSIGNED NULL,
 note VARCHAR(255) NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 KEY idx_inventory_user_date (user_id, created_at),
 KEY idx_inventory_product_date (product_id, created_at),
 CONSTRAINT fk_inventory_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
 CONSTRAINT fk_inventory_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
) ENGINE=InnoDB;
