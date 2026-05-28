<?php
/**
 * buyer/checkout.php — Validation de commande
 */
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth-guard.php';

$userId = $_SESSION['user_id'];
$items  = getCartItems($pdo, $userId);
if (empty($items)) { header('Location: ' . BASE_URL . 'buyer/cart.php'); exit; }

$subtotal = array_sum(array_map(fn($i) => $i['price'] * $i['qty'], $items));
$errors   = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf'] ?? '')) { $errors[] = 'Token invalide.'; }

    $address = sanitize($_POST['address'] ?? '');
    $note    = sanitize($_POST['note']    ?? '');
    if (strlen($address) < 10) $errors[] = 'Adresse de retrait/livraison invalide.';

    if (empty($errors)) {
        $pdo->beginTransaction();
        try {
            // Verrouiller les articles du panier et vérifier le stock de manière atomique
            $productIds = array_map(fn($i) => $i['product_id'], $items);
            if (empty($productIds)) {
                throw new \Exception('Votre panier est vide.');
            }
            $placeholders = implode(',', array_fill(0, count($productIds), '?'));
            $stmt = $pdo->prepare("SELECT id, stock, name FROM products WHERE id IN ($placeholders) FOR UPDATE");
            $stmt->execute($productIds);
            $dbProducts = $stmt->fetchAll(\PDO::FETCH_UNIQUE);

            foreach ($items as $item) {
                if (!isset($dbProducts[$item['product_id']])) {
                    throw new \Exception("L'article « " . e($item['name']) . " » n'est plus disponible.");
                }
                if ($dbProducts[$item['product_id']]['stock'] < $item['qty']) {
                    throw new \Exception("Stock insuffisant pour « " . e($item['name']) . " ». Il ne reste que " . $dbProducts[$item['product_id']]['stock'] . " exemplaire(s).");
                }
            }

            // Créer la commande
            $stmt = $pdo->prepare("INSERT INTO orders (buyer_id, total, status, address, note) VALUES (?,?,?,?,?)");
            $stmt->execute([$userId, $subtotal, 'pending', $address, $note]);
            $orderId = (int)$pdo->lastInsertId();

            // Ajouter les items et décrémenter le stock
            $stmtItem  = $pdo->prepare("INSERT INTO order_items (order_id, product_id, seller_id, qty, price_unit) VALUES (?,?,?,?,?)");
            $stmtStock = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");

            foreach ($items as $item) {
                $stmtItem->execute([$orderId, $item['product_id'], $item['seller_id'], $item['qty'], $item['price']]);
                $stmtStock->execute([$item['qty'], $item['product_id']]);
            }

            // Vider le panier
            $pdo->prepare("DELETE FROM cart_items WHERE user_id=?")->execute([$userId]);
            $pdo->commit();

            flash('success', '🎉 Commande #'.$orderId.' passée avec succès !');
            header('Location: ' . BASE_URL . 'buyer/order-detail.php?id='.$orderId);
            exit;
        } catch (\Exception $e) {
            $pdo->rollBack();
            $errors[] = 'Erreur lors de la commande : ' . $e->getMessage();
        }
    }
}

$user = currentUser();
$pageTitle = 'Finaliser ma commande';
include __DIR__ . '/../includes/header.php';
?>

<div class="page-wrap">
  <div class="container-md">
    <nav class="breadcrumb">
      <a href="<?= BASE_URL ?>index.php">Accueil</a><span class="sep">/</span>
      <a href="<?= BASE_URL ?>buyer/cart.php">Panier</a><span class="sep">/</span>
      <span>Finaliser</span>
    </nav>
    <h1 class="section-title">✅ Finaliser ma commande</h1>

    <?php foreach ($errors as $err): ?>
    <div class="alert alert-error">⚠ <?= e($err) ?></div>
    <?php endforeach; ?>

    <div class="cart-layout">
      <div>
        <form method="post" action="<?= BASE_URL ?>buyer/checkout.php">
          <input type="hidden" name="csrf" value="<?= csrfToken() ?>" />

          <div class="card" style="margin-bottom:1.5rem;">
            <h3 class="card-title" style="margin-bottom:1.2rem;">📍 Infos de contact / retrait</h3>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.8rem;margin-bottom:.8rem;">
              <div class="form-group" style="margin:0;">
                <label class="form-label">Prénom</label>
                <input class="form-control" type="text" value="<?= e($user['prenom']) ?>" readonly />
              </div>
              <div class="form-group" style="margin:0;">
                <label class="form-label">Nom</label>
                <input class="form-control" type="text" value="<?= e($user['nom']) ?>" readonly />
              </div>
            </div>
            <div class="form-group">
              <label class="form-label">Email de contact</label>
              <input class="form-control" type="email" value="<?= e($user['email']) ?>" readonly />
            </div>
            <div class="form-group">
              <label class="form-label">Point de retrait ou adresse <span>*</span></label>
              <textarea class="form-control" name="address" rows="2" placeholder="Ex: Campus ENSAM, Bâtiment A, Hall principal…" required><?= e($_POST['address'] ?? '') ?></textarea>
            </div>
            <div class="form-group" style="margin-bottom:0;">
              <label class="form-label">Note pour le(s) vendeur(s)</label>
              <textarea class="form-control" name="note" rows="2" placeholder="Instructions, disponibilités…"><?= e($_POST['note'] ?? '') ?></textarea>
            </div>
          </div>

          <button type="submit" class="btn btn-primary btn-full btn-lg">Confirmer la commande 🎓</button>
        </form>
      </div>

      <!-- Résumé -->
      <div class="order-summary">
        <h3 style="font-family:var(--font-head);font-weight:700;color:var(--white);margin-bottom:1.2rem;">Récapitulatif</h3>
        <?php foreach ($items as $item):
          $imgs = json_decode($item['images']??'[]',true);
          $img  = $imgs[0] ?? 'https://via.placeholder.com/80x80/1a1a1a/555?text=+';
        ?>
        <div style="display:flex;align-items:center;gap:.8rem;padding:.7rem 0;border-bottom:1px solid var(--border);">
          <img src="<?= e($img) ?>" alt="" style="width:48px;height:48px;object-fit:cover;border-radius:6px;flex-shrink:0;" />
          <div style="flex:1;min-width:0;">
            <div style="font-size:.83rem;color:var(--light);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= e($item['name']) ?></div>
            <div style="font-size:.72rem;color:var(--muted);">Qté : <?= $item['qty'] ?></div>
          </div>
          <div style="font-size:.88rem;color:var(--gold);font-weight:600;"><?= formatPrice($item['price']*$item['qty']) ?></div>
        </div>
        <?php endforeach; ?>
        <div class="summary-total" style="padding-top:1rem;"><span>Total</span><span><?= formatPrice($subtotal) ?></span></div>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
