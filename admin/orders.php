<?php
// admin/orders.php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/admin-guard.php';

$orders = $pdo->query("
    SELECT o.*, u.nom, u.prenom, u.filiere, COUNT(oi.id) AS item_count
    FROM orders o
    JOIN users u ON u.id = o.buyer_id
    LEFT JOIN order_items oi ON oi.order_id = o.id
    GROUP BY o.id
    ORDER BY o.created_at DESC
    LIMIT 60
")->fetchAll();

$pageTitle = 'Admin — Commandes';
$activeNav = 'admin';
include __DIR__ . '/../includes/header.php';
?>
<div class="page-wrap">
  <div class="container">
    <nav class="breadcrumb"><a href="/admin/index.php">Admin</a><span class="sep">/</span><span>Commandes</span></nav>
    <h1 class="section-title">📦 Toutes les commandes</h1>
    <div class="table-wrap">
      <table class="table">
        <thead><tr><th>#</th><th>Acheteur</th><th>Articles</th><th>Total</th><th>Statut</th><th>Date</th></tr></thead>
        <tbody>
          <?php foreach ($orders as $o): ?>
          <tr>
            <td style="color:var(--light);font-weight:600;">#<?= $o['id'] ?></td>
            <td><?= e($o['prenom'].' '.$o['nom']) ?> <span style="color:var(--muted);font-size:.75rem;">(<?= e($o['filiere']) ?>)</span></td>
            <td><?= $o['item_count'] ?></td>
            <td style="color:var(--gold);"><?= formatPrice((float)$o['total']) ?></td>
            <td><?php $sc=match($o['status']){'delivered'=>'green','shipped'=>'blue','confirmed'=>'gold','cancelled'=>'red',default=>'muted'}; ?><span class="badge badge-<?= $sc ?>"><?= statusLabel($o['status']) ?></span></td>
            <td style="color:var(--muted);font-size:.78rem;"><?= date('d/m/Y H:i', strtotime($o['created_at'])) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
