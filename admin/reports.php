<?php
// admin/reports.php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/admin-guard.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrf($_POST['csrf'] ?? '')) {
    $rid    = (int)($_POST['report_id'] ?? 0);
    $action = $_POST['action'] ?? '';
    if ($action === 'resolve') $pdo->prepare("UPDATE reports SET status='resolved' WHERE id=?")->execute([$rid]);
    if ($action === 'ban_product') {
        $pid = (int)($_POST['product_id'] ?? 0);
        $pdo->prepare("UPDATE products SET status='banned' WHERE id=?")->execute([$pid]);
        $pdo->prepare("UPDATE reports SET status='resolved' WHERE id=?")->execute([$rid]);
    }
    flash('success', 'Signalement traité.');
    header('Location: /admin/reports.php'); exit;
}

$reports = $pdo->query("
    SELECT r.*, p.name AS product_name, p.status AS product_status,
           u.prenom AS reporter_prenom, u.nom AS reporter_nom
    FROM reports r
    JOIN products p ON p.id = r.product_id
    JOIN users u ON u.id = r.reporter_id
    ORDER BY r.status ASC, r.created_at DESC
")->fetchAll();

$pageTitle = 'Admin — Signalements';
$activeNav = 'admin';
include __DIR__ . '/../includes/header.php';
?>
<div class="page-wrap">
  <div class="container">
    <nav class="breadcrumb"><a href="/admin/index.php">Admin</a><span class="sep">/</span><span>Signalements</span></nav>
    <h1 class="section-title">🚩 Signalements (<?= count(array_filter($reports, fn($r)=>$r['status']==='open')) ?> ouverts)</h1>

    <?php if (empty($reports)): ?>
    <p style="color:var(--muted);">Aucun signalement.</p>
    <?php else: ?>
    <?php foreach ($reports as $r): ?>
    <div class="card" style="margin-bottom:1rem;<?= $r['status']==='resolved'?'opacity:.6;':'' ?>">
      <div style="display:flex;justify-content:space-between;flex-wrap:wrap;gap:1rem;">
        <div>
          <div style="font-weight:600;color:var(--white);">Annonce : <a href="/product.php?id=<?= $r['product_id'] ?>" target="_blank" style="color:var(--gold);"><?= e($r['product_name']) ?></a></div>
          <div style="font-size:.8rem;color:var(--muted);margin:.3rem 0;">Signalé par <?= e($r['reporter_prenom'].' '.$r['reporter_nom']) ?> — <?= timeAgo($r['created_at']) ?></div>
          <div style="font-size:.85rem;color:var(--text);">Motif : <?= e($r['reason'] ?? '—') ?></div>
          <?php if ($r['details']): ?><div style="font-size:.82rem;color:var(--muted);margin-top:.3rem;"><?= e($r['details']) ?></div><?php endif; ?>
        </div>
        <div style="display:flex;align-items:center;gap:.6rem;">
          <span class="badge badge-<?= $r['status']==='open'?'red':'muted' ?>"><?= $r['status']==='open'?'Ouvert':'Résolu' ?></span>
          <?php if ($r['status'] === 'open'): ?>
          <form method="post" style="display:flex;gap:.4rem;">
            <input type="hidden" name="csrf" value="<?= csrfToken() ?>">
            <input type="hidden" name="report_id" value="<?= $r['id'] ?>">
            <input type="hidden" name="product_id" value="<?= $r['product_id'] ?>">
            <button name="action" value="resolve" class="btn btn-secondary btn-sm">Ignorer</button>
            <?php if ($r['product_status'] !== 'banned'): ?>
            <button name="action" value="ban_product" class="btn btn-danger btn-sm" onclick="return confirm('Bannir cette annonce ?')">Bannir l\'annonce</button>
            <?php endif; ?>
          </form>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
