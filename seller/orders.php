<?php
/**
 * seller/orders.php — Commandes reçues par le vendeur
 */
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/seller-guard.php';

$sellerId = $_SESSION['user_id'];

// Mise à jour statut
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrf($_POST['csrf'] ?? '')) {
    $orderId   = (int)($_POST['order_id']  ?? 0);
    $newStatus = sanitize($_POST['status'] ?? '');
    $allowed   = ['confirmed','shipped','delivered','cancelled'];
    if ($orderId && in_array($newStatus, $allowed)) {
        // Vérifier que ce vendeur a bien un item dans cette commande
        $check = $pdo->prepare("SELECT 1 FROM order_items WHERE order_id=? AND seller_id=?");
        $check->execute([$orderId, $sellerId]);
        if ($check->fetchColumn()) {
            $pdo->prepare("UPDATE orders SET status=? WHERE id=?")->execute([$newStatus, $orderId]);
            flash('success', 'Statut mis à jour.');
        }
    }
    header('Location: ' . BASE_URL . 'seller/orders.php'); exit;
}

$orders = $pdo->prepare("
    SELECT DISTINCT o.*, oi.product_id, oi.qty, oi.price_unit,
           p.name AS product_name,
           u.nom AS buyer_nom, u.prenom AS buyer_prenom, u.email AS buyer_email, u.filiere
    FROM order_items oi
    JOIN orders o   ON o.id  = oi.order_id
    JOIN products p ON p.id  = oi.product_id
    JOIN users u    ON u.id  = o.buyer_id
    WHERE oi.seller_id = ?
    ORDER BY o.created_at DESC
");
$orders->execute([$sellerId]);
$orders = $orders->fetchAll();

$pageTitle = 'Commandes reçues';
$activeNav = 'seller';
include __DIR__ . '/../includes/header.php';
?>

<div class="page-wrap">
  <div class="container">
    <nav class="breadcrumb"><a href="<?= BASE_URL ?>seller/dashboard.php">Dashboard</a><span class="sep">/</span><span>Commandes reçues</span></nav>
    <h1 class="section-title">📦 Commandes reçues</h1>

    <?php if (empty($orders)): ?>
    <div style="text-align:center;padding:4rem;color:var(--muted);">
      <div style="font-size:3rem;margin-bottom:1rem;">📭</div>
      <p>Aucune commande reçue pour l'instant.</p>
    </div>
    <?php else: ?>
    <?php foreach ($orders as $o): ?>
    <div class="card" style="margin-bottom:1rem;">
      <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:1rem;">
        <div>
          <div style="font-weight:700;color:var(--white);">Commande #<?= $o['id'] ?> — <?= e($o['product_name']) ?></div>
          <div style="font-size:.8rem;color:var(--muted);margin-top:.2rem;">
            Acheteur : <?= e($o['buyer_prenom'].' '.$o['buyer_nom']) ?> (<?= e($o['filiere']) ?>) — <a href="mailto:<?= e($o['buyer_email']) ?>" style="color:var(--green-lt);"><?= e($o['buyer_email']) ?></a>
          </div>
          <div style="font-size:.8rem;color:var(--muted);">Qté : <?= $o['qty'] ?> × <?= formatPrice((float)$o['price_unit']) ?> = <strong style="color:var(--gold);"><?= formatPrice($o['price_unit']*$o['qty']) ?></strong></div>
          <div style="font-size:.78rem;color:var(--muted);margin-top:.3rem;">📍 <?= e($o['address'] ?? '—') ?></div>
          <?php if ($o['note']): ?><div style="font-size:.78rem;color:var(--muted);">💬 <?= e($o['note']) ?></div><?php endif; ?>
        </div>
        <div style="display:flex;align-items:center;gap:1rem;flex-wrap:wrap;">
          <?php $sc = match($o['status']) { 'delivered'=>'green','shipped'=>'blue','confirmed'=>'gold','cancelled'=>'red',default=>'muted' }; ?>
          <span class="badge badge-<?= $sc ?>"><?= statusLabel($o['status']) ?></span>
          <?php if (!in_array($o['status'], ['delivered','cancelled'])): ?>
          <form method="post" style="display:flex;gap:.4rem;align-items:center;">
            <input type="hidden" name="csrf" value="<?= csrfToken() ?>" />
            <input type="hidden" name="order_id" value="<?= $o['id'] ?>" />
            <select class="form-control" name="status" style="width:auto;padding:.35rem .7rem;font-size:.78rem;">
              <option value="confirmed" <?= $o['status']==='confirmed'?'selected':'' ?>>Confirmée</option>
              <option value="shipped"   <?= $o['status']==='shipped'  ?'selected':'' ?>>Expédiée</option>
              <option value="delivered" <?= $o['status']==='delivered'?'selected':'' ?>>Livrée</option>
              <option value="cancelled">Annuler</option>
            </select>
            <button type="submit" class="btn btn-primary btn-sm">Mettre à jour</button>
          </form>
          <?php endif; ?>
        </div>
      </div>
      <div style="font-size:.72rem;color:var(--muted);margin-top:.6rem;">Reçue <?= timeAgo($o['created_at']) ?></div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
