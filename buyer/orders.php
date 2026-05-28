<?php
/**
 * buyer/orders.php — Historique des commandes
 */
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth-guard.php';

$userId = $_SESSION['user_id'];
$stmt   = $pdo->prepare("
    SELECT o.*,
           COUNT(oi.id) AS item_count
    FROM orders o
    LEFT JOIN order_items oi ON oi.order_id = o.id
    WHERE o.buyer_id = ?
    GROUP BY o.id
    ORDER BY o.created_at DESC
");
$stmt->execute([$userId]);
$orders = $stmt->fetchAll();

$pageTitle = 'Mes commandes';
include __DIR__ . '/../includes/header.php';
?>

<div class="page-wrap">
  <div class="container">
    <nav class="breadcrumb"><a href="<?= BASE_URL ?>index.php">Accueil</a><span class="sep">/</span><span>Mes commandes</span></nav>
    <h1 class="section-title">📦 Mes commandes</h1>

    <?php if (empty($orders)): ?>
    <div style="text-align:center;padding:4rem;color:var(--muted);">
      <div style="font-size:3rem;margin-bottom:1rem;">📭</div>
      <p>Aucune commande pour l'instant. <a href="<?= BASE_URL ?>shop.php" style="color:var(--green-lt);">Explorer le catalogue</a></p>
    </div>
    <?php else: ?>
    <div class="table-wrap">
      <table class="table">
        <thead>
          <tr><th>#</th><th>Date</th><th>Articles</th><th>Total</th><th>Statut</th><th>Action</th></tr>
        </thead>
        <tbody>
          <?php foreach ($orders as $o): ?>
          <tr>
            <td style="color:var(--light);font-weight:600;">#<?= $o['id'] ?></td>
            <td><?= date('d/m/Y H:i', strtotime($o['created_at'])) ?></td>
            <td><?= $o['item_count'] ?> article<?= $o['item_count']>1?'s':'' ?></td>
            <td style="color:var(--gold);font-weight:600;"><?= formatPrice((float)$o['total']) ?></td>
            <td>
              <?php $sc = match($o['status']) { 'delivered'=>'green','shipped'=>'blue','confirmed'=>'gold','cancelled'=>'red',default=>'muted' }; ?>
              <span class="badge badge-<?= $sc ?>"><?= statusLabel($o['status']) ?></span>
            </td>
            <td><a href="<?= BASE_URL ?>buyer/order-detail.php?id=<?= $o['id'] ?>" class="btn btn-secondary btn-sm">Détails</a></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
