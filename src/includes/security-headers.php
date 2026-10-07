<?php
/**
 * Headers de sécurité HTTP pour EPN Web
 * 
 * À appeler au début de chaque réponse
 */

function setSecurityHeaders() {
    // HSTS: Forcer HTTPS pendant 1 an
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains', true);
    
    // Empêcher le MIME-sniffing
    header('X-Content-Type-Options: nosniff', true);
    
    // Clickjacking protection
    header('X-Frame-Options: SAMEORIGIN', true);
    
    // XSS protection (legacy, pour les anciens navigateurs)
    header('X-XSS-Protection: 1; mode=block', true);
    
    // Referrer policy
    header('Referrer-Policy: strict-origin-when-cross-origin', true);
    
    // Content Security Policy (stricte)
    // À adapter selon les ressources externes utilisées
    $csp = "default-src 'self'; " .
           "script-src 'self' 'unsafe-inline'; " .
           "style-src 'self' 'unsafe-inline'; " .
           "img-src 'self' data: https:; " .
           "font-src 'self'; " .
           "connect-src 'self'; " .
           "frame-ancestors 'self'; " .
           "base-uri 'self'; " .
           "form-action 'self'";
    header("Content-Security-Policy: $csp", true);
    
    // Permissions policy (anciennement Feature-Policy)
    header("Permissions-Policy: accelerometer=(), camera=(), geolocation=(), gyroscope=(), magnetometer=(), microphone=(), payment=(), usb=()", true);
}

/**
 * Valide un CSRF token dans les requêtes POST/PUT/DELETE
 * 
 * @param string $token Token à valider (depuis $_POST ou headers)
 * @return bool true si valide, false sinon
 */
function validateCSRFTokenFromRequest($token = null) {
    if ($token === null) {
        // Essayer de récupérer le token depuis les sources standard
        $token = $_POST['csrf_token'] ?? 
                 ($_POST['_token'] ?? 
                 (getallheaders()['X-CSRF-Token'] ?? ''));
    }
    
    return validateCSRFToken($token);
}

/**
 * Middleware pour vérifier le CSRF sur les requêtes non-GET
 * 
 * @return bool true si la vérification réussit ou si la méthode ne le nécessite pas
 */
function requireValidCSRFToken() {
    if (!in_array($_SERVER['REQUEST_METHOD'], ['POST', 'PUT', 'DELETE', 'PATCH'])) {
        return true;  // GET, HEAD, OPTIONS ne nécessitent pas de CSRF
    }
    
    $token = $_POST['csrf_token'] ?? 
            ($_POST['_token'] ?? 
            (getallheaders()['X-CSRF-Token'] ?? ''));
    
    if (!validateCSRFTokenFromRequest($token)) {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Token CSRF invalide ou absent',
            'error' => 'CSRF_VALIDATION_FAILED'
        ]);
        exit;
    }
    
    return true;
}

/**
 * Obtient tous les headers de sécurité recommandés
 * 
 * @return array Associatif des headers
 */
function getSecurityHeaders() {
    return [
        'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains',
        'X-Content-Type-Options' => 'nosniff',
        'X-Frame-Options' => 'SAMEORIGIN',
        'X-XSS-Protection' => '1; mode=block',
        'Referrer-Policy' => 'strict-origin-when-cross-origin',
        'Permissions-Policy' => 'accelerometer=(), camera=(), geolocation=(), gyroscope=(), magnetometer=(), microphone=(), payment=(), usb=()',
    ];
}

?>
