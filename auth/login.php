<?php
/**
 * auth/login.php — Connexion étudiant
 */
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

if (isLoggedIn()) { header('Location: ' . BASE_URL . 'index.php'); exit; }

$error  = '';
$email  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf'] ?? '')) { $error = 'Token invalide, réessaie.'; }
    else {
        $email    = strtolower(trim($_POST['email']    ?? ''));
        $password = $_POST['password'] ?? '';

        if (!$email || !$password) {
            $error = 'Remplis tous les champs.';
        } else {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user']    = $user;

                $redirect = $_SESSION['redirect_after_login'] ?? BASE_URL . 'index.php';
                unset($_SESSION['redirect_after_login']);
                flash('success', 'Bon retour, ' . e($user['prenom']) . ' !');
                header('Location: ' . $redirect);
                exit;
            } else {
                $error = 'Email ou mot de passe incorrect.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1"/>
  <title>Connexion — ENSAM Market</title>
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css"/>

  <!-- CSS intégré respectant ton thème -->
  <style>
    body {
        background-color: var(--deep);
        color: var(--text);
        font-family: var(--font-body);
    }

    .auth-wrap {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        padding: 1rem;
    }

    .auth-card {
        background-color: #1a1a1a;
        padding: 2rem;
        border-radius: 12px;
        box-shadow: 0 12px 24px rgba(0,0,0,0.6);
        max-width: 420px;
        width: 100%;
        box-sizing: border-box;
    }

    .auth-logo {
        text-align: center;
        margin-bottom: 1rem;
    }

    .nav-logo {
        font-family: var(--font-head);
        font-weight: 700;
        font-size: 1.5rem;
        color: var(--white);
    }

    .nav-logo-dot {
        color: var(--green-lt);
    }

    .auth-title {
        text-align: center;
        font-size: 1.8rem;
        margin-bottom: 0.5rem;
        color: var(--white);
    }

    .auth-sub {
        text-align: center;
        font-size: 0.95rem;
        color: var(--muted);
        margin-bottom: 1.5rem;
    }

    .alert {
        padding: 0.8rem 1rem;
        border-radius: 8px;
        font-size: 0.9rem;
        margin-bottom: 1rem;
    }

    .alert-error {
        background-color: #2c2c2c;
        color: #ff5c5c;
        border: 1px solid #ff5c5c;
    }

    .alert-success {
        background-color: #1a4426;
        color: #c4ffc2;
        border: 1px solid var(--green-lt);
    }

    .form-group {
        display: flex;
        flex-direction: column;
        margin-bottom: 1rem;
    }

    .form-label {
        font-weight: 500;
        margin-bottom: 0.3rem;
        font-size: 0.95rem;
        color: var(--text);
    }

    .form-label span {
        color: #ff5c5c;
    }

    .form-control {
        background-color: #1a1a1a;
        border: 1px solid #333;
        border-radius: 8px;
        padding: 0.6rem 0.8rem;
        color: var(--white);
        font-size: 0.95rem;
        transition: all 0.2s ease;
        width: 100%;
        box-sizing: border-box;
    }

    .form-control:focus {
        outline: none;
        border-color: var(--green-lt);
        box-shadow: 0 0 5px rgba(46,196,124,0.4);
    }

    .form-hint a {
        color: var(--green-lt);
        text-decoration: none;
        font-size: 0.85rem;
    }

    .form-hint a:hover {
        text-decoration: underline;
    }

    .btn {
        cursor: pointer;
        border: none;
        border-radius: 8px;
        font-size: 1rem;
        font-weight: 600;
        padding: 0.75rem 1rem;
        transition: all 0.2s ease;
    }

    .btn-primary {
        background-color: var(--green);
        color: var(--white);
    }

    .btn-primary:hover {
        background-color: var(--green-lt);
    }

    .btn-full { width: 100%; }

    .btn-lg { font-size: 1.05rem; padding: 0.85rem 1rem; }

    .auth-switch {
        text-align: center;
        margin-top: 1rem;
        font-size: 0.9rem;
        color: var(--muted);
    }

    .auth-switch a {
        color: var(--green-lt);
        text-decoration: none;
        font-weight: 500;
    }

    .auth-switch a:hover {
        text-decoration: underline;
    }
  </style>
</head>
<body>
<div class="auth-wrap">
  <div class="auth-card">
    <div class="auth-logo">
      <span class="nav-logo">ENSAM<span class="nav-logo-dot">●</span>Market</span>
    </div>
    <h1 class="auth-title">Connexion</h1>
    <p class="auth-sub">Accède à ton espace étudiant</p>

    <?php if ($error): ?>
    <div class="alert alert-error">⚠ <?= e($error) ?></div>
    <?php endif; ?>

    <?php if (isset($_GET['registered'])): ?>
    <div class="alert alert-success">✓ Compte créé ! Tu peux te connecter.</div>
    <?php endif; ?>

    <form method="post" action="">
      <input type="hidden" name="csrf" value="<?= csrfToken() ?>" />

      <div class="form-group">
        <label class="form-label">Email <span>*</span></label>
        <input class="form-control" type="email" name="email" value="<?= e($email) ?>" placeholder="prenom.nom@ensam.ac.ma" required autofocus />
      </div>

      <div class="form-group">
        <label class="form-label">Mot de passe <span>*</span></label>
        <input class="form-control" type="password" name="password" required />
        <p class="form-hint" style="text-align:right;">
          <a href="<?= BASE_URL ?>auth/forgot-password.php">Mot de passe oublié ?</a>
        </p>
      </div>

      <button type="submit" class="btn btn-primary btn-full btn-lg">Se connecter</button>
    </form>

    <p class="auth-switch">Pas encore de compte ? <a href="<?= BASE_URL ?>auth/register.php">S'inscrire</a></p>
  </div>
</div>
</body>
</html>