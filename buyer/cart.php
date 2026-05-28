<?php
/**
 * buyer/cart.php — Panier acheteur
 */
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth-guard.php';

$userId = $_SESSION['user_id'];

// ── Actions ───────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Ajouter
    if ($action === 'add') {
        $pid = (int)($_POST['product_id'] ?? 0);
        $qty = max(1, (int)($_POST['qty'] ?? 1));
        // Vérifier que le produit existe et n'est pas le sien
        $stmt = $pdo->prepare("SELECT id, seller_id, stock, name FROM products WHERE id = ? AND status = 'active'");
        $stmt->execute([$pid]);
        $prod = $stmt->fetch();
        if ($prod && $prod['seller_id'] != $userId) {
            // Vérifier le stock en incluant la quantité déjà dans le panier
            $stmt = $pdo->prepare("SELECT qty FROM cart_items WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$userId, $pid]);
            $currentQty = (int)$stmt->fetchColumn();

            if ($prod['stock'] < ($currentQty + $qty)) {
                flash('error', 'Stock insuffisant pour "' . e($prod['name']) . '". ' . $prod['stock'] . ' en stock, ' . $currentQty . ' dans votre panier.');
            } else {
                $pdo->prepare("INSERT INTO cart_items (user_id, product_id, qty) VALUES (?,?,?) ON DUPLICATE KEY UPDATE qty = qty + VALUES(qty)")->execute([$userId, $pid, $qty]);
                flash('success', 'Article ajouté au panier !');
            }
        } elseif ($prod && $prod['seller_id'] == $userId) {
            flash('error', 'Tu ne peux pas acheter ton propre article.');
        }
        header('Location: ' . BASE_URL . 'buyer/cart.php'); exit;
    }

    // Mettre à jour le panier (via le bouton "Mettre à jour")
    if (isset($_POST['update_cart'])) {
        foreach ($_POST['qtys'] ?? [] as $pid => $qty) {
            $pid = (int)$pid; $qty = (int)$qty;
            if ($qty < 1) $pdo->prepare("DELETE FROM cart_items WHERE user_id=? AND product_id=?")->execute([$userId,$pid]);
            else $pdo->prepare("UPDATE cart_items SET qty=? WHERE user_id=? AND product_id=?")->execute([$qty,$userId,$pid]);
        }
        flash('success', 'Panier mis à jour.');
        header('Location: ' . BASE_URL . 'buyer/cart.php'); exit;
    }

    // Supprimer un article (via le bouton "✕")
    if (isset($_POST['remove_item'])) {
        $pid = (int)$_POST['remove_item'];
        $pdo->prepare("DELETE FROM cart_items WHERE user_id=? AND product_id=?")->execute([$userId,$pid]);
        flash('success', 'Article retiré du panier.');
        header('Location: ' . BASE_URL . 'buyer/cart.php'); exit;
    }

    // Vider le panier
    if ($action === 'clear') {
        $pdo->prepare("DELETE FROM cart_items WHERE user_id=?")->execute([$userId]);
        flash('success', 'Panier vidé.');
        header('Location: ' . BASE_URL . 'buyer/cart.php'); exit;
    }
}

$items    = getCartItems($pdo, $userId);
$subtotal = array_sum(array_map(fn($i) => $i['price'] * $i['qty'], $items));
$total    = $subtotal; // livraison = 0 (entre étudiants)

$pageTitle = 'Mon Panier';
$activeNav = '';
include __DIR__ . '/../includes/header.php';
?>

<div class="page-wrap">
  <div class="container">
    <nav class="breadcrumb">
      <a href="<?= BASE_URL ?>index.php">Accueil</a><span class="sep">/</span><span>Mon Panier</span>
    </nav>
    <h1 class="section-title">🛒 Mon Panier <?php if ($items): ?><small style="font-size:.9rem;color:var(--muted);font-family:var(--font-body);">(<?= count($items) ?> article<?= count($items)>1?'s':'' ?>)</small><?php endif; ?></h1>

    <?php if (empty($items)): ?>
    <div style="text-align:center;padding:5rem 0;">
      <div style="font-size:4rem;margin-bottom:1rem;">🛒</div>
      <h2 style="font-family:var(--font-head);font-size:1.8rem;color:var(--white);margin-bottom:.8rem;">Ton panier est vide</h2>
      <p style="color:var(--muted);margin-bottom:2rem;">Explore le catalogue pour trouver des articles.</p>
      <a href="<?= BASE_URL ?>shop.php" class="btn btn-primary btn-lg">Explorer le catalogue →</a>
    </div>

    <?php else: ?>
    <div class="cart-layout">
      <!-- Items -->
      <div>
        <form method="post" action="<?= BASE_URL ?>buyer/cart.php">
          <?php foreach ($items as $item):
            $imgs = json_decode($item['images'] ?? '[]', true);
            $img  = $imgs[0] ?? 'https://via.placeholder.com/200x200/1a1a1a/555?text=Photo';
          ?>
          <div class="cart-item">
            <img class="cart-item-img" src="<?= e($img) ?>" alt="<?= e($item['name']) ?>" />
            <div style="flex:1;">
              <div class="cart-item-name"><?= e($item['name']) ?></div>
              <div class="cart-item-meta">Vendu par <?= e($item['prenom'].' '.$item['nom']) ?> · <?= formatPrice((float)$item['price']) ?>/unité</div>
              <div style="display:flex;align-items:center;justify-content:space-between;margin-top:.8rem;flex-wrap:wrap;gap:.5rem;">
                <div class="qty-control">
                  <button type="button" class="qty-btn qty-minus">−</button>
                  <input type="number" class="qty-input" name="qtys[<?= $item['product_id'] ?>]" value="<?= $item['qty'] ?>" min="0" max="<?= $item['stock'] ?>" />
                  <button type="button" class="qty-btn qty-plus">+</button>
                </div>
                <div style="display:flex;align-items:center;gap:1rem;">
                  <span class="cart-item-price"><?= formatPrice($item['price'] * $item['qty']) ?></span>
                  <button type="submit" name="remove_item" value="<?= $item['product_id'] ?>" class="cart-remove" title="Retirer l'article">✕</button>
                </div>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
          <div style="display:flex;justify-content:space-between;margin-top:1rem;flex-wrap:wrap;gap:.8rem;">
            <button type="submit" name="update_cart" value="1" class="btn btn-secondary btn-sm">Mettre à jour le panier</button>
            <form method="post" action="<?= BASE_URL ?>buyer/cart.php">
              <input type="hidden" name="action" value="clear" />
              <button type="submit" class="btn btn-secondary btn-sm" onclick="return confirm('Vider le panier ?')">🗑 Vider</button>
            </form>
          </div>
        </form>
        <div style="margin-top:1.5rem;"><a href="<?= BASE_URL ?>shop.php" style="color:var(--green-lt);font-size:.83rem;">← Continuer mes achats</a></div>
      </div>

      <!-- Résumé -->
      <div class="order-summary">
        <h3 style="font-family:var(--font-head);font-size:1.2rem;font-weight:700;color:var(--white);margin-bottom:1.5rem;">Récapitulatif</h3>
        <div class="summary-row"><span>Sous-total</span><span><?= formatPrice($subtotal) ?></span></div>
        <div class="summary-row"><span>Livraison</span><span style="color:var(--green-lt);">Gratuite 🎓</span></div>
        <div class="summary-total"><span>Total</span><span><?= formatPrice($total) ?></span></div>
        <a href="<?= BASE_URL ?>buyer/checkout.php" class="btn btn-primary btn-full btn-lg" style="margin-top:1rem;">
          Passer commande →
        </a>
        <div style="text-align:center;margin-top:1rem;font-size:.75rem;color:var(--muted);">🔒 Échange sécurisé entre étudiants ENSAM</div>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
