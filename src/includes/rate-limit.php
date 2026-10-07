<?php
/**
 * Rate limiting pour prévenir les attaques par brute force
 */

/**
 * Vérifie si une adresse IP a dépassé le limite de tentatives échouées
 * 
 * @param string $identifier IP ou username
 * @param int $maxAttempts Nombre de tentatives autorisées
 * @param int $windowSeconds Fenêtre de temps en secondes
 * @return bool true si la limite est dépassée
 */
function isRateLimited($identifier, $maxAttempts = 5, $windowSeconds = 3600) {
    $cacheKey = "ratelimit:$identifier";
    
    // Essayer d'utiliser APCu si disponible (plus rapide)
    if (function_exists('apcu_fetch')) {
        $attempts = apcu_fetch($cacheKey);
        if ($attempts === false) {
            return false;
        }
        return $attempts >= $maxAttempts;
    }
    
    // Fallback: utiliser la session (moins efficace)
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $sessionKey = "ratelimit:" . hash('sha256', $identifier);
    
    if (!isset($_SESSION[$sessionKey])) {
        return false;
    }
    
    $data = $_SESSION[$sessionKey];
    $now = time();
    
    // Vérifier si la fenêtre est expirée
    if ($now - $data['first_attempt'] > $windowSeconds) {
        unset($_SESSION[$sessionKey]);
        return false;
    }
    
    return $data['count'] >= $maxAttempts;
}

/**
 * Incrémente le compteur de tentatives pour un identifiant
 * 
 * @param string $identifier IP ou username
 * @param int $windowSeconds Fenêtre de temps en secondes
 */
function recordAttempt($identifier, $windowSeconds = 3600) {
    $cacheKey = "ratelimit:$identifier";
    
    // APCu si disponible
    if (function_exists('apcu_inc')) {
        apcu_inc($cacheKey, 1);
        apcu_store($cacheKey, apcu_fetch($cacheKey), $windowSeconds);
        return;
    }
    
    // Session fallback
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $sessionKey = "ratelimit:" . hash('sha256', $identifier);
    $now = time();
    
    if (!isset($_SESSION[$sessionKey])) {
        $_SESSION[$sessionKey] = [
            'count' => 1,
            'first_attempt' => $now
        ];
    } else {
        $data = $_SESSION[$sessionKey];
        
        // Réinitialiser si la fenêtre est expirée
        if ($now - $data['first_attempt'] > $windowSeconds) {
            $_SESSION[$sessionKey] = [
                'count' => 1,
                'first_attempt' => $now
            ];
        } else {
            $data['count']++;
            $_SESSION[$sessionKey] = $data;
        }
    }
}

/**
 * Réinitialise le compteur pour un identifiant
 * 
 * @param string $identifier IP ou username
 */
function clearRateLimit($identifier) {
    $cacheKey = "ratelimit:$identifier";
    
    if (function_exists('apcu_delete')) {
        apcu_delete($cacheKey);
    }
    
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $sessionKey = "ratelimit:" . hash('sha256', $identifier);
    unset($_SESSION[$sessionKey]);
}

/**
 * Obtient le nombre de tentatives restantes
 * 
 * @param string $identifier IP ou username
 * @param int $maxAttempts Nombre de tentatives autorisées
 * @param int $windowSeconds Fenêtre de temps en secondes
 * @return int Nombre de tentatives restantes (peut être négatif)
 */
function getRemainingAttempts($identifier, $maxAttempts = 5, $windowSeconds = 3600) {
    $cacheKey = "ratelimit:$identifier";
    
    if (function_exists('apcu_fetch')) {
        $attempts = apcu_fetch($cacheKey);
        if ($attempts === false) {
            return $maxAttempts;
        }
        return max(0, $maxAttempts - $attempts);
    }
    
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $sessionKey = "ratelimit:" . hash('sha256', $identifier);
    
    if (!isset($_SESSION[$sessionKey])) {
        return $maxAttempts;
    }
    
    $data = $_SESSION[$sessionKey];
    $now = time();
    
    if ($now - $data['first_attempt'] > $windowSeconds) {
        return $maxAttempts;
    }
    
    return max(0, $maxAttempts - $data['count']);
}

?>
