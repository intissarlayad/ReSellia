<?php
// auth/logout.php
session_start();
session_destroy();
require_once __DIR__ . '/../includes/config.php';
header('Location: ' . BASE_URL . 'auth/login.php');
exit;
