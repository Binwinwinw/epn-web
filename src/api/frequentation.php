<?php
/**
 * API pour la gestion des fréquentations
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
    $tableFrequentation = getTableForSite('frequentation', $site);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Site invalide', 'error' => $e->getMessage()]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    if ($id > 0) {
        $stmt = $pdo->prepare("SELECT * FROM $tableFrequentation WHERE NUM_FREQUENTATION = ?");
        $stmt->execute([$id]);
        $frequentation = $stmt->fetch();
        
        if ($frequentation) {
            echo json_encode(['success' => true, 'frequentation' => $frequentation]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Fréquentation non trouvée']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'ID manquant']);
    }
    
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérifier le CSRF token
    requireValidCSRFToken();
    
    // Vérifier les permissions en fonction de l'action (create vs update)
    $numFrequentation = isset($_POST['num_frequentation']) && !empty($_POST['num_frequentation']) 
        ? validateInteger($_POST['num_frequentation'], 1, null, 'num_frequentation')
        : null;
    
    if ($numFrequentation) {
        // Modification: requires update permission
        requirePermission('frequentation:update');
    } else {
        // Création: requires create permission
        requirePermission('frequentation:create');
    }
    
    try {
        $numFrequentation = isset($_POST['num_frequentation']) && !empty($_POST['num_frequentation']) 
            ? validateInteger($_POST['num_frequentation'], 1, null, 'num_frequentation')
            : null;
        
        // Enums validés
        $espacesValides = ['Accueil', 'Espace numérique', 'Espace jeunes', 'Espace enfants'];
        $utilisationsValides = ['Recherche emploi', 'Loisirs', 'Formation', 'Démarches administratives', 'Autres'];
        
        // Validation stricte des champs
        $data = [
            'DATE_FREQUENTATION' => validateDate($_POST['date_frequentation'] ?? ''),
            'ESPACE_FREQUENTE' => validateEnum($_POST['espace_frequente'] ?? '', $espacesValides, 'Espace'),
            'REFERENT' => validateString($_POST['referent'] ?? '', 0, 100),
            'CIVILITE' => validateEnum($_POST['civilite'] ?? '', ['M', 'Mme', 'Mlle', 'Dr', 'Autre'], 'Civilité'),
            'NOMS_PRENOMS' => validateString($_POST['noms_prenoms'] ?? '', 1, 255, 'Nom/Prénoms'),
            'POSTES' => validateString($_POST['postes'] ?? '', 0, 50),
            'HEURE_ENTREE' => validateTime($_POST['heure_entree'] ?? ''),
            'HEURE_SORTIE' => validateTime($_POST['heure_sortie'] ?? ''),
            'UTILISATIONS' => validateString($_POST['utilisations'] ?? '', 0, 255),
            'CASQUE' => validateEnum($_POST['casque'] ?? '', ['Oui', 'Non', ''], 'Casque'),
            'WEBCAM' => validateEnum($_POST['webcam'] ?? '', ['Oui', 'Non', ''], 'Webcam'),
            'NOMBRE_COPIES' => validateInteger($_POST['nombre_copies'] ?? 0, 0, 1000, 'Nombre de copies'),
            'OBSERVATIONS' => validateString($_POST['observations'] ?? '', 0, 1000),
            'CODE_VILLE' => validatePostalCode($_POST['code_ville'] ?? '')
        ];
        
        // Récupérer et valider les tests
        $tests = json_decode($_POST['tests'] ?? '{}', true) ?? [];
        for ($i = 1; $i <= 20; $i++) {
            $data["TEST$i"] = isset($tests["TEST$i"]) ? 'X' : '';
        }
        
        $postesUserColumn = getPostesUserColumn($pdo);

        if ($numFrequentation) {
            // Modification - Récupérer les anciennes valeurs d'abord
            $stmt = $pdo->prepare("SELECT * FROM $tableFrequentation WHERE NUM_FREQUENTATION = ?");
            $stmt->execute([$numFrequentation]);
            $oldRecord = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $sql = "UPDATE $tableFrequentation SET ";
            $fields = [];
            $values = [];
            foreach ($data as $key => $value) {
                $fields[] = "$key = ?";
                $values[] = $value;
            }
            $values[] = $numFrequentation;
            $sql .= implode(', ', $fields) . " WHERE NUM_FREQUENTATION = ?";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($values);
            
            // Audit log pour update
            auditLog('UPDATE', 'frequentation', $numFrequentation, $oldRecord, $data);
            
            echo json_encode(['success' => true, 'message' => 'Fréquentation mise à jour']);
        } else {
            // Création
            $fields = array_keys($data);
            $placeholders = str_repeat('?,', count($fields) - 1) . '?';
            
            $sql = "INSERT INTO $tableFrequentation (" . implode(', ', $fields) . ") VALUES ($placeholders)";
            $values = array_values($data);
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($values);
            $newId = $pdo->lastInsertId();

            // Mettre à jour le poste dans la table postes
            if (!empty($data['POSTES'])) {
                $numeroPoste = (int)filter_var($data['POSTES'], FILTER_SANITIZE_NUMBER_INT);
                if ($numeroPoste > 0 && $numeroPoste <= 8) {
                    $dateUtilisation = date('Y-m-d');
                    $heureFin = !empty($data['HEURE_SORTIE']) ? $data['HEURE_SORTIE'] : date('H:i:s', strtotime('+1 hour'));

                    $insertColumns = ['NUMERO_POSTE', 'SITE'];
                    $insertValues = [$numeroPoste, $site];

                    if ($postesUserColumn) {
                        $insertColumns[] = $postesUserColumn;
                        $insertValues[] = $data['NOMS_PRENOMS'];
                    }

                    $insertColumns = array_merge($insertColumns, ['UTILISATIONS', 'HEURE_DEBUT', 'HEURE_FIN', 'STATUT', 'DATE_UTILISATION']);
                    $insertValues = array_merge($insertValues, [$data['UTILISATIONS'], $data['HEURE_ENTREE'], $heureFin, 'En cours', $dateUtilisation]);

                    $updateParts = [];
                    if ($postesUserColumn) {
                        $updateParts[] = $postesUserColumn . ' = VALUES(' . $postesUserColumn . ')';
                    }
                    $updateParts[] = 'UTILISATIONS = VALUES(UTILISATIONS)';
                    $updateParts[] = 'HEURE_DEBUT = VALUES(HEURE_DEBUT)';
                    $updateParts[] = 'HEURE_FIN = VALUES(HEURE_FIN)';
                    $updateParts[] = "STATUT = 'En cours'";

                    $placeholders = implode(', ', array_fill(0, count($insertColumns), '?'));
                    $stmt = $pdo->prepare(
                        'INSERT INTO postes (' . implode(', ', $insertColumns) . ') VALUES (' . $placeholders . ') ON DUPLICATE KEY UPDATE ' . implode(', ', $updateParts)
                    );
                    $stmt->execute($insertValues);
                }
            }
            
            // Audit log pour insert
            auditLog('INSERT', 'frequentation', $newId, null, $data);
            
            echo json_encode(['success' => true, 'message' => 'Fréquentation créée', 'id' => $newId]);
        }
        
    } catch (ValidationException $e) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => $e->getMessage(), 'error' => 'VALIDATION_ERROR']);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erreur base de données']);
        error_log("DB Error in frequentation.php: " . $e->getMessage());
    }
    
} else {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
}
?>

