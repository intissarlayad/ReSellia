<?php
/**
 * product.php — Fiche produit
 */
session_start();
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: /shop.php'); exit; }

$stmt = $pdo->prepare("
    SELECT p.*, c.name AS cat_name, c.slug AS cat_slug, c.icon AS cat_icon,
           u.id AS seller_id, u.nom, u.prenom, u.filiere, u.promo, u.avatar,
           (SELECT COUNT(*) FROM reviews r WHERE r.seller_id = u.id) AS review_count,
           (SELECT AVG(rating) FROM reviews r WHERE r.seller_id = u.id) AS avg_rating
    FROM products p
    LEFT JOIN categories c ON c.id = p.category_id
    LEFT JOIN users u ON u.id = p.seller_id
    WHERE p.id = ? AND p.status = 'active'
");
$stmt->execute([$id]);
$p = $stmt->fetch();
if (!$p) { http_response_code(404); die('Produit introuvable.'); }

// Incrémenter vues
$pdo->prepare("UPDATE products SET views = views + 1 WHERE id = ?")->execute([$id]);

$imgs    = json_decode($p['images'] ?? '[]', true) ?: ['https://via.placeholder.com/800x800/1a1a1a/555?text=No+Image'];
$inWish  = false;
if (isLoggedIn()) {
    $w = $pdo->prepare("SELECT 1 FROM wishlist WHERE user_id = ? AND product_id = ?");
    $w->execute([$_SESSION['user_id'], $id]);
    $inWish = (bool)$w->fetchColumn();
}

// Produits similaires
$similar = $pdo->prepare("
    SELECT p.*, c.name AS cat_name, u.nom, u.prenom
    FROM products p
    LEFT JOIN categories c ON c.id = p.category_id
    LEFT JOIN users u ON u.id = p.seller_id
    WHERE p.status = 'active' AND p.category_id = ? AND p.id != ?
    LIMIT 4
");
$similar->execute([$p['category_id'], $id]);
$similarProducts = $similar->fetchAll();

$pageTitle = $p['name'];
$activeNav = 'shop';
include 'includes/header.php';
?>

<div class="page-wrap">
  <div class="container">

    <nav class="breadcrumb">
      <a href="<?= BASE_URL ?>index.php">Accueil</a><span class="sep">/</span>
      <a href="<?= BASE_URL ?>shop.php">Catalogue</a><span class="sep">/</span>
      <a href="<?= BASE_URL ?>shop.php?cat=<?= e($p['cat_slug']) ?>"><?= e($p['cat_icon'].' '.$p['cat_name']) ?></a><span class="sep">/</span>
      <span><?= e($p['name']) ?></span>
    </nav>

    <!-- Produit -->
    <div style="display:grid;grid-template-columns:1.1fr 1fr;gap:4rem;align-items:start;margin-bottom:4rem;">
      <!-- Galerie -->
      <div style="position:sticky;top:calc(var(--nav-h)+1rem);">
        <div style="aspect-ratio:1;background:var(--card);border:1px solid var(--border);border-radius:var(--radius-lg);overflow:hidden;margin-bottom:.8rem;">
          <img id="main-img" src="<?= e($imgs[0]) ?>" alt="<?= e($p['name']) ?>" style="width:100%;height:100%;object-fit:cover;" />
        </div>
        <?php if (count($imgs) > 1): ?>
        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:.5rem;">
          <?php foreach ($imgs as $i => $img): ?>
          <div onclick="document.getElementById('main-img').src='<?= e($img) ?>';document.querySelectorAll('.thumb-btn').forEach(t=>t.style.borderColor='var(--border)');this.style.borderColor='var(--green)'"
               class="thumb-btn"
               style="aspect-ratio:1;background:var(--card);border:2px solid <?= $i===0?'var(--green)':'var(--border)' ?>;border-radius:var(--radius);overflow:hidden;cursor:pointer;">
            <img src="<?= e($img) ?>" alt="" style="width:100%;height:100%;object-fit:cover;" loading="lazy" />
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>

      <!-- Infos -->
      <div>
        <div style="display:flex;align-items:center;gap:.6rem;margin-bottom:.8rem;">
          <span class="badge badge-<?= $p['condition_p']==='neuf'?'green':($p['condition_p']==='bon_etat'?'blue':'muted') ?>"><?= conditionLabel($p['condition_p']) ?></span>
          <span class="badge badge-muted"><?= e($p['cat_icon'].' '.$p['cat_name']) ?></span>
        </div>

        <h1 style="font-family:var(--font-head);font-size:clamp(1.5rem,3vw,2.2rem);font-weight:700;color:var(--white);line-height:1.2;margin-bottom:1rem;"><?= e($p['name']) ?></h1>

        <div style="font-family:var(--font-head);font-size:2.5rem;font-weight:800;color:var(--gold);margin-bottom:1.5rem;"><?= formatPrice((float)$p['price']) ?></div>

        <p style="color:var(--text);line-height:1.7;margin-bottom:2rem;font-size:.92rem;"><?= nl2br(e($p['description'])) ?></p>

        <!-- Vendeur -->
        <a href="#seller-section" style="display:flex;align-items:center;gap:1rem;background:var(--deep);border:1px solid var(--border);border-radius:var(--radius);padding:1rem;margin-bottom:1.5rem;text-decoration:none;">
          <div style="width:44px;height:44px;border-radius:50%;background:var(--green-dk);border:2px solid var(--green);display:flex;align-items:center;justify-content:center;font-weight:700;color:var(--white);font-size:1rem;flex-shrink:0;">
            <?= mb_strtoupper(mb_substr($p['prenom'],0,1)) ?>
          </div>
          <div>
            <div style="font-weight:600;color:var(--light);font-size:.9rem;"><?= e($p['prenom'].' '.$p['nom']) ?></div>
            <div style="font-size:.75rem;color:var(--muted);"><?= e($p['filiere'].' · Promo '.$p['promo']) ?></div>
          </div>
          <?php if ($p['avg_rating']): ?>
          <div style="margin-left:auto;text-align:center;">
            <div style="color:var(--gold);font-size:.85rem;">★ <?= number_format($p['avg_rating'],1) ?></div>
            <div style="font-size:.68rem;color:var(--muted);"><?= $p['review_count'] ?> avis</div>
          </div>
          <?php endif; ?>
        </a>

        <!-- Actions -->
        <?php if (isLoggedIn()):
          $isMine = ($_SESSION['user']['id'] === $p['seller_id']); ?>
        <?php if ($isMine): ?>
          <div class="alert alert-info">📌 C'est votre annonce. <a href="<?= BASE_URL ?>seller/product-edit.php?id=<?= $p['id'] ?>" style="color:var(--gold);">Modifier</a></div>
        <?php else: ?>
          <form method="post" action="<?= BASE_URL ?>buyer/cart.php">
            <input type="hidden" name="action" value="add" />
            <input type="hidden" name="product_id" value="<?= $p['id'] ?>" />
            <div style="display:flex;gap:.8rem;margin-bottom:1rem;">
              <div class="qty-control">
                <button type="button" class="qty-btn qty-minus">−</button>
                <input type="number" class="qty-input" name="qty" value="1" min="1" max="<?= (int)$p['stock'] ?>" />
                <button type="button" class="qty-btn qty-plus">+</button>
              </div>
              <button type="submit" class="btn btn-primary" style="flex:1;" <?= $p['stock']<1?'disabled':'' ?>>
                <?= $p['stock'] > 0 ? '🛒 Ajouter au panier' : '❌ Épuisé' ?>
              </button>
            </div>
            <button type="button" class="btn btn-secondary btn-full" data-wish="<?= $p['id'] ?>" style="<?= $inWish?'border-color:var(--red);color:var(--red);':'' ?>">
              <?= $inWish ? '❤️ Dans ta wishlist' : '🤍 Ajouter à la wishlist' ?>
            </button>
          </form>
        <?php endif; ?>
        <?php else: ?>
          <a href="<?= BASE_URL ?>auth/login.php" class="btn btn-primary btn-full btn-lg">Connecte-toi pour acheter</a>
        <?php endif; ?>

        <!-- Meta -->
        <div style="margin-top:1.5rem;border-top:1px solid var(--border);padding-top:1.2rem;">
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:.5rem;">
            <?php $metas = [['Stock',$p['stock'].' disponible'.($p['stock']>1?'s':'')],['Vues',$p['views'].' vue'.($p['views']>1?'s':'')],['Posté',timeAgo($p['created_at'])]]; ?>
            <?php foreach ($metas as [$k,$v]): ?>
            <div style="font-size:.78rem;">
              <span style="color:var(--muted);"><?= $k ?> : </span>
              <span style="color:var(--text);"><?= $v ?></span>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>

    <!-- Vendeur section -->
    <div id="seller-section" class="card" style="margin-bottom:3rem;">
      <h3 class="card-title">🎓 À propos du vendeur</h3>
      <div style="display:flex;align-items:center;gap:1.2rem;margin-top:1rem;">
        <div style="width:56px;height:56px;border-radius:50%;background:var(--green-dk);border:2px solid var(--green);display:flex;align-items:center;justify-content:center;font-weight:700;color:var(--white);font-size:1.2rem;"><?= mb_strtoupper(mb_substr($p['prenom'],0,1)) ?></div>
        <div>
          <div style="font-weight:700;color:var(--white);"><?= e($p['prenom'].' '.$p['nom']) ?></div>
          <div style="font-size:.82rem;color:var(--muted);"><?= e($p['filiere']) ?> — Promotion <?= e($p['promo']) ?></div>
          <?php if ($p['avg_rating']): ?><div style="color:var(--gold);font-size:.82rem;">★ <?= number_format($p['avg_rating'],1) ?>/5 (<?= $p['review_count'] ?> avis)</div><?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Produits similaires -->
    <?php if (!empty($similarProducts)): ?>
    <div>
      <div class="section-header"><h2 class="section-title">Articles similaires</h2><a href="<?= BASE_URL ?>shop.php?cat=<?= e($p['cat_slug']) ?>" class="btn btn-outline btn-sm">Voir tout →</a></div>
      <div class="products-grid">
        <?php foreach ($similarProducts as $sp):
          $si = json_decode($sp['images']??'[]',true);
          $si = $si[0] ?? 'https://via.placeholder.com/400x400/1a1a1a/555?text=Photo';
        ?>
        <div class="product-card">
          <div class="product-img-wrap">
            <a href="<?= BASE_URL ?>product.php?id=<?= $sp['id'] ?>"><img src="<?= e($si) ?>" alt="<?= e($sp['name']) ?>" loading="lazy"/></a>
          </div>
          <div class="product-info">
            <div class="product-name"><?= e($sp['name']) ?></div>
            <div class="product-seller">par <?= e($sp['prenom'].' '.$sp['nom']) ?></div>
            <div class="product-price"><?= formatPrice((float)$sp['price']) ?></div>
          </div>
          <div class="product-footer"><a href="<?= BASE_URL ?>product.php?id=<?= $sp['id'] ?>" class="btn btn-secondary btn-sm btn-full">Voir</a></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

  </div>
</div>

<?php include 'includes/footer.php'; ?>
