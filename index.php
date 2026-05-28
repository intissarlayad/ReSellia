<?php
/**
 * index.php — Homepage ENSAM Market
 */
session_start();
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

// Récupérer la wishlist de l'utilisateur pour afficher l'état des boutons
$wishlist = [];
if (isLoggedIn()) {
    $stmt = $pdo->prepare("SELECT product_id FROM wishlist WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $wishlist = $stmt->fetchAll(PDO::FETCH_COLUMN);
}

// Récupérer les derniers produits actifs
$stmt = $pdo->query("
    SELECT p.*, c.name AS cat_name, c.slug AS cat_slug,
           u.nom, u.prenom, u.filiere
    FROM products p
    LEFT JOIN categories c ON c.id = p.category_id
    LEFT JOIN users u ON u.id = p.seller_id
    WHERE p.status = 'active'
    ORDER BY p.created_at DESC
    LIMIT 8
");
$latestProducts = $stmt->fetchAll();

// Récupérer les catégories avec compte
$stmt = $pdo->query("
    SELECT c.*, COUNT(p.id) AS product_count
    FROM categories c
    LEFT JOIN products p ON p.category_id = c.id AND p.status = 'active'
    GROUP BY c.id
    ORDER BY product_count DESC
");
$categories = $stmt->fetchAll();

// Stats globales
$stats = $pdo->query("
    SELECT
      (SELECT COUNT(*) FROM users WHERE role='student') AS total_users,
      (SELECT COUNT(*) FROM products WHERE status='active') AS total_products,
      (SELECT COUNT(*) FROM orders) AS total_orders
")->fetch();

$pageTitle = 'Accueil';
$activeNav = 'home';
include 'includes/header.php';
?>

  <!-- ══ HERO ══════════════════════════════════════════════════ -->
  <section class="hero">
    <div class="container">
      <div style="max-width:680px;" data-reveal>
        <div class="badge">
          🎓 Réservé aux étudiants ENSAM
        </div>
        <h1>
          La marketplace<br><span style="color:var(--green-lt);">des étudiants</span> ENSAM
        </h1>
        <p style="font-size:1.05rem;color:var(--text);max-width:500px;margin-bottom:2rem;line-height:1.7;">
          Achète et vends livres, matériel, vêtements et services entre étudiants. Simple, rapide, entre vous.
        </p>
        <div style="display:flex;gap:1rem;flex-wrap:wrap;">
          <a href="<?= BASE_URL ?>shop.php" class="btn btn-primary btn-lg">Explorer le catalogue</a>
          <?php if (!isLoggedIn()): ?>
            <a href="<?= BASE_URL ?>auth/register.php" class="btn btn-outline btn-lg">Rejoindre la communauté</a>
          <?php else: ?>
            <a href="<?= BASE_URL ?>seller/product-add.php" class="btn btn-gold btn-lg">+ Vendre un article</a>
          <?php endif; ?>
        </div>
      </div>

      <!-- Stats -->
      <div class="stats" data-reveal>
        <?php $statsArr = [['🎓', 'Étudiants', $stats['total_users']], ['📦', 'Articles en vente', $stats['total_products']], ['✅', 'Échanges réalisés', $stats['total_orders']]]; ?>
        <?php foreach ($statsArr as [$icon, $label, $val]): ?>
          <div>
            <div><?= $icon ?> <?= number_format($val) ?></div>
            <div style="font-size:.78rem;color:var(--muted);letter-spacing:.05em;"><?= $label ?></div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- ══ CATEGORIES ════════════════════════════════════════════ -->
  <section style="padding:4rem 0;">
    <div class="container">
      <div class="section-header" data-reveal>
        <h2 class="section-title">Parcourir par catégorie</h2>
        <a href="<?= BASE_URL ?>shop.php" class="btn btn-outline btn-sm">Tout voir →</a>
      </div>
      <div class="categories-grid">
        <?php foreach ($categories as $i => $cat): ?>
          <a href="<?= BASE_URL ?>shop.php?cat=<?= e($cat['slug']) ?>" data-reveal data-delay="<?= $i + 1 ?>">
            <div style="font-size:2rem;margin-bottom:.5rem;"><?= $cat['icon'] ?></div>
            <div><?= e($cat['name']) ?></div>
            <div class="muted"><?= $cat['product_count'] ?>
              article<?= $cat['product_count'] != 1 ? 's' : '' ?></div>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- ══ DERNIERS PRODUITS ══════════════════════════════════════ -->
  <section style="padding:5rem 0;">
    <div class="container">
      <div class="section-header" data-reveal>
        <h2 class="section-title">Dernières annonces 🔥</h2>
        <a href="<?= BASE_URL ?>shop.php" class="btn btn-outline btn-sm">Voir tout →</a>
      </div>

      <?php if (empty($latestProducts)): ?>
        <div style="text-align:center;padding:4rem;color:var(--muted);">
          <div style="font-size:3rem;margin-bottom:1rem;">📭</div>
          <p>Aucune annonce pour l'instant. <a href="<?= BASE_URL ?>seller/product-add.php" style="color:var(--green-lt);">Sois le
              premier à vendre !</a></p>
        </div>
      <?php else: ?>
        <div class="products-grid">
          <?php foreach ($latestProducts as $i => $p):
            $imgs = json_decode($p['images'] ?? '[]', true);
            $img = $imgs[0] ?? 'https://via.placeholder.com/400x400/1a1a1a/555?text=No+Image';
            ?>
            <div class="product-card" data-reveal data-delay="<?= ($i % 4) + 1 ?>">
              <div class="product-img-wrap">
                <a href="<?= BASE_URL ?>product.php?id=<?= $p['id'] ?>">
                  <img src="<?= e($img) ?>" alt="<?= e($p['name']) ?>" loading="lazy" />
                </a>
                <?php if (isLoggedIn() && $p['seller_id'] != $_SESSION['user_id']):
                  $isWished = in_array($p['id'], $wishlist);
                ?>
                  <button class="product-wish <?= $isWished ? 'active' : '' ?>" data-wish="<?= $p['id'] ?>" title="Ajouter à la wishlist">
                    <?= $isWished ? '♥' : '♡' ?>
                  </button>
                <?php endif; ?>
                <div class="product-badge">
                  <span
                    class="badge badge-<?= $p['condition_p'] === 'neuf' ? 'green' : ($p['condition_p'] === 'bon_etat' ? 'blue' : 'muted') ?>">
                    <?= conditionLabel($p['condition_p']) ?>
                  </span>
                </div>
              </div>
              <div class="product-info">
                <div class="product-cat"><?= e($p['cat_name'] ?? '') ?></div>
                <div class="product-name" title="<?= e($p['name']) ?>"><?= e($p['name']) ?></div>
                <div class="product-seller">par <?= e($p['prenom'] . ' ' . $p['nom']) ?> · <?= e($p['filiere'] ?? '') ?></div>
                <div class="product-price"><?= formatPrice((float) $p['price']) ?></div>
              </div>
              <div class="product-footer">
                <a href="<?= BASE_URL ?>product.php?id=<?= $p['id'] ?>" class="btn btn-secondary btn-sm btn-full">Voir l'annonce</a>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </section>

  <!-- ══ CTA vendeur ════════════════════════════════════════════ -->
  <?php if (!isLoggedIn() || currentUser()['mode_actuel'] === 'buyer'): ?>
    <section class="cta-vendeur">
      <div class="container-sm">
        <div data-reveal>
          <div style="font-size:3rem;margin-bottom:1rem;">🏪</div>
          <h2>Tu as des articles à vendre ?</h2>
          <p style="color:var(--text);margin-bottom:2rem;">Cours, polycopiés, matériel, vêtements… publie ton annonce en 2
            minutes.</p>
          <?php if (isLoggedIn()): ?>
            <a href="<?= BASE_URL ?>account/switch-mode.php" class="btn btn-gold btn-lg">Devenir vendeur →</a>
          <?php else: ?>
            <a href="<?= BASE_URL ?>auth/register.php" class="btn btn-gold btn-lg">Créer un compte gratuit →</a>
          <?php endif; ?>
        </div>
      </div>
    </section>
  <?php endif; ?>

<?php include 'includes/footer.php'; ?>