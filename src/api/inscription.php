<?php
/**
 * API pour la gestion des inscriptions
 * 
 * Sécurité:
 * - Authentification requise
 * - CSRF token obligatoire en POST
 * - Validation stricte des données
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

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Récupérer une inscription
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    if ($id > 0) {
        $stmt = $pdo->prepare("SELECT * FROM inscription WHERE NUM_INSCRIPTION = ?");
        $stmt->execute([$id]);
        $inscription = $stmt->fetch();
        
        if ($inscription) {
            echo json_encode(['success' => true, 'inscription' => $inscription]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Inscription non trouvée']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'ID manquant']);
    }
    
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérifier le CSRF token
    requireValidCSRFToken();
    
    // Vérifier les permissions
    $numInscription = isset($_POST['num_inscription']) && !empty($_POST['num_inscription']) 
        ? (int)$_POST['num_inscription'] 
        : null;
    
    if ($numInscription) {
        requirePermission('inscription:update');
    } else {
        requirePermission('inscription:create');
    }
    
    try {
        $numInscription = isset($_POST['num_inscription']) && !empty($_POST['num_inscription']) 
            ? validateInteger($_POST['num_inscription'], 1, null, 'num_inscription')
            : null;
        
        // Enums validés
        $espaces = ['Accueil', 'Espace numérique', 'Salle 1', 'Salle 2', 'Salle 3'];
        
        $data = [
            'DATE_INSCRIPTION' => validateDate($_POST['date_inscription'] ?? ''),
            'ESPACE' => validateEnum($_POST['espace'] ?? '', $espaces, 'Espace'),
            'REFERENT' => validateString($_POST['referent'] ?? '', 0, 100),
            'DATE_NAISSANCE' => validateDate($_POST['date_naissance'] ?? ''),
            'CIVILITE' => validateEnum($_POST['civilite'] ?? '', ['M', 'Mme', 'Mlle', 'Dr', 'Autre'], 'Civilité'),
            'NOMS_PRENOMS' => validateString($_POST['noms_prenoms'] ?? '', 1, 255, 'Nom/Prénoms'),
            'ADRESSE' => validateString($_POST['adresse'] ?? '', 0, 255),
            'CODE_VILLE' => validatePostalCode($_POST['code_ville'] ?? ''),
            'PAYS' => validateString($_POST['pays'] ?? 'France', 1, 100),
            'TELEPHONES_PORTABLE' => validatePhoneNumber($_POST['telephone_portable'] ?? ''),
            'TELEPHONES_FIXE' => validatePhoneNumber($_POST['telephone_fixe'] ?? ''),
            'SITUATION' => validateString($_POST['situation'] ?? '', 0, 255),
            'EQUIPEMENT_PERSONNEL' => validateString($_POST['equipement_personnel'] ?? '', 0, 255),
            'CADRE_UTILISATION' => validateString($_POST['cadre_utilisation'] ?? '', 0, 255),
            'PROFIL' => validateString($_POST['profil'] ?? '', 0, 255),
            'EMAIL' => validateEmail($_POST['email'] ?? '')
        ];
        
        if ($numInscription) {
            // Modification - Récupérer les anciennes valeurs
            $stmt = $pdo->prepare("SELECT * FROM inscription WHERE NUM_INSCRIPTION = ?");
            $stmt->execute([$numInscription]);
            $oldRecord = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $sql = "UPDATE inscription SET ";
            $fields = [];
            $values = [];
            foreach ($data as $key => $value) {
                $fields[] = "$key = ?";
                $values[] = $value;
            }
            $values[] = $numInscription;
            $sql .= implode(', ', $fields) . " WHERE NUM_INSCRIPTION = ?";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($values);
            
            // Audit log
            auditLog('UPDATE', 'inscription', $numInscription, $oldRecord, $data);
        } else {
            // Création
            $fields = array_keys($data);
            $placeholders = str_repeat('?,', count($fields) - 1) . '?';
            
            $sql = "INSERT INTO inscription (" . implode(', ', $fields) . ") VALUES ($placeholders)";
            $values = array_values($data);
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($values);
            $newId = $pdo->lastInsertId();
            
            // Audit log
            auditLog('INSERT', 'inscription', $newId, null, $data);
        }
        
        echo json_encode(['success' => true, 'message' => 'Inscription enregistrée']);
    } catch (ValidationException $e) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => $e->getMessage(), 'error' => 'VALIDATION_ERROR']);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erreur base de données']);
        error_log("DB Error in inscription.php: " . $e->getMessage());
    }
    
} else {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
}
?>

