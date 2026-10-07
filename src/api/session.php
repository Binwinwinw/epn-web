<?php
/**
 * API pour la gestion des sessions - DÉPRÉCIÉ
 * 
 * ⚠️ Cette API est remplacée par /src/api/auth.php
 * 
 * Les appels doivent être redirigés vers :
 * - POST /src/api/auth.php?action=login
 * - POST /src/api/auth.php?action=logout
 * - POST /src/api/auth.php?action=changeSite
 * - GET /src/api/auth.php?action=check
 * 
 * Sécurité:
 * - Authentification requise
 * - Validation stricte des données
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/validation.php';
require_once __DIR__ . '/../includes/security-headers.php';

header('Content-Type: application/json; charset=utf-8');

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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';
    
    switch ($action) {
        case 'setSite':
            // Utiliser la nouvelle fonction
            if (changeSite($input['site'] ?? 'BAC')) {
                echo json_encode(['success' => true, 'site' => getCurrentSite()]);
            } else {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Site invalide']);
            }
            break;
            
        case 'getSite':
            $site = getCurrentSite();
            echo json_encode(['success' => true, 'site' => $site]);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Action inconnue']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
}
?>


