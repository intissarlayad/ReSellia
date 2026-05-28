<?php
// seller/product-delete.php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/seller-guard.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verifyCsrf($_POST['csrf'] ?? '')) {
    header('Location: ' . BASE_URL . 'seller/products.php'); exit;
}
$pid = (int)($_POST['product_id'] ?? 0);
$pdo->prepare("DELETE FROM products WHERE id = ? AND seller_id = ?")->execute([$pid, $_SESSION['user_id']]);
flash('success', 'Annonce supprimée.');
header('Location: ' . BASE_URL . 'seller/products.php');
exit;
