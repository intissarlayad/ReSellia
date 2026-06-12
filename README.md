<div align="center">

# Resellia-ensam-market

**A full-stack student-to-student e-commerce platform — built exclusively for ENSAM Meknès**

<p>
  <img src="https://img.shields.io/badge/PHP-8.x-777BB4?style=flat-square&logo=php&logoColor=white" />
  <img src="https://img.shields.io/badge/MySQL-5.7+-4479A1?style=flat-square&logo=mysql&logoColor=white" />
  <img src="https://img.shields.io/badge/Tailwind_CSS-06B6D4?style=flat-square&logo=tailwindcss&logoColor=white" />
  <img src="https://img.shields.io/badge/Version-v1.0-58a6ff?style=flat-square" />
  <img src="https://img.shields.io/badge/Status-Live-28a745?style=flat-square" />
  <img src="https://img.shields.io/badge/Hosted-InfinityFree-6c47ff?style=flat-square" />
</p>

<h3>🌐 Live Application</h3>
<a href="https://monprojetphp.infinityfreeapp.com/">
  <img src="https://img.shields.io/badge/🚀_Open_ENSAM_Market-166534?style=for-the-badge&logo=php" alt="Launch App" />
</a>

<br/><br/>

*Buy & sell between ENSAM students — simply, locally, and securely*

**Categories:** 8 (Books, Electronics, Clothing, Services…) &nbsp;•&nbsp;
**DB Tables:** 9 &nbsp;•&nbsp;
**User Modes:** Buyer / Seller / Admin

</div>

---

## Table of Contents

1. [Overview](#overview)
2. [Concept & Problem Solved](#concept--problem-solved)
3. [Project Structure](#project-structure)
4. [Database Schema](#database-schema)
5. [Features by Module](#features-by-module)
6. [Security](#security)
7. [Getting Started](#getting-started)
8. [Local Installation](#local-installation)
9. [Roadmap](#roadmap)

---

## Overview

ENSAM students need a way to exchange textbooks, course materials, equipment, and services among themselves — without relying on generic platforms disconnected from campus life.

**ReSellia** is a complete e-commerce platform built from scratch in PHP/MySQL following an MVC architecture, exclusively for ENSAM Meknès students. It features a dual-profile experience (Buyer ↔ Seller), a full order management workflow, an internal AJAX API, and an admin panel for BDE moderators.

---

## Concept & Problem Solved

**What the platform does:** ReSellia lets any student publish listings, browse a filterable catalogue, place orders, and track purchases — all within a verified, admin-moderated environment.

**Why it fits the campus context:** Payments are handled directly between students in cash or bank transfer (on-campus pickup), with no online payment gateway or external intermediary. The platform is designed around peer trust within a closed academic community.

---

## Project Structure

The architecture follows a clear **MVC pattern** with strict separation of concerns:

```
resellia/
│
├── index.php                   # Homepage — recent listings + global stats
├── shop.php                    # Catalogue with filters, sort, pagination
├── product.php                 # Product page + similar listings
├── faq.php / regles.php / contact.php
│
├── auth/                       # Sign up, login, password reset
│   ├── login.php
│   ├── register.php
│   └── forgot-password.php
│
├── account/                    # Profile, settings, mode switch
│   ├── profile.php
│   ├── settings.php
│   └── switch-mode.php
│
├── buyer/                      # Buyer space
│   ├── cart.php
│   ├── checkout.php
│   ├── orders.php
│   ├── order-detail.php
│   └── wishlist.php
│
├── seller/                     # Seller space
│   ├── dashboard.php
│   ├── product-add.php
│   ├── product-edit.php
│   ├── product-delete.php
│   ├── products.php
│   └── orders.php
│
├── admin/                      # BDE Admin panel
│   ├── index.php
│   ├── users.php
│   ├── products.php
│   ├── orders.php
│   └── reports.php
│
├── api/                        # Internal AJAX endpoints
│   ├── wishlist-toggle.php
│   ├── product-search.php
│   ├── cart-add.php
│   └── cart-remove.php
│
├── includes/                   # Infrastructure layer
│   ├── db.php                  # PDO connection
│   ├── functions.php           # Sanitisation, CSRF, auth helpers
│   ├── config.php              # BASE_URL
│   ├── header.php / footer.php
│   ├── auth-guard.php          # Middleware: authentication required
│   ├── seller-guard.php        # Middleware: seller mode required
│   └── admin-guard.php         # Middleware: admin role required
│
├── assets/                     # CSS, JS, images
└── database/
    └── ensam_market.sql        # Full database schema
```

---

## Database Schema

The schema includes **9 tables** covering the full business domain:

| Table | Description | Key Columns |
|---|---|---|
| `users` | Student accounts (buyers & sellers) | `mode_actuel`, `role`, `is_verified` |
| `categories` | 8 product categories | `slug`, `icon` |
| `products` | Listings published by sellers | `status` (active / pending / banned), `condition_p`, `views` |
| `orders` | Orders placed by buyers | `status` (5 states), `address`, `note` |
| `order_items` | Order lines (product × qty × unit price) | `qty`, `price_unit` |
| `cart` | Active shopping cart | `qty` |
| `wishlist` | Saved items | `user_id`, `product_id` |
| `reviews` | Seller ratings & reviews | `rating`, `seller_id` |
| `reports` | Inappropriate listing reports | `reason`, `status` |

> The full SQL schema is available in `database/ensam_market.sql`

---

## Features by Module

### 🛍️ Buyer Space

| Feature | Detail |
|---|---|
| Filterable catalogue | By category, condition (`new` / `good` / `used`), sorted by price / date / popularity |
| Shopping cart | AJAX add/remove, session persistence |
| Checkout | Delivery address or on-campus pickup point |
| Order tracking | 5 statuses: `pending → confirmed → shipped → delivered → cancelled` |
| Wishlist | AJAX toggle, personal saved list |
| Product page | Image gallery, seller info, average rating, similar listings |

### 🏪 Seller Space

| Feature | Detail |
|---|---|
| Dashboard | Overview of active listings and incoming orders |
| New listing | Multi-photo upload, description, price, condition — submitted as `pending` |
| Edit & delete | After any edit, listing returns to admin validation queue |
| Order management | Confirm, ship, deliver, or cancel orders |

### 🔐 Authentication & Accounts

| Feature | Detail |
|---|---|
| Registration | Email + password + field of study + year |
| Login / Logout | Secure session management |
| Forgot password | Token-based reset flow |
| Profile & avatar | Personal information update |
| Mode switch | Instant Buyer ↔ Seller toggle on a single account |

### 🛠️ Admin Panel

| Feature | Detail |
|---|---|
| Listing moderation | Approve or reject `pending` products |
| User management | Suspensions, role assignment |
| Global order tracking | View across all transactions |
| Reports & stats | Platform-wide activity overview |
| Report handling | Review and resolve flagged listings |

---

## Security

| Mechanism | Implementation |
|---|---|
| Password hashing | `bcrypt` via `password_hash()` |
| CSRF protection | Token generated with `random_bytes(32)`, verified on every POST |
| Input sanitisation | `strip_tags()` + `htmlspecialchars()` on all user input |
| SQL injection | 100% PDO prepared statements — zero raw queries |
| Middleware guards | `auth-guard`, `seller-guard`, `admin-guard` on every protected route |

---

## Getting Started

ReSellia is live and requires no installation. Any ENSAM student can register and use the platform directly.

**🔗 Live URL:** [monprojetphp.infinityfreeapp.com](https://monprojetphp.infinityfreeapp.com/)

**User workflow:**
1. **Access** — Open the link and land on the homepage with the latest listings.
2. **Register** — Create an account with your email and ENSAM details (field, year).
3. **Browse** — Filter the catalogue, add items to your cart or wishlist.
4. **Buy** — Checkout → the seller receives the order and arranges on-campus handoff.
5. **Sell** — Switch to Seller mode → publish a listing → wait for admin approval → listing goes live.

---

## Local Installation

**Prerequisites:** PHP 8.x · MySQL 5.7+ · Apache/Nginx (or XAMPP/WAMP)

```bash
# 1. Clone the repository
git clone https://github.com/<your-username>/resellia-ensam-market.git
cd resellia-ensam-market

# 2. Import the database
mysql -u root -p < database/ensam_market.sql

# 3. Configure the DB connection
# Edit includes/db.php with your MySQL credentials

# 4. Set the base URL
# Edit includes/config.php
define('BASE_URL', '/resellia-ensam-market/');

# 5. Start the dev server (CLI)
php -S localhost:8000

# OR drop the folder into htdocs/ (XAMPP) and open:
# http://localhost/resellia-ensam-market/
```

---

## Roadmap

- [x] Full MVC architecture with role-based middleware guards
- [x] Dual Buyer / Seller mode on a single account
- [x] AJAX cart and wishlist without page reload
- [x] Order workflow with 5 statuses
- [x] Admin panel with moderation, reports, and user management
- [x] Cloud deployment on InfinityFree
- [ ] Internal messaging system between buyer and seller
- [ ] Real-time notifications (new orders, listing approval)
- [ ] Full seller rating & review front-end
- [ ] PDF export for orders and invoices
- [ ] Mobile payment integration (CMI, Wave…)

---

<div align="center">
  <sub>Built for the ENSAM Meknès student community — peer-to-peer commerce, on campus.</sub>
</div>
