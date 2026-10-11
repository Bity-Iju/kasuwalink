-- Kasuwalink Phase 7: database-backed login throttling
-- Import into the existing kasuwalink database in phpMyAdmin.
CREATE TABLE IF NOT EXISTS login_attempts (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(190) NOT NULL,
  ip_address VARCHAR(45) NOT NULL,
  attempts INT UNSIGNED NOT NULL DEFAULT 0,
  first_attempt_at DATETIME NOT NULL,
  last_attempt_at DATETIME NOT NULL,
  blocked_until DATETIME NULL,
  UNIQUE KEY uq_login_email_ip (email, ip_address),
  KEY idx_login_blocked_until (blocked_until),
  KEY idx_login_last_attempt (last_attempt_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
