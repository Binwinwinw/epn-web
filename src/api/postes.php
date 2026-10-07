<?php
/**
 * API pour la gestion des postes
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

$method = $_SERVER['REQUEST_METHOD'];
$postesUserColumn = getPostesUserColumn($pdo);

try {
    switch ($method) {
        case 'GET':
            // Récupérer les postes
            $site = isset($_GET['site']) ? strtoupper($_GET['site']) : null;
            $date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
            
            if ($site) {
                // Récupérer les postes d'un site spécifique
                $stmt = $pdo->prepare("
                    SELECT * FROM postes 
                    WHERE SITE = ? AND DATE_UTILISATION = ?
                    ORDER BY NUMERO_POSTE
                ");
                $stmt->execute([$site, $date]);
                $postes = normalizePostesRecords($stmt->fetchAll());
            } else {
                // Récupérer tous les postes (les deux sites)
                $stmt = $pdo->prepare("
                    SELECT * FROM postes 
                    WHERE DATE_UTILISATION = ?
                    ORDER BY SITE, NUMERO_POSTE
                ");
                $stmt->execute([$date]);
                $postes = normalizePostesRecords($stmt->fetchAll());
            }
            
            echo json_encode([
                'success' => true,
                'postes' => $postes
            ]);
            break;
            
        case 'POST':
            // Vérifier le CSRF token
            requireValidCSRFToken();
            
            // Vérifier les permissions
            requirePermission('poste:create');
            
            // Ajouter un nouveau poste
            $input = json_decode(file_get_contents('php://input'), true);
            $action = $input['action'] ?? '';
            
            if ($action === 'create') {
                try {
                    $site = validateEnum(strtoupper($input['site'] ?? 'BAC'), ['BAC', 'MAC'], 'Site');
                    $numeroPoste = validateInteger($input['numero_poste'] ?? 1, 1, 50);
                    $dateUtilisation = validateDate($input['date_utilisation'] ?? date('Y-m-d'));
                    $statut = validateEnum($input['statut'] ?? 'Libre', ['Libre', 'Occupé', 'Hors service'], 'Statut');
                    $nomsPrenoms = validateString($input['noms_prenoms'] ?? '', 0, 255);
                    $utilisations = validateString($input['utilisations'] ?? '', 0, 500);
                    $heureDebut = validateTime($input['heure_debut'] ?? '');
                    $heureFin = validateTime($input['heure_fin'] ?? '');
                    
                    // Vérifier si le poste existe déjà pour cette date
                    $stmt = $pdo->prepare("
                        SELECT ID_POSTE FROM postes 
                        WHERE NUMERO_POSTE = ? AND SITE = ? AND DATE_UTILISATION = ?
                    ");
                    $stmt->execute([$numeroPoste, $site, $dateUtilisation]);
                    $existant = $stmt->fetch();
                    
                    if ($existant) {
                        http_response_code(409);
                        echo json_encode([
                            'success' => false,
                            'message' => 'Ce poste existe déjà pour cette date'
                        ]);
                        break;
                    }
                    
                    // Insérer le nouveau poste avec compatibilité de schéma
                    $insertColumns = ['NUMERO_POSTE', 'SITE', 'DATE_UTILISATION', 'STATUT'];
                    $insertValues = [$numeroPoste, $site, $dateUtilisation, $statut];

                    if ($postesUserColumn) {
                        $insertColumns[] = $postesUserColumn;
                        $insertValues[] = $nomsPrenoms;
                    }

                    $insertColumns = array_merge($insertColumns, ['UTILISATIONS', 'HEURE_DEBUT', 'HEURE_FIN']);
                    $insertValues = array_merge($insertValues, [$utilisations, $heureDebut, $heureFin]);

                    $placeholders = implode(', ', array_fill(0, count($insertColumns), '?'));
                    $stmt = $pdo->prepare(
                        'INSERT INTO postes (' . implode(', ', $insertColumns) . ') VALUES (' . $placeholders . ')'
                    );
                    
                    $stmt->execute($insertValues);
                    
                    echo json_encode([
                        'success' => true,
                        'message' => 'Poste ajouté avec succès',
                        'id' => $pdo->lastInsertId()
                    ]);
                } catch (ValidationException $e) {
                    http_response_code(400);
                    echo json_encode([
                        'success' => false,
                        'message' => $e->getMessage(),
                        'error' => 'VALIDATION_ERROR'
                    ]);
                }
            } else {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Action non reconnue'
                ]);
            }
            break;
            
        default:
            echo json_encode([
                'success' => false,
                'message' => 'Méthode non autorisée'
            ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>

