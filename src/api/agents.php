<?php
/**
 * API pour la gestion des agents
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

// Liste des agents (pour l'instant en dur, peut être migré vers la table agents)
$agents = [
    ['ID_AGENT' => 2, 'NOM_AGENT' => 'BALMY Audrey', 'ACTIF' => 1],
    ['ID_AGENT' => 3, 'NOM_AGENT' => 'BONNET Jonathan', 'ACTIF' => 1],
    ['ID_AGENT' => 4, 'NOM_AGENT' => 'EURANIE Yohan', 'ACTIF' => 1],
    ['ID_AGENT' => 5, 'NOM_AGENT' => 'JEAN-PIERRE Jonathan', 'ACTIF' => 1],
    ['ID_AGENT' => 6, 'NOM_AGENT' => 'PETIT-FRERE Dominique', 'ACTIF' => 1],
    ['ID_AGENT' => 7, 'NOM_AGENT' => 'THEOTISTE Théodoric', 'ACTIF' => 1],
    ['ID_AGENT' => 1, 'NOM_AGENT' => 'ZOBEL Georges-André', 'ACTIF' => 1],
];

// Vérifier si la table agents existe et la remplir si nécessaire
try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM agents");
    $count = $stmt->fetchColumn();
    
    if ($count == 0) {
        // Insérer les agents
        $stmt = $pdo->prepare("INSERT INTO agents (NOM_AGENT, ACTIF) VALUES (?, ?)");
        foreach ($agents as $agent) {
            $stmt->execute([$agent['NOM_AGENT'], $agent['ACTIF']]);
        }
    } else {
        // Récupérer depuis la base
        $stmt = $pdo->query("SELECT * FROM agents WHERE ACTIF = 1 ORDER BY NOM_AGENT");
        $agents = $stmt->fetchAll();
    }
    
    echo json_encode(['success' => true, 'agents' => $agents]);
} catch (PDOException $e) {
    // Si la table n'existe pas, retourner la liste en dur
    echo json_encode(['success' => true, 'agents' => $agents]);
}
?>

