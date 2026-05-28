<?php
/**
 * auth/register.php — Inscription étudiant ENSAM
 */
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

if (isLoggedIn()) { header('Location: ' . BASE_URL . 'index.php'); exit; }

$errors = [];
$values = ['nom'=>'','prenom'=>'','email'=>'','filiere'=>'','promo'=>''];

$filieres = ['GI'=>'Génie Industriel','GMP'=>'Génie Mécanique et Productique','GE'=>'Génie Électrique','GC'=>'Génie Civil','GM'=>'Génie des Matériaux','GCH'=>'Génie Chimique'];
$promos   = ['2024','2025','2026','2027','2028'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf'] ?? '')) { $errors[] = 'Token invalide.'; }

    $values['nom']     = sanitize($_POST['nom']     ?? '');
    $values['prenom']  = sanitize($_POST['prenom']  ?? '');
    $values['email']   = strtolower(trim($_POST['email'] ?? ''));
    $values['filiere'] = sanitize($_POST['filiere'] ?? '');
    $values['promo']   = sanitize($_POST['promo']   ?? '');
    $password          = $_POST['password']          ?? '';
    $confirm           = $_POST['confirm']           ?? '';

    if (strlen($values['nom'])    < 2) $errors[] = 'Nom invalide.';
    if (strlen($values['prenom']) < 2) $errors[] = 'Prénom invalide.';
    if (!filter_var($values['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Email invalide.';
    if (strlen($password) < 8)    $errors[] = 'Mot de passe : minimum 8 caractères.';
    if ($password !== $confirm)   $errors[] = 'Les mots de passe ne correspondent pas.';
    if (!isset($filieres[$values['filiere']])) $errors[] = 'Filière invalide.';
    if (empty($values['promo']) || !in_array($values['promo'], $promos)) $errors[] = 'Promotion invalide.';

    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$values['email']]);
        if ($stmt->fetch()) {
            $errors[] = 'Cet email est déjà utilisé.';
        } else {
            $hash  = password_hash($password, PASSWORD_BCRYPT, ['cost'=>12]);
            $token = generateToken();

            $stmt = $pdo->prepare("INSERT INTO users (nom,prenom,email,password_hash,filiere,promo,token,role,mode_actuel) VALUES (?,?,?,?,?,?,?,?,?)");
            $stmt->execute([$values['nom'],$values['prenom'],$values['email'],$hash,$values['filiere'],$values['promo'],$token,'student','buyer']);

            $newId = (int)$pdo->lastInsertId();

            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$newId]);
            $user = $stmt->fetch();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user']    = $user;

            flash('success', 'Bienvenue ' . e($user['prenom']) . ' ! Votre compte a été créé.');
            header('Location: ' . BASE_URL . 'index.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" /><meta name="viewport" content="width=device-width,initial-scale=1"/>
  <title>Inscription — ENSAM Market</title>
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css"/>

  <!-- CSS intégré respectant ton thème et grid corrigé -->
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
        max-width: 480px;
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

    .form-hint {
        font-size: 0.8rem;
        color: var(--muted);
        margin-top: 0.25rem;
    }

    /* Grid Filière / Promotion corrigé */
    .auth-card form > div[style*="grid-template-columns"] {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.8rem;
        width: 100%;
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
    <h1 class="auth-title">Créer un compte</h1>
    <p class="auth-sub">Rejoins la communauté étudiante ENSAM</p>

    <?php foreach ($errors as $e): ?>
    <div class="alert alert-error">⚠ <?= e($e) ?></div>
    <?php endforeach; ?>

    <form method="post" action="">
      <input type="hidden" name="csrf" value="<?= csrfToken() ?>" />

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:.8rem;">
        <div class="form-group">
          <label class="form-label">Prénom <span>*</span></label>
          <input class="form-control" type="text" name="prenom" value="<?= e($values['prenom']) ?>" required autofocus />
        </div>
        <div class="form-group">
          <label class="form-label">Nom <span>*</span></label>
          <input class="form-control" type="text" name="nom" value="<?= e($values['nom']) ?>" required />
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Email <span>*</span></label>
        <input class="form-control" type="email" name="email" value="<?= e($values['email']) ?>" placeholder="prenom.nom@ensam.ac.ma" required />
        <p class="form-hint">De préférence ton email ENSAM</p>
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:.8rem;">
        <div class="form-group">
          <label class="form-label">Filière <span>*</span></label>
          <select class="form-control" name="filiere" required>
            <option value="">— Choisir —</option>
            <?php foreach ($filieres as $k => $v): ?>
            <option value="<?= e($k) ?>" <?= $values['filiere']===$k ? 'selected':'' ?>><?= e($k) ?> — <?= e($v) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Promotion <span>*</span></label>
          <select class="form-control" name="promo" required>
            <option value="">— Année —</option>
            <?php foreach ($promos as $p): ?>
            <option value="<?= $p ?>" <?= $values['promo']===$p ? 'selected':'' ?>><?= $p ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Mot de passe <span>*</span></label>
        <input class="form-control" type="password" name="password" placeholder="Minimum 8 caractères" required />
      </div>

      <div class="form-group">
        <label class="form-label">Confirmer le mot de passe <span>*</span></label>
        <input class="form-control" type="password" name="confirm" required />
      </div>

      <button type="submit" class="btn btn-primary btn-full btn-lg">Créer mon compte 🎓</button>
    </form>

    <p class="auth-switch">Déjà inscrit ? <a href="<?= BASE_URL ?>auth/login.php">Se connecter</a></p>
  </div>
</div>
</body>
</html>