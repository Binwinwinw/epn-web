<?php
/**
 * Accueil après sélection du site
 * Équivalent à ACCUEILSITE2.vb
 */
$routerPublicUrl = '../../public/index.php';
if (!defined('EPN_APP_ROUTER')) {
    $redirectQuery = $_GET;
    $redirectQuery['page'] = 'accueil-site';
    header('Location: ' . $routerPublicUrl . '?' . http_build_query($redirectQuery));
    exit;
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/nav-logout.php';

$assetBase = $assetBase ?? 'assets';
$homeAppUrl = $homeAppUrl ?? 'index.php?page=accueil';
$inscriptionsUrl = $inscriptionsUrl ?? 'index.php?page=inscriptions';
$frequentationsUrl = $frequentationsUrl ?? 'index.php?page=frequentations';
$ateliersUrl = $ateliersUrl ?? 'index.php?page=ateliers';
$materielUrl = $materielUrl ?? 'index.php?page=materiel';
$statistiquesUrl = $statistiquesUrl ?? 'index.php?page=statistiques';
$styleVersion = (string) (@filemtime(__DIR__ . '/../../public/assets/css/style.css') ?: time());

$site = isset($_GET['site']) ? strtoupper($_GET['site']) : 'BAC';
if (!in_array($site, ['BAC', 'MAC'])) {
    $site = 'BAC';
}

// Déterminer les couleurs selon le site
$siteConfig = [
    'BAC' => [
        'nom' => 'EPN SITE BAC',
        'couleur' => '#2563eb', // Bleu professionnel
        'couleurFond' => '#dbeafe', // Bleu très clair
        'couleurTexte' => '#1e40af' // Bleu foncé
    ],
    'MAC' => [
        'nom' => 'EPN SITE MAC',
        'couleur' => '#0891b2', // Teal/Cyan moderne
        'couleurFond' => '#cffafe', // Cyan très clair
        'couleurTexte' => '#0e7490' // Teal foncé
    ]
];

$config = $siteConfig[$site];

// Formatage de la date en français (compatible PHP 8.1+)
if (class_exists('IntlDateFormatter')) {
    // Utiliser IntlDateFormatter (méthode recommandée)
    $formatter = new IntlDateFormatter(
        'fr_FR',
        IntlDateFormatter::FULL,
        IntlDateFormatter::NONE,
        date_default_timezone_get(),
        IntlDateFormatter::GREGORIAN,
        'EEEE d MMMM yyyy'
    );
    $dateJour = $formatter->format(new DateTime());
} else {
    // Fallback pour les serveurs sans extension Intl (sans utiliser strftime)
    $timestamp = time();
    $joursSemaine = ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
    $mois = ['', 'janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];
    $jourNum = (int)date('w', $timestamp);
    $jourNom = $joursSemaine[$jourNum];
    $jourMois = date('d', $timestamp);
    $moisNum = (int)date('n', $timestamp);
    $moisNom = $mois[$moisNum];
    $annee = date('Y', $timestamp);
    $dateJour = "$jourNom $jourMois $moisNom $annee";
}

// Horaires selon le jour (récupération fiable du jour de la semaine)
$jourSemaine = date('w'); // 0 (dimanche) à 6 (samedi)
$jours = ['dim', 'lun', 'mar', 'mer', 'jeu', 'ven', 'sam'];
$jour = $jours[$jourSemaine];
$horaires = getHoraires($jour);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $config['nom']; ?> - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="<?= h($assetBase) ?>/css/style.css?v=<?= h($styleVersion) ?>">
    <link rel="stylesheet" href="<?= h($assetBase) ?>/css/uiverse-modern.css?v=<?= h($styleVersion) ?>">
    <style>
        :root {
            --site-color: <?php echo $config['couleur']; ?>;
            --site-bg: <?php echo $config['couleurFond']; ?>;
            --site-text: <?php echo $config['couleurTexte']; ?>;
        }

        .menu-site .btn-menu {
            flex-direction: row;
            justify-content: flex-start;
            gap: 10px;
        }

        .menu-site .btn-menu .icon {
            background-color: <?php echo $config['couleurFond']; ?> !important;
            color: <?php echo $config['couleurTexte']; ?> !important;
        }

        .bento-sites {
            margin-top: 24px;
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
        }

        .bento-card {
            position: relative;
            overflow: hidden;
            border-radius: 20px;
            padding: 20px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 20px 45px rgba(2, 6, 23, 0.14);
            color: #ffffff;
            min-height: 170px;
        }

        .bento-card::before {
            content: '';
            position: absolute;
            inset: -35% auto auto -20%;
            width: 160px;
            height: 160px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.14);
            filter: blur(2px);
        }

        .bento-card > * {
            position: relative;
            z-index: 1;
        }

        .bento-card.mac {
            background: linear-gradient(135deg, #0e7490 0%, #06b6d4 100%);
        }

        .bento-card.bac {
            background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 100%);
        }

        .bento-label {
            display: inline-block;
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 999px;
            padding: 6px 10px;
            margin-bottom: 10px;
        }

        .bento-title {
            margin: 0 0 8px;
            font-size: 2rem;
            line-height: 1;
            font-weight: 800;
        }

        .bento-text {
            margin: 0 0 16px;
            font-size: 0.98rem;
            line-height: 1.45;
            opacity: 0.95;
        }

        .bento-action {
            display: inline-block;
            text-decoration: none;
            background: rgba(255, 255, 255, 0.94);
            color: #0f172a;
            font-weight: 700;
            border-radius: 12px;
            padding: 10px 14px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .bento-action:hover,
        .bento-action:focus-visible {
            transform: translateY(-2px);
            box-shadow: 0 8px 22px rgba(15, 23, 42, 0.22);
            outline: none;
        }

        @media (max-width: 900px) {
            .bento-sites {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container-site" data-site="<?php echo $site; ?>">
        <?php renderRetourTop(h($homeAppUrl), '← Retour à l\'accueil'); ?>
        <header class="header-site" style="background-color: <?php echo $config['couleur']; ?>;">
            <h1><?php echo $config['nom']; ?></h1>
            <div class="date-display" id="dateJour"><?php echo $dateJour; ?></div>
        </header>

        <nav class="menu-site">
            <button class="btn-menu" onclick="window.location.href='<?= h($inscriptionsUrl) ?>&site=<?php echo $site; ?>'">
                <span class="icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M14 3H8a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V9M14 3v6h6M9 13h6M9 17h4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
                <span>Inscriptions</span>
            </button>
            <button class="btn-menu" onclick="window.location.href='<?= h($frequentationsUrl) ?>&site=<?php echo $site; ?>'">
                <span class="icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M16 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8M19 8a3 3 0 0 1 0 6M22 21v-2a4 4 0 0 0-3-3.87" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
                <span>Fréquentations</span>
            </button>
            <button class="btn-menu" onclick="window.location.href='<?= h($ateliersUrl) ?>&site=<?php echo $site; ?>'">
                <span class="icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M2 8l10-5 10 5-10 5-10-5Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><path d="M6 10v5c0 1.5 2.7 3 6 3s6-1.5 6-3v-5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
                <span>Ateliers</span>
            </button>
            <button class="btn-menu" onclick="window.location.href='<?= h($materielUrl) ?>&site=<?php echo $site; ?>'">
                <span class="icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="3" y="4" width="18" height="12" rx="2" stroke="currentColor" stroke-width="1.8"/><path d="M12 16v4M8 20h8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
                <span>Matériel</span>
            </button>
            <button class="btn-menu" onclick="window.location.href='<?= h($statistiquesUrl) ?>&site=<?php echo $site; ?>'">
                <span class="icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4 19h16" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><path d="M7 16V10" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><path d="M12 16V6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><path d="M17 16v-3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg></span>
                <span>Statistiques</span>
            </button>
            <?php renderLogoutMenuBtn(); ?>
        </nav>

        <div class="horaires-info">
            <div class="horaire-section">
                <h3>MATIN</h3>
                <div class="horaire-times">
                    <span class="ouverture"><?php echo $horaires['matin']['ouverture']; ?></span>
                    <span>-</span>
                    <span class="fermeture"><?php echo $horaires['matin']['fermeture']; ?></span>
                </div>
            </div>
            <div class="horaire-section">
                <h3>APRÈS-MIDI</h3>
                <div class="horaire-times">
                    <span class="ouverture"><?php echo $horaires['apresmidi']['ouverture']; ?></span>
                    <span>-</span>
                    <span class="fermeture"><?php echo $horaires['apresmidi']['fermeture']; ?></span>
                </div>
            </div>
            <div class="gratuit-info">
                <span>Consultation libre : GRATUIT</span>
            </div>
        </div>

        <section class="bento-sites" aria-label="Accès rapide par site">
            <article class="bento-card mac">
                <span class="bento-label">Site EPN</span>
                <h2 class="bento-title">MAC</h2>
                <p class="bento-text">Accéder rapidement aux modules du site MAC pour démarrer la saisie.</p>
                <a class="bento-action" href="index.php?page=accueil-site&amp;site=MAC">Ouvrir MAC</a>
            </article>

            <article class="bento-card bac">
                <span class="bento-label">Site EPN</span>
                <h2 class="bento-title">BAC</h2>
                <p class="bento-text">Basculer vers le site BAC en un clic pour continuer le suivi.</p>
                <a class="bento-action" href="index.php?page=accueil-site&amp;site=BAC">Ouvrir BAC</a>
            </article>
        </section>

        </div>

    <script>
        function updateDate() {
            const now = new Date();
            const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            document.getElementById('dateJour').textContent = now.toLocaleDateString('fr-FR', options);
        }
        updateDate();
        setInterval(updateDate, 60000);
    </script>
</body>
</html>

<?php
function getHoraires($jour) {
    $horaires = [
        'lun' => ['matin' => ['ouverture' => '07:40:00', 'fermeture' => '12:30:00'], 
                  'apresmidi' => ['ouverture' => '14:00:00', 'fermeture' => '16:30:00']],
        'mar' => ['matin' => ['ouverture' => '07:40:00', 'fermeture' => '12:30:00'], 
                  'apresmidi' => ['ouverture' => '14:00:00', 'fermeture' => '16:30:00']],
        'mer' => ['matin' => ['ouverture' => '07:40:00', 'fermeture' => '12:30:00'], 
                  'apresmidi' => ['ouverture' => '14:00:00', 'fermeture' => '16:30:00']],
        'jeu' => ['matin' => ['ouverture' => '07:40:00', 'fermeture' => '13:30:00'], 
                  'apresmidi' => ['ouverture' => '00:00:00', 'fermeture' => '00:00:00']],
        'ven' => ['matin' => ['ouverture' => '07:40:00', 'fermeture' => '13:30:00'], 
                  'apresmidi' => ['ouverture' => '00:00:00', 'fermeture' => '00:00:00']],
        'sam' => ['matin' => ['ouverture' => '09:00:00', 'fermeture' => '12:00:00'], 
                  'apresmidi' => ['ouverture' => '00:00:00', 'fermeture' => '00:00:00']],
        'dim' => ['matin' => ['ouverture' => '00:00:00', 'fermeture' => '00:00:00'], 
                  'apresmidi' => ['ouverture' => '00:00:00', 'fermeture' => '00:00:00']]
    ];
    
    $jourKey = substr($jour, 0, 3);
    return isset($horaires[$jourKey]) ? $horaires[$jourKey] : $horaires['lun'];
}
?>

