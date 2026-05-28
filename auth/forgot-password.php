<?php
/**
 * auth/forgot-password.php
 */
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$sent  = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim($_POST['email'] ?? ''));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email invalide.';
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if ($user) {
            $token = generateToken();
            $pdo->prepare("UPDATE users SET token = ? WHERE id = ?")->execute([$token, $user['id']]);
            // TODO: envoyer l'email avec le lien /auth/reset-password.php?token=$token
            // mail($email, 'Réinitialisation mot de passe — ENSAM Market', "Lien : https://yoursite.com/auth/reset-password.php?token=$token");
        }
        $sent = true; // Toujours afficher "email envoyé" pour éviter l'énumération
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1"/>
  <title>Mot de passe oublié — ENSAM Market</title>
  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css"/>
</head>
<body>
<div class="auth-wrap">
  <div class="auth-card">
    <div class="auth-logo"><span class="nav-logo">ENSAM<span class="nav-logo-dot">●</span>Market</span></div>
    <h1 class="auth-title">Mot de passe oublié</h1>
    <p class="auth-sub">Saisis ton email pour recevoir un lien de réinitialisation.</p>

    <?php if ($sent): ?>
    <div class="alert alert-success">✓ Si cet email existe, un lien t'a été envoyé.</div>
    <?php else: ?>

    <?php if ($error): ?><div class="alert alert-error">⚠ <?= e($error) ?></div><?php endif; ?>

    <form method="post">
      <div class="form-group">
        <label class="form-label">Email</label>
        <input class="form-control" type="email" name="email" placeholder="ton@email.com" required autofocus />
      </div>
      <button type="submit" class="btn btn-primary btn-full">Envoyer le lien</button>
    </form>
    <?php endif; ?>

    <p class="auth-switch"><a href="<?= BASE_URL ?>auth/login.php">← Retour à la connexion</a></p>
  </div>
</div>
</body>
</html>
