<?php
require_once __DIR__ . '/_guard.php';
if (!empty($_SESSION['platform_admin_id'])) { header('Location: index.php'); exit; }
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = strtolower(trim((string)($_POST['email'] ?? '')));
    $password = (string)($_POST['password'] ?? '');
    $q = $pdo->prepare('SELECT id,full_name,email,password_hash,is_active FROM admin_users WHERE email=? LIMIT 1');
    $q->execute([$email]); $admin = $q->fetch();
    if ($admin && (int)$admin['is_active'] === 1 && password_verify($password, $admin['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['platform_admin_id'] = (int)$admin['id'];
        $_SESSION['platform_admin_name'] = $admin['full_name'];
        $_SESSION['platform_admin_email'] = $admin['email'];
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
        $q = $pdo->prepare('UPDATE admin_users SET last_login_at=NOW() WHERE id=?'); $q->execute([(int)$admin['id']]);
        header('Location: index.php'); exit;
    }
    $error = 'Administrator email or password is incorrect, or the account is disabled.';
}
?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Admin login · Kasuwalink</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"><style>body{background:#f5f7fb}.login{max-width:460px;margin:10vh auto}.card{border:0;border-radius:18px;box-shadow:0 12px 35px #18315312}.brand{color:#153e75;font-weight:800}</style></head><body><div class="container login"><div class="card p-4 p-md-5"><div class="brand mb-3">KASUWALINK · ADMIN</div><h1 class="h3 fw-bold">Platform administrator</h1><p class="text-secondary mb-4">Sign in to manage registered businesses.</p><?php if(isset($_GET['created'])):?><div class="alert alert-success">Administrator created. Log in with your new credentials.</div><?php endif;?><?php if($error):?><div class="alert alert-danger"><?=e($error)?></div><?php endif;?><form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><div class="mb-3"><label class="form-label">Email address</label><input class="form-control" type="email" name="email" autocomplete="username" required value="<?=e($_POST['email']??'')?>"></div><div class="mb-4"><label class="form-label">Password</label><input class="form-control" type="password" name="password" autocomplete="current-password" required></div><button class="btn btn-primary w-100">Sign in</button></form><div class="small mt-4"><a href="../index.php">Back to Kasuwalink</a> · <a href="setup.php">First-time setup</a></div></div></div></body></html>
