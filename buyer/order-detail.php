<?php
/**
 * buyer/order-detail.php — Détail d'une commande
 */
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth-guard.php';

$userId  = $_SESSION['user_id'];
$orderId = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND buyer_id = ?");
$stmt->execute([$orderId, $userId]);
$order = $stmt->fetch();
if (!$order) { header('Location: /buyer/orders.php'); exit; }

$items = $pdo->prepare("
    SELECT oi.*, p.name, p.images, u.nom, u.prenom, u.email
    FROM order_items oi
    JOIN products p ON p.id = oi.product_id
    JOIN users u    ON u.id = oi.seller_id
    WHERE oi.order_id = ?
");
$items->execute([$orderId]);
$items = $items->fetchAll();

$pageTitle = 'Commande #' . $orderId;
include __DIR__ . '/../includes/header.php';
?>

<div class="page-wrap">
  <div class="container-md">
    <nav class="breadcrumb">
      <a href="<?= BASE_URL ?>index.php">Accueil</a><span class="sep">/</span>
      <a href="<?= BASE_URL ?>buyer/orders.php">Mes commandes</a><span class="sep">/</span>
      <span>#<?= $orderId ?></span>
    </nav>

    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem;margin-bottom:2rem;">
      <h1 class="section-title" style="margin-bottom:0;">Commande #<?= $orderId ?></h1>
      <?php $sc = match($order['status']) { 'delivered'=>'green','shipped'=>'blue','confirmed'=>'gold','cancelled'=>'red',default=>'muted' }; ?>
      <span class="badge badge-<?= $sc ?>" style="font-size:.85rem;padding:.4rem 1rem;"><?= statusLabel($order['status']) ?></span>
    </div>

    <div class="card" style="margin-bottom:1.5rem;">
      <h3 class="card-title" style="margin-bottom:1rem;">📋 Détails</h3>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:.8rem;font-size:.85rem;">
        <div><span style="color:var(--muted);">Date : </span><?= date('d/m/Y à H:i', strtotime($order['created_at'])) ?></div>
        <div><span style="color:var(--muted);">Total : </span><strong style="color:var(--gold);"><?= formatPrice((float)$order['total']) ?></strong></div>
        <div style="grid-column:1/-1;"><span style="color:var(--muted);">Adresse : </span><?= e($order['address']) ?></div>
        <?php if ($order['note']): ?><div style="grid-column:1/-1;"><span style="color:var(--muted);">Note : </span><?= e($order['note']) ?></div><?php endif; ?>
      </div>
    </div>

    <div class="card">
      <h3 class="card-title" style="margin-bottom:1.2rem;">🛍 Articles commandés</h3>
      <?php foreach ($items as $item):
        $imgs = json_decode($item['images']??'[]',true);
        $img  = $imgs[0] ?? 'https://via.placeholder.com/100x100/1a1a1a/555?text=+';
      ?>
      <div style="display:flex;gap:1rem;padding:1rem 0;border-bottom:1px solid var(--border);">
        <img src="<?= e($img) ?>" alt="" style="width:72px;height:72px;object-fit:cover;border-radius:var(--radius);flex-shrink:0;" />
        <div style="flex:1;">
          <div style="font-weight:600;color:var(--light);"><?= e($item['name']) ?></div>
          <div style="font-size:.78rem;color:var(--muted);margin-top:.2rem;">Vendu par <?= e($item['prenom'].' '.$item['nom']) ?> — <a href="mailto:<?= e($item['email']) ?>" style="color:var(--green-lt);"><?= e($item['email']) ?></a></div>
          <div style="display:flex;justify-content:space-between;margin-top:.5rem;">
            <span style="font-size:.82rem;color:var(--text);">Qté : <?= $item['qty'] ?> × <?= formatPrice((float)$item['price_unit']) ?></span>
            <span style="font-weight:600;color:var(--gold);"><?= formatPrice($item['price_unit'] * $item['qty']) ?></span>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
      <div style="display:flex;justify-content:flex-end;padding-top:1rem;">
        <strong style="color:var(--white);font-size:1.05rem;">Total : <span style="color:var(--gold);"><?= formatPrice((float)$order['total']) ?></span></strong>
      </div>
    </div>

    <div style="margin-top:1.5rem;">
      <a href="<?= BASE_URL ?>buyer/orders.php" class="btn btn-secondary">← Retour à mes commandes</a>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
