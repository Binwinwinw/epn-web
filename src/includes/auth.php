<?php
/**
 * Système d'authentification minimaliste pour EPN Web
 * 
 * Fonctionnalités:
 * - Vérification de session utilisateur
 * - CSRF token generation et validation
 * - Contrôle d'accès par rôle (optionnel)
 */

// Vérifier que config.php est chargé
if (!defined('DB_HOST')) {
    require_once __DIR__ . '/../config/config.php';
}

/**
 * Vérifie que l'utilisateur est authentifié
 * Redirige vers login si non authentifié
 */
function requireAuth() {
    if (!isUserAuthenticated()) {
        http_response_code(401);
        header('Content-Type: application/json');
        die(json_encode(['success' => false, 'message' => 'Authentification requise', 'error' => 'AUTH_REQUIRED']));
    }
}

/**
 * Vérifie si l'utilisateur actuel est authentifié
 */
function isUserAuthenticated() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Obtient l'utilisateur actuellement connecté
 */
function getCurrentUser() {
    if (!isUserAuthenticated()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'] ?? null,
        'role' => $_SESSION['role'] ?? 'agent',
        'site' => $_SESSION['site'] ?? 'BAC'
    ];
}

/**
 * Génère un token CSRF pour les formulaires
 */
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Valide un token CSRF (pour les POST/PUT/DELETE)
 */
function validateCSRFToken($token) {
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    
    // Utiliser hash_equals pour éviter les attaques par timing
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Obtient le token CSRF actuel ou en génère un nouveau
 */
function getCSRFToken() {
    return generateCSRFToken();
}

/**
 * Simule une authentification pour développement
 * ⚠️ À REMPLACER par un vrai système de login en production
 */
function simulateAuthForDevelopment() {
    // En développement, on crée une session basique
    // Remplacer par un vrai login en production
    if (!isUserAuthenticated()) {
        $_SESSION['user_id'] = 1;
        $_SESSION['username'] = 'dev_user';
        $_SESSION['role'] = 'admin';
        $_SESSION['site'] = $_SESSION['site'] ?? 'BAC';
    }
}

/**
 * Détruit la session utilisateur (logout)
 */
function logout() {
    session_destroy();
    session_start();
}

/**
 * Change le site actif de l'utilisateur (BAC ou MAC)
 * Valide que le nouveau site est autorisé
 */
function changeSite($newSite) {
    $allowedSites = ['BAC', 'MAC'];
    
    if (!in_array(strtoupper($newSite), $allowedSites)) {
        return false;
    }
    
    $_SESSION['site'] = strtoupper($newSite);
    return true;
}

/**
 * Obtient le site actif
 */
function getCurrentSite() {
    return $_SESSION['site'] ?? 'BAC';
}

/**
 * Whitelist des tables pour un site donné
 * Évite les SQL injections sur les noms de table
 */
function getTableForSite($baseTableName, $site = null) {
    if ($site === null) {
        $site = getCurrentSite();
    }
    
    $site = strtoupper($site);
    $allowedSites = ['BAC' => '1', 'MAC' => '2'];
    
    if (!isset($allowedSites[$site])) {
        throw new Exception("Site invalide: $site");
    }
    
    // Mapper les noms de table de base à leurs variantes par site
    $tableMap = [
        'frequentation' => "frequentation{$allowedSites[$site]}",
        'atelier' => "atelier{$allowedSites[$site]}",
        'inscription' => "inscription{$allowedSites[$site]}",
    ];
    
    if (!isset($tableMap[$baseTableName])) {
        throw new Exception("Table non reconnue: $baseTableName");
    }
    
    return $tableMap[$baseTableName];
}

?>
