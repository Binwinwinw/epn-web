<?php
/**
 * Reset des mots de passe de démo
 * Usage: php bin/reset-passwords.php
 */

$users = [
    'admin' => 'changeme123',
    'referent1' => 'password123',
    'referent2' => 'password123',
    'agent1' => 'password123'
];

echo "🔐 Génération des hashes de mots de passe\n";
echo "========================================\n\n";

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
        echo "Utilisateur: $username\n";
        echo "  Mot de passe: $password\n";
        echo "  Hash: $hash\n";
        
        $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE username = ?");
        $stmt->execute([$hash, $username]);
        echo "  ✅ Mis à jour\n\n";
    }
    
    echo "✅ Tous les mots de passe ont été réinitialisés!\n";
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}
?>
