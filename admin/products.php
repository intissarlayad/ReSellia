<?php
/**
 * admin/products.php — Modération des annonces
 */
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/admin-guard.php';

// Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrf($_POST['csrf'] ?? '')) {
    $pid    = (int)($_POST['product_id'] ?? 0);
    $action = $_POST['action'] ?? '';
    $newStatus = match($action) { 'approve'=>'active','ban'=>'banned', default=>null };
    if ($pid && $newStatus) {
        $pdo->prepare("UPDATE products SET status=? WHERE id=?")->execute([$newStatus, $pid]);
        flash('success', 'Annonce ' . ($newStatus==='active' ? 'validée' : 'bannie') . '.');
    }
    header('Location: /admin/products.php'); exit;
}

$statusFilter = $_GET['status'] ?? '';
$where  = $statusFilter ? "WHERE p.status = '" . $pdo->quote($statusFilter) . "'" : '';
$products = $pdo->query("
    SELECT p.*, c.name AS cat_name, u.nom, u.prenom, u.filiere
    FROM products p
    LEFT JOIN categories c ON c.id = p.category_id
    LEFT JOIN users u ON u.id = p.seller_id
    $where
    ORDER BY p.created_at DESC
    LIMIT 50
")->fetchAll();

$pageTitle = 'Admin — Annonces';
$activeNav = 'admin';
include __DIR__ . '/../includes/header.php';
?>

<div class="page-wrap">
  <div class="container">
    <nav class="breadcrumb"><a href="/admin/index.php">Admin</a><span class="sep">/</span><span>Annonces</span></nav>
    <div class="section-header">
      <h1 class="section-title" style="margin-bottom:0;">📋 Toutes les annonces</h1>
      <div style="display:flex;gap:.5rem;">
        <a href="/admin/products.php"                class="btn btn-sm <?= !$statusFilter?'btn-primary':'btn-secondary' ?>">Toutes</a>
        <a href="/admin/products.php?status=pending" class="btn btn-sm <?= $statusFilter==='pending'?'btn-primary':'btn-secondary' ?>">En attente</a>
        <a href="/admin/products.php?status=active"  class="btn btn-sm <?= $statusFilter==='active'?'btn-primary':'btn-secondary' ?>">Actives</a>
        <a href="/admin/products.php?status=banned"  class="btn btn-sm <?= $statusFilter==='banned'?'btn-primary':'btn-secondary' ?>">Bannies</a>
      </div>
    </div>

    <div class="table-wrap">
      <table class="table">
        <thead><tr><th>ID</th><th>Titre</th><th>Vendeur</th><th>Prix</th><th>Statut</th><th>Date</th><th>Actions</th></tr></thead>
        <tbody>
          <?php foreach ($products as $p): ?>
          <tr>
            <td style="color:var(--muted);"><?= $p['id'] ?></td>
            <td><a href="/product.php?id=<?= $p['id'] ?>" target="_blank" style="color:var(--light);"><?= e($p['name']) ?></a></td>
            <td style="color:var(--muted);"><?= e($p['prenom'].' '.$p['nom']) ?></td>
            <td style="color:var(--gold);"><?= formatPrice((float)$p['price']) ?></td>
            <td><?php $sc=match($p['status']){'active'=>'green','pending'=>'gold',default=>'red'}; ?><span class="badge badge-<?= $sc ?>"><?= statusLabel($p['status']) ?></span></td>
            <td style="color:var(--muted);font-size:.78rem;"><?= date('d/m/Y', strtotime($p['created_at'])) ?></td>
            <td>
              <div style="display:flex;gap:.4rem;flex-wrap:wrap;">
                <?php if ($p['status'] !== 'active'): ?>
                <form method="post"><input type="hidden" name="csrf" value="<?= csrfToken() ?>"><input type="hidden" name="product_id" value="<?= $p['id'] ?>"><input type="hidden" name="action" value="approve"><button class="btn btn-primary btn-sm">✓</button></form>
                <?php endif; ?>
                <?php if ($p['status'] !== 'banned'): ?>
                <form method="post"><input type="hidden" name="csrf" value="<?= csrfToken() ?>"><input type="hidden" name="product_id" value="<?= $p['id'] ?>"><input type="hidden" name="action" value="ban"><button class="btn btn-danger btn-sm">✕</button></form>
                <?php endif; ?>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
