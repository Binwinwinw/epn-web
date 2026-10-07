<?php
/**
 * Role-Based Access Control (RBAC) pour EPN Web
 * 
 * Définit les rôles, permissions et contrôles d'accès
 */

/**
 * Rôles disponibles et leurs permissions
 * Format: 'role' => ['permission1', 'permission2', ...]
 */
const ROLE_PERMISSIONS = [
    'admin' => [
        // Authentification
        'auth:create_user',
        'auth:delete_user',
        'auth:reset_password',
        'auth:change_role',
        'auth:change_sites',
        
        // Données - Full access
        'frequentation:create',
        'frequentation:read',
        'frequentation:update',
        'frequentation:delete',
        'frequentation:export',
        
        'atelier:create',
        'atelier:read',
        'atelier:update',
        'atelier:delete',
        'atelier:export',
        
        'inscription:create',
        'inscription:read',
        'inscription:update',
        'inscription:delete',
        'inscription:export',
        
        'poste:create',
        'poste:read',
        'poste:update',
        'poste:delete',
        
        // Statistiques
        'stats:view_all',
        'stats:export',
        
        // System
        'system:view_logs',
        'system:view_audit',
        'system:export_audit',
    ],
    
    'referent' => [
        // Données - Read + Create/Update own
        'frequentation:create',
        'frequentation:read',
        'frequentation:update',      // Peut modifier ses propres
        
        'atelier:create',
        'atelier:read',
        'atelier:update',            // Peut modifier les siens
        
        'inscription:create',
        'inscription:read',
        'inscription:update',        // Peut modifier les siennes
        
        // Statistiques - Limité
        'stats:view_own_site',
        'stats:export_own_site',
    ],
    
    'agent' => [
        // Données - Read only
        'frequentation:read',
        'atelier:read',
        'inscription:read',
        'poste:read',
        
        // Statistiques - View own site
        'stats:view_own_site',
    ],
];

/**
 * Vérifier si utilisateur a un rôle spécifique
 * 
 * @param string $requiredRole
 * @return void Lance 403 si rôle insuffisant
 */
function requireRole($requiredRole) {
    $user = getCurrentUser();
    
    if (!$user || $user['role'] !== $requiredRole) {
        logAccessDenied($requiredRole);
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => "Accès refusé. Rôle requis: $requiredRole",
            'error' => 'INSUFFICIENT_ROLE'
        ]);
        exit;
    }
}

/**
 * Vérifier si utilisateur a une permission spécifique
 * 
 * @param string $permission Format: "resource:action" (ex: "frequentation:delete")
 * @return void Lance 403 si permission manquante
 */
function requirePermission($permission) {
    if (!hasPermission($permission)) {
        logAccessDenied($permission);
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Permission refusée',
            'error' => 'INSUFFICIENT_PERMISSION',
            'permission' => $permission
        ]);
        exit;
    }
}

/**
 * Vérifier si utilisateur a une permission (retourne boolean)
 * 
 * @param string $permission
 * @return bool
 */
function hasPermission($permission) {
    $user = getCurrentUser();
    
    if (!$user) {
        return false;
    }
    
    $role = $user['role'] ?? 'agent';
    $permissions = ROLE_PERMISSIONS[$role] ?? [];
    
    return in_array($permission, $permissions);
}

/**
 * Vérifier si utilisateur peut modifier une ressource
 * (propriété: l'utilisateur est le créateur ou est admin)
 * 
 * @param int $userId User ID qui a créé la ressource
 * @return bool
 */
function canModifyResource($userId) {
    $user = getCurrentUser();
    
    if (!$user) {
        return false;
    }
    
    // Admin peut tout modifier
    if ($user['role'] === 'admin') {
        return true;
    }
    
    // Utilisateur peut modifier ses propres ressources
    return $user['id'] === $userId;
}

/**
 * Vérifier si utilisateur peut accéder à un site spécifique
 * 
 * @param string $site BAC ou MAC
 * @return bool
 */
function canAccessSite($site) {
    $user = getCurrentUser();
    
    if (!$user) {
        return false;
    }
    
    $sites = $user['sites'] ?? [];
    return in_array($site, $sites);
}

/**
 * Vérifier si utilisateur a accès à une ressource dans un site
 * 
 * @param string $site
 * @return void Lance 403 si pas accès
 */
function requireSiteAccess($site) {
    if (!canAccessSite($site)) {
        logAccessDenied("site:$site");
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => "Accès refusé au site: $site",
            'error' => 'SITE_ACCESS_DENIED'
        ]);
        exit;
    }
}

/**
 * Enregistrer une tentative d'accès refusé (audit)
 * 
 * @param string $reason Raison du refus
 */
function logAccessDenied($reason) {
    global $pdo;
    
    $user = getCurrentUser();
    $userId = $user['id'] ?? null;
    $username = $user['username'] ?? 'anonymous';
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    
    try {
        $stmt = $pdo->prepare('
            INSERT INTO auth_logs (user_id, username, action, status, ip_address, user_agent)
            VALUES (?, ?, ?, ?, ?, ?)
        ');
        $stmt->execute([
            $userId,
            $username,
            'access_denied',
            "Reason: $reason",
            $ipAddress,
            substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500)
        ]);
    } catch (Exception $e) {
        // Silencieusement échouer
    }
}

/**
 * Middleware pour vérifier la permission avant exécution
 * Utilisé en décorateur au début de chaque action
 * 
 * @param string $permission
 * @return bool
 */
function canUser($permission) {
    return hasPermission($permission);
}

/**
 * Get all roles (pour UI)
 */
function getAllRoles() {
    return array_keys(ROLE_PERMISSIONS);
}

/**
 * Get permissions for a role
 */
function getRolePermissions($role) {
    return ROLE_PERMISSIONS[$role] ?? [];
}

/**
 * Get user's readable role name (français)
 */
function getRoleLabel($role) {
    $labels = [
        'admin' => 'Administrateur',
        'referent' => 'Référent',
        'agent' => 'Agent'
    ];
    return $labels[$role] ?? $role;
}

?>
