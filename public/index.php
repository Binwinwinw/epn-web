<?php
declare(strict_types=1);

// Charger la config, l'auth et les headers de sécurité
require_once __DIR__ . '/../src/config/config.php';
require_once __DIR__ . '/../src/includes/auth.php';
require_once __DIR__ . '/../src/includes/security-headers.php';

// Appliquer les headers de sécurité globalement
setSecurityHeaders();

$route = $_GET['page'] ?? 'landingpage';

if (!isset($_GET['page'])) {
    header('Location: index.php?page=landingpage');
    exit;
}

if ($route === 'landing') {
    header('Location: index.php?page=landingpage');
    exit;
}

if ($route === 'app') {
    header('Location: index.php?page=accueil');
    exit;
}

// Définir les routes et les pages publiques/protégées
$publicRoutes = ['landingpage', 'landing', 'login', 'help'];
$protectedRoutes = [
    'accueil' => 'Home',
    'accueil-site' => 'Site Home',
    'inscriptions' => 'Inscriptions',
    'frequentations' => 'Frequentations',
    'ateliers' => 'Ateliers',
    'materiel' => 'Materiel',
    'tableau-bord' => 'Dashboard',
    'statistiques' => 'Statistics'
];

$routes = [
    'landingpage' => __DIR__ . '/../src/pages/landingpage.php',
    'landing' => __DIR__ . '/../src/pages/landingpage.php',
    'login' => __DIR__ . '/../src/pages/login.php',
    'app' => __DIR__ . '/../src/pages/accueil.php',
    'accueil' => __DIR__ . '/../src/pages/accueil.php',
    'accueil-site' => __DIR__ . '/../src/pages/accueil-site.php',
    'inscriptions' => __DIR__ . '/../src/pages/inscriptions.php',
    'frequentations' => __DIR__ . '/../src/pages/frequentations.php',
    'ateliers' => __DIR__ . '/../src/pages/ateliers.php',
    'materiel' => __DIR__ . '/../src/pages/materiel.php',
    'tableau-bord' => __DIR__ . '/../src/pages/tableau-bord.php',
    'statistiques' => __DIR__ . '/../src/pages/statistiques.php',
    'help' => __DIR__ . '/../src/pages/help.php',
];

if (!isset($routes[$route])) {
    http_response_code(404);
    $route = 'landingpage';
}

// Vérifier l'authentification pour les pages protégées
if (in_array($route, array_keys($protectedRoutes))) {
    if (!isUserAuthenticated()) {
        header('Location: index.php?page=login');
        exit;
    }
}

define('EPN_APP_ROUTER', true);

$assetBase = 'assets';
$apiBase = '../src/api';
$routerUrl = 'index.php';
$publicHomeUrl = 'index.php?page=landingpage';
$loginUrl = 'index.php?page=login';
$appUrl = 'index.php?page=accueil';
$homeAppUrl = 'index.php?page=accueil';
$helpUrl = 'index.php?page=help';
$dashboardUrl = 'index.php?page=tableau-bord';
$statistiquesUrl = 'index.php?page=statistiques';
$accueilSiteUrl = 'index.php?page=accueil-site';
$inscriptionsUrl = 'index.php?page=inscriptions';
$frequentationsUrl = 'index.php?page=frequentations';
$ateliersUrl = 'index.php?page=ateliers';
$materielUrl = 'index.php?page=materiel';

require $routes[$route];
