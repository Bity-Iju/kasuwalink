-- Kasuwalink Phase 6 migration. Run once against the existing kasuwalink database.
CREATE TABLE IF NOT EXISTS activity_logs (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 user_id BIGINT UNSIGNED NOT NULL,
 actor_id BIGINT UNSIGNED NOT NULL,
 actor_type ENUM('owner','staff') NOT NULL DEFAULT 'owner',
 actor_name VARCHAR(120) NOT NULL,
 action VARCHAR(100) NOT NULL,
 entity_type VARCHAR(60) NULL,
 entity_id BIGINT UNSIGNED NULL,
 details VARCHAR(500) NULL,
 ip_address VARCHAR(45) NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 KEY idx_activity_user_date (user_id, created_at),
 KEY idx_activity_actor (user_id, actor_type, actor_id),
 CONSTRAINT fk_activity_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;
