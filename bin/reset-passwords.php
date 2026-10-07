<?php

/**
 * Reset volontaire des mots de passe de démonstration
 * Usage: php bin/reset-passwords.php --force --admin-password "StrongPassword123" --demo-password "StrongPassword456"
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("This script must be run from the command line.\n");
}

$options = getopt('', ['force', 'admin-password::', 'demo-password::']);
if (!isset($options['force'])) {
    fwrite(STDERR, "Erreur: ajoutez --force pour confirmer la réinitialisation des mots de passe.\n");
    exit(1);
}

$adminPassword = $options['admin-password'] ?? null;
$demoPassword = $options['demo-password'] ?? null;

if (empty($adminPassword) || strlen($adminPassword) < 12) {
    fwrite(STDERR, "Erreur: --admin-password est requis et doit contenir au moins 12 caractères.\n");
    exit(1);
}

$users = [
    'admin' => $adminPassword,
];

if (!empty($demoPassword) && strlen($demoPassword) >= 12) {
    $users['agent1'] = $demoPassword;
}

echo "🔐 Réinitialisation des comptes avec mots de passe forts\n";
echo "===================================================\n\n";

$config = require '../src/config/config.local.php';
$dbConfig = $config['database'];

try {
    $pdo = new PDO(
        "mysql:host={$dbConfig['host']};dbname={$dbConfig['name']}",
        $dbConfig['user'],
        $dbConfig['pass']
    );

    foreach ($users as $username => $password) {
        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE username = ?");
        $stmt->execute([$hash, $username]);
        echo "✅ $username mis à jour\n";
    }

    echo "\n✅ Réinitialisation terminée.\n";
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    exit(1);
}
