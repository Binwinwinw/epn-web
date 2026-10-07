<?php
/**
 * Setup rapide - Crée la BDD et les utilisateurs de démo
 * Usage: php bin/install.php
 */

$config = require_once __DIR__ . '/../src/config/config.local.php';

$dbConfig = $config['database'];
$host = $dbConfig['host'];
$dbName = $dbConfig['name'];
$user = $dbConfig['user'];
$pass = $dbConfig['pass'];

echo "🚀 EPN Web - Setup Rapide\n";
echo "========================\n\n";

// 1. Créer la base de données si elle n'existe pas
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
    
    // Créer la BD si elle n'existe pas
    $rootPdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $rootPdo->exec("USE `$dbName`");
    echo "✅ Base de données '$dbName' prête\n\n";
} catch (PDOException $e) {
    die("❌ Erreur BD: " . $e->getMessage() . "\n");
}

// 2. Déployer la migration users
echo "[2/5] Création de la table 'users'...\n";
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

// 3. Créer les utilisateurs de démo
echo "[3/5] Création des utilisateurs de démo...\n";
$users = [
    ['admin', 'changeme123', 'admin'],
    ['agent1', 'password123', 'agent'],
    ['referent1', 'password123', 'referent']
];

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbName",
        $user,
        $pass
    );

    foreach ($users as [$username, $password, $role]) {
        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        $stmt = $pdo->prepare("INSERT IGNORE INTO users (username, password_hash, role) VALUES (?, ?, ?)");
        $stmt->execute([$username, $hash, $role]);
        echo "  ✅ $username ($role)\n";
    }
} catch (PDOException $e) {
    echo "❌ Erreur utilisateurs: " . $e->getMessage() . "\n";
}

echo "\n[4/5] Configuration validée\n";
echo "[5/5] Setup terminé avec succès! ✅\n";
?>
