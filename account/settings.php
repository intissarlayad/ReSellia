<?php
/**
 * account/settings.php — Changer le mot de passe
 */
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth-guard.php';

$userId  = $_SESSION['user_id'];
$errors  = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf'] ?? '')) { $errors[] = 'Token invalide.'; }
    $current = $_POST['current_password'] ?? '';
    $new     = $_POST['new_password']     ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id=?");
    $stmt->execute([$userId]);
    $hash = $stmt->fetchColumn();

    if (!password_verify($current, $hash)) $errors[] = 'Mot de passe actuel incorrect.';
    if (strlen($new) < 8)                  $errors[] = 'Nouveau mot de passe trop court (min 8 caractères).';
    if ($new !== $confirm)                 $errors[] = 'Les mots de passe ne correspondent pas.';

    if (empty($errors)) {
        $pdo->prepare("UPDATE users SET password_hash=? WHERE id=?")->execute([password_hash($new, PASSWORD_BCRYPT, ['cost'=>12]), $userId]);
        $success = true;
    }
}

$pageTitle = 'Paramètres';
include __DIR__ . '/../includes/header.php';
?>

<div class="page-wrap">
  <div class="container-sm">
    <nav class="breadcrumb"><a href="<?= BASE_URL ?>account/profile.php">Mon Profil</a><span class="sep">/</span><span>Paramètres</span></nav>
    <h1 class="section-title">⚙️ Paramètres du compte</h1>

    <?php if ($success): ?><div class="alert alert-success">✓ Mot de passe modifié avec succès.</div><?php endif; ?>
    <?php foreach ($errors as $err): ?><div class="alert alert-error">⚠ <?= e($err) ?></div><?php endforeach; ?>

    <form method="post" class="card">
      <input type="hidden" name="csrf" value="<?= csrfToken() ?>" />
      <h3 class="card-title" style="margin-bottom:1.2rem;">🔒 Changer le mot de passe</h3>

      <div class="form-group">
        <label class="form-label">Mot de passe actuel <span>*</span></label>
        <input class="form-control" type="password" name="current_password" required />
      </div>
      <div class="form-group">
        <label class="form-label">Nouveau mot de passe <span>*</span></label>
        <input class="form-control" type="password" name="new_password" placeholder="Minimum 8 caractères" required />
      </div>
      <div class="form-group">
        <label class="form-label">Confirmer le nouveau mot de passe <span>*</span></label>
        <input class="form-control" type="password" name="confirm_password" required />
      </div>

      <button type="submit" class="btn btn-primary">Modifier le mot de passe</button>
    </form>

    <div style="margin-top:1.5rem;">
      <a href="<?= BASE_URL ?>account/profile.php" class="btn btn-secondary">← Retour au profil</a>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
