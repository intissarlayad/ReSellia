<?php
/**
 * admin/users.php — Gestion des étudiants
 */
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/admin-guard.php';

// Toggle ban (utiliser is_verified = 0 pour bloquer)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrf($_POST['csrf'] ?? '')) {
    $uid    = (int)($_POST['user_id'] ?? 0);
    $action = $_POST['action'] ?? '';
    if ($uid && $uid !== $_SESSION['user_id']) {
        if ($action === 'toggle_verify') {
            $pdo->prepare("UPDATE users SET is_verified = 1 - is_verified WHERE id=?")->execute([$uid]);
            flash('success', 'Statut étudiant mis à jour.');
        } elseif ($action === 'make_admin') {
            $pdo->prepare("UPDATE users SET role='admin' WHERE id=?")->execute([$uid]);
            flash('success', 'Étudiant promu administrateur.');
        }
    }
    header('Location: /admin/users.php'); exit;
}

$search = sanitize($_GET['q'] ?? '');
$users  = $pdo->prepare("
    SELECT u.*,
           (SELECT COUNT(*) FROM products WHERE seller_id=u.id AND status='active') AS active_products,
           (SELECT COUNT(*) FROM orders WHERE buyer_id=u.id) AS orders_count
    FROM users u
    WHERE u.role='student'
    " . ($search ? "AND (u.nom LIKE ? OR u.prenom LIKE ? OR u.email LIKE ? OR u.filiere LIKE ?)" : "") . "
    ORDER BY u.created_at DESC
    LIMIT 50
");
if ($search) $users->execute(["%$search%","%$search%","%$search%","%$search%"]);
else         $users->execute();
$users = $users->fetchAll();

$pageTitle = 'Admin — Étudiants';
$activeNav = 'admin';
include __DIR__ . '/../includes/header.php';
?>

<div class="page-wrap">
  <div class="container">
    <nav class="breadcrumb"><a href="/admin/index.php">Admin</a><span class="sep">/</span><span>Étudiants</span></nav>
    <div class="section-header">
      <h1 class="section-title" style="margin-bottom:0;">👥 Étudiants (<?= count($users) ?>)</h1>
    </div>

    <form method="get" style="margin-bottom:1.5rem;display:flex;gap:.5rem;">
      <input class="form-control" type="text" name="q" value="<?= e($search) ?>" placeholder="Rechercher par nom, filière, email…" style="max-width:350px;" />
      <button type="submit" class="btn btn-primary btn-sm">Chercher</button>
      <?php if ($search): ?><a href="/admin/users.php" class="btn btn-secondary btn-sm">✕</a><?php endif; ?>
    </form>

    <div class="table-wrap">
      <table class="table">
        <thead><tr><th>Étudiant</th><th>Filière</th><th>Promo</th><th>Mode</th><th>Annonces</th><th>Commandes</th><th>Vérifié</th><th>Actions</th></tr></thead>
        <tbody>
          <?php foreach ($users as $u): ?>
          <tr>
            <td>
              <div style="font-weight:600;color:var(--light);"><?= e($u['prenom'].' '.$u['nom']) ?></div>
              <div style="font-size:.72rem;color:var(--muted);"><?= e($u['email']) ?></div>
            </td>
            <td style="color:var(--muted);"><?= e($u['filiere'] ?? '—') ?></td>
            <td style="color:var(--muted);"><?= e($u['promo'] ?? '—') ?></td>
            <td><span class="badge badge-<?= $u['mode_actuel']==='seller'?'green':'blue' ?>"><?= $u['mode_actuel'] ?></span></td>
            <td><?= $u['active_products'] ?></td>
            <td><?= $u['orders_count'] ?></td>
            <td><?php if ($u['is_verified']): ?><span class="badge badge-green">✓</span><?php else: ?><span class="badge badge-muted">✕</span><?php endif; ?></td>
            <td>
              <form method="post" style="display:inline;">
                <input type="hidden" name="csrf" value="<?= csrfToken() ?>">
                <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                <input type="hidden" name="action" value="toggle_verify">
                <button class="btn btn-secondary btn-sm"><?= $u['is_verified'] ? 'Bloquer' : 'Activer' ?></button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
