<div align="center">


  <h1>ReSellia — ENSAM Market</h1>
  <p><strong>Plateforme e-commerce étudiante réservée à l'ENSAM Meknès</strong></p>

  <!-- Badges -->
  <p>
    <img src="https://img.shields.io/badge/PHP-8.x-777BB4?style=flat-square&logo=php&logoColor=white" />
    <img src="https://img.shields.io/badge/MySQL-5.7+-4479A1?style=flat-square&logo=mysql&logoColor=white" />
    <img src="https://img.shields.io/badge/Tailwind%20CSS-06B6D4?style=flat-square&logo=tailwindcss&logoColor=white" />
    <img src="https://img.shields.io/badge/Version-v1.0-58a6ff?style=flat-square" />
    <img src="https://img.shields.io/badge/Status-En%20ligne-success?style=flat-square" />
    <img src="https://img.shields.io/badge/Hosted-InfinityFree-6c47ff?style=flat-square" />
  </p>

  <div align="center">
    <h2>🌐 L'Application est en ligne !</h2>
    <a href="https://monprojetphp.infinityfreeapp.com/">
      <img src="https://img.shields.io/badge/🚀_Accéder_à_ENSAM_Market-166534?style=for-the-badge&logo=php" alt="Launch App" />
    </a>
  </div>

  <p>
    <em>Achetez & Vendez entre étudiants ENSAM — simplement, localement, en confiance</em>
  </p>

  <p>
    <b>Catégories :</b> 8 (Livres, Électronique, Vêtements, Services…) &nbsp;&nbsp;•&nbsp;&nbsp;
    <b>Tables DB :</b> 9 &nbsp;&nbsp;•&nbsp;&nbsp;
    <b>Modes utilisateur :</b> Acheteur / Vendeur / Admin
  </p>
</div>

---

## Table of Contents

1. [Vue d'ensemble](#vue-densemble)
2. [Solution & Concept](#solution--concept)
3. [Architecture du projet](#architecture-du-projet)
4. [Base de données & Modèle](#base-de-données--modèle)
5. [Fonctionnalités par module](#fonctionnalités-par-module)
6. [Sécurité](#sécurité)
7. [Screenshots](#screenshots)
8. [Getting Started & Déploiement](#getting-started--déploiement)
9. [Installation locale](#installation-locale)
10. [Roadmap](#roadmap)
11. [Team & Contact](#team--contact)

---

## Vue d'ensemble

Les étudiants de l'ENSAM ont besoin d'échanger livres, polycopiés, matériel et services entre eux — sans passer par des plateformes génériques non adaptées au contexte campus.

**ReSellia** est une plateforme e-commerce complète, construite de A à Z en PHP/MySQL avec architecture MVC, réservée aux étudiants de l'ENSAM Meknès. Elle offre une expérience double-profil (Acheteur ↔ Vendeur), un workflow complet de commandes, une API interne AJAX, et un panneau d'administration pour les modérateurs BDE.

---

## Solution & Concept

**Ce que fait la plateforme :** ReSellia permet à chaque étudiant de publier des annonces, parcourir un catalogue filtrable, passer des commandes et suivre ses achats — le tout dans un environnement sécurisé et validé par des administrateurs.

**Pourquoi c'est adapté au campus :** Le règlement se fait en espèces ou virement direct entre étudiants (remise sur campus), sans paiement en ligne, sans intermédiaire externe. La plateforme est pensée pour la confiance entre pairs.

---

## Architecture du projet

L'architecture suit un pattern **MVC** clair avec séparation stricte des responsabilités :

```
ReSellia/
│
├── index.php                   # Homepage — produits récents + stats globales
├── shop.php                    # Catalogue avec filtres, tri, pagination
├── product.php                 # Fiche produit + produits similaires
├── faq.php / regles.php / contact.php
│
├── auth/                       # Inscription, connexion, reset mot de passe
│   ├── login.php
│   ├── register.php
│   └── forgot-password.php
│
├── account/                    # Profil, paramètres, switch-mode
│   ├── profile.php
│   ├── settings.php
│   └── switch-mode.php
│
├── buyer/                      # Espace Acheteur
│   ├── cart.php
│   ├── checkout.php
│   ├── orders.php
│   ├── order-detail.php
│   └── wishlist.php
│
├── seller/                     # Espace Vendeur
│   ├── dashboard.php
│   ├── product-add.php
│   ├── product-edit.php
│   ├── product-delete.php
│   ├── products.php
│   └── orders.php
│
├── admin/                      # Panneau Administrateur BDE
│   ├── index.php
│   ├── users.php
│   ├── products.php
│   ├── orders.php
│   └── reports.php
│
├── api/                        # Endpoints AJAX internes
│   ├── wishlist-toggle.php
│   ├── product-search.php
│   ├── cart-add.php
│   └── cart-remove.php
│
├── includes/                   # Couche infrastructure
│   ├── db.php                  # Connexion PDO
│   ├── functions.php           # Sanitisation, CSRF, auth helpers
│   ├── config.php              # BASE_URL
│   ├── header.php / footer.php
│   ├── auth-guard.php          # Middleware : authentification requise
│   ├── seller-guard.php        # Middleware : mode vendeur requis
│   └── admin-guard.php         # Middleware : rôle admin requis
│
├── assets/                     # CSS, JS, images
└── database/                   # Schéma SQL complet
```

---

## Base de données & Modèle

Le schéma comprend **9 tables** couvrant l'intégralité du domaine :

| Table | Description | Colonnes clés |
|---|---|---|
| `users` | Étudiants (acheteurs & vendeurs) | `mode_actuel`, `role`, `is_verified` |
| `categories` | 8 catégories de produits | `slug`, `icon` |
| `products` | Annonces publiées par les vendeurs | `status` (active/pending/banned), `condition_p`, `views` |
| `orders` | Commandes passées par les acheteurs | `status` (5 états), `address`, `note` |
| `order_items` | Lignes de commande (produit × qté × prix) | `qty`, `price_unit` |
| `cart` | Panier en cours | `qty` |
| `wishlist` | Articles sauvegardés | `user_id`, `product_id` |
| `reviews` | Avis sur les vendeurs | `rating`, `seller_id` |
| `reports` | Signalements de produits inappropriés | `reason`, `status` |

> Le schéma SQL complet est disponible dans `database/ensam_market.sql`

---

## Fonctionnalités par module

### 🛍️ Espace Acheteur

| Fonctionnalité | Détail |
|---|---|
| Catalogue filtrable | Par catégorie, état (`neuf` / `bon_etat` / `usage`), tri prix/date/popularité |
| Panier | Ajout/suppression AJAX, persistance session |
| Checkout | Adresse de livraison ou point de retrait campus |
| Suivi commandes | 5 statuts : `pending → confirmed → shipped → delivered → cancelled` |
| Wishlist | Toggle AJAX, liste personnelle |
| Fiche produit | Galerie images, infos vendeur, note moyenne, produits similaires |

### 🏪 Espace Vendeur

| Fonctionnalité | Détail |
|---|---|
| Dashboard | Vue d'ensemble des annonces et commandes reçues |
| Nouvelle annonce | Upload multi-photos, description, prix, état — soumis en `pending` |
| Édition & suppression | Après modification, repasse en validation admin |
| Gestion commandes | Confirmer, expédier, livrer ou annuler |

### 🔐 Authentification & Comptes

| Fonctionnalité | Détail |
|---|---|
| Inscription | Email + mot de passe + filière + promo |
| Connexion / Déconnexion | Session sécurisée |
| Mot de passe oublié | Token de réinitialisation |
| Profil & avatar | Mise à jour des informations personnelles |
| Switch de mode | Basculement instantané Acheteur ↔ Vendeur |

### 🛠️ Panneau Administrateur

| Fonctionnalité | Détail |
|---|---|
| Modération annonces | Valider / refuser les produits `pending` |
| Gestion utilisateurs | Suspensions, rôles |
| Suivi global commandes | Vue de toutes les transactions |
| Rapports & statistiques | Activité globale de la plateforme |
| Traitement signalements | Examiner et résoudre les reports |

---

## Sécurité

| Mécanisme | Implémentation |
|---|---|
| Hachage mots de passe | `bcrypt` via `password_hash()` |
| Protection CSRF | Token généré par `random_bytes(32)`, vérifié sur chaque POST |
| Sanitisation | `strip_tags()` + `htmlspecialchars()` sur toutes les entrées |
| Requêtes SQL | 100% préparées avec PDO — zéro injection SQL possible |
| Guards middleware | `auth-guard`, `seller-guard`, `admin-guard` sur chaque espace protégé |

---

## Screenshots

### 🔹 Homepage — Catalogue produits
> *(Capture : `assets/screenshots/homepage.png`)*

### 🔹 Dashboard Vendeur
> *(Capture : `assets/screenshots/seller-dashboard.png`)*

### 🔹 Panneau Administrateur
> *(Capture : `assets/screenshots/admin-panel.png`)*

### 🔹 Fiche Produit
> *(Capture : `assets/screenshots/product-detail.png`)*

---

## Getting Started & Déploiement

## Déploiement Cloud

ReSellia est hébergé et accessible sans aucune installation. N'importe quel étudiant ENSAM peut s'inscrire et utiliser la plateforme directement en ligne.

**🔗 Lien de l'application :** [monprojetphp.infinityfreeapp.com](https://monprojetphp.infinityfreeapp.com/)

### Workflow utilisateur :
1. **Accès** : L'étudiant ouvre le lien et arrive sur la homepage avec les derniers produits.
2. **Inscription** : Création de compte avec email et infos ENSAM (filière, promo).
3. **Navigation** : Parcours du catalogue filtré, ajout au panier ou à la wishlist.
4. **Achat** : Checkout → le vendeur reçoit la commande et organise la remise sur campus.
5. **Vente** : Basculer en mode Vendeur → publier une annonce → attendre validation admin → annonce en ligne.

---

## Installation locale

**Prérequis :** PHP 8.x · MySQL 5.7+ · Apache/Nginx (ou XAMPP/WAMP)

```bash
# 1. Cloner le dépôt
git clone https://github.com/intissarlayad/resellia.git
cd resellia

# 2. Importer la base de données
mysql -u root -p < database/ensam_market.sql

# 3. Configurer la connexion BD
# Éditer includes/db.php avec tes identifiants MySQL

# 4. Configurer le BASE_URL
# Éditer includes/config.php
define('BASE_URL', '/resellia/');

# 5. Lancer (CLI)
php -S localhost:8000

# ou déposer dans htdocs/ (XAMPP) et accéder via http://localhost/resellia/
```

---

## Roadmap

- [x] Architecture MVC complète avec guards middleware
- [x] Double mode Acheteur / Vendeur sur compte unique
- [x] Panier et wishlist AJAX sans rechargement
- [x] Workflow commandes avec 5 statuts
- [x] Panneau admin avec modération, rapports, gestion users
- [x] Déploiement cloud sur InfinityFree
- [ ] Système de messagerie interne entre acheteur et vendeur
- [ ] Notifications en temps réel (nouvelles commandes, validation annonce)
- [ ] Avis et notation vendeurs (interface front complète)
- [ ] Export PDF des commandes / factures
- [ ] Intégration paiement mobile (CMI, Wave…)

---


Pour toute question, suggestion ou rapport de bug, ouvrir une Issue sur le dépôt GitHub ou contacter directement via email.

---

<div align="center">
  <sub>Built with ❤️ for the ENSAM Meknès student community. Projet de développement e-commerce.</sub>
</div>
