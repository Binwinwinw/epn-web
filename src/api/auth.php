<?php
/**
 * API d'authentification
 * Gère le login, logout, et vérification de session
 * 
 * Rate limiting, validation stricte, CSRF tokens
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/validation.php';
require_once __DIR__ . '/../includes/rate-limit.php';

header('Content-Type: application/json; charset=utf-8');

$action = $_GET['action'] ?? '';

switch ($_SERVER['REQUEST_METHOD']) {
    case 'POST':
        handleAuthPOST($pdo, $action);
        break;
    
    case 'GET':
        handleAuthGET($pdo, $action);
        break;
    
    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
        break;
}

/**
 * Gère les requêtes POST (login, logout, etc.)
 */
function handleAuthPOST($pdo, $action) {
    switch ($action) {
        case 'login':
            doLogin($pdo);
            break;
        
        case 'logout':
            doLogout();
            break;
        
        case 'changeSite':
            changeUserSite();
            break;
        
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Action invalide']);
            break;
    }
}

/**
 * Gère les requêtes GET (vérification de session, etc.)
 */
function handleAuthGET($pdo, $action) {
    switch ($action) {
        case 'check':
            checkSession();
            break;
        
        case 'user':
            getCurrentUserInfo();
            break;
        
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Action invalide']);
            break;
    }
}

/**
 * Effectue le login avec rate limiting et validation
 */
function doLogin($pdo) {
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    
    // Vérifier le rate limiting sur l'IP
    if (isRateLimited($ipAddress, 5, 3600)) {
        http_response_code(429);
        echo json_encode([
            'success' => false, 
            'message' => 'Trop de tentatives. Veuillez réessayer dans une heure'
        ]);
        logAuthAttempt($pdo, 'unknown', 'login', 'rate_limited', null, $ipAddress);
        return;
    }
    
    // Récupérer et valider les données
    $input = json_decode(file_get_contents('php://input'), true);
    $username = $input['username'] ?? '';
    $password = $input['password'] ?? '';
    
    try {
        // Validation stricte
        $username = validateString($username, 1, 100, 'Utilisateur');
        
        if (empty($password)) {
            throw new ValidationException('Mot de passe requis');
        }
    } catch (ValidationException $e) {
        recordAttempt($ipAddress);
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        logAuthAttempt($pdo, $username, 'login', 'validation_failed', null, $ipAddress);
        return;
    }
    
    // Chercher l'utilisateur
    $stmt = $pdo->prepare('
        SELECT id, username, password_hash, role, sites, is_active, email, full_name
        FROM users
        WHERE username = ?
    ');
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Vérifier l'existence et l'activation
    if (!$user || !$user['is_active']) {
        recordAttempt($ipAddress);
        recordAttempt("user:$username");  // Rate limit aussi par username
        logAuthAttempt($pdo, $username, 'login', 'failed', null, $ipAddress);
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Identifiants invalides']);
        return;
    }
    
    // Vérifier le mot de passe
    if (!password_verify($password, $user['password_hash'])) {
        recordAttempt($ipAddress);
        recordAttempt("user:{$user['id']}");
        logAuthAttempt($pdo, $username, 'login', 'failed', $user['id'], $ipAddress);
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Identifiants invalides']);
        return;
    }
    
    // Vérifier le rate limiting par username
    if (isRateLimited("user:{$user['id']}", 10, 3600)) {
        logAuthAttempt($pdo, $username, 'login', 'rate_limited', $user['id'], $ipAddress);
        http_response_code(429);
        echo json_encode(['success' => false, 'message' => 'Compte temporairement verrouillé']);
        return;
    }
    
    // ✅ Authentification réussie
    // Réinitialiser les rate limits
    clearRateLimit($ipAddress);
    clearRateLimit("user:{$user['id']}");
    
    // Mettre à jour last_login
    $updateStmt = $pdo->prepare('
        UPDATE users SET last_login = NOW() WHERE id = ?
    ');
    $updateStmt->execute([$user['id']]);
    
    // Créer la session avec token CSRF
    // Note: session_start() est déjà appelé dans config.php, pas besoin de le faire ici
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true);  // Sécurité: régénérer l'ID de session
    }
    
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['login_time'] = time();
    $_SESSION['login_ip'] = $ipAddress;
    
    // Sites autorisés
    $sites = array_map('trim', explode(',', $user['sites']));
    $_SESSION['sites'] = $sites;
    $_SESSION['site'] = $sites[0];
    
    // Générer un nouveau CSRF token pour cette session
    generateCSRFToken();
    
    // Log de succès
    logAuthAttempt($pdo, $username, 'login', 'success', $user['id'], $ipAddress);
    
    echo json_encode([
        'success' => true,
        'message' => 'Connexion réussie',
        'user' => [
            'id' => $user['id'],
            'username' => $user['username'],
            'role' => $user['role'],
            'full_name' => $user['full_name'],
            'email' => $user['email'],
            'sites' => $sites,
            'current_site' => $_SESSION['site']
        ]
    ]);
}

/**
 * Effectue le logout
 */
function doLogout() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (isset($_SESSION['username'])) {
        $username = $_SESSION['username'];
    }
    
    // Détruire la session
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }
    session_destroy();
    
    // Démarrer une nouvelle session vierge pour le token CSRF
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Déconnexion réussie'
    ]);
}

/**
 * Change le site actif de l'utilisateur
 */
function changeUserSite() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isUserAuthenticated()) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Non authentifié']);
        return;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    $newSite = strtoupper($input['site'] ?? '');
    
    // Vérifier que le site est autorisé pour cet utilisateur
    $sites = $_SESSION['sites'] ?? ['BAC'];
    if (!in_array($newSite, $sites)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Vous n\'avez pas accès à ce site']);
        return;
    }
    
    $_SESSION['site'] = $newSite;
    
    echo json_encode([
        'success' => true,
        'message' => 'Site changé',
        'current_site' => $_SESSION['site']
    ]);
}

/**
 * Vérifie si l'utilisateur est actuellement connecté
 */
function checkSession() {
    session_start();
    
    if (!isUserAuthenticated()) {
        http_response_code(401);
        echo json_encode(['success' => false, 'authenticated' => false]);
        return;
    }
    
    echo json_encode([
        'success' => true,
        'authenticated' => true,
        'user' => [
            'id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'role' => $_SESSION['role'],
            'full_name' => $_SESSION['full_name'] ?? '',
            'email' => $_SESSION['email'] ?? '',
            'sites' => $_SESSION['sites'] ?? [],
            'current_site' => $_SESSION['site'] ?? 'BAC'
        ]
    ]);
}

/**
 * Retourne les infos de l'utilisateur actuel
 */
function getCurrentUserInfo() {
    session_start();
    
    if (!isUserAuthenticated()) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Non authentifié']);
        return;
    }
    
    $user = getCurrentUser();
    echo json_encode([
        'success' => true,
        'user' => $user
    ]);
}

/**
 * Enregistre une tentative d'authentification
 */
function logAuthAttempt($pdo, $username, $action, $status, $userId = null, $ipAddress = null) {
    if ($ipAddress === null) {
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }
    $userAgent = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500);
    
    try {
        $stmt = $pdo->prepare('
            INSERT INTO auth_logs (user_id, username, action, ip_address, user_agent, status)
            VALUES (?, ?, ?, ?, ?, ?)
        ');
        $stmt->execute([$userId, $username, $action, $ipAddress, $userAgent, $status]);
    } catch (Exception $e) {
        // Silencieusement échouer le logging si la table n'existe pas
    }
}

?>
