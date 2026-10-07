<?php
/**
 * Configuration hybride de l'application EPN
 * Détection automatique de l'environnement (local/production)
 */

// Détection automatique de l'environnement
function detectEnvironment() {
    // Priorité 1: Variable d'environnement explicite (peut être définie dans .htaccess ou serveur)
    if (isset($_SERVER['EPN_ENVIRONMENT']) && !empty($_SERVER['EPN_ENVIRONMENT'])) {
        return $_SERVER['EPN_ENVIRONMENT'];
    }
    
    // Priorité 2: Fichier .env.local ou .env.production
    if (file_exists(__DIR__ . '/.env.production')) {
        return 'production';
    }
    if (file_exists(__DIR__ . '/.env.local')) {
        return 'local';
    }
    
    // Priorité 3: Si config.prod.php existe, on est probablement en production
    // (sauf si on est explicitement en localhost)
    $isExplicitLocalhost = (
        isset($_SERVER['HTTP_HOST']) && (
            $_SERVER['HTTP_HOST'] === 'localhost' ||
            $_SERVER['HTTP_HOST'] === '127.0.0.1' ||
            strpos($_SERVER['HTTP_HOST'], 'localhost:') === 0 ||
            strpos($_SERVER['HTTP_HOST'], '127.0.0.1:') === 0
        )
    ) || (
        isset($_SERVER['SERVER_NAME']) && (
            $_SERVER['SERVER_NAME'] === 'localhost' ||
            $_SERVER['SERVER_NAME'] === '127.0.0.1'
        )
    );
    
    // Si config.prod.php existe et qu'on n'est PAS explicitement en localhost, utiliser production
    if (file_exists(__DIR__ . '/config.prod.php') && !$isExplicitLocalhost) {
        return 'production';
    }
    
    // Sinon, utiliser local par défaut
    return 'local';
}

// Charger la configuration appropriée
$environment = detectEnvironment();
$configFile = $environment === 'local' ? 'config.local.php' : 'config.prod.php';

// Log de débogage (uniquement si mode debug activé temporairement)
// Décommentez la ligne suivante pour voir quelle configuration est chargée :
// error_log("EPN Config: Environnement détecté = $environment, Fichier = $configFile, HTTP_HOST = " . ($_SERVER['HTTP_HOST'] ?? 'non défini'));

// Vérifier si le fichier de configuration existe
if (!file_exists(__DIR__ . '/' . $configFile)) {
    // Si le fichier de production n'existe pas, utiliser la configuration locale par défaut
    if ($environment === 'production' && file_exists(__DIR__ . '/config.local.php')) {
        $configFile = 'config.local.php';
        $environment = 'local';
        error_log("Avertissement: config.prod.php introuvable, utilisation de config.local.php");
    } else {
        die("Erreur: Fichier de configuration '$configFile' introuvable. Veuillez créer ce fichier.");
    }
}

// Charger la configuration
$config = require __DIR__ . '/' . $configFile;

// Extraire les configurations
$dbConfig = $config['database'];
$appConfig = $config['app'];

// Configuration de la base de données
define('DB_HOST', $dbConfig['host']);
define('DB_NAME', $dbConfig['name']);
define('DB_USER', $dbConfig['user']);
define('DB_PASS', $dbConfig['pass']);
define('DB_CHARSET', $dbConfig['charset']);

// Configuration de l'application
define('SITE_NAME', $appConfig['name']);
define('SITE_URL', $appConfig['url']);
define('ENVIRONMENT', $appConfig['environment']);
define('DEBUG_MODE', $appConfig['debug']);

// Configuration des chemins web (racine du site pour accès depuis le navigateur)
// API_BASE est utilisé pour les redirections et les appels AJAX depuis le frontend
define('API_BASE', SITE_URL);

// Configuration des sessions
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
session_start();

// Timezone
date_default_timezone_set($config['timezone']);

// Mode debug
if (DEBUG_MODE) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}

// Connexion à la base de données
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::ATTR_PERSISTENT         => false, // Pas de connexion persistante
    ];
    
    // Timeout de connexion
    $options[PDO::ATTR_TIMEOUT] = 5;
    
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    
    // Vérifier la connexion
    $pdo->query("SELECT 1");
    
} catch (PDOException $e) {
    $errorMessage = "Erreur de connexion à la base de données : " . $e->getMessage();
    
    if (DEBUG_MODE) {
        die($errorMessage);
    } else {
        error_log($errorMessage);
        die("Erreur de connexion à la base de données. Veuillez contacter l'administrateur.");
    }
}

// Fonction helper pour échapper les données
function h($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// Fonction helper pour formater la date
function formatDate($date) {
    if (empty($date)) return '';
    $timestamp = strtotime($date);
    return date('d/m/Y', $timestamp);
}

// Fonction helper pour formater l'heure
function formatTime($time) {
    if (empty($time)) return '';
    return date('H:i:s', strtotime($time));
}

// Fonction helper pour formater une date longue en français
function formatDateLongFr($date = null) {
    $timestamp = $date ? strtotime($date) : time();

    if ($timestamp === false) {
        return '';
    }

    if (class_exists('IntlDateFormatter')) {
        $formatter = new IntlDateFormatter(
            'fr_FR',
            IntlDateFormatter::FULL,
            IntlDateFormatter::NONE,
            date_default_timezone_get(),
            IntlDateFormatter::GREGORIAN,
            'EEEE d MMMM y'
        );

        $formatted = $formatter->format($timestamp);
        if ($formatted !== false) {
            return ucfirst($formatted);
        }
    }

    $jours = ['dimanche', 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'];
    $mois = [1 => 'janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];

    return ucfirst($jours[(int) date('w', $timestamp)] . ' ' . date('j', $timestamp) . ' ' . $mois[(int) date('n', $timestamp)] . ' ' . date('Y', $timestamp));
}

// Compatibilité de schéma pour la table postes
function getPostesUserColumn(PDO $pdo): ?string {
    static $column = null;

    if ($column !== null) {
        return $column;
    }

    try {
        $columns = $pdo->query('SHOW COLUMNS FROM postes')->fetchAll(PDO::FETCH_COLUMN);
    } catch (Throwable $e) {
        $column = null;
        return $column;
    }

    if (in_array('NOMS_PRENOMS', $columns, true)) {
        $column = 'NOMS_PRENOMS';
    } elseif (in_array('DESCRIPTION', $columns, true)) {
        $column = 'DESCRIPTION';
    } else {
        $column = null;
    }

    return $column;
}

function normalizePostesRecords(array $records): array {
    foreach ($records as &$record) {
        if ((!array_key_exists('NOMS_PRENOMS', $record) || $record['NOMS_PRENOMS'] === '') && array_key_exists('DESCRIPTION', $record)) {
            $record['NOMS_PRENOMS'] = $record['DESCRIPTION'];
        }
    }
    unset($record);

    return $records;
}

// Fonction pour obtenir l'environnement actuel
function getEnvironment() {
    return defined('ENVIRONMENT') ? ENVIRONMENT : 'unknown';
}

// Fonction pour vérifier si on est en mode debug
function isDebugMode() {
    return defined('DEBUG_MODE') ? DEBUG_MODE : false;
}
?>
