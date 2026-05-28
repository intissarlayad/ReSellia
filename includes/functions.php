<?php
/**
 * includes/functions.php — Fonctions utilitaires globales
 */

require_once __DIR__ . '/config.php';

// ── Sécurité ──────────────────────────────────────────────────
function e(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

function sanitize(string $str): string {
    return trim(strip_tags($str));
}

function generateToken(): string {
    return bin2hex(random_bytes(32));
}

function csrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = generateToken();
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrf(string $token): bool {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// ── Auth helpers ─────────────────────────────────────────────
function isLoggedIn(): bool {
    return !empty($_SESSION['user_id']);
}

function currentUser(): ?array {
    return $_SESSION['user'] ?? null;
}

function isSeller(): bool {
    return isLoggedIn() && ($_SESSION['user']['mode_actuel'] ?? '') === 'seller';
}

function isAdmin(): bool {
    return isLoggedIn() && ($_SESSION['user']['role'] ?? '') === 'admin';
}

function requireLogin(string $redirect = 'auth/login.php'): void {
    if (!isLoggedIn()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        header('Location: ' . BASE_URL . $redirect);
        exit;
    }
}

function requireSeller(): void {
    requireLogin();
    if (!isSeller()) {
        header('Location: ' . BASE_URL . 'account/switch-mode.php?need=seller');
        exit;
    }
}

function requireAdmin(): void {
    requireLogin();
    if (!isAdmin()) {
        header('Location: ' . BASE_URL . 'index.php?error=unauthorized');
        exit;
    }
}

// ── Formatage ─────────────────────────────────────────────────
function formatPrice(float $price): string {
    return number_format($price, 2, ',', ' ') . ' MAD';
}

function timeAgo(string $datetime): string {
    $now  = new DateTime();
    $past = new DateTime($datetime);
    $diff = $now->diff($past);

    if ($diff->y > 0)  return $diff->y . ' an'    . ($diff->y > 1 ? 's' : '');
    if ($diff->m > 0)  return $diff->m . ' mois';
    if ($diff->d > 0)  return $diff->d . ' jour'  . ($diff->d > 1 ? 's' : '');
    if ($diff->h > 0)  return $diff->h . ' heure' . ($diff->h > 1 ? 's' : '');
    if ($diff->i > 0)  return $diff->i . ' min';
    return 'À l\'instant';
}

function conditionLabel(string $c): string {
    return match($c) {
        'neuf'     => 'Neuf',
        'bon_etat' => 'Bon état',
        'usage'    => 'Usagé',
        default    => $c,
    };
}

function statusLabel(string $s): string {
    return match($s) {
        'pending'   => 'En attente',
        'confirmed' => 'Confirmée',
        'shipped'   => 'Expédiée',
        'delivered' => 'Livrée',
        'cancelled' => 'Annulée',
        'active'    => 'Active',
        'banned'    => 'Bannie',
        default     => $s,
    };
}

// ── Upload image ─────────────────────────────────────────────
function uploadProductImage(array $file, int $sellerId): ?string {
    $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
    $maxSize      = 5 * 1024 * 1024; // 5 MB

    if (!in_array($file['type'], $allowedTypes)) return null;
    if ($file['size'] > $maxSize) return null;

    $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'prod_' . $sellerId . '_' . uniqid() . '.' . strtolower($ext);
    $uploadDir = __DIR__ . '/../assets/uploads/products/';
    $dest     = $uploadDir . $filename;

    // Créer le dossier de téléversement s'il n'existe pas
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    if (move_uploaded_file($file['tmp_name'], $dest)) {
        return BASE_URL . 'assets/uploads/products/' . $filename;
    }
    return null;
}

// ── Panier (DB) ───────────────────────────────────────────────
function getCartCount(PDO $pdo, int $userId): int {
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(qty), 0) FROM cart_items WHERE user_id = ?");
    $stmt->execute([$userId]);
    return (int)$stmt->fetchColumn();
}

function getCartItems(PDO $pdo, int $userId): array {
    $stmt = $pdo->prepare("
        SELECT ci.*, p.name, p.price, p.images, p.stock, p.seller_id,
               u.nom, u.prenom
        FROM cart_items ci
        JOIN products p ON p.id = ci.product_id
        JOIN users u    ON u.id = p.seller_id
        WHERE ci.user_id = ?
    ");
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

// ── Flash messages ────────────────────────────────────────────
function flash(string $type, string $msg): void {
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}

function getFlash(): ?array {
    if (!empty($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $f;
    }
    return null;
}
