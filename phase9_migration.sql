-- Kasuwalink Phase 9: Paystack-backed subscription payments.
-- Import ONCE into the existing kasuwalink database after Phase 8 migrations.
CREATE TABLE IF NOT EXISTS subscription_payments (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 user_id BIGINT UNSIGNED NOT NULL,
 plan ENUM('pro','business') NOT NULL,
 plan_key VARCHAR(40) NOT NULL,
 amount_kobo INT UNSIGNED NOT NULL,
 currency CHAR(3) NOT NULL DEFAULT 'NGN',
 duration_days SMALLINT UNSIGNED NOT NULL,
 reference VARCHAR(100) NOT NULL UNIQUE,
 gateway_transaction_id VARCHAR(100) NULL,
 status ENUM('pending','success','failed') NOT NULL DEFAULT 'pending',
 gateway_response JSON NULL,
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 paid_at DATETIME NULL,
 INDEX idx_subscription_payments_user_created (user_id, created_at),
 INDEX idx_subscription_payments_status_created (status, created_at),
 CONSTRAINT fk_subscription_payments_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
