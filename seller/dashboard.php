<?php
/**
 * seller/dashboard.php — Dashboard vendeur
 */
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/seller-guard.php';

$sellerId = $_SESSION['user_id'];

// Stats vendeur
$stats = $pdo->prepare("
    SELECT
      (SELECT COUNT(*) FROM products WHERE seller_id = :s1 AND status='active') AS active_products,
      (SELECT COUNT(*) FROM products WHERE seller_id = :s2) AS total_products,
      (SELECT COUNT(DISTINCT oi.order_id) FROM order_items oi WHERE oi.seller_id = :s3) AS total_orders,
      (SELECT COALESCE(SUM(oi.price_unit * oi.qty),0) FROM order_items oi JOIN orders o ON o.id=oi.order_id WHERE oi.seller_id = :s4 AND o.status='delivered') AS total_revenue
");
$stats->execute([':s1'=>$sellerId,':s2'=>$sellerId,':s3'=>$sellerId,':s4'=>$sellerId]);
$stats = $stats->fetch();

// Dernières commandes reçues
$recentOrders = $pdo->prepare("
    SELECT o.*, oi.qty, oi.price_unit, p.name AS product_name,
           u.nom AS buyer_nom, u.prenom AS buyer_prenom
    FROM order_items oi
    JOIN orders o   ON o.id = oi.order_id
    JOIN products p ON p.id = oi.product_id
    JOIN users u    ON u.id = o.buyer_id
    WHERE oi.seller_id = ?
    ORDER BY o.created_at DESC
    LIMIT 5
");
$recentOrders->execute([$sellerId]);
$recentOrders = $recentOrders->fetchAll();

// Mes produits
$myProducts = $pdo->prepare("
    SELECT p.*, c.name AS cat_name
    FROM products p
    LEFT JOIN categories c ON c.id = p.category_id
    WHERE p.seller_id = ?
    ORDER BY p.created_at DESC
    LIMIT 6
");
$myProducts->execute([$sellerId]);
$myProducts = $myProducts->fetchAll();

$pageTitle = 'Dashboard Vendeur';
$activeNav = 'seller';
include __DIR__ . '/../includes/header.php';
?>

<div class="page-wrap">
  <div class="container">

    <!-- En-tête -->
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem;margin-bottom:2rem;">
      <div>
        <h1 style="font-family:var(--font-head);font-size:1.8rem;font-weight:800;color:var(--white);">🏪 Mon Espace Vendeur</h1>
        <p style="color:var(--muted);font-size:.88rem;">Bonjour, <?= e($_SESSION['user']['prenom']) ?> 👋</p>
      </div>
      <a href="<?= BASE_URL ?>seller/product-add.php" class="btn btn-primary">+ Nouvelle annonce</a>
    </div>

    <!-- Stats -->
    <div class="stats-grid" style="margin-bottom:2.5rem;">
      <div class="stat-card">
        <div class="stat-label">Annonces actives</div>
        <div class="stat-value"><?= $stats['active_products'] ?></div>
        <div class="stat-sub">/ <?= $stats['total_products'] ?> au total</div>
      </div>
      <div class="stat-card">
        <div class="stat-label">Commandes reçues</div>
        <div class="stat-value"><?= $stats['total_orders'] ?></div>
      </div>
      <div class="stat-card">
        <div class="stat-label">Revenus (livrées)</div>
        <div class="stat-value" style="font-size:1.5rem;"><?= formatPrice((float)$stats['total_revenue']) ?></div>
      </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:2rem;">

      <!-- Dernières commandes -->
      <div class="card">
        <div class="section-header" style="margin-bottom:1rem;">
          <h3 class="card-title">📦 Dernières commandes</h3>
          <a href="<?= BASE_URL ?>seller/orders.php" class="btn btn-secondary btn-sm">Tout voir</a>
        </div>
        <?php if (empty($recentOrders)): ?>
        <p style="color:var(--muted);font-size:.85rem;">Aucune commande reçue.</p>
        <?php else: ?>
        <?php foreach ($recentOrders as $o): ?>
        <div style="padding:.8rem 0;border-bottom:1px solid var(--border);font-size:.83rem;">
          <div style="display:flex;justify-content:space-between;align-items:center;">
            <div>
              <span style="color:var(--light);font-weight:600;"><?= e($o['product_name']) ?></span>
              <div style="color:var(--muted);font-size:.75rem;">par <?= e($o['buyer_prenom'].' '.$o['buyer_nom']) ?> · <?= timeAgo($o['created_at']) ?></div>
            </div>
            <?php $sc = match($o['status']) { 'delivered'=>'green','shipped'=>'blue','confirmed'=>'gold','cancelled'=>'red',default=>'muted' }; ?>
            <span class="badge badge-<?= $sc ?>"><?= statusLabel($o['status']) ?></span>
          </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
      </div>

      <!-- Mes produits -->
      <div class="card">
        <div class="section-header" style="margin-bottom:1rem;">
          <h3 class="card-title">📋 Mes annonces</h3>
          <a href="<?= BASE_URL ?>seller/products.php" class="btn btn-secondary btn-sm">Tout gérer</a>
        </div>
        <?php if (empty($myProducts)): ?>
        <p style="color:var(--muted);font-size:.85rem;">Aucune annonce. <a href="<?= BASE_URL ?>seller/product-add.php" style="color:var(--green-lt);">Ajouter la première</a></p>
        <?php else: ?>
        <?php foreach ($myProducts as $p):
          $imgs = json_decode($p['images']??'[]',true);
          $img  = $imgs[0] ?? '';
        ?>
        <div style="display:flex;align-items:center;gap:.8rem;padding:.7rem 0;border-bottom:1px solid var(--border);">
          <?php if ($img): ?>
          <img src="<?= e($img) ?>" alt="" style="width:40px;height:40px;object-fit:cover;border-radius:4px;flex-shrink:0;" />
          <?php else: ?>
          <div style="width:40px;height:40px;background:var(--deep);border-radius:4px;display:flex;align-items:center;justify-content:center;font-size:1.2rem;flex-shrink:0;">📦</div>
          <?php endif; ?>
          <div style="flex:1;min-width:0;">
            <div style="font-size:.83rem;color:var(--light);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= e($p['name']) ?></div>
            <div style="font-size:.72rem;color:var(--muted);"><?= formatPrice((float)$p['price']) ?> · Stock: <?= $p['stock'] ?></div>
          </div>
          <?php $sc = match($p['status']) { 'active'=>'green','pending'=>'gold',default=>'red' }; ?>
          <span class="badge badge-<?= $sc ?>" style="font-size:.65rem;"><?= statusLabel($p['status']) ?></span>
          <a href="<?= BASE_URL ?>seller/product-edit.php?id=<?= $p['id'] ?>" class="btn btn-secondary btn-sm" style="padding:.3rem .6rem;">✏️</a>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>

  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
