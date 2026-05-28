<?php
/**
 * seller/products.php — Liste des annonces du vendeur
 */
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/seller-guard.php';

$sellerId = $_SESSION['user_id'];
$stmt     = $pdo->prepare("
    SELECT p.*, c.name AS cat_name
    FROM products p
    LEFT JOIN categories c ON c.id = p.category_id
    WHERE p.seller_id = ?
    ORDER BY p.created_at DESC
");
$stmt->execute([$sellerId]);
$products = $stmt->fetchAll();

$pageTitle = 'Mes annonces';
$activeNav = 'seller';
include __DIR__ . '/../includes/header.php';
?>

<div class="page-wrap">
  <div class="container">
    <div class="section-header">
      <div>
        <nav class="breadcrumb"><a href="<?= BASE_URL ?>seller/dashboard.php">Dashboard</a><span class="sep">/</span><span>Mes annonces</span></nav>
        <h1 class="section-title" style="margin-bottom:0;">📋 Mes annonces</h1>
      </div>
      <a href="<?= BASE_URL ?>seller/product-add.php" class="btn btn-primary">+ Nouvelle annonce</a>
    </div>

    <?php if (empty($products)): ?>
    <div style="text-align:center;padding:4rem;color:var(--muted);">
      <div style="font-size:3rem;margin-bottom:1rem;">📭</div>
      <p style="margin-bottom:1.5rem;">Aucune annonce pour l'instant.</p>
      <a href="<?= BASE_URL ?>seller/product-add.php" class="btn btn-primary">Créer ma première annonce</a>
    </div>
    <?php else: ?>
    <div class="table-wrap">
      <table class="table">
        <thead>
          <tr><th>Photo</th><th>Titre</th><th>Catégorie</th><th>Prix</th><th>Stock</th><th>État</th><th>Statut</th><th>Vues</th><th>Actions</th></tr>
        </thead>
        <tbody>
          <?php foreach ($products as $p):
            $imgs = json_decode($p['images']??'[]',true);
            $img  = $imgs[0] ?? '';
          ?>
          <tr>
            <td>
              <?php if ($img): ?>
              <img src="<?= e($img) ?>" alt="" style="width:44px;height:44px;object-fit:cover;border-radius:4px;" />
              <?php else: ?>
              <div style="width:44px;height:44px;background:var(--deep);border-radius:4px;display:flex;align-items:center;justify-content:center;">📦</div>
              <?php endif; ?>
            </td>
            <td><a href="<?= BASE_URL ?>product.php?id=<?= $p['id'] ?>" style="color:var(--light);font-weight:600;" target="_blank"><?= e($p['name']) ?></a></td>
            <td style="color:var(--muted);"><?= e($p['cat_name'] ?? '—') ?></td>
            <td style="color:var(--gold);font-weight:600;"><?= formatPrice((float)$p['price']) ?></td>
            <td><?= $p['stock'] ?></td>
            <td>
              <span class="badge badge-<?= $p['condition_p']==='neuf'?'green':($p['condition_p']==='bon_etat'?'blue':'muted') ?>">
                <?= conditionLabel($p['condition_p']) ?>
              </span>
            </td>
            <td>
              <?php $sc = match($p['status']) { 'active'=>'green','pending'=>'gold',default=>'red' }; ?>
              <span class="badge badge-<?= $sc ?>"><?= statusLabel($p['status']) ?></span>
            </td>
            <td><?= $p['views'] ?></td>
            <td>
              <div style="display:flex;gap:.4rem;">
                <a href="<?= BASE_URL ?>seller/product-edit.php?id=<?= $p['id'] ?>" class="btn btn-secondary btn-sm">✏️</a>
                <form method="post" action="<?= BASE_URL ?>seller/product-delete.php" onsubmit="return confirm('Supprimer cette annonce ?')">
                  <input type="hidden" name="csrf" value="<?= csrfToken() ?>" />
                  <input type="hidden" name="product_id" value="<?= $p['id'] ?>" />
                  <button type="submit" class="btn btn-danger btn-sm">🗑</button>
                </form>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
