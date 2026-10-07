<?php
/**
 * Page d'accueil - Sélection du site EPN
 * Équivalent à ACCUEILSITE1.vb
 */
$routerPublicUrl = '../../public/index.php';
if (!defined('EPN_APP_ROUTER')) {
    $redirectQuery = $_GET;
    $redirectQuery['page'] = 'accueil';
    header('Location: ' . $routerPublicUrl . '?' . http_build_query($redirectQuery));
    exit;
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/nav-logout.php';

$assetBase = $assetBase ?? 'assets';
$apiBase = $apiBase ?? '../src/api';
$publicHomeUrl = $publicHomeUrl ?? $routerPublicUrl;
$helpUrl = $helpUrl ?? 'index.php?page=help';
$dashboardUrl = $dashboardUrl ?? 'index.php?page=tableau-bord';
$statistiquesUrl = $statistiquesUrl ?? 'index.php?page=statistiques';
$accueilSiteUrl = $accueilSiteUrl ?? 'index.php?page=accueil-site';
$tailwindVersion = (string) (@filemtime(__DIR__ . '/../../public/assets/css/tailwind.generated.css') ?: time());

if (class_exists('IntlDateFormatter')) {
    $formatter = new IntlDateFormatter(
        'fr_FR',
        IntlDateFormatter::LONG,
        IntlDateFormatter::NONE,
        date_default_timezone_get(),
        IntlDateFormatter::GREGORIAN,
        'EEEE d MMMM yyyy'
    );
    $dateJour = $formatter->format(new DateTime());
} else {
    $dateJour = date('d/m/Y');
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h(SITE_NAME) ?> - Accueil application</title>
    <link rel="stylesheet" href="<?= h($assetBase) ?>/css/tailwind.generated.css?v=<?= h($tailwindVersion) ?>">
    <link rel="stylesheet" href="<?= h($assetBase) ?>/css/design-system.css">
    <style>
        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: var(--font-family-base);
            color: var(--color-neutral-900);
            background:
                radial-gradient(circle at top left, rgba(0, 102, 204, 0.10), transparent 26%),
                radial-gradient(circle at bottom right, rgba(0, 168, 107, 0.10), transparent 24%),
                var(--color-neutral-50);
        }

        .skip-link {
            position: absolute;
            top: -60px;
            left: 16px;
            z-index: 100;
            background: var(--color-neutral-900);
            color: var(--color-white);
            padding: 12px 16px;
            border-radius: 12px;
        }

        .skip-link:focus {
            top: 16px;
        }

        .hero-surface {
            background: linear-gradient(135deg, #04162d 0%, var(--color-primary-dark) 46%, #0b7d79 100%);
        }

        .glass-panel {
            backdrop-filter: blur(14px);
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }

        .selection-card {
            transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
        }

        .selection-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 20px 40px rgba(15, 23, 42, 0.10);
        }

        .selection-card[data-site="BAC"]:hover {
            border-color: rgba(0, 102, 204, 0.35);
        }

        .selection-card[data-site="MAC"]:hover {
            border-color: rgba(0, 168, 107, 0.35);
        }

        .selection-card[data-site="dashboard"]:hover {
            border-color: rgba(245, 158, 11, 0.35);
        }

        .icon-wrap {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 52px;
            height: 52px;
            border-radius: 16px;
        }

        .icon-bac {
            background: rgba(0, 102, 204, 0.10);
            color: #0052a3;
        }

        .icon-mac {
            background: rgba(16, 185, 129, 0.12);
            color: #0f8a63;
        }

        .icon-dashboard {
            background: rgba(245, 158, 11, 0.14);
            color: #c47b07;
        }

        .sites-bento {
            background:
                radial-gradient(circle at 10% 0%, rgba(0, 102, 204, 0.10), transparent 40%),
                radial-gradient(circle at 90% 100%, rgba(0, 168, 107, 0.10), transparent 45%),
                #f8fafc;
        }

        .bento-card {
            position: relative;
            overflow: hidden;
            transition: transform 0.25s ease, box-shadow 0.25s ease;
        }

        .bento-card::before {
            content: '';
            position: absolute;
            width: 180px;
            height: 180px;
            border-radius: 999px;
            top: -70px;
            right: -50px;
            background: rgba(255, 255, 255, 0.16);
        }

        .bento-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 24px 36px rgba(15, 23, 42, 0.15);
        }

        .bento-mac {
            background: linear-gradient(135deg, #0e7490 0%, #06b6d4 100%);
        }

        .bento-bac {
            background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 100%);
        }

        a:focus-visible,
        button:focus-visible {
            outline: 3px solid var(--color-warning);
            outline-offset: 3px;
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation: none !important;
                transition: none !important;
                scroll-behavior: auto !important;
            }
        }
    </style>
</head>
<body>
    <a href="#contenu" class="skip-link">Aller au contenu</a>

    <header class="sticky top-0 z-50 border-b border-slate-200 bg-white/95 backdrop-blur">
        <nav class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-500">Ville de Ducos</p>
                <h1 class="text-xl font-bold text-slate-900 sm:text-2xl">EPN Gestion</h1>
            </div>
            <div class="flex items-center gap-2 sm:gap-3">
                <a href="<?= h($publicHomeUrl) ?>" class="hidden rounded-lg px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 sm:inline-flex">Accueil public</a>
                <a href="<?= h($statistiquesUrl) ?>" class="hidden rounded-lg px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 lg:inline-flex">Analyses</a>
                <a href="<?= h($helpUrl) ?>" class="hidden rounded-lg px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 lg:inline-flex">Aide</a>
                <a href="<?= h($dashboardUrl) ?>" class="rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow hover:bg-blue-700 sm:px-5">Vue globale</a>
                <button onclick="_epnLogout()" class="inline-flex items-center gap-2 rounded-lg px-4 py-2.5 text-sm font-semibold shadow" style="background:#dc2626;color:#111;border:1px solid rgba(127,29,29,0.3);" onmouseover="this.style.background='#b91c1c'" onmouseout="this.style.background='#dc2626'" title="Se déconnecter">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                    <span>Déconnexion</span>
                </button>
            </div>
        </nav>
    </header>

    <main id="contenu">
        <section class="hero-surface px-4 py-16 text-white sm:py-20">
            <div class="mx-auto grid max-w-7xl gap-10 lg:grid-cols-2 lg:items-center">
                <div>
                    <span class="mb-4 inline-flex rounded-full bg-white/15 px-4 py-1 text-sm font-medium text-blue-50">Accueil application • Sélection du site</span>
                    <h2 class="mb-6 max-w-2xl text-4xl font-extrabold leading-tight sm:text-5xl">Choisissez votre espace de travail en un clic.</h2>
                    <p class="mb-8 max-w-2xl text-lg text-blue-50 sm:text-xl">Accédez au site BAC, au site MAC ou à la vue d'ensemble pour piloter rapidement l'activité des Espaces Publics Numériques.</p>
                    <div class="flex flex-col gap-3 sm:flex-row">
                        <a href="#sites" class="rounded-xl bg-white px-6 py-3 text-center font-bold text-blue-700 shadow hover:bg-slate-100">Sélectionner un site</a>
                        <a href="<?= h($dashboardUrl) ?>" class="rounded-xl border border-white/60 px-6 py-3 text-center font-semibold text-white hover:bg-white/10">Ouvrir le tableau de bord</a>
                        <a href="<?= h($statistiquesUrl) ?>" class="rounded-xl border border-emerald-300/70 bg-emerald-400/10 px-6 py-3 text-center font-semibold text-white hover:bg-emerald-400/20">Statistiques et exports</a>
                    </div>
                    <div class="mt-8 flex flex-wrap gap-3 text-sm text-blue-50">
                        <span class="glass-panel rounded-full px-3 py-1"><?= h($dateJour) ?></span>
                        <span class="glass-panel rounded-full px-3 py-1">2 sites disponibles</span>
                        <span class="glass-panel rounded-full px-3 py-1">Accès rapide et sécurisé</span>
                    </div>
                </div>

                <div class="glass-panel rounded-3xl p-6 shadow-2xl">
                    <div class="mb-5 flex items-center justify-between">
                        <div>
                            <p class="text-sm text-blue-100">Portail métier</p>
                            <h3 class="text-2xl font-bold text-white">Navigation guidée</h3>
                        </div>
                        <span class="rounded-full bg-white/10 px-3 py-1 text-xs font-semibold text-blue-50">Prêt</span>
                    </div>
                    <div class="space-y-3">
                        <div class="rounded-2xl bg-white/90 p-4 text-slate-900">
                            <p class="text-sm text-slate-500">Étape 1</p>
                            <p class="mt-1 font-semibold">Choisir BAC, MAC ou la vue globale</p>
                        </div>
                        <div class="rounded-2xl bg-white/90 p-4 text-slate-900">
                            <p class="text-sm text-slate-500">Étape 2</p>
                            <p class="mt-1 font-semibold">Accéder aux modules d'inscription, fréquentation, ateliers et matériel</p>
                        </div>
                        <div class="rounded-2xl bg-white/90 p-4 text-slate-900">
                            <p class="text-sm text-slate-500">Étape 3</p>
                            <p class="mt-1 font-semibold">Suivre l'activité du jour et mieux organiser le service</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="sites" class="sites-bento px-4 py-14 sm:py-16">
            <div class="mx-auto max-w-7xl">
                <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.18em] text-slate-600">Accès rapide</p>
                        <h2 class="mt-2 text-3xl font-extrabold text-slate-900 sm:text-4xl">Choisir BAC ou MAC</h2>
                    </div>
                    <p class="max-w-xl text-sm text-slate-600 sm:text-base">Deux entrées directes pour ouvrir votre site de travail sans passer par les modules intermédiaires.</p>
                </div>

                <div class="grid gap-5 md:grid-cols-2">
                    <article class="bento-card bento-bac rounded-3xl border border-white/30 p-6 text-white shadow-xl sm:p-7">
                        <span class="inline-flex rounded-full bg-white/20 px-3 py-1 text-xs font-bold uppercase tracking-[0.14em]">Site EPN</span>
                        <h3 class="mt-4 text-4xl font-black">BAC</h3>
                        <p class="mt-3 text-base text-blue-50">Lancer rapidement les actions d'inscription, fréquentation, ateliers et matériel pour le site BAC.</p>
                        <button type="button" onclick="selectSite('BAC', this)" class="mt-6 inline-flex min-h-[44px] items-center justify-center rounded-xl bg-white px-5 py-3 text-sm font-bold text-blue-700 shadow hover:bg-blue-50">
                            Ouvrir le site BAC
                        </button>
                    </article>

                    <article class="bento-card bento-mac rounded-3xl border border-white/30 p-6 text-white shadow-xl sm:p-7">
                        <span class="inline-flex rounded-full bg-white/20 px-3 py-1 text-xs font-bold uppercase tracking-[0.14em]">Site EPN</span>
                        <h3 class="mt-4 text-4xl font-black">MAC</h3>
                        <p class="mt-3 text-base text-cyan-50">Accéder au même parcours métier côté MAC, avec navigation instantanée et session mise à jour.</p>
                        <button type="button" onclick="selectSite('MAC', this)" class="mt-6 inline-flex min-h-[44px] items-center justify-center rounded-xl bg-white px-5 py-3 text-sm font-bold text-cyan-700 shadow hover:bg-cyan-50">
                            Ouvrir le site MAC
                        </button>
                    </article>
                </div>
            </div>
        </section>

        
    </main>

    <script>
        function setLoadingState(button, label) {
            if (!button) {
                return;
            }

            button.disabled = true;
            button.dataset.originalLabel = button.textContent;
            button.textContent = label;
            button.classList.add('opacity-80', 'cursor-not-allowed');
        }

        function restoreButtonState(button) {
            if (!button || !button.dataset.originalLabel) {
                return;
            }

            button.disabled = false;
            button.textContent = button.dataset.originalLabel;
            button.classList.remove('opacity-80', 'cursor-not-allowed');
        }

        function selectSite(site, button) {
            setLoadingState(button, 'Ouverture...');

            fetch('<?= h($apiBase) ?>/session.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ action: 'setSite', site: site })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = '<?= h($accueilSiteUrl) ?>&site=' + site;
                    return;
                }

                restoreButtonState(button);
                alert('Impossible d’ouvrir ce site pour le moment.');
            })
            .catch(error => {
                console.error('Erreur:', error);
                window.location.href = '<?= h($accueilSiteUrl) ?>&site=' + site;
            });
        }

        function selectTableauBord(button) {
            setLoadingState(button, 'Ouverture...');
            window.location.href = '<?= h($dashboardUrl) ?>';
        }

    </script>
    <?php renderLogoutScript($apiBase); ?>
</body>
</html>

