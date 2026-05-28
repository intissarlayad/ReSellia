<?php
/**
 * account/switch-mode.php — Basculer entre acheteur et vendeur
 */
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth-guard.php';

$userId = $_SESSION['user_id'];
$user   = currentUser();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrf($_POST['csrf'] ?? '')) {
    $newMode = $user['mode_actuel'] === 'seller' ? 'buyer' : 'seller';
    $pdo->prepare("UPDATE users SET mode_actuel=? WHERE id=?")->execute([$newMode, $userId]);
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id=?");
    $stmt->execute([$userId]);
    $_SESSION['user'] = $stmt->fetch();
    $dest = $newMode === 'seller' ? '/seller/dashboard.php' : '/index.php';
    $dest = $newMode === 'seller' ? BASE_URL . 'seller/dashboard.php' : BASE_URL . 'index.php';
    flash('success', 'Mode basculé : ' . ($newMode === 'seller' ? 'Vendeur 🏪' : 'Acheteur 🛍') . ' !');
    header('Location: ' . $dest); exit;
}

$pageTitle = 'Changer de mode';
include __DIR__ . '/../includes/header.php';
?>

<div class="page-wrap">
  <div class="container-sm" style="text-align:center;">
    <h1 class="section-title">🔄 Changer de mode</h1>
    <p style="color:var(--muted);margin-bottom:3rem;">Tu es actuellement en mode <strong style="color:var(--<?= $user['mode_actuel']==='seller'?'green-lt':'blue' ?>);"><?= $user['mode_actuel']==='seller'?'Vendeur 🏪':'Acheteur 🛍' ?></strong>.</p>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;max-width:500px;margin:0 auto 2rem;">
      <!-- Acheteur -->
      <div class="card" style="<?= $user['mode_actuel']==='buyer'?'border-color:var(--green);':'' ?>">
        <div style="font-size:2.5rem;margin-bottom:.8rem;">🛍</div>
        <h3 style="font-family:var(--font-head);color:var(--white);margin-bottom:.5rem;">Acheteur</h3>
        <p style="font-size:.82rem;color:var(--muted);">Browse le catalogue, commande des articles, gère ta wishlist.</p>
        <?php if ($user['mode_actuel']==='buyer'): ?>
        <span class="badge badge-green" style="margin-top:.8rem;">Mode actuel</span>
        <?php endif; ?>
      </div>
      <!-- Vendeur -->
      <div class="card" style="<?= $user['mode_actuel']==='seller'?'border-color:var(--green);':'' ?>">
        <div style="font-size:2.5rem;margin-bottom:.8rem;">🏪</div>
        <h3 style="font-family:var(--font-head);color:var(--white);margin-bottom:.5rem;">Vendeur</h3>
        <p style="font-size:.82rem;color:var(--muted);">Publie des annonces, gère tes ventes, suis tes revenus.</p>
        <?php if ($user['mode_actuel']==='seller'): ?>
        <span class="badge badge-green" style="margin-top:.8rem;">Mode actuel</span>
        <?php endif; ?>
      </div>
    </div>

    <form method="post">
      <input type="hidden" name="csrf" value="<?= csrfToken() ?>" />
      <button type="submit" class="btn btn-primary btn-lg">
        Passer en mode <?= $user['mode_actuel']==='seller' ? 'Acheteur 🛍' : 'Vendeur 🏪' ?>
      </button>
    </form>
    <div style="margin-top:1rem;"><a href="<?= BASE_URL ?>account/profile.php" style="color:var(--muted);font-size:.83rem;">← Retour au profil</a></div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
