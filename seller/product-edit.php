<?php
/**
 * seller/product-edit.php — Modifier une annonce
 */
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/seller-guard.php';

$sellerId = $_SESSION['user_id'];
$id       = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND seller_id = ?");
$stmt->execute([$id, $sellerId]);
$product = $stmt->fetch();
if (!$product) { header('Location: ' . BASE_URL . 'seller/products.php'); exit; }

$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
$errors     = [];
$values     = $product;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf'] ?? '')) { $errors[] = 'Token invalide.'; }

    $values['name']        = sanitize($_POST['name']        ?? '');
    $values['description'] = sanitize($_POST['description'] ?? '');
    $values['price']       = (float)($_POST['price']        ?? 0);
    $values['stock']       = max(0,(int)($_POST['stock']    ?? 0));
    $values['condition_p'] = sanitize($_POST['condition_p'] ?? '');
    $values['category_id'] = (int)($_POST['category_id']   ?? 0);

    if (strlen($values['name']) < 3) $errors[] = 'Titre trop court.';
    if ($values['price'] <= 0)       $errors[] = 'Prix invalide.';

    // Nouvelles images
    $existingImages = json_decode($product['images'] ?? '[]', true) ?: [];
    if (!empty($_FILES['images']['name'][0])) {
        foreach ($_FILES['images']['tmp_name'] as $i => $tmp) {
            if ($_FILES['images']['error'][$i] === UPLOAD_ERR_OK) {
                $fileArr = ['name'=>$_FILES['images']['name'][$i],'type'=>$_FILES['images']['type'][$i],'tmp_name'=>$tmp,'size'=>$_FILES['images']['size'][$i]];
                $url = uploadProductImage($fileArr, $sellerId);
                if ($url) $existingImages[] = $url;
            }
        }
    }

    if (empty($errors)) {
        $pdo->prepare("UPDATE products SET name=?,description=?,price=?,stock=?,condition_p=?,category_id=?,images=?,status='pending' WHERE id=?")->execute([$values['name'],$values['description'],$values['price'],$values['stock'],$values['condition_p'],$values['category_id'],json_encode($existingImages),$id]);
        flash('success', '✅ Annonce mise à jour (en attente de validation).');
        header('Location: ' . BASE_URL . 'seller/products.php');
        exit;
    }
}

$existingImgs = json_decode($values['images'] ?? '[]', true) ?: [];
$pageTitle = 'Modifier : ' . $product['name'];
$activeNav = 'seller';
include __DIR__ . '/../includes/header.php';
?>

<div class="page-wrap">
  <div class="container-md">
    <nav class="breadcrumb">
      <a href="<?= BASE_URL ?>seller/dashboard.php">Dashboard</a><span class="sep">/</span>
      <a href="<?= BASE_URL ?>seller/products.php">Mes annonces</a><span class="sep">/</span>
      <span>Modifier</span>
    </nav>
    <h1 class="section-title">✏️ Modifier l'annonce</h1>

    <?php foreach ($errors as $err): ?><div class="alert alert-error">⚠ <?= e($err) ?></div><?php endforeach; ?>

    <form method="post" enctype="multipart/form-data">
      <input type="hidden" name="csrf" value="<?= csrfToken() ?>" />

      <div class="card" style="margin-bottom:1.5rem;">
        <h3 class="card-title" style="margin-bottom:1.2rem;">📝 Informations</h3>

        <div class="form-group">
          <label class="form-label">Titre <span>*</span></label>
          <input class="form-control" type="text" name="name" value="<?= e($values['name']) ?>" required />
        </div>
        <div class="form-group">
          <label class="form-label">Description</label>
          <textarea class="form-control" name="description" rows="4"><?= e($values['description']) ?></textarea>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:.8rem;">
          <div class="form-group">
            <label class="form-label">Prix (MAD) <span>*</span></label>
            <input class="form-control" type="number" name="price" value="<?= e($values['price']) ?>" min="1" step="0.5" required />
          </div>
          <div class="form-group">
            <label class="form-label">Stock</label>
            <input class="form-control" type="number" name="stock" value="<?= (int)$values['stock'] ?>" min="0" />
          </div>
          <div class="form-group">
            <label class="form-label">État</label>
            <select class="form-control" name="condition_p">
              <?php foreach (['neuf'=>'Neuf','bon_etat'=>'Bon état','usage'=>'Usagé'] as $v => $l): ?>
              <option value="<?= $v ?>" <?= $values['condition_p']===$v?'selected':'' ?>><?= $l ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Catégorie</label>
          <select class="form-control" name="category_id">
            <?php foreach ($categories as $cat): ?>
            <option value="<?= $cat['id'] ?>" <?= $values['category_id']==$cat['id']?'selected':'' ?>><?= $cat['icon'] ?> <?= e($cat['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="card" style="margin-bottom:1.5rem;">
        <h3 class="card-title" style="margin-bottom:.8rem;">📸 Photos actuelles</h3>
        <div style="display:flex;gap:.5rem;flex-wrap:wrap;margin-bottom:1rem;">
          <?php foreach ($existingImgs as $img): ?>
          <img src="<?= e($img) ?>" alt="" style="width:80px;height:80px;object-fit:cover;border-radius:var(--radius);border:1px solid var(--border);" />
          <?php endforeach; ?>
          <?php if (empty($existingImgs)): ?><span style="color:var(--muted);font-size:.83rem;">Aucune photo</span><?php endif; ?>
        </div>
        <label class="form-label">Ajouter de nouvelles photos</label>
        <input class="form-control" type="file" id="product-images" name="images[]" accept="image/jpeg,image/png,image/webp" multiple />
        <div id="image-preview" style="display:flex;gap:.5rem;flex-wrap:wrap;margin-top:.8rem;"></div>
      </div>

      <div style="display:flex;gap:1rem;flex-wrap:wrap;">
        <button type="submit" class="btn btn-primary">Sauvegarder ✅</button>
        <a href="<?= BASE_URL ?>seller/products.php" class="btn btn-secondary">Annuler</a>
        <form method="post" action="<?= BASE_URL ?>seller/product-delete.php" style="margin-left:auto;" onsubmit="return confirm('Supprimer cette annonce définitivement ?')">
          <input type="hidden" name="csrf" value="<?= csrfToken() ?>" />
          <input type="hidden" name="product_id" value="<?= $id ?>" />
          <button type="submit" class="btn btn-danger">🗑 Supprimer</button>
        </form>
      </div>
    </form>
  </div>
</div>

<script>
document.getElementById('product-images').addEventListener('change', function(event) {
    const previewContainer = document.getElementById('image-preview');
    previewContainer.innerHTML = ''; // Vider les prévisualisations précédentes
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
