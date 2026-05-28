<?php
/**
 * faq.php — Foire Aux Questions ENSAM Market
 */
session_start();
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'FAQ';
$activeNav = '';
include 'includes/header.php';

$faqs = [
    "Compte & Inscription" => [
        ["q"=>"Qui peut s'inscrire sur ENSAM Market ?",
         "r"=>"La plateforme est réservée aux étudiants de l'ENSAM. Nous recommandons d'utiliser ton adresse email institutionnelle pour faciliter la vérification."],
        ["q"=>"Quelle est la différence entre le mode Acheteur et le mode Vendeur ?",
         "r"=>"Tu as un seul compte, mais deux modes. En mode <strong>Acheteur</strong> tu parcours le catalogue et passes des commandes. En mode <strong>Vendeur</strong> tu publies des annonces et gères tes ventes. Tu peux basculer entre les deux à tout moment depuis ton profil."],
        ["q"=>"Comment passer en mode Vendeur ?",
         "r"=>"Va dans <em>Mon Compte → Changer de mode</em> puis clique sur « Passer en mode Vendeur ». C'est immédiat et gratuit."],
        ["q"=>"J'ai oublié mon mot de passe, que faire ?",
         "r"=>"Clique sur « Mot de passe oublié ? » sur la page de connexion. Entre ton email et tu recevras un lien de réinitialisation."],
    ],
    "Acheter" => [
        ["q"=>"Comment passer une commande ?",
         "r"=>"Parcours le catalogue, clique sur un article, ajoute-le au panier, puis valide depuis la page Panier en indiquant un point de retrait ou une adresse."],
        ["q"=>"Le paiement est-il en ligne ?",
         "r"=>"Non. ENSAM Market est une plateforme d'échange entre étudiants. Le règlement se fait en espèces ou par virement directement entre acheteur et vendeur, lors de la remise sur le campus."],
        ["q"=>"Comment contacter le vendeur après une commande ?",
         "r"=>"Dans <em>Mes Commandes → Voir le détail</em>, l'email du vendeur est affiché et cliquable pour le contacter directement."],
        ["q"=>"Un article est marqué épuisé, que faire ?",
         "r"=>"Ajoute-le à ta Wishlist pour ne pas l'oublier, et contacte le vendeur par email pour vérifier s'il peut remettre du stock."],
    ],
    "Vendre" => [
        ["q"=>"Comment publier une annonce ?",
         "r"=>"Passe en mode Vendeur, clique sur <em>+ Nouvelle annonce</em>, remplis le formulaire (titre, description, prix, photos, état) et soumets. Ton annonce sera visible après validation par un administrateur."],
        ["q"=>"Combien de temps prend la validation d'une annonce ?",
         "r"=>"Les annonces sont généralement validées sous 24h. Tu seras notifié dès que ton annonce est en ligne."],
        ["q"=>"Puis-je modifier une annonce après publication ?",
         "r"=>"Oui. Mais après modification, ton annonce repasse en attente de validation avant d'être de nouveau visible dans le catalogue."],
        ["q"=>"Quels articles peut-on vendre ?",
         "r"=>"Livres, polycopiés, matériel électronique, vêtements, fournitures, équipements sportifs, services (cours particuliers, aide projets…). Tout article illégal ou inapproprié est interdit et entraîne la suspension du compte."],
    ],
    "Sécurité & Signalements" => [
        ["q"=>"Que faire si une annonce est frauduleuse ou inappropriée ?",
         "r"=>"Utilise le bouton « Signaler » sur la fiche produit. Notre équipe admin traitera le signalement rapidement. En cas d'urgence, contacte directement le BDE."],
        ["q"=>"Mes données personnelles sont-elles en sécurité ?",
         "r"=>"Oui. Ton mot de passe est chiffré (bcrypt), tes données ne sont partagées qu'avec les autres étudiants ENSAM dans le cadre des échanges, et ne sont jamais vendues à des tiers."],
        ["q"=>"Un vendeur ne répond pas à ma commande, que faire ?",
         "r"=>"Contacte-le par email depuis le détail de la commande. Si aucune réponse après 48h, contacte le BDE via la page Contact."],
    ],
];
?>

<div class="page-hero">
    <div class="container">
        <h1>❓ Foire Aux Questions</h1>
        <p>Tout ce que tu dois savoir pour utiliser ENSAM Market</p>
    </div>
</div>

<div class="page-wrap">
    <div class="container-md">

        <!-- Recherche rapide -->
        <div style="margin-bottom:2.5rem;text-align:center;">
            <input type="text" id="faq-search" class="form-control"
                   placeholder="🔍 Chercher une question…"
                   style="max-width:480px;margin:0 auto;font-size:.95rem;padding:.85rem 1.2rem;" />
        </div>

        <!-- Sections -->
        <?php foreach ($faqs as $section => $items): ?>
        <div class="faq-section" style="margin-bottom:2.5rem;" data-reveal>

            <h2 style="font-family:var(--font-head);font-size:1.1rem;font-weight:700;
                       color:var(--green-lt);margin-bottom:1rem;
                       display:flex;align-items:center;gap:.6rem;">
                <span style="width:4px;height:1.1rem;background:var(--green);
                             border-radius:2px;display:inline-block;flex-shrink:0;"></span>
                <?= e($section) ?>
            </h2>

            <?php foreach ($items as $item): ?>
            <div class="faq-item" style="border:1px solid var(--border);border-radius:var(--radius);
                                         margin-bottom:.5rem;overflow:hidden;">
                <button class="faq-toggle"
                        style="width:100%;text-align:left;background:var(--card);border:none;
                               padding:1rem 1.2rem;cursor:pointer;
                               display:flex;justify-content:space-between;align-items:center;
                               color:var(--light);font-size:.88rem;font-weight:500;
                               font-family:var(--font-body);gap:1rem;transition:var(--transition);">
                    <span><?= e($item['q']) ?></span>
                    <span class="faq-icon"
                          style="color:var(--green);font-size:1.2rem;
                                 flex-shrink:0;transition:transform .25s;line-height:1;">+</span>
                </button>
                <div class="faq-body"
                     style="display:none;padding:1rem 1.2rem 1.2rem;background:var(--deep);
                            font-size:.87rem;color:var(--text);line-height:1.8;
                            border-top:1px solid var(--border);">
                    <?= $item['r'] ?>
                </div>
            </div>
            <?php endforeach; ?>

        </div>
        <?php endforeach; ?>

        <!-- CTA -->
        <div style="text-align:center;padding:2rem;background:var(--card);
                    border:1px solid var(--border);border-radius:var(--radius-lg);" data-reveal>
            <p style="color:var(--text);margin-bottom:1rem;">Tu n'as pas trouvé ta réponse ?</p>
            <a href="<?= BASE_URL ?>contact.php" class="btn btn-primary">Contacter le BDE →</a>
        </div>

    </div>
</div>

<script>
// Accordion
document.querySelectorAll('.faq-toggle').forEach(btn => {
    btn.addEventListener('click', () => {
        const body = btn.nextElementSibling;
        const icon = btn.querySelector('.faq-icon');
        const open = body.style.display === 'block';
        body.style.display = open ? 'none' : 'block';
        icon.textContent   = open ? '+' : '−';
        icon.style.transform = open ? '' : 'rotate(180deg)';
    });
});
// Recherche live
document.getElementById('faq-search').addEventListener('input', function() {
    const q = this.value.toLowerCase().trim();
    document.querySelectorAll('.faq-item').forEach(i => {
        i.style.display = (!q || i.textContent.toLowerCase().includes(q)) ? '' : 'none';
    });
    document.querySelectorAll('.faq-section').forEach(s => {
        const visible = [...s.querySelectorAll('.faq-item')].some(i => i.style.display !== 'none');
        s.style.display = visible ? '' : 'none';
    });
});
</script>

<?php include 'includes/footer.php'; ?>
