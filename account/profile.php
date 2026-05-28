<?php
/**
 * account/profile.php — Profil étudiant
 */
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth-guard.php';

$userId = $_SESSION['user_id'];
$errors = [];
$success = false;
$user   = currentUser();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf'] ?? '')) { $errors[] = 'Token invalide.'; }
    $nom     = sanitize($_POST['nom']     ?? '');
    $prenom  = sanitize($_POST['prenom']  ?? '');
    $filiere = sanitize($_POST['filiere'] ?? '');
    $promo   = sanitize($_POST['promo']   ?? '');

    if (strlen($nom)    < 2) $errors[] = 'Nom invalide.';
    if (strlen($prenom) < 2) $errors[] = 'Prénom invalide.';

    if (empty($errors)) {
        $pdo->prepare("UPDATE users SET nom=?,prenom=?,filiere=?,promo=? WHERE id=?")->execute([$nom,$prenom,$filiere,$promo,$userId]);
        // Rafraîchir la session
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id=?");
        $stmt->execute([$userId]);
        $_SESSION['user'] = $stmt->fetch();
        $user = $_SESSION['user'];
        $success = true;
    }
}

$filieres = ['GI'=>'GI — Génie Industriel','GMP'=>'GMP — Génie Mécanique','GE'=>'GE — Génie Électrique','GC'=>'GC — Génie Civil','GM'=>'GM — Génie des Matériaux','GCH'=>'GCH — Génie Chimique'];
$pageTitle = 'Mon Profil';
include __DIR__ . '/../includes/header.php';
?>

<div class="page-wrap">
  <div class="container-sm">
    <nav class="breadcrumb"><a href="<?= BASE_URL ?>index.php">Accueil</a><span class="sep">/</span><span>Mon Profil</span></nav>
    <h1 class="section-title">👤 Mon Profil</h1>

    <?php if ($success): ?><div class="alert alert-success">✓ Profil mis à jour avec succès.</div><?php endif; ?>
    <?php foreach ($errors as $err): ?><div class="alert alert-error">⚠ <?= e($err) ?></div><?php endforeach; ?>

    <!-- Avatar -->
    <div style="text-align:center;margin-bottom:2rem;">
      <div style="width:80px;height:80px;border-radius:50%;background:var(--green-dk);border:3px solid var(--green);display:flex;align-items:center;justify-content:center;font-weight:700;color:var(--white);font-size:2rem;margin:0 auto .8rem;">
        <?= mb_strtoupper(mb_substr($user['prenom'],0,1)) ?>
      </div>
      <div style="font-weight:700;color:var(--white);"><?= e($user['prenom'].' '.$user['nom']) ?></div>
      <div style="font-size:.8rem;color:var(--muted);"><?= e($user['email']) ?></div>
      <div style="margin-top:.5rem;">
        <span class="badge badge-<?= $user['mode_actuel']==='seller'?'green':'blue' ?>"><?= $user['mode_actuel']==='seller'?'🏪 Vendeur':'🛍 Acheteur' ?></span>
      </div>
    </div>

    <form method="post" class="card">
      <input type="hidden" name="csrf" value="<?= csrfToken() ?>" />
      <h3 class="card-title" style="margin-bottom:1.2rem;">✏️ Modifier mes informations</h3>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:.8rem;">
        <div class="form-group">
          <label class="form-label">Prénom</label>
          <input class="form-control" type="text" name="prenom" value="<?= e($user['prenom']) ?>" required />
        </div>
        <div class="form-group">
          <label class="form-label">Nom</label>
          <input class="form-control" type="text" name="nom" value="<?= e($user['nom']) ?>" required />
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Email</label>
        <input class="form-control" type="email" value="<?= e($user['email']) ?>" readonly />
        <p class="form-hint">L'email ne peut pas être modifié.</p>
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:.8rem;">
        <div class="form-group">
          <label class="form-label">Filière</label>
          <select class="form-control" name="filiere">
            <?php foreach ($filieres as $k => $v): ?>
            <option value="<?= $k ?>" <?= $user['filiere']===$k?'selected':'' ?>><?= $v ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Promotion</label>
          <input class="form-control" type="text" name="promo" value="<?= e($user['promo'] ?? '') ?>" placeholder="2026" />
        </div>
      </div>

      <button type="submit" class="btn btn-primary">Sauvegarder</button>
    </form>

    <div style="margin-top:1.5rem;display:flex;gap:1rem;flex-wrap:wrap;">
      <a href="<?= BASE_URL ?>account/switch-mode.php" class="btn btn-secondary">🔄 Changer de mode (Acheteur/Vendeur)</a>
      <a href="<?= BASE_URL ?>account/settings.php" class="btn btn-secondary">⚙️ Paramètres</a>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
