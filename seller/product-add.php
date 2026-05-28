<?php
/**
 * seller/product-add.php — Ajouter une annonce
 */
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/seller-guard.php';

$sellerId   = $_SESSION['user_id'];
$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
$errors     = [];
$values     = ['name'=>'','description'=>'','price'=>'','stock'=>1,'condition_p'=>'bon_etat','category_id'=>''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf'] ?? '')) { $errors[] = 'Token invalide.'; }

    $values['name']        = sanitize($_POST['name']        ?? '');
    $values['description'] = sanitize($_POST['description'] ?? '');
    $values['price']       = (float)($_POST['price']        ?? 0);
    $values['stock']       = max(1,(int)($_POST['stock']    ?? 1));
    $values['condition_p'] = sanitize($_POST['condition_p'] ?? 'bon_etat');
    $values['category_id'] = (int)($_POST['category_id']   ?? 0);

    if (strlen($values['name']) < 3)       $errors[] = 'Titre trop court (min 3 caractères).';
    if ($values['price'] <= 0)             $errors[] = 'Prix invalide.';
    if (!$values['category_id'])           $errors[] = 'Sélectionne une catégorie.';

    // Upload images
    $imageUrls = [];
    if (!empty($_FILES['images']['name'][0])) {
        foreach ($_FILES['images']['tmp_name'] as $i => $tmp) {
            if ($_FILES['images']['error'][$i] === UPLOAD_ERR_OK) {
                $fileArr = ['name'=>$_FILES['images']['name'][$i],'type'=>$_FILES['images']['type'][$i],'tmp_name'=>$tmp,'size'=>$_FILES['images']['size'][$i]];
                $url = uploadProductImage($fileArr, $sellerId);
                if ($url) $imageUrls[] = $url;
                else $errors[] = 'Image '.(++$i).' invalide (JPG/PNG/WebP, max 5Mo).';
            }
        }
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO products (seller_id,category_id,name,description,price,images,stock,condition_p,status) VALUES (?,?,?,?,?,?,?,?,?)");
        $stmt->execute([$sellerId,$values['category_id'],$values['name'],$values['description'],$values['price'],json_encode($imageUrls),$values['stock'],$values['condition_p'],'active']);
        $newId = (int)$pdo->lastInsertId();
        flash('success', '✅ Annonce publiée avec succès !');
        header('Location: ' . BASE_URL . 'seller/products.php');
        exit;
    }
}

$pageTitle = 'Nouvelle annonce';
$activeNav = 'seller';
include __DIR__ . '/../includes/header.php';
?>

<div class="page-wrap">
  <div class="container-md">
    <nav class="breadcrumb">
      <a href="<?= BASE_URL ?>seller/dashboard.php">Dashboard</a><span class="sep">/</span>
      <a href="<?= BASE_URL ?>seller/products.php">Mes annonces</a><span class="sep">/</span>
      <span>Nouvelle annonce</span>
    </nav>
    <h1 class="section-title">+ Nouvelle annonce</h1>

    <?php foreach ($errors as $err): ?>
    <div class="alert alert-error">⚠ <?= e($err) ?></div>
    <?php endforeach; ?>

    <form method="post" enctype="multipart/form-data">
      <input type="hidden" name="csrf" value="<?= csrfToken() ?>" />

      <div class="card" style="margin-bottom:1.5rem;">
        <h3 class="card-title" style="margin-bottom:1.2rem;">📝 Informations</h3>

        <div class="form-group">
          <label class="form-label">Titre de l'annonce <span>*</span></label>
          <input class="form-control" type="text" name="name" value="<?= e($values['name']) ?>" placeholder="Ex: Livre Résistance des Matériaux — 3ème année GMP" required />
        </div>

        <div class="form-group">
          <label class="form-label">Description <span>*</span></label>
          <textarea class="form-control" name="description" rows="4" placeholder="État, édition, ce qui est inclus…"><?= e($values['description']) ?></textarea>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:.8rem;">
          <div class="form-group">
            <label class="form-label">Prix (MAD) <span>*</span></label>
            <input class="form-control" type="number" name="price" value="<?= e($values['price']) ?>" min="1" step="0.5" placeholder="0.00" required />
          </div>
          <div class="form-group">
            <label class="form-label">Quantité disponible</label>
            <input class="form-control" type="number" name="stock" value="<?= $values['stock'] ?>" min="1" />
          </div>
          <div class="form-group">
            <label class="form-label">État <span>*</span></label>
            <select class="form-control" name="condition_p">
              <?php foreach (['neuf'=>'Neuf','bon_etat'=>'Bon état','usage'=>'Usagé'] as $v => $l): ?>
              <option value="<?= $v ?>" <?= $values['condition_p']===$v?'selected':'' ?>><?= $l ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Catégorie <span>*</span></label>
          <select class="form-control" name="category_id" required>
            <option value="">— Choisir une catégorie —</option>
            <?php foreach ($categories as $cat): ?>
            <option value="<?= $cat['id'] ?>" <?= $values['category_id']==$cat['id']?'selected':'' ?>><?= $cat['icon'] ?> <?= e($cat['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="card" style="margin-bottom:1.5rem;">
        <h3 class="card-title" style="margin-bottom:.5rem;">📸 Photos</h3>
        <p class="form-hint" style="margin-bottom:1rem;">Jusqu'à 4 photos — JPG, PNG ou WebP — max 5Mo chacune</p>
        <input class="form-control" type="file" id="product-images" name="images[]" accept="image/jpeg,image/png,image/webp" multiple />
        <div id="image-preview" style="display:flex;gap:.5rem;flex-wrap:wrap;margin-top:.8rem;"></div>
      </div>

      <div style="display:flex;gap:1rem;">
        <button type="submit" class="btn btn-primary btn-lg">Publier l'annonce 🚀</button>
        <a href="<?= BASE_URL ?>seller/products.php" class="btn btn-secondary btn-lg">Annuler</a>
      </div>
    </form>
  </div>
</div>

<script>
document.getElementById('product-images').addEventListener('change', function(event) {
    const previewContainer = document.getElementById('image-preview');
    previewContainer.innerHTML = ''; // Vider les anciennes prévisualisations
    if (this.files) {
        Array.from(this.files).forEach(file => {
            if (!file.type.startsWith('image/')){ return; }
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.style.width = '80px';
                img.style.height = '80px';
                img.style.objectFit = 'cover';
                img.style.borderRadius = 'var(--radius)';
                previewContainer.appendChild(img);
            }
            reader.readAsDataURL(file);
        });
    }
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
