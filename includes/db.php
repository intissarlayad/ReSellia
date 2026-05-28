<?php
/**
 * includes/db.php — Connexion PDO MySQL
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'ensam_market');
define('DB_USER', 'root');       // ← changer en production
define('DB_PASS', '');           // ← changer en production
define('DB_CHARSET', 'utf8mb4');

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    // En production, logger l'erreur sans l'afficher
    error_log('DB Error: ' . $e->getMessage());
    die(json_encode(['error' => 'Connexion à la base de données impossible.']));
}
