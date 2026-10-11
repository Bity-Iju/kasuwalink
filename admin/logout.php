<?php
require_once dirname(__DIR__) . '/includes/config.php';
unset($_SESSION['platform_admin_id'], $_SESSION['platform_admin_name'], $_SESSION['platform_admin_email']);
$_SESSION['csrf'] = bin2hex(random_bytes(32));
header('Location: login.php'); exit;
