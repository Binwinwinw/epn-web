<?php
/**
 * API pour la gestion des ateliers
 * 
 * Sécurité:
 * - Authentification requise
 * - CSRF token obligatoire en POST
 * - Validation stricte des données
 * - Whitelist des tables par site
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/validation.php';
require_once __DIR__ . '/../includes/security-headers.php';
require_once __DIR__ . '/../includes/rbac.php';
require_once __DIR__ . '/../includes/audit.php';

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

// Récupérer le site de manière sécurisée
$site = isset($_GET['site']) ? strtoupper($_GET['site']) : getCurrentSite();

// Vérifier l'accès au site
requireSiteAccess($site);

try {
    $tableAteliers = getTableForSite('atelier', $site);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Site invalide', 'error' => $e->getMessage()]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    if ($id > 0) {
        $stmt = $pdo->prepare("SELECT * FROM $tableAteliers WHERE NUM_ATELIER = ?");
        $stmt->execute([$id]);
        $atelier = $stmt->fetch();
        
        if ($atelier) {
            echo json_encode(['success' => true, 'atelier' => $atelier]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Atelier non trouvé']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'ID manquant']);
    }
    
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérifier le CSRF token
    requireValidCSRFToken();
    
    // Vérifier les permissions
    $numAtelier = isset($_POST['num_atelier']) && !empty($_POST['num_atelier']) 
        ? (int)$_POST['num_atelier'] 
        : null;
    
    if ($numAtelier) {
        requirePermission('atelier:update');
    } else {
        requirePermission('atelier:create');
    }
    
    try {
        $numAtelier = isset($_POST['num_atelier']) && !empty($_POST['num_atelier']) 
            ? validateInteger($_POST['num_atelier'], 1, null, 'num_atelier')
            : null;
        
        // Enums validés
        $espaces = ['Accueil', 'Salle 1', 'Salle 2', 'Salle 3'];
        $statuts = ['Public', 'Privé', 'Annulé'];
        
        $data = [
            'DATE_ATELIER' => validateDate($_POST['date_atelier'] ?? ''),
            'ESPACE_ATELIER' => validateEnum($_POST['espace_atelier'] ?? '', $espaces, 'Espace'),
            'REFERENT' => validateString($_POST['referent'] ?? '', 0, 100),
            'CIVILITE' => validateEnum($_POST['civilite'] ?? '', ['M', 'Mme', 'Mlle', 'Dr', 'Autre'], 'Civilité'),
            'NOMS_PRENOMS' => validateString($_POST['noms_prenoms'] ?? '', 1, 255, 'Nom/Prénoms'),
            'TELEPHONES_PORTABLE' => validatePhoneNumber($_POST['telephone_portable'] ?? ''),
            'TELEPHONES_FIXE' => validatePhoneNumber($_POST['telephone_fixe'] ?? ''),
            'ATELIER_CHOISI' => validateString($_POST['atelier_choisi'] ?? '', 0, 255),
            'PERIODE_ATELIER' => validateString($_POST['periode_atelier'] ?? '', 0, 50),
            'SITUATION_FORMATION' => validateString($_POST['situation_formation'] ?? '', 0, 255),
            'OBSERVATIONS' => validateString($_POST['observations'] ?? '', 0, 1000),
            'STATUT' => validateEnum($_POST['statut'] ?? 'Public', $statuts, 'Statut'),
            'FORMATEUR' => validateString($_POST['formateur'] ?? '', 0, 100),
            'CODE_VILLE' => validatePostalCode($_POST['code_ville'] ?? '')
        ];
        
        if ($numAtelier) {
            // Modification - Récupérer les anciennes valeurs
            $stmt = $pdo->prepare("SELECT * FROM $tableAteliers WHERE NUM_ATELIER = ?");
            $stmt->execute([$numAtelier]);
            $oldRecord = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $sql = "UPDATE $tableAteliers SET ";
            $fields = [];
            $values = [];
            foreach ($data as $key => $value) {
                $fields[] = "$key = ?";
                $values[] = $value;
            }
            $values[] = $numAtelier;
            $sql .= implode(', ', $fields) . " WHERE NUM_ATELIER = ?";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($values);
            
            // Audit log
            auditLog('UPDATE', 'atelier', $numAtelier, $oldRecord, $data);
        } else {
            // Création
            $fields = array_keys($data);
            $placeholders = str_repeat('?,', count($fields) - 1) . '?';
            
            $sql = "INSERT INTO $tableAteliers (" . implode(', ', $fields) . ") VALUES ($placeholders)";
            $values = array_values($data);
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($values);
            $newId = $pdo->lastInsertId();
            
            // Audit log
            auditLog('INSERT', 'atelier', $newId, null, $data);
        }
        
        echo json_encode(['success' => true, 'message' => 'Atelier enregistré']);
    } catch (ValidationException $e) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => $e->getMessage(), 'error' => 'VALIDATION_ERROR']);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erreur base de données']);
        error_log("DB Error in atelier.php: " . $e->getMessage());
    }
    
} else {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
}
?>

