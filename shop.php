<?php
/**
 * shop.php — Catalogue des produits
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

$catSlug   = sanitize($_GET['cat']   ?? '');
$sortBy    = sanitize($_GET['sort']  ?? 'new');
$search    = sanitize($_GET['q']     ?? '');
$condition = sanitize($_GET['cond']  ?? '');
$page      = max(1,(int)($_GET['page'] ?? 1));
$perPage   = 12;

// Catégories
$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
$currentCat = null;
if ($catSlug) {
    foreach ($categories as $c) { if ($c['slug'] === $catSlug) { $currentCat = $c; break; } }
}

// Requête dynamique
$where  = ["p.status = 'active'"];
$params = [];
if ($currentCat) { $where[] = "p.category_id = ?"; $params[] = $currentCat['id']; }
if ($search)     { $where[] = "(p.name LIKE ? OR p.description LIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; }
if ($condition)  { $where[] = "p.condition_p = ?"; $params[] = $condition; }

$whereStr = 'WHERE ' . implode(' AND ', $where);
$orderStr = match($sortBy) {
    'price_asc'  => 'p.price ASC',
    'price_desc' => 'p.price DESC',
    'popular'    => 'p.views DESC',
    default      => 'p.created_at DESC',
};

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM products p $whereStr");
$countStmt->execute($params);
$total    = (int)$countStmt->fetchColumn();
$totalPages = max(1, (int)ceil($total / $perPage));
$offset   = ($page - 1) * $perPage;

$stmt = $pdo->prepare("
    SELECT p.*, c.name AS cat_name, c.slug AS cat_slug,
           u.nom, u.prenom, u.filiere
    FROM products p
    LEFT JOIN categories c ON c.id = p.category_id
    LEFT JOIN users u ON u.id = p.seller_id
    $whereStr
    ORDER BY $orderStr
    LIMIT $perPage OFFSET $offset
");
$stmt->execute($params);
$products = $stmt->fetchAll();

$pageTitle = $currentCat ? $currentCat['name'] : 'Catalogue';
$activeNav = 'shop';
include 'includes/header.php';
?>

<div class="page-hero">
  <div class="container">
    <h1><?= $currentCat ? e($currentCat['icon'].' '.$currentCat['name']) : '🛍 Catalogue' ?></h1>
    <p><?= $total ?> article<?= $total!=1?'s':'' ?> disponible<?= $total!=1?'s':'' ?></p>
  </div>
</div>

<div class="page-wrap">
  <div class="container">

    <nav class="breadcrumb">
      <a href="<?= BASE_URL ?>index.php">Accueil</a><span class="sep">/</span>
      <a href="<?= BASE_URL ?>shop.php">Catalogue</a>
      <?php if ($currentCat): ?><span class="sep">/</span><span><?= e($currentCat['name']) ?></span><?php endif; ?>
    </nav>

    <!-- Search bar -->
    <form method="get" action="<?= BASE_URL ?>shop.php" style="margin-bottom:1.5rem;">
      <?php if ($catSlug): ?><input type="hidden" name="cat" value="<?= e($catSlug) ?>" /><?php endif; ?>
      <div style="display:flex;gap:.5rem;">
        <input class="form-control" type="text" name="q" value="<?= e($search) ?>" placeholder="Rechercher une annonce…" style="flex:1;"/>
        <button type="submit" class="btn btn-primary">Rechercher</button>
        <?php if ($search || $catSlug || $condition): ?>
        <a href="<?= BASE_URL ?>shop.php" class="btn btn-secondary">✕ Reset</a>
        <?php endif; ?>
      </div>
    </form>

    <div style="display:flex;gap:2rem;align-items:flex-start;">

      <!-- Sidebar filtres -->
      <aside style="width:220px;flex-shrink:0;position:sticky;top:calc(var(--nav-h)+1rem);">
        <div class="card" style="padding:1.2rem;">
          <h3 style="font-size:.75rem;text-transform:uppercase;letter-spacing:.1em;color:var(--muted);margin-bottom:1rem;">Catégories</h3>
          <a href="<?= BASE_URL ?>shop.php<?= $search ? '?q='.urlencode($search) : '' ?>" style="display:block;padding:.4rem .5rem;font-size:.83rem;border-radius:6px;color:<?= !$catSlug?'var(--green-lt)':'var(--text)' ?>;font-weight:<?= !$catSlug?'600':'400' ?>;">Toutes les catégories</a>
          <?php foreach ($categories as $cat): ?>
          <a href="<?= BASE_URL ?>shop.php?cat=<?= e($cat['slug']) ?><?= $search ? '&q='.urlencode($search) : '' ?>" style="display:flex;align-items:center;gap:.5rem;padding:.4rem .5rem;font-size:.83rem;border-radius:6px;color:<?= $catSlug===$cat['slug']?'var(--green-lt)':'var(--text)' ?>;font-weight:<?= $catSlug===$cat['slug']?'600':'400' ?>;">
            <?= $cat['icon'] ?> <?= e($cat['name']) ?>
          </a>
          <?php endforeach; ?>

          <hr class="divider" />
          <h3 style="font-size:.75rem;text-transform:uppercase;letter-spacing:.1em;color:var(--muted);margin-bottom:.8rem;">État</h3>
          <?php foreach ([''=>'Tous','neuf'=>'Neuf','bon_etat'=>'Bon état','usage'=>'Usagé'] as $val => $label): ?>
          <a href="?<?= http_build_query(array_merge($_GET, ['cond'=>$val,'page'=>1])) ?>" style="display:block;padding:.35rem .5rem;font-size:.82rem;border-radius:6px;color:<?= $condition===$val?'var(--green-lt)':'var(--text)' ?>;">
            <?= $label ?>
          </a>
          <?php endforeach; ?>
        </div>
      </aside>

      <!-- Grille produits -->
      <div style="flex:1;min-width:0;">
        <!-- Toolbar -->
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.2rem;flex-wrap:wrap;gap:.8rem;">
          <span style="font-size:.82rem;color:var(--muted);"><?= $total ?> résultat<?= $total!=1?'s':'' ?></span>
          <form method="get" style="display:flex;align-items:center;gap:.5rem;">
            <?php foreach (['cat','q','cond'] as $k) if (!empty($_GET[$k])) echo "<input type='hidden' name='$k' value='".e($_GET[$k])."'>"; ?>
            <span style="font-size:.78rem;color:var(--muted);">Trier :</span>
            <select class="form-control" name="sort" style="width:auto;padding:.4rem .8rem;" onchange="this.form.submit()">
              <option value="new"        <?= $sortBy==='new'        ? 'selected':'' ?>>Les plus récents</option>
              <option value="price_asc"  <?= $sortBy==='price_asc'  ? 'selected':'' ?>>Prix croissant</option>
              <option value="price_desc" <?= $sortBy==='price_desc' ? 'selected':'' ?>>Prix décroissant</option>
              <option value="popular"    <?= $sortBy==='popular'    ? 'selected':'' ?>>Les plus vus</option>
            </select>
          </form>
        </div>

        <?php if (empty($products)): ?>
        <div style="text-align:center;padding:4rem;color:var(--muted);">
          <div style="font-size:3rem;margin-bottom:1rem;">🔍</div>
          <p>Aucune annonce trouvée.</p>
        </div>
        <?php else: ?>
        <div class="products-grid">
          <?php foreach ($products as $i => $p):
            $imgs = json_decode($p['images'] ?? '[]', true);
            $img  = $imgs[0] ?? 'https://via.placeholder.com/400x400/1a1a1a/555?text=Photo';
          ?>
          <div class="product-card" data-reveal data-delay="<?= ($i%4)+1 ?>">
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
                <span class="badge badge-<?= $p['condition_p']==='neuf'?'green':($p['condition_p']==='bon_etat'?'blue':'muted') ?>">
                  <?= conditionLabel($p['condition_p']) ?>
                </span>
              </div>
            </div>
            <div class="product-info">
              <div class="product-cat"><?= e($p['cat_name'] ?? '') ?></div>
              <div class="product-name"><?= e($p['name']) ?></div>
              <div class="product-seller">par <?= e($p['prenom'].' '.$p['nom']) ?></div>
              <div class="product-price"><?= formatPrice((float)$p['price']) ?></div>
            </div>
            <div class="product-footer">
              <a href="<?= BASE_URL ?>product.php?id=<?= $p['id'] ?>" class="btn btn-secondary btn-sm btn-full">Voir l'annonce</a>
            </div>
          </div>
          <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div style="display:flex;justify-content:center;gap:.4rem;padding:2.5rem 0 0;">
          <?php
          $baseQuery = array_merge($_GET, ['page'=>0]);
          for ($i = 1; $i <= $totalPages; $i++):
            $q = http_build_query(array_merge($baseQuery, ['page'=>$i]));
          ?>
          <a href="?<?= $q ?>" class="btn <?= $i===$page?'btn-primary':'btn-secondary' ?> btn-sm"><?= $i ?></a>
          <?php endfor; ?>
        </div>
        <?php endif; ?>

        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
