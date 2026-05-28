<!-- Mobile Nav Menu -->
<div class="mobile-nav">
    <a href="<?= BASE_URL ?>index.php">Accueil</a>
    <a href="<?= BASE_URL ?>shop.php">Catalogue</a>
    <?php if (isLoggedIn()): ?>
        <a href="<?= BASE_URL ?>buyer/orders.php">Mes Commandes</a>
        <a href="<?= BASE_URL ?>account/profile.php">Mon Profil</a>
        <a href="<?= BASE_URL ?>auth/logout.php" style="color:#ff5c5c;">Déconnexion</a>
    <?php else: ?>
        <a href="<?= BASE_URL ?>auth/login.php">Connexion</a>
        <a href="<?= BASE_URL ?>auth/register.php">Inscription</a>
    <?php endif; ?>
</div>

<!-- Footer -->
<footer class="footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-col">
                <div class="nav-logo footer-logo">ENSAM<span>Market</span><span class="nav-logo-dot">●</span></div>
                <p style="margin-top:1rem;color:var(--muted);line-height:1.6;">
                    La plateforme d'échange dédiée aux étudiants de l'ENSAM. Achetez, vendez, échangez en toute confiance.
                </p>
            </div>
            <div class="footer-col">
                <h4>Liens rapides</h4>
                <ul>
                    <li><a href="<?= BASE_URL ?>index.php">Accueil</a></li>
                    <li><a href="<?= BASE_URL ?>shop.php">Catalogue</a></li>
                    <?php if (!isLoggedIn()): ?>
                        <li><a href="<?= BASE_URL ?>auth/register.php">Inscription</a></li>
                    <?php endif; ?>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Support</h4>
                <ul>
                    <li><a href="<?= BASE_URL ?>faq.php">FAQ</a></li>
                    <li><a href="<?= BASE_URL ?>regles.php">Règles de la communauté</a></li>
                    <li><a href="<?= BASE_URL ?>contact.php">Contact BDE</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <div>&copy; <?= date('Y') ?> ENSAM Market. Projet étudiant.</div>
            <div>I & B</div>
        </div>
    </div>
</footer>