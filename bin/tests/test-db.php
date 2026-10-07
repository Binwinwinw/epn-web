<?php
/**
 * Test de connexion à la base de données
 * Usage: php bin/tests/test-db.php
 */

$config = require '../../src/config/config.local.php';
$dbConfig = $config['database'];
echo "BD Host: " . $dbConfig['host'] . "\n";
echo "BD Name: " . $dbConfig['name'] . "\n";
echo "BD User: " . $dbConfig['user'] . "\n";
echo "\n";

try {
    $pdo = new PDO(
        "mysql:host=" . $dbConfig['host'] . ";dbname=" . $dbConfig['name'],
        $dbConfig['user'],
        $dbConfig['pass']
    );
    echo "✅ Connexion réussie\n";
    $result = $pdo->query("SHOW TABLES")->fetchAll();
    echo "Tables trouvées: " . count($result) . "\n";
    foreach ($result as $row) {
        echo "  - " . json_encode($row) . "\n";
    }
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}
?>
