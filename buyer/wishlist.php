<?php
/**
 * buyer/wishlist.php — Favoris
 */
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth-guard.php';

$userId = $_SESSION['user_id'];
$stmt   = $pdo->prepare("
    SELECT p.*, c.name AS cat_name, u.nom, u.prenom
    FROM wishlist w
    JOIN products p ON p.id = w.product_id
    LEFT JOIN categories c ON c.id = p.category_id
    LEFT JOIN users u ON u.id = p.seller_id
    WHERE w.user_id = ? AND p.status = 'active'
    ORDER BY w.added_at DESC
");
$stmt->execute([$userId]);
$wishlist = $stmt->fetchAll();

$pageTitle = 'Ma Wishlist';
include __DIR__ . '/../includes/header.php';
?>

<div class="page-wrap">
  <div class="container">
    <nav class="breadcrumb"><a href="<?= BASE_URL ?>index.php">Accueil</a><span class="sep">/</span><span>Ma Wishlist</span></nav>
    <h1 class="section-title">❤️ Ma Wishlist <small style="font-size:.9rem;color:var(--muted);font-family:var(--font-body);">(<?= count($wishlist) ?>)</small></h1>

    <?php if (empty($wishlist)): ?>
    <div style="text-align:center;padding:4rem;color:var(--muted);">
      <div style="font-size:3rem;margin-bottom:1rem;">🤍</div>
      <p>Ta wishlist est vide. <a href="<?= BASE_URL ?>shop.php" style="color:var(--green-lt);">Explorer les annonces</a></p>
    </div>
    <?php else: ?>
    <div class="products-grid">
      <?php foreach ($wishlist as $p):
        $imgs = json_decode($p['images']??'[]',true);
        $img  = $imgs[0] ?? 'https://via.placeholder.com/400x400/1a1a1a/555?text=Photo';
      ?>
      <div class="product-card" data-reveal>
        <div class="product-img-wrap">
          <a href="<?= BASE_URL ?>product.php?id=<?= $p['id'] ?>"><img src="<?= e($img) ?>" alt="<?= e($p['name']) ?>" loading="lazy"/></a>
          <button class="product-wish active" data-wish="<?= $p['id'] ?>">❤️</button>
        </div>
        <div class="product-info">
          <div class="product-cat"><?= e($p['cat_name']) ?></div>
          <div class="product-name"><?= e($p['name']) ?></div>
          <div class="product-seller">par <?= e($p['prenom'].' '.$p['nom']) ?></div>
          <div class="product-price"><?= formatPrice((float)$p['price']) ?></div>
        </div>
        <div class="product-footer"><a href="<?= BASE_URL ?>product.php?id=<?= $p['id'] ?>" class="btn btn-secondary btn-sm btn-full">Voir l'annonce</a></div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
