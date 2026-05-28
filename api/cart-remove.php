<?php
// api/cart-remove.php
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
if (!isLoggedIn()) { echo json_encode(['error'=>'Non connecté']); exit; }
$data = json_decode(file_get_contents('php://input'), true);
$pid  = (int)($data['product_id'] ?? 0);
$pdo->prepare("DELETE FROM cart_items WHERE user_id=? AND product_id=?")->execute([$_SESSION['user_id'], $pid]);
echo json_encode(['success'=>true, 'cart_count'=>getCartCount($pdo,$_SESSION['user_id'])]);
