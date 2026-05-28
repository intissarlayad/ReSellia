<?php
// api/cart-add.php — AJAX : ajouter au panier
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isLoggedIn()) { echo json_encode(['error'=>'Non connecté']); exit; }

$data = json_decode(file_get_contents('php://input'), true);
$pid  = (int)($data['product_id'] ?? 0);
$qty  = max(1,(int)($data['qty'] ?? 1));
$uid  = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT id, seller_id, stock, status FROM products WHERE id=?");
$stmt->execute([$pid]);
$prod = $stmt->fetch();

if (!$prod || $prod['status'] !== 'active') { echo json_encode(['error'=>'Produit indisponible']); exit; }
if ($prod['seller_id'] == $uid)             { echo json_encode(['error'=>'Tu ne peux pas acheter ton propre article']); exit; }

// Vérifier le stock en incluant la quantité déjà dans le panier
$stmt = $pdo->prepare("SELECT qty FROM cart_items WHERE user_id = ? AND product_id = ?");
$stmt->execute([$uid, $pid]);
$currentQty = (int)$stmt->fetchColumn();

if ($prod['stock'] < ($currentQty + $qty)) {
    echo json_encode(['error' => 'Stock insuffisant. ' . $prod['stock'] . ' en stock, ' . $currentQty . ' dans votre panier.']);
    exit;
}

$pdo->prepare("INSERT INTO cart_items (user_id, product_id, qty) VALUES (?,?,?) ON DUPLICATE KEY UPDATE qty = qty + VALUES(qty)")->execute([$uid, $pid, $qty]);
$count = getCartCount($pdo, $uid);
echo json_encode(['success'=>true, 'cart_count'=>$count]);
