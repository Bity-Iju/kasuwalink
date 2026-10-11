<?php
require_once dirname(__DIR__) . '/includes/config.php';
function require_platform_admin(): void {
    if (empty($_SESSION['platform_admin_id'])) {
        header('Location: login.php');
        exit;
    }
}
function admin_page_start(string $title): void {
    $flash = $_SESSION['admin_flash'] ?? null;
    unset($_SESSION['admin_flash']);
    ?>
    <!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?= e($title) ?> · Kasuwalink Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
    :root{--navy:#153e75;--ink:#152238;--muted:#697789;--line:#e6ebf2;--bg:#f5f7fb}
    body{background:var(--bg);color:var(--ink);font-family:Inter,system-ui,-apple-system,Segoe UI,sans-serif}
    .admin-nav{background:var(--navy)}.brand-mark{display:inline-grid;place-items:center;background:#fff;color:var(--navy);border-radius:10px;width:32px;height:32px;margin-right:8px;font-weight:800}
    .page-wrap{max-width:1200px}.card{border:1px solid var(--line);border-radius:16px;box-shadow:0 5px 18px rgba(23,42,69,.035)}.stat{font-size:1.7rem;font-weight:800;letter-spacing:-.04em}.muted{color:var(--muted)}.table>:not(caption)>*>*{padding:.85rem .7rem}.table thead th{color:var(--muted);font-size:.78rem;text-transform:uppercase;letter-spacing:.04em}
    </style></head><body><nav class="navbar navbar-expand-lg navbar-dark admin-nav"><div class="container page-wrap"><a class="navbar-brand fw-bold" href="index.php"><span class="brand-mark">K</span> Kasuwalink <span class="small fw-normal opacity-75 ms-1">Admin</span></a><button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#adminNav"><span class="navbar-toggler-icon"></span></button><div class="collapse navbar-collapse" id="adminNav"><div class="navbar-nav ms-auto"><a class="nav-link" href="index.php">Overview</a><a class="nav-link" href="businesses.php">Businesses</a><a class="nav-link" href="payments.php">Payments</a><a class="nav-link" href="plans.php">Plans</a><span class="navbar-text mx-lg-3"><?= e($_SESSION['platform_admin_name'] ?? 'Administrator') ?></span><a class="btn btn-sm btn-light" href="logout.php">Log out</a></div></div></div></nav><main class="container page-wrap py-4">
    <?php if ($flash): ?><div class="alert alert-<?= e($flash['type'] ?? 'success') ?> alert-dismissible fade show"><?= e($flash['message'] ?? '') ?><button class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>
    <?php
}
function admin_page_end(): void { echo '</main><script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script></body></html>'; }
