<?php

/**
 * Setup sécurisé - Crée la base de données et un compte administrateur de production
 * Usage: php bin/install.php --admin-password "VotreMotDePasseFort" [--demo-user demo --demo-password "MotDePasseFort"]
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("This script must be run from the command line.\n");
}

$options = getopt('', ['admin-user::', 'admin-password::', 'demo-user::', 'demo-password::']);
$adminUser = $options['admin-user'] ?? 'admin';
$adminPassword = $options['admin-password'] ?? null;

if (empty($adminPassword) || strlen($adminPassword) < 12) {
    fwrite(STDERR, "Erreur: --admin-password est requis et doit contenir au moins 12 caractères.\n");
    exit(1);
}

$config = require_once __DIR__ . '/../src/config/config.local.php';

$dbConfig = $config['database'];
$host = $dbConfig['host'];
$dbName = $dbConfig['name'];
$user = $dbConfig['user'];
$pass = $dbConfig['pass'];

echo "🚀 EPN Web - Secure Setup\n";
echo "========================\n\n";

echo "[1/5] Création de la base de données...\n";
try {
    $rootPdo = new PDO(
        "mysql:host=$host;charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );

    $rootPdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $rootPdo->exec("USE `$dbName`");
    echo "✅ Base de données '$dbName' prête\n\n";
} catch (PDOException $e) {
    die("❌ Erreur BD: " . $e->getMessage() . "\n");
}

echo "[2/5] Déploiement des migrations...\n";
$migrations = [
    '../database/migration_001_users.sql' => 'Users table',
    '../database/migration_002_audit_logging.sql' => 'Audit tables'
];

foreach ($migrations as $file => $desc) {
    if (!file_exists($file)) {
        echo "⚠️  Migration non trouvée: $file\n";
        continue;
    }

    $sql = file_get_contents($file);
    try {
        $rootPdo->exec($sql);
        echo "✅ $desc déployée\n";
    } catch (PDOException $e) {
        echo "❌ Erreur $desc: " . $e->getMessage() . "\n";
    }
}

echo "\n";

echo "[3/5] Création du compte administrateur principal...\n";
$adminHash = password_hash($adminPassword, PASSWORD_BCRYPT, ['cost' => 12]);

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbName",
        $user,
        $pass
    );

    $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, role, sites, is_active) VALUES (?, ?, 'admin', 'BAC,MAC', 1) ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash), role = VALUES(role), sites = VALUES(sites), is_active = VALUES(is_active)");
    $stmt->execute([$adminUser, $adminHash]);
    echo "  ✅ $adminUser (admin)\n";
} catch (PDOException $e) {
    echo "❌ Erreur utilisateur admin: " . $e->getMessage() . "\n";
}

if (isset($options['demo-user'])) {
    $demoUser = $options['demo-user'];
    $demoPassword = $options['demo-password'] ?? 'ChangeMeStrong!123';
    if (strlen($demoPassword) < 12) {
        fwrite(STDERR, "Erreur: le mot de passe du compte de démonstration doit faire au moins 12 caractères.\n");
        exit(1);
    }

    echo "\n[4/5] Création d'un compte de démonstration...\n";
    $demoHash = password_hash($demoPassword, PASSWORD_BCRYPT, ['cost' => 12]);
    $demoStmt = $pdo->prepare("INSERT INTO users (username, password_hash, role, sites, is_active) VALUES (?, ?, 'agent', 'BAC', 1) ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash), role = VALUES(role), sites = VALUES(sites), is_active = VALUES(is_active)");
    $demoStmt->execute([$demoUser, $demoHash]);
    echo "  ✅ $demoUser (agent)\n";
} else {
    echo "\n[4/5] Aucun compte de démonstration créé (mode sécurisé activé).\n";
}

echo "\n[5/5] Setup terminé avec succès! ✅\n";
echo "Important: changez le mot de passe administrateur après la première connexion.\n";
