<?php
/**
 * admin/index.php — Dashboard administrateur
 */
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/admin-guard.php';

$stats = $pdo->query("
    SELECT
      (SELECT COUNT(*) FROM users WHERE role='student') AS students,
      (SELECT COUNT(*) FROM products WHERE status='active') AS active_products,
      (SELECT COUNT(*) FROM products WHERE status='pending') AS pending_products,
      (SELECT COUNT(*) FROM orders) AS total_orders,
      (SELECT COUNT(*) FROM reports WHERE status='open') AS open_reports
")->fetch();

$pendingProducts = $pdo->query("
    SELECT p.*, c.name AS cat_name, u.nom, u.prenom, u.filiere
    FROM products p
    LEFT JOIN categories c ON c.id = p.category_id
    LEFT JOIN users u ON u.id = p.seller_id
    WHERE p.status = 'pending'
    ORDER BY p.created_at ASC
    LIMIT 10
")->fetchAll();

$pageTitle = 'Admin Dashboard';
$activeNav = 'admin';
include __DIR__ . '/../includes/header.php';
?>

<div class="page-wrap">
  <div class="container">
    <h1 class="section-title">🔧 Dashboard Administrateur</h1>

    <!-- Stats -->
    <div class="stats-grid">
      <div class="stat-card"><div class="stat-label">Étudiants</div><div class="stat-value"><?= $stats['students'] ?></div></div>
      <div class="stat-card"><div class="stat-label">Annonces actives</div><div class="stat-value" style="color:var(--green-lt);"><?= $stats['active_products'] ?></div></div>
      <div class="stat-card"><div class="stat-label">En attente</div><div class="stat-value" style="color:var(--gold);"><?= $stats['pending_products'] ?></div></div>
      <div class="stat-card"><div class="stat-label">Commandes</div><div class="stat-value"><?= $stats['total_orders'] ?></div></div>
      <div class="stat-card"><div class="stat-label">Signalements ouverts</div><div class="stat-value" style="color:var(--red);"><?= $stats['open_reports'] ?></div></div>
    </div>

    <!-- Navigation admin -->
    <div style="display:flex;gap:1rem;flex-wrap:wrap;margin-bottom:2rem;">
      <a href="/admin/users.php"    class="btn btn-secondary">👥 Gérer les étudiants</a>
      <a href="/admin/products.php" class="btn btn-secondary">📋 Toutes les annonces</a>
      <a href="/admin/orders.php"   class="btn btn-secondary">📦 Toutes les commandes</a>
      <a href="/admin/reports.php"  class="btn btn-secondary">🚩 Signalements</a>
    </div>

    <!-- Produits en attente -->
    <div class="card">
      <div class="section-header" style="margin-bottom:1rem;">
        <h3 class="card-title">⏳ Annonces en attente de validation (<?= $stats['pending_products'] ?>)</h3>
        <a href="/admin/products.php?status=pending" class="btn btn-secondary btn-sm">Tout voir</a>
      </div>
      <?php if (empty($pendingProducts)): ?>
      <p style="color:var(--muted);font-size:.85rem;">Aucune annonce en attente.</p>
      <?php else: ?>
      <div class="table-wrap">
        <table class="table">
          <thead><tr><th>Titre</th><th>Vendeur</th><th>Catégorie</th><th>Prix</th><th>Soumis</th><th>Actions</th></tr></thead>
          <tbody>
            <?php foreach ($pendingProducts as $p): ?>
            <tr>
              <td><a href="/product.php?id=<?= $p['id'] ?>" target="_blank" style="color:var(--light);"><?= e($p['name']) ?></a></td>
              <td style="color:var(--muted);"><?= e($p['prenom'].' '.$p['nom']) ?> (<?= e($p['filiere']) ?>)</td>
              <td><?= e($p['cat_name'] ?? '—') ?></td>
              <td style="color:var(--gold);"><?= formatPrice((float)$p['price']) ?></td>
              <td style="color:var(--muted);font-size:.78rem;"><?= timeAgo($p['created_at']) ?></td>
              <td>
                <div style="display:flex;gap:.4rem;">
                  <form method="post" action="/admin/products.php">
                    <input type="hidden" name="csrf" value="<?= csrfToken() ?>" />
                    <input type="hidden" name="product_id" value="<?= $p['id'] ?>" />
                    <input type="hidden" name="action" value="approve" />
                    <button class="btn btn-primary btn-sm">✓ Valider</button>
                  </form>
                  <form method="post" action="/admin/products.php">
                    <input type="hidden" name="csrf" value="<?= csrfToken() ?>" />
                    <input type="hidden" name="product_id" value="<?= $p['id'] ?>" />
                    <input type="hidden" name="action" value="ban" />
                    <button class="btn btn-danger btn-sm">✕ Refuser</button>
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
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
