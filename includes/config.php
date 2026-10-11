<?php
declare(strict_types=1);
// Local XAMPP defaults. Change credentials before deploying online.
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'kasuwalink');
define('DB_USER', 'root');
define('DB_PASS', '');
define('APP_NAME', 'Kasuwalink');
date_default_timezone_set('Africa/Lagos');
// Baseline browser security headers. Configure HTTPS at the hosting server before enabling HSTS.
if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
}
if (session_status() !== PHP_SESSION_ACTIVE) {
    $secure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    session_set_cookie_params(['httponly'=>true, 'secure'=>$secure, 'samesite'=>'Lax']);
    session_start();
}
try {
    $pdo = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4', DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    exit('Database connection failed. Import database.sql in phpMyAdmin and verify includes/config.php.');
}
function e(?string $value): string { return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function csrf_token(): string { if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32)); return $_SESSION['csrf']; }
function verify_csrf(): void { if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) { http_response_code(419); exit('Invalid or expired form token. Please go back and try again.'); } }
function require_login(): void {
    global $pdo;
    if (empty($_SESSION['user_id'])) { header('Location: login.php'); exit; }
    try {
        $q = $pdo->prepare('SELECT is_active FROM users WHERE id=? LIMIT 1');
        $q->execute([(int)$_SESSION['user_id']]);
        $active = $q->fetchColumn();
        if ($active === false || (int)$active !== 1) {
            unset($_SESSION['user_id'], $_SESSION['actor_id'], $_SESSION['account_type'], $_SESSION['permissions'], $_SESSION['full_name'], $_SESSION['business_name']);
            flash('This business account is disabled or unavailable. Please contact support.', 'danger');
            header('Location: login.php'); exit;
        }
        if (($_SESSION['account_type'] ?? 'owner') === 'staff') {
            $q = $pdo->prepare('SELECT is_active FROM staff_users WHERE id=? AND owner_user_id=? LIMIT 1');
            $q->execute([(int)($_SESSION['actor_id'] ?? 0), (int)$_SESSION['user_id']]);
            if ((int)$q->fetchColumn() !== 1) {
                unset($_SESSION['user_id'], $_SESSION['actor_id'], $_SESSION['account_type'], $_SESSION['permissions'], $_SESSION['full_name'], $_SESSION['business_name']);
                flash('This staff account is disabled or unavailable. Please contact the business owner.', 'danger');
                header('Location: login.php'); exit;
            }
        }
    } catch (Throwable $ignored) {
        // If account-state checks cannot run, fail closed on protected pages.
        http_response_code(503); exit('Unable to verify account status. Check the database migration and connection.');
    }
}
function money($amount): string { return '₦'.number_format((float)$amount, 2); }
function flash(string $message, string $type='success'): void { $_SESSION['flash']=['message'=>$message,'type'=>$type]; }
function redirect(string $url): never { header('Location: '.$url); exit; }


/** Staff permissions are scoped to the owning business (session user_id). */
function is_owner(): bool { return ($_SESSION['account_type'] ?? 'owner') === 'owner'; }
function require_owner(): void { require_login(); if (!is_owner()) { http_response_code(403); exit('Access denied. Only the business owner can manage staff accounts.'); } }
function require_permission(string $permission): void {
    require_login();
    if (is_owner()) return;
    $allowed = $_SESSION['permissions'] ?? [];
    if (empty($allowed[$permission])) { http_response_code(403); exit('Access denied. Your staff account does not have permission to view this page. Contact your business owner.'); }
}


/** Record a security/business event without breaking the primary workflow if logging fails. */
function audit_log(PDO $pdo, string $action, string $entityType='', ?int $entityId=null, string $details=''): void {
    try {
        $uid = (int)($_SESSION['user_id'] ?? 0);
        if ($uid < 1) return;
        $actorId = isset($_SESSION['actor_id']) ? (int)$_SESSION['actor_id'] : $uid;
        $actorType = (($_SESSION['account_type'] ?? 'owner') === 'staff') ? 'staff' : 'owner';
        $q = $pdo->prepare('INSERT INTO activity_logs (user_id, actor_id, actor_type, actor_name, action, entity_type, entity_id, details, ip_address) VALUES (?,?,?,?,?,?,?,?,?)');
        $q->execute([$uid, $actorId, $actorType, (string)($_SESSION['full_name'] ?? 'Unknown'), mb_substr($action,0,100), $entityType ?: null, $entityId, $details ? mb_substr($details,0,500) : null, mb_substr((string)($_SERVER['REMOTE_ADDR'] ?? ''),0,45) ?: null]);
    } catch (Throwable $ignored) { /* Audit failure must not break a sale or stock update. */ }
}


/** Basic database-backed login throttling. Requires phase7_migration.sql. */
function login_is_rate_limited(PDO $pdo, string $email, string $ip): bool {
    $q = $pdo->prepare('SELECT blocked_until FROM login_attempts WHERE email = ? AND ip_address = ? LIMIT 1');
    $q->execute([mb_substr(mb_strtolower(trim($email)), 0, 190), mb_substr($ip, 0, 45)]);
    $row = $q->fetch();
    return $row && !empty($row['blocked_until']) && strtotime((string)$row['blocked_until']) > time();
}
function record_failed_login(PDO $pdo, string $email, string $ip): void {
    $email = mb_substr(mb_strtolower(trim($email)), 0, 190);
    $ip = mb_substr($ip, 0, 45);
    $q = $pdo->prepare('INSERT INTO login_attempts (email, ip_address, attempts, first_attempt_at, last_attempt_at, blocked_until) VALUES (?, ?, 1, NOW(), NOW(), NULL) ON DUPLICATE KEY UPDATE attempts = IF(first_attempt_at < DATE_SUB(NOW(), INTERVAL 15 MINUTE), 1, attempts + 1), first_attempt_at = IF(first_attempt_at < DATE_SUB(NOW(), INTERVAL 15 MINUTE), NOW(), first_attempt_at), last_attempt_at = NOW(), blocked_until = IF(IF(first_attempt_at < DATE_SUB(NOW(), INTERVAL 15 MINUTE), 1, attempts + 1) >= 5, DATE_ADD(NOW(), INTERVAL 15 MINUTE), blocked_until)');
    $q->execute([$email, $ip]);
}
function clear_login_attempts(PDO $pdo, string $email, string $ip): void {
    $q = $pdo->prepare('DELETE FROM login_attempts WHERE email = ? AND ip_address = ?');
    $q->execute([mb_substr(mb_strtolower(trim($email)), 0, 190), mb_substr($ip, 0, 45)]);
}
