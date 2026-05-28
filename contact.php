<?php
/**
 * contact.php — Infos de contact du BDE ENSAM (sans formulaire)
 */
session_start();
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Contact BDE';
$activeNav = '';
$sent   = false;
$errors = [];
$vals   = ['nom'=>'','email'=>'','sujet'=>'','message'=>''];

// Pré-remplir si connecté
$user = currentUser();
if ($user) {
    $vals['nom']   = $user['prenom'] . ' ' . $user['nom'];
    $vals['email'] = $user['email'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf'] ?? '')) { $errors[] = 'Token invalide.'; }

    $vals['nom']     = sanitize($_POST['nom']     ?? '');
    $vals['email']   = strtolower(trim($_POST['email']   ?? ''));
    $vals['sujet']   = sanitize($_POST['sujet']   ?? '');
    $vals['message'] = sanitize($_POST['message'] ?? '');

    if (strlen($vals['nom'])     < 2) $errors[] = 'Nom invalide.';
    if (!filter_var($vals['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Email invalide.';
    if (strlen($vals['sujet'])   < 3) $errors[] = 'Sujet trop court.';
    if (strlen($vals['message']) < 10) $errors[] = 'Message trop court (min 10 caractères).';

    if (empty($errors)) {
        // TODO: envoyer par email
        // mail('bde@ensam.ac.ma', '[ENSAM Market] ' . $vals['sujet'],
        //     "De : {$vals['nom']} <{$vals['email']}>\n\n{$vals['message']}");
        $sent = true;
    }
}

include 'includes/header.php';
?>

<div class="page-hero">
    <div class="container">
        <h1>📬 Contacter le BDE</h1>
        <p>Une question, un problème ou une suggestion ? L'équipe te répond sous 48h.</p>
    </div>
</div>

<div class="page-wrap">
<div class="container-md">

    <div style="display:grid;grid-template-columns:1fr 1.4fr;gap:2.5rem;align-items:start;">

        <!-- Infos BDE -->
        <div data-reveal>

            <div class="card" style="margin-bottom:1.2rem;">
                <h3 style="font-family:var(--font-head);font-weight:700;color:var(--white);
                           margin-bottom:1.2rem;font-size:1rem;">🏫 Bureau des Étudiants</h3>

                <div style="display:flex;flex-direction:column;gap:.9rem;">

                    <div style="display:flex;align-items:center;gap:.9rem;">
                        <div style="width:38px;height:38px;border-radius:50%;
                                    background:rgba(26,122,74,.15);border:1px solid var(--green);
                                    display:flex;align-items:center;justify-content:center;
                                    flex-shrink:0;font-size:1rem;">📧</div>
                        <div>
                            <div style="font-size:.72rem;color:var(--muted);margin-bottom:.1rem;">Email</div>
                            <a href="mailto:bde@ensam.ac.ma"
                               style="color:var(--green-lt);font-size:.88rem;">
                                bde@ensam.ac.ma
                            </a>
                        </div>
                    </div>

                    <div style="display:flex;align-items:center;gap:.9rem;">
                        <div style="width:38px;height:38px;border-radius:50%;
                                    background:rgba(26,122,74,.15);border:1px solid var(--green);
                                    display:flex;align-items:center;justify-content:center;
                                    flex-shrink:0;font-size:1rem;">📍</div>
                        <div>
                            <div style="font-size:.72rem;color:var(--muted);margin-bottom:.1rem;">Localisation</div>
                            <div style="color:var(--text);font-size:.88rem;">
                                Local BDE — Bâtiment Administration
                            </div>
                        </div>
                    </div>

                    <div style="display:flex;align-items:center;gap:.9rem;">
                        <div style="width:38px;height:38px;border-radius:50%;
                                    background:rgba(26,122,74,.15);border:1px solid var(--green);
                                    display:flex;align-items:center;justify-content:center;
                                    flex-shrink:0;font-size:1rem;">🕐</div>
                        <div>
                            <div style="font-size:.72rem;color:var(--muted);margin-bottom:.1rem;">Horaires</div>
                            <div style="color:var(--text);font-size:.88rem;">
                                Lun – Ven : 10h00 – 17h00
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Réseaux sociaux -->
            <div class="card">
                <h3 style="font-family:var(--font-head);font-weight:700;color:var(--white);
                           margin-bottom:1rem;font-size:1rem;">📱 Réseaux sociaux</h3>
                <div style="display:flex;flex-direction:column;gap:.7rem;">
                    <a href="#" target="_blank" rel="noopener"
                       style="display:flex;align-items:center;gap:.7rem;padding:.6rem .8rem;
                              background:var(--deep);border:1px solid var(--border);
                              border-radius:var(--radius);font-size:.85rem;color:var(--text);
                              transition:var(--transition);"
                       onmouseover="this.style.borderColor='#E1306C';this.style.color='#E1306C'"
                       onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--text)'">
                        <span style="font-size:1.1rem;">📸</span> Instagram — @bde_ensam
                    </a>
                    <a href="#" target="_blank" rel="noopener"
                       style="display:flex;align-items:center;gap:.7rem;padding:.6rem .8rem;
                              background:var(--deep);border:1px solid var(--border);
                              border-radius:var(--radius);font-size:.85rem;color:var(--text);
                              transition:var(--transition);"
                       onmouseover="this.style.borderColor='#25D366';this.style.color='#25D366'"
                       onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--text)'">
                        <span style="font-size:1.1rem;">💬</span> Groupe WhatsApp étudiants
                    </a>
                    <a href="#" target="_blank" rel="noopener"
                       style="display:flex;align-items:center;gap:.7rem;padding:.6rem .8rem;
                              background:var(--deep);border:1px solid var(--border);
                              border-radius:var(--radius);font-size:.85rem;color:var(--text);
                              transition:var(--transition);"
                       onmouseover="this.style.borderColor='#0A66C2';this.style.color='#0A66C2'"
                       onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--text)'">
                        <span style="font-size:1.1rem;">💼</span> LinkedIn — ENSAM Meknès
                    </a>
                </div>
            </div>

        </div>

        <!-- Formulaire -->
        <div data-reveal>
            <?php if ($sent): ?>
            <div style="text-align:center;padding:3rem 2rem;background:var(--card);
                        border:1px solid rgba(26,122,74,.35);border-radius:var(--radius-lg);">
                <div style="font-size:3rem;margin-bottom:1rem;">✅</div>
                <h2 style="font-family:var(--font-head);color:var(--white);margin-bottom:.7rem;">
                    Message envoyé !
                </h2>
                <p style="color:var(--text);margin-bottom:1.5rem;font-size:.9rem;">
                    Le BDE te répondra à <strong><?= e($vals['email']) ?></strong> sous 48h.
                </p>
                <a href="<?= BASE_URL ?>index.php" class="btn btn-primary">Retour à l'accueil</a>
            </div>

            <?php else: ?>

            <?php foreach ($errors as $err): ?>
            <div class="alert alert-error">⚠ <?= e($err) ?></div>
            <?php endforeach; ?>

            

            <?php endif; ?>
        </div>

    </div>

    <!-- FAQ link -->
    <div style="text-align:center;margin-top:2.5rem;padding:1.2rem;background:var(--card);
                border:1px solid var(--border);border-radius:var(--radius-lg);" data-reveal>
        <p style="color:var(--muted);font-size:.85rem;margin-bottom:.8rem;">
            Avant de contacter le BDE, consulte peut-être la FAQ — ta réponse y est sûrement !
        </p>
        <a href="<?= BASE_URL ?>faq.php" class="btn btn-secondary btn-sm">Voir la FAQ →</a>
    </div>

</div>
</div>

<?php include 'includes/footer.php'; ?>
