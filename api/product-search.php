<?php
// api/product-search.php — Recherche live (AJAX)
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$q = sanitize($_GET['q'] ?? '');
if (strlen($q) < 2) { echo json_encode([]); exit; }

$stmt = $pdo->prepare("
    SELECT p.id, p.name, p.price, p.images, c.name AS cat_name
    FROM products p
    LEFT JOIN categories c ON c.id = p.category_id
    WHERE p.status='active' AND (p.name LIKE ? OR p.description LIKE ?)
    LIMIT 6
");
$stmt->execute(["%$q%","%$q%"]);
$results = $stmt->fetchAll();

$out = [];
foreach ($results as $p) {
    $imgs = json_decode($p['images']??'[]',true);
    $out[] = ['id'=>$p['id'],'name'=>$p['name'],'price'=>formatPrice((float)$p['price']),'cat'=>$p['cat_name'],'image'=>$imgs[0]??null];
}
echo json_encode($out);
