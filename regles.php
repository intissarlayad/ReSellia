<?php
/**
 * regles.php — Règles de la communauté ENSAM Market
 */
session_start();
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Règles de la communauté';
$activeNav = '';
include 'includes/header.php';

$rules = [
    ['icon'=>'✅','color'=>'green','titre'=>'1. Accès réservé aux étudiants ENSAM','intro'=>null,
     'items'=>[
        "La plateforme est exclusivement réservée aux étudiants inscrits à l'ENSAM.",
        "L'utilisation d'un faux nom ou d'un email non étudiant est interdite.",
        "Un seul compte par étudiant est autorisé.",
        "La création de comptes multiples pour contourner une suspension est interdite.",
     ]],
    ['icon'=>'📦','color'=>'green','titre'=>'2. Annonces honnêtes et conformes','intro'=>null,
     'items'=>[
        "Les photos doivent représenter <strong>l'article réel</strong> que tu vends.",
        "L'état (Neuf / Bon état / Usagé) doit être déclaré honnêtement.",
        "Le prix doit être raisonnable et cohérent avec le marché étudiant.",
        "Les annonces dupliquées pour le même article sont interdites.",
        "Il est interdit de publier une annonce pour un article que tu ne possèdes pas encore.",
     ]],
    ['icon'=>'🚫','color'=>'red','titre'=>'3. Articles interdits à la vente',
     'intro'=>'Il est <strong style="color:var(--red)">strictement interdit</strong> de vendre ou proposer :',
     'items'=>[
        "Substances illicites ou médicaments sans prescription",
        "Armes, objets dangereux ou contrefaçons",
        "Contenu volé ou piraté (logiciels crackés, examens volés…)",
        "Contenu pornographique, choquant ou discriminatoire",
        "Services trompeurs ou illégaux",
        "Tout article contraire à la loi marocaine",
     ]],
    ['icon'=>'🤝','color'=>'green','titre'=>'4. Transactions en bonne foi','intro'=>null,
     'items'=>[
        "Une commande confirmée doit être honorée par le vendeur.",
        "Le vendeur s'engage à remettre l'article dans un délai raisonnable.",
        "En cas d'annulation, le vendeur doit prévenir l'acheteur immédiatement.",
        "Il est interdit de demander un paiement sans intention de livrer.",
        "Les arnaques et escroqueries sont passibles de signalement aux autorités.",
     ]],
    ['icon'=>'💬','color'=>'green','titre'=>'5. Respect et bienveillance','intro'=>null,
     'items'=>[
        "Toute communication entre étudiants doit rester respectueuse.",
        'Le harcèlement, les insultes et les menaces sont <strong style="color:var(--red)">strictement interdits</strong>.',
        "Il est interdit d'utiliser la plateforme pour du spam ou du démarchage.",
        "Les avis doivent être honnêtes et constructifs.",
     ]],
    ['icon'=>'⚠️','color'=>'gold','titre'=>'6. Sanctions applicables','intro'=>null,
     'items'=>[
        '<span style="color:var(--gold)">Avertissement</span> — infractions mineures (première occurrence)',
        '<span style="color:orange">Suspension temporaire</span> — blocage du compte 7 à 30 jours',
        '<span style="color:var(--red)">Suspension définitive</span> — infractions graves ou récidives',
        '<span style="color:var(--red)">Signalement aux autorités</span> — escroquerie ou vente illicite',
     ]],
];
?>

<div class="page-hero">
    <div class="container">
        <h1>📋 Règles de la communauté</h1>
        <p>Pour que la plateforme reste un espace de confiance entre étudiants ENSAM</p>
    </div>
</div>

<div class="page-wrap">
<div class="container-md">

<div class="card" style="margin-bottom:2rem;border-color:rgba(26,122,74,.35);" data-reveal>
    <p style="color:var(--text);line-height:1.8;font-size:.92rem;">
        ENSAM Market est une plateforme créée
        <strong style="color:var(--white)">par et pour les étudiants</strong> de l'ENSAM.
        Son bon fonctionnement repose sur le respect mutuel et la confiance.
        En t'inscrivant, tu acceptes ces règles.
        Tout manquement peut entraîner la
        <strong style="color:var(--red)">suspension définitive du compte</strong>.
    </p>
</div>

<?php foreach ($rules as $rule):
    $borderCol = match($rule['color']) {
        'red'  => 'rgba(192,57,43,.25)',
        'gold' => 'rgba(212,168,67,.2)',
        default=> 'var(--border)',
    };
    $iconBg = match($rule['color']) {
        'red'  => 'rgba(192,57,43,.12)',
        'gold' => 'rgba(212,168,67,.1)',
        default=> 'rgba(26,122,74,.15)',
    };
    $iconBorder = match($rule['color']) {
        'red'  => 'var(--red)',
        'gold' => 'var(--gold)',
        default=> 'var(--green)',
    };
?>
<div style="display:flex;align-items:flex-start;gap:1.2rem;margin-bottom:1.2rem;" data-reveal>
    <div style="width:44px;height:44px;border-radius:50%;background:<?= $iconBg ?>;
                border:1px solid <?= $iconBorder ?>;display:flex;align-items:center;
                justify-content:center;font-size:1.2rem;flex-shrink:0;margin-top:2px;">
        <?= $rule['icon'] ?>
    </div>
    <div class="card" style="flex:1;padding:1.2rem 1.5rem;border-color:<?= $borderCol ?>;">
        <h3 style="font-family:var(--font-head);font-weight:700;color:var(--white);
                   margin-bottom:.8rem;"><?= e($rule['titre']) ?></h3>
        <?php if ($rule['intro']): ?>
        <p style="color:var(--text);font-size:.87rem;margin-bottom:.7rem;"><?= $rule['intro'] ?></p>
        <?php endif; ?>
        <ul style="color:var(--text);font-size:.87rem;line-height:1.85;list-style:disc;padding-left:1.2rem;">
            <?php foreach ($rule['items'] as $item): ?>
            <li><?= $item ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>
<?php endforeach; ?>

<div style="text-align:center;padding:1.5rem 2rem;background:var(--card);
            border:1px solid var(--border);border-radius:var(--radius-lg);" data-reveal>
    <p style="font-size:.8rem;color:var(--muted);margin-bottom:1rem;">
        Ces règles peuvent être mises à jour par l'équipe du BDE.
        Dernière mise à jour : <strong style="color:var(--text)"><?= date('d/m/Y') ?></strong>
    </p>
    <div style="display:flex;gap:1rem;justify-content:center;flex-wrap:wrap;">
        <a href="<?= BASE_URL ?>faq.php"     class="btn btn-secondary btn-sm">Voir la FAQ</a>
        <a href="<?= BASE_URL ?>contact.php" class="btn btn-primary btn-sm">Signaler un problème</a>
    </div>
</div>

</div>
</div>

<?php include 'includes/footer.php'; ?>
