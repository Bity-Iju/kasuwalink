-- Kasuwalink Phase 10: configurable plans and server-side usage limits. Import once.
CREATE TABLE IF NOT EXISTS subscription_plan_settings (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 plan_key VARCHAR(40) NOT NULL UNIQUE,
 plan_code ENUM('pro','business') NOT NULL,
 label VARCHAR(80) NOT NULL,
 amount_kobo INT UNSIGNED NOT NULL,
 duration_days SMALLINT UNSIGNED NOT NULL DEFAULT 30,
 product_limit INT UNSIGNED NOT NULL DEFAULT 50,
 staff_limit INT UNSIGNED NOT NULL DEFAULT 1,
 features_text TEXT NULL,
 is_active TINYINT(1) NOT NULL DEFAULT 1,
 updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO subscription_plan_settings (plan_key,plan_code,label,amount_kobo,duration_days,product_limit,staff_limit,features_text,is_active) VALUES
('pro_monthly','pro','Pro Monthly',200000,30,500,5,'Advanced reports\nCSV exports and stock tools\nUp to 500 products and 5 staff accounts',1),
('business_monthly','business','Business Monthly',500000,30,0,0,'Inventory and team workflows\nUnlimited products and staff accounts\nFor larger shops and teams',1)
ON DUPLICATE KEY UPDATE plan_key=VALUES(plan_key);
