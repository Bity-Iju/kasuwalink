-- Kasuwalink Phase 4: staff accounts and role permissions
-- Import this file into the existing kasuwalink database in phpMyAdmin.
CREATE TABLE IF NOT EXISTS staff_users (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 owner_user_id BIGINT UNSIGNED NOT NULL,
 full_name VARCHAR(120) NOT NULL,
 email VARCHAR(190) NOT NULL UNIQUE,
 password_hash VARCHAR(255) NOT NULL,
 can_sales TINYINT(1) NOT NULL DEFAULT 0,
 can_products TINYINT(1) NOT NULL DEFAULT 0,
 can_customers TINYINT(1) NOT NULL DEFAULT 0,
 can_expenses TINYINT(1) NOT NULL DEFAULT 0,
 can_reports TINYINT(1) NOT NULL DEFAULT 0,
 can_inventory TINYINT(1) NOT NULL DEFAULT 0,
 can_debts TINYINT(1) NOT NULL DEFAULT 0,
 is_active TINYINT(1) NOT NULL DEFAULT 1,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 INDEX idx_staff_owner (owner_user_id),
 CONSTRAINT fk_staff_owner FOREIGN KEY (owner_user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
