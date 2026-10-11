<?php require_once __DIR__.'/includes/config.php'; redirect(!empty($_SESSION['user_id'])?'dashboard.php':'register.php');
