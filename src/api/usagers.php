<?php
/**
 * API pour la recherche d'usagers
 * 
 * Sécurité:
 * - Authentification requise
 * - Validation stricte des données
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/validation.php';
require_once __DIR__ . '/../includes/security-headers.php';

header('Content-Type: application/json');

// Ajouter les headers de sécurité
setSecurityHeaders();

// Vérifier l'authentification
if (!isUserAuthenticated()) {
    if (DEBUG_MODE) {
        simulateAuthForDevelopment();
    } else {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Authentification requise']);
        exit;
    }
}

$recherche = isset($_GET['recherche']) ? $_GET['recherche'] : '';

if (empty($recherche)) {
    echo json_encode(['success' => true, 'usagers' => []]);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT * FROM inscription 
        WHERE NOMS_PRENOMS LIKE ? OR DATE_INSCRIPTION LIKE ?
        ORDER BY NOMS_PRENOMS
        LIMIT 50
    ");
    $searchTerm = "%$recherche%";
    $stmt->execute([$searchTerm, $searchTerm]);
    $usagers = $stmt->fetchAll();
    
    echo json_encode(['success' => true, 'usagers' => $usagers]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>

