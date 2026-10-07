<?php
$assetBase = $assetBase ?? '../../public/assets';
$routerUrl = $routerUrl ?? '../../public/index.php';
$publicHomeUrl = $publicHomeUrl ?? ($routerUrl . '?page=landingpage');
$appUrl = $appUrl ?? ($routerUrl . '?page=accueil');
$helpUrl = $helpUrl ?? ($routerUrl . '?page=help');
$tailwindVersion = (string) (@filemtime(__DIR__ . '/../../public/assets/css/tailwind.generated.css') ?: time());

if (!defined('EPN_APP_ROUTER')) {
    header('Location: ' . $publicHomeUrl);
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EPN Gestion - Accueil public</title>
    <link rel="stylesheet" href="<?= htmlspecialchars($assetBase, ENT_QUOTES, 'UTF-8') ?>/css/tailwind.generated.css?v=<?= htmlspecialchars($tailwindVersion, ENT_QUOTES, 'UTF-8') ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($assetBase, ENT_QUOTES, 'UTF-8') ?>/css/design-system.css">
    <style>
        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: var(--font-family-base);
            color: var(--color-neutral-900);
            background:
                radial-gradient(circle at top left, rgba(0, 102, 204, 0.10), transparent 28%),
                radial-gradient(circle at bottom right, rgba(0, 168, 107, 0.10), transparent 24%),
                var(--color-neutral-50);
        }

        .skip-link {
            position: absolute;
            left: 16px;
            top: -60px;
            z-index: 100;
            background: var(--color-neutral-900);
            color: var(--color-white);
            padding: 12px 16px;
            border-radius: 12px;
        }

        .skip-link:focus {
            top: 16px;
        }

        .premium-hero {
            background: linear-gradient(135deg, #04162d 0%, var(--color-primary-dark) 46%, #0b7d79 100%);
        }

        .glass-panel {
            backdrop-filter: blur(14px);
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }

        .feature-card,
        .info-card {
            transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
        }

        .feature-card:hover,
        .info-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 20px 40px rgba(15, 23, 42, 0.10);
            border-color: rgba(0, 102, 204, 0.22);
        }

        .icon-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 48px;
            height: 48px;
            border-radius: 14px;
            background: linear-gradient(135deg, rgba(0, 102, 204, 0.12), rgba(0, 168, 107, 0.12));
            color: var(--color-primary);
        }

        a:focus-visible,
        button:focus-visible {
            outline: 3px solid var(--color-warning);
            outline-offset: 3px;
            border-radius: 12px;
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                scroll-behavior: auto !important;
                animation: none !important;
                transition: none !important;
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
                <a href="#modules" class="hidden rounded-lg px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 sm:inline-flex">Modules</a>
                <a href="#parcours" class="hidden rounded-lg px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 lg:inline-flex">Parcours</a>
                <a href="<?= htmlspecialchars($helpUrl, ENT_QUOTES, 'UTF-8') ?>" class="hidden rounded-lg px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 lg:inline-flex">Aide</a>
                <a href="<?= htmlspecialchars($appUrl, ENT_QUOTES, 'UTF-8') ?>" class="rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow hover:bg-blue-700 sm:px-5">Accéder à l'application</a>
            </div>
        </nav>
    </header>

    <main id="contenu">
        <section class="premium-hero px-4 py-16 text-white sm:py-20 lg:py-24">
            <div class="mx-auto grid max-w-7xl gap-10 lg:grid-cols-2 lg:items-center">
                <div>
                    <span class="mb-4 inline-flex rounded-full bg-white/15 px-4 py-1 text-sm font-medium text-blue-50">Service public numérique • BAC & MAC</span>
                    <h2 class="mb-6 max-w-2xl text-4xl font-extrabold leading-tight sm:text-5xl">Une entrée publique claire, moderne et rassurante pour votre application EPN.</h2>
                    <p class="mb-8 max-w-2xl text-lg text-blue-50 sm:text-xl">Présentez les services, valorisez les usages et donnez un accès simple à une plateforme de gestion pensée pour les équipes de terrain.</p>
                    <div class="flex flex-col gap-3 sm:flex-row">
                        <a href="<?= htmlspecialchars($appUrl, ENT_QUOTES, 'UTF-8') ?>" class="rounded-xl bg-white px-6 py-3 text-center font-bold text-blue-700 shadow hover:bg-slate-100">Ouvrir l'application</a>
                        <a href="#modules" class="rounded-xl border border-white/60 px-6 py-3 text-center font-semibold text-white hover:bg-white/10">Découvrir les fonctionnalités</a>
                    </div>
                    <div class="mt-8 grid gap-3 sm:grid-cols-3">
                        <div class="glass-panel rounded-2xl px-4 py-3 text-sm">2 sites suivis</div>
                        <div class="glass-panel rounded-2xl px-4 py-3 text-sm">Tableau de bord centralisé</div>
                        <div class="glass-panel rounded-2xl px-4 py-3 text-sm">Accès simple et rapide</div>
                    </div>
                </div>

                <div class="glass-panel rounded-3xl p-6 shadow-2xl">
                    <div class="mb-4 flex items-center justify-between">
                        <div>
                            <p class="text-sm text-blue-100">Vue de pilotage</p>
                            <h3 class="text-2xl font-bold text-white">Gestion EPN</h3>
                        </div>
                        <span class="rounded-full bg-white/10 px-3 py-1 text-xs font-semibold text-blue-50">En ligne</span>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="rounded-2xl bg-white/90 p-4 text-slate-900">
                            <p class="text-sm text-slate-500">Sites</p>
                            <p class="mt-2 text-3xl font-bold">2</p>
                            <p class="mt-1 text-sm">BAC et MAC</p>
                        </div>
                        <div class="rounded-2xl bg-white/90 p-4 text-slate-900">
                            <p class="text-sm text-slate-500">Modules</p>
                            <p class="mt-2 text-3xl font-bold">6</p>
                            <p class="mt-1 text-sm">outils métiers</p>
                        </div>
                        <div class="rounded-2xl bg-white/90 p-4 text-slate-900 sm:col-span-2">
                            <p class="text-sm text-slate-500">Mission</p>
                            <p class="mt-2 text-lg font-semibold">Mieux accueillir, organiser et suivre l'activité quotidienne des Espaces Publics Numériques de Ducos.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="px-4 py-12 sm:py-16">
            <div class="mx-auto grid max-w-7xl gap-5 md:grid-cols-3">
                <article class="info-card rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <span class="icon-badge mb-4">
                        <svg viewBox="0 0 24 24" fill="none" class="h-6 w-6" stroke="currentColor" stroke-width="1.8"><path d="M12 3l7 4v5c0 4.5-3 7.5-7 9-4-1.5-7-4.5-7-9V7l7-4z"/><path d="M9.5 12l1.8 1.8L15 10.2"/></svg>
                    </span>
                    <p class="mb-2 text-sm font-semibold uppercase tracking-[0.2em] text-blue-600">Fiable</p>
                    <h3 class="mb-2 text-xl font-bold">Un accueil plus rassurant</h3>
                    <p class="text-slate-600">Une présentation propre et crédible pour mettre en confiance dès la première visite.</p>
                </article>

                <article class="info-card rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <span class="icon-badge mb-4">
                        <svg viewBox="0 0 24 24" fill="none" class="h-6 w-6" stroke="currentColor" stroke-width="1.8"><rect x="4" y="5" width="16" height="12" rx="2"/><path d="M8 19h8"/></svg>
                    </span>
                    <p class="mb-2 text-sm font-semibold uppercase tracking-[0.2em] text-green-600">Pratique</p>
                    <h3 class="mb-2 text-xl font-bold">Une vue claire des usages</h3>
                    <p class="text-slate-600">Les services essentiels sont présentés avec une hiérarchie simple et lisible.</p>
                </article>

                <article class="info-card rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <span class="icon-badge mb-4">
                        <svg viewBox="0 0 24 24" fill="none" class="h-6 w-6" stroke="currentColor" stroke-width="1.8"><path d="M5 12h4l2-5 2 10 2-5h4"/></svg>
                    </span>
                    <p class="mb-2 text-sm font-semibold uppercase tracking-[0.2em] text-orange-600">Moderne</p>
                    <h3 class="mb-2 text-xl font-bold">Une image plus premium</h3>
                    <p class="text-slate-600">Le design reprend des codes actuels tout en restant accessible et institutionnel.</p>
                </article>
            </div>
        </section>

        <section id="modules" class="px-4 py-16 sm:py-20">
            <div class="mx-auto max-w-7xl">
                <div class="mb-12 text-center">
                    <p class="mb-2 text-sm font-semibold uppercase tracking-[0.25em] text-blue-600">Fonctionnalités</p>
                    <h3 class="text-3xl font-bold sm:text-4xl">Une solution pensée pour le terrain</h3>
                    <p class="mx-auto mt-4 max-w-3xl text-lg text-slate-600">Chaque module répond à un besoin concret des équipes d'accueil, d'animation et de pilotage.</p>
                </div>

                <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                    <article class="feature-card rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <span class="icon-badge mb-4"><svg viewBox="0 0 24 24" fill="none" class="h-6 w-6" stroke="currentColor" stroke-width="1.8"><path d="M8 6h10"/><path d="M8 12h10"/><path d="M8 18h10"/><path d="M4 6h.01M4 12h.01M4 18h.01"/></svg></span>
                        <h4 class="mb-2 text-xl font-bold">Inscriptions</h4>
                        <p class="text-slate-600">Enregistrement rapide des usagers et suivi des dossiers d'inscription.</p>
                    </article>

                    <article class="feature-card rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <span class="icon-badge mb-4"><svg viewBox="0 0 24 24" fill="none" class="h-6 w-6" stroke="currentColor" stroke-width="1.8"><path d="M4 19h16"/><path d="M7 16V9"/><path d="M12 16V5"/><path d="M17 16v-4"/></svg></span>
                        <h4 class="mb-2 text-xl font-bold">Tableau de bord</h4>
                        <p class="text-slate-600">Vue d'ensemble en temps réel sur les postes, l'activité et les deux sites.</p>
                    </article>

                    <article class="feature-card rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <span class="icon-badge mb-4"><svg viewBox="0 0 24 24" fill="none" class="h-6 w-6" stroke="currentColor" stroke-width="1.8"><path d="M4 7h16"/><path d="M7 3v4"/><path d="M17 3v4"/><rect x="4" y="7" width="16" height="13" rx="2"/></svg></span>
                        <h4 class="mb-2 text-xl font-bold">Ateliers</h4>
                        <p class="text-slate-600">Organisation des sessions, présence des participants et suivi des actions.</p>
                    </article>

                    <article class="feature-card rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <span class="icon-badge mb-4"><svg viewBox="0 0 24 24" fill="none" class="h-6 w-6" stroke="currentColor" stroke-width="1.8"><path d="M17 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></span>
                        <h4 class="mb-2 text-xl font-bold">Fréquentations</h4>
                        <p class="text-slate-600">Mesure des passages et meilleure lecture de la fréquentation quotidienne.</p>
                    </article>

                    <article class="feature-card rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <span class="icon-badge mb-4"><svg viewBox="0 0 24 24" fill="none" class="h-6 w-6" stroke="currentColor" stroke-width="1.8"><rect x="3" y="4" width="18" height="12" rx="2"/><path d="M8 20h8"/><path d="M12 16v4"/></svg></span>
                        <h4 class="mb-2 text-xl font-bold">Postes informatiques</h4>
                        <p class="text-slate-600">Suivi de l'occupation, disponibilité et état des équipements.</p>
                    </article>

                    <article class="feature-card rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <span class="icon-badge mb-4"><svg viewBox="0 0 24 24" fill="none" class="h-6 w-6" stroke="currentColor" stroke-width="1.8"><path d="M12 2v4"/><path d="M12 18v4"/><path d="M4.93 4.93l2.83 2.83"/><path d="M16.24 16.24l2.83 2.83"/><path d="M2 12h4"/><path d="M18 12h4"/><path d="M4.93 19.07l2.83-2.83"/><path d="M16.24 7.76l2.83-2.83"/></svg></span>
                        <h4 class="mb-2 text-xl font-bold">API métier</h4>
                        <p class="text-slate-600">Base robuste pour les interactions AJAX et les mises à jour dynamiques.</p>
                    </article>
                </div>
            </div>
        </section>

        <section id="parcours" class="bg-slate-100 px-4 py-16 sm:py-20">
            <div class="mx-auto max-w-7xl">
                <div class="mb-10 text-center">
                    <p class="mb-2 text-sm font-semibold uppercase tracking-[0.25em] text-green-600">Parcours</p>
                    <h3 class="text-3xl font-bold sm:text-4xl">Comment l'application accompagne vos équipes</h3>
                </div>

                <div class="grid gap-6 md:grid-cols-3">
                    <article class="rounded-2xl bg-white p-6 shadow-sm">
                        <div class="mb-4 inline-flex h-10 w-10 items-center justify-center rounded-full bg-blue-100 font-bold text-blue-700">1</div>
                        <h4 class="mb-2 text-xl font-bold">Choisir un site</h4>
                        <p class="text-slate-600">Accédez rapidement au contexte BAC, MAC ou à la vue globale selon votre besoin.</p>
                    </article>
                    <article class="rounded-2xl bg-white p-6 shadow-sm">
                        <div class="mb-4 inline-flex h-10 w-10 items-center justify-center rounded-full bg-green-100 font-bold text-green-700">2</div>
                        <h4 class="mb-2 text-xl font-bold">Gérer l'activité</h4>
                        <p class="text-slate-600">Suivez les inscriptions, les ateliers, les fréquentations et l'usage des postes.</p>
                    </article>
                    <article class="rounded-2xl bg-white p-6 shadow-sm">
                        <div class="mb-4 inline-flex h-10 w-10 items-center justify-center rounded-full bg-orange-100 font-bold text-orange-700">3</div>
                        <h4 class="mb-2 text-xl font-bold">Décider plus vite</h4>
                        <p class="text-slate-600">Appuyez-vous sur une vue claire pour mieux organiser le service public numérique.</p>
                    </article>
                </div>
            </div>
        </section>

        <section class="px-4 py-16 sm:py-20">
            <div class="mx-auto grid max-w-7xl gap-8 lg:grid-cols-[1.15fr_0.85fr]">
                <div class="rounded-3xl bg-slate-900 p-8 text-white shadow-xl">
                    <p class="mb-2 text-sm font-semibold uppercase tracking-[0.25em] text-slate-300">Pourquoi cette application ?</p>
                    <h3 class="mb-4 text-3xl font-bold">Un portail simple, utile et ancré dans le service public</h3>
                    <ul class="space-y-3 text-slate-200">
                        <li>• Centraliser la gestion des EPN de Ducos.</li>
                        <li>• Simplifier le travail quotidien des équipes.</li>
                        <li>• Offrir une interface claire, rapide et accessible.</li>
                    </ul>
                </div>

                <div class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
                    <p class="mb-2 text-sm font-semibold uppercase tracking-[0.25em] text-blue-600">Accès rapide</p>
                    <h3 class="mb-4 text-2xl font-bold">Prêt à entrer ?</h3>
                    <p class="mb-6 text-slate-600">La landing page présente la solution. L'application permet ensuite de passer directement à l'action.</p>
                    <div class="flex flex-col gap-3">
                        <a href="<?= htmlspecialchars($appUrl, ENT_QUOTES, 'UTF-8') ?>" class="rounded-lg bg-green-600 px-5 py-3 text-center font-semibold text-white hover:bg-green-700">Ouvrir l'application</a>
                        <a href="<?= htmlspecialchars($helpUrl, ENT_QUOTES, 'UTF-8') ?>" class="rounded-lg border border-slate-300 px-5 py-3 text-center font-semibold text-slate-800 hover:bg-slate-50">Aide</a>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="bg-slate-900 px-4 py-10 text-slate-300">
        <div class="mx-auto max-w-7xl text-center">
            <p class="text-lg font-semibold text-white">EPN Gestion</p>
            <p class="mt-2">Plateforme de gestion des Espaces Publics Numériques de la Ville de Ducos.</p>
        </div>
    </footer>
</body>
</html>
