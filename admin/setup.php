<?php
require_once __DIR__ . '/_guard.php';
$count = 0;
try { $count = (int)$pdo->query('SELECT COUNT(*) FROM admin_users')->fetchColumn(); }
catch (Throwable $e) { http_response_code(503); exit('Admin tables not found. Import phase8_migration.sql into the kasuwalink database first.'); }
if ($count > 0) { header('Location: login.php'); exit; }
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = trim((string)($_POST['full_name'] ?? ''));
    $email = strtolower(trim((string)($_POST['email'] ?? '')));
    $password = (string)($_POST['password'] ?? '');
    if (mb_strlen($name) < 2 || mb_strlen($name) > 120) $errors[] = 'Enter your name (2–120 characters).';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 190) $errors[] = 'Enter a valid email address.';
    if (strlen($password) < 12) $errors[] = 'Use a password with at least 12 characters.';
    if (!$errors) {
        try {
            $q = $pdo->prepare('INSERT INTO admin_users (full_name,email,password_hash) VALUES (?,?,?)');
            $q->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT)]);
            header('Location: login.php?created=1'); exit;
        } catch (PDOException $e) { $errors[] = 'The setup could not be completed. Verify the database and try again.'; }
    }
}
?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Initial admin setup · Kasuwalink</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"><style>body{background:#f5f7fb}.setup{max-width:520px;margin:8vh auto}.card{border:0;border-radius:18px;box-shadow:0 12px 35px #18315312}</style></head><body><div class="container setup"><div class="card p-4 p-md-5"><div class="fw-bold text-primary mb-3">KASUWALINK · PLATFORM ADMIN</div><h1 class="h3 fw-bold">Create first administrator</h1><p class="text-secondary">This one-time setup is available only while no platform admin exists.</p><?php foreach($errors as $error): ?><div class="alert alert-danger"><?=e($error)?></div><?php endforeach; ?><form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><div class="mb-3"><label class="form-label">Full name</label><input class="form-control" name="full_name" maxlength="120" required value="<?=e($_POST['full_name']??'')?>"></div><div class="mb-3"><label class="form-label">Admin email</label><input class="form-control" type="email" name="email" maxlength="190" required value="<?=e($_POST['email']??'')?>"></div><div class="mb-4"><label class="form-label">Strong password</label><input class="form-control" type="password" name="password" minlength="12" required autocomplete="new-password"><div class="form-text">Use at least 12 characters.</div></div><button class="btn btn-primary w-100">Create administrator</button></form><div class="alert alert-warning small mt-4 mb-0">After creating the first admin, remove or rename <code>admin/setup.php</code> before public deployment.</div></div></div></body></html>
