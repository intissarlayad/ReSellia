-- ============================================================
-- ENSAM MARKET — Schéma de base de données
-- ============================================================

CREATE DATABASE IF NOT EXISTS ensam_market CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ensam_market;

-- ── Étudiants (acheteur ET vendeur) ─────────────────────────
CREATE TABLE users (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  nom           VARCHAR(100) NOT NULL,
  prenom        VARCHAR(100) NOT NULL,
  email         VARCHAR(255) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  filiere       VARCHAR(100),
  promo         VARCHAR(20),
  avatar        VARCHAR(500) DEFAULT NULL,
  mode_actuel   ENUM('buyer','seller') DEFAULT 'buyer',
  role          ENUM('student','admin') DEFAULT 'student',
  is_verified   TINYINT(1) DEFAULT 0,
  token         VARCHAR(100) DEFAULT NULL,
  created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ── Catégories ───────────────────────────────────────────────
CREATE TABLE categories (
  id    INT AUTO_INCREMENT PRIMARY KEY,
  name  VARCHAR(100) NOT NULL,
  slug  VARCHAR(100) NOT NULL UNIQUE,
  icon  VARCHAR(50)
);

INSERT INTO categories (name, slug, icon) VALUES
('Livres & Cours',     'livres',       '📚'),
('Électronique',       'electronique', '💻'),
('Vêtements',          'vetements',    '👕'),
('Fournitures',        'fournitures',  '🖊️'),
('Sport & Loisirs',    'sport',        '⚽'),
('Logement & Coloc',   'logement',     '🏠'),
('Services & Cours',   'services',     '🎓'),
('Divers',             'divers',       '📦');

-- ── Produits postés par les étudiants-vendeurs ───────────────
CREATE TABLE products (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  seller_id   INT NOT NULL,
  category_id INT,
  name        VARCHAR(255) NOT NULL,
  description TEXT,
  price       DECIMAL(10,2) NOT NULL,
  images      JSON,
  stock       INT DEFAULT 1,
  condition_p ENUM('neuf','bon_etat','usage') DEFAULT 'bon_etat',
  status      ENUM('active','pending','banned') DEFAULT 'pending',
  views       INT DEFAULT 0,
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (category_id) REFERENCES categories(id)
);

-- ── Commandes ────────────────────────────────────────────────
CREATE TABLE orders (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  buyer_id     INT NOT NULL,
  total        DECIMAL(10,2) NOT NULL,
  status       ENUM('pending','confirmed','shipped','delivered','cancelled') DEFAULT 'pending',
  address      TEXT,
  note         TEXT,
  created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (buyer_id) REFERENCES users(id)
);

CREATE TABLE order_items (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  order_id    INT NOT NULL,
  product_id  INT NOT NULL,
  seller_id   INT NOT NULL,
  qty         INT DEFAULT 1,
  price_unit  DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (order_id)   REFERENCES orders(id) ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(id),
  FOREIGN KEY (seller_id)  REFERENCES users(id)
);

-- ── Panier persistant ────────────────────────────────────────
CREATE TABLE cart_items (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  user_id    INT NOT NULL,
  product_id INT NOT NULL,
  qty        INT DEFAULT 1,
  added_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY unique_cart (user_id, product_id),
  FOREIGN KEY (user_id)    REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- ── Wishlist ─────────────────────────────────────────────────
CREATE TABLE wishlist (
  user_id    INT NOT NULL,
  product_id INT NOT NULL,
  added_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (user_id, product_id),
  FOREIGN KEY (user_id)    REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- ── Signalements ─────────────────────────────────────────────
CREATE TABLE reports (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  reporter_id INT NOT NULL,
  product_id  INT NOT NULL,
  reason      VARCHAR(255),
  details     TEXT,
  status      ENUM('open','resolved') DEFAULT 'open',
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (reporter_id) REFERENCES users(id),
  FOREIGN KEY (product_id)  REFERENCES products(id)
);

-- ── Avis / notes ─────────────────────────────────────────────
CREATE TABLE reviews (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  buyer_id   INT NOT NULL,
  seller_id  INT NOT NULL,
  order_id   INT NOT NULL,
  rating     TINYINT NOT NULL CHECK (rating BETWEEN 1 AND 5),
  comment    TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (buyer_id)  REFERENCES users(id),
  FOREIGN KEY (seller_id) REFERENCES users(id),
  FOREIGN KEY (order_id)  REFERENCES orders(id)
);

-- ── Données de test ──────────────────────────────────────────
INSERT INTO users (nom, prenom, email, password_hash, filiere, promo, mode_actuel, role, is_verified) VALUES
('Admin',    'ENSAM',   'admin@ensam.ac.ma',   '$2y$12$examplehashadmin',   'Administration', '2024', 'buyer', 'admin',   1),
('Alami',    'Youssef', 'y.alami@ensam.ac.ma', '$2y$12$examplehashuser1',   'GI',             '2026', 'seller','student', 1),
('Benali',   'Sara',    's.benali@ensam.ac.ma','$2y$12$examplehashuser2',   'GMP',            '2025', 'buyer', 'student', 1);
