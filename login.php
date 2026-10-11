<?php
require_once __DIR__.'/includes/config.php';
if (!empty($_SESSION['user_id'])) redirect('dashboard.php');
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    $ip = (string)($_SERVER['REMOTE_ADDR'] ?? 'unknown');
    if (login_is_rate_limited($pdo, $email, $ip)) {
        $error = 'Too many unsuccessful login attempts. Please wait 15 minutes before trying again.';
    } else {
        $stmt = $pdo->prepare('SELECT id,full_name,business_name,password_hash,is_active FROM users WHERE email=? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password_hash']) && (int)$user['is_active'] !== 1) {
            $error = 'This business account is disabled. Please contact Kasuwalink support.';
        } elseif ($user && password_verify($password, $user['password_hash'])) {
            clear_login_attempts($pdo, $email, $ip);
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int)$user['id'];
            $_SESSION['actor_id'] = (int)$user['id'];
            $_SESSION['account_type'] = 'owner';
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['business_name'] = $user['business_name'];
            unset($_SESSION['permissions']);
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
            audit_log($pdo, 'Successful login', 'user', (int)$user['id']);
            redirect('dashboard.php');
        }
        if ($error === '') {
        $stmt = $pdo->prepare('SELECT s.id,s.owner_user_id,s.full_name,s.password_hash,s.can_sales,s.can_products,s.can_customers,s.can_expenses,s.can_reports,s.can_inventory,s.can_debts,u.business_name FROM staff_users s JOIN users u ON u.id=s.owner_user_id WHERE s.email=? AND s.is_active=1 AND u.is_active=1 LIMIT 1');
        $stmt->execute([$email]);
        $staff = $stmt->fetch();
        if ($staff && password_verify($password, $staff['password_hash'])) {
            clear_login_attempts($pdo, $email, $ip);
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int)$staff['owner_user_id'];
            $_SESSION['actor_id'] = (int)$staff['id'];
            $_SESSION['account_type'] = 'staff';
            $_SESSION['full_name'] = $staff['full_name'];
            $_SESSION['business_name'] = $staff['business_name'];
            $_SESSION['permissions'] = ['sales'=>(bool)$staff['can_sales'],'products'=>(bool)$staff['can_products'],'customers'=>(bool)$staff['can_customers'],'expenses'=>(bool)$staff['can_expenses'],'reports'=>(bool)$staff['can_reports'],'inventory'=>(bool)$staff['can_inventory'],'debts'=>(bool)$staff['can_debts']];
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
            audit_log($pdo, 'Successful staff login', 'staff', (int)$staff['id']);
            redirect('dashboard.php');
        }
        record_failed_login($pdo, $email, $ip);
        $error = 'Email or password is incorrect.';
        }
    }
}
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Log in · Kasuwalink</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"><link href="assets/style.css" rel="stylesheet"></head><body><div class="auth-wrap"><div class="card auth-card"><a class="navbar-brand fw-bold mb-4" href="index.php"><span class="brand-mark">K</span> Kasuwalink</a><h1 class="h3 fw-bold">Welcome back</h1><p class="muted mb-4">Log in to your business workspace.</p><?php if($error):?><div class="alert alert-danger"><?=e($error)?></div><?php endif;?><form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><div class="mb-3"><label class="form-label" for="email">Email address</label><input class="form-control" id="email" type="email" name="email" required autocomplete="username" value="<?=e($_POST['email']??'')?>"></div><div class="mb-4"><label class="form-label" for="password">Password</label><input class="form-control" id="password" type="password" name="password" required autocomplete="current-password"></div><button class="btn btn-primary w-100 py-2">Log in</button></form><p class="text-center mt-4 mb-0">New to Kasuwalink? <a href="register.php">Create account</a></p></div></div></body></html>
