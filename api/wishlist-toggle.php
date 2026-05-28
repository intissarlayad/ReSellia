<?php
// api/wishlist-toggle.php
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isLoggedIn()) {
    echo json_encode(['error' => 'Non connecté', 'action' => 'login_required']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$pid  = (int)($data['product_id'] ?? 0);
$uid  = $_SESSION['user_id'];

if (!$pid) {
    echo json_encode(['error' => 'ID produit manquant']);
    exit;
}

// Vérifier que le produit existe et n'appartient pas à l'utilisateur
$stmt = $pdo->prepare("SELECT seller_id FROM products WHERE id = ?");
$stmt->execute([$pid]);
$product = $stmt->fetch();

if (!$product || $product['seller_id'] == $uid) {
    echo json_encode(['error' => 'Action non autorisée.']);
    exit;
}

// Vérifier si l'article est déjà dans la wishlist
$stmt = $pdo->prepare("SELECT 1 FROM wishlist WHERE user_id = ? AND product_id = ?");
$stmt->execute([$uid, $pid]);

if ($stmt->fetchColumn()) {
    $pdo->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?")->execute([$uid, $pid]);
    echo json_encode(['success' => true, 'action' => 'removed']);
} else {
    $pdo->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)")->execute([$uid, $pid]);
    echo json_encode(['success' => true, 'action' => 'added']);
}
