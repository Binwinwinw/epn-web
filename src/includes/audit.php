<?php
/**
 * Audit Logging - Enregistre toutes les modifications de données
 * 
 * Traçabilité complète: qui a modifié quoi, quand, comment
 */

/**
 * Enregistrer une modification (INSERT/UPDATE/DELETE)
 * 
 * @param string $action INSERT|UPDATE|DELETE
 * @param string $tableName Nom de la table
 * @param int $recordId ID du record
 * @param array $oldValues Anciennes valeurs (null pour INSERT)
 * @param array $newValues Nouvelles valeurs (null pour DELETE)
 */
function auditLog($action, $tableName, $recordId, $oldValues = null, $newValues = null) {
    global $pdo;
    
    $user = getCurrentUser();
    $userId = $user['id'] ?? null;
    $username = $user['username'] ?? 'system';
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $userAgent = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500);
    
    try {
        $stmt = $pdo->prepare('
            INSERT INTO audit_logs 
            (user_id, username, action, table_name, record_id, old_values, new_values, ip_address, user_agent)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ');
        
        $stmt->execute([
            $userId,
            $username,
            $action,
            $tableName,
            $recordId,
            $oldValues ? json_encode($oldValues) : null,
            $newValues ? json_encode($newValues) : null,
            $ipAddress,
            $userAgent
        ]);
        
        return true;
    } catch (Exception $e) {
        error_log("Audit logging error: " . $e->getMessage());
        return false;
    }
}

/**
 * Enregistrer une tentative d'accès refusée (dans rbac.php aussi)
 * 
 * @param string $permission Permission requise
 * @param string $role Rôle requis (optionnel)
 */
function logAccessDeniedAudit($permission, $role = null) {
    global $pdo;
    
    $user = getCurrentUser();
    $userId = $user['id'] ?? null;
    $username = $user['username'] ?? 'anonymous';
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $userAgent = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500);
    
    try {
        $stmt = $pdo->prepare('
            INSERT INTO access_logs 
            (user_id, username, required_permission, required_role, ip_address, user_agent, status)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ');
        
        $stmt->execute([
            $userId,
            $username,
            $permission,
            $role,
            $ipAddress,
            $userAgent,
            'DENIED'
        ]);
    } catch (Exception $e) {
        error_log("Access audit logging error: " . $e->getMessage());
    }
}

/**
 * Enregistrer une action sensible (delete, export, user change)
 * 
 * @param string $action Description de l'action
 * @param string $severity CRITICAL|HIGH|MEDIUM|LOW
 * @param array $details Détails additionnels
 */
function logSensitiveAction($action, $severity = 'MEDIUM', $details = []) {
    global $pdo;
    
    $user = getCurrentUser();
    $userId = $user['id'] ?? null;
    $username = $user['username'] ?? 'system';
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    
    try {
        $stmt = $pdo->prepare('
            INSERT INTO audit_logs 
            (user_id, username, action, table_name, old_values, ip_address)
            VALUES (?, ?, ?, ?, ?, ?)
        ');
        
        $auditData = array_merge(
            ['severity' => $severity, 'action' => $action],
            $details
        );
        
        $stmt->execute([
            $userId,
            $username,
            'SENSITIVE_ACTION',
            'system',
            json_encode($auditData),
            $ipAddress
        ]);
    } catch (Exception $e) {
        error_log("Sensitive action audit logging error: " . $e->getMessage());
    }
}

/**
 * Obtenir l'historique d'audit pour un record
 * 
 * @param string $tableName
 * @param int $recordId
 * @return array Historique complet
 */
function getAuditHistory($tableName, $recordId) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare('
            SELECT * FROM audit_logs
            WHERE table_name = ? AND record_id = ?
            ORDER BY created_at DESC
        ');
        $stmt->execute([$tableName, $recordId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Obtenir les modifications récentes pour un utilisateur
 * 
 * @param int $userId
 * @param int $days Nombre de jours (défaut: 7)
 * @return array
 */
function getUserAuditHistory($userId, $days = 7) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare('
            SELECT * FROM audit_logs
            WHERE user_id = ? AND created_at > DATE_SUB(NOW(), INTERVAL ? DAY)
            ORDER BY created_at DESC
        ');
        $stmt->execute([$userId, $days]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Obtenir les actions sensibles (DELETE, user changes, etc.)
 * 
 * @param int $days
 * @return array
 */
function getSensitiveActions($days = 30) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare('
            SELECT * FROM audit_logs
            WHERE action IN ("DELETE", "SENSITIVE_ACTION")
            AND created_at > DATE_SUB(NOW(), INTERVAL ? DAY)
            ORDER BY created_at DESC
        ');
        $stmt->execute([$days]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Vérifier les modifications suspectes (tentatives d'escalade, etc.)
 * 
 * @param int $days
 * @return array Logs suspects
 */
function getSuspiciousActivity($days = 7) {
    global $pdo;
    
    try {
        // Tentatives d'accès refusées (IPs différentes = suspect)
        $stmt = $pdo->prepare('
            SELECT 
                username, 
                COUNT(*) as denied_count,
                COUNT(DISTINCT ip_address) as ip_count,
                MIN(created_at) as first_attempt,
                MAX(created_at) as last_attempt
            FROM access_logs
            WHERE status = "DENIED" AND created_at > DATE_SUB(NOW(), INTERVAL ? DAY)
            GROUP BY username
            HAVING denied_count > 5
            ORDER BY denied_count DESC
        ');
        $stmt->execute([$days]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Export des audit logs (CSV)
 * 
 * @param int $days
 * @param string $filename
 */
function exportAuditLogs($days = 30, $filename = 'audit_logs.csv') {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare('
            SELECT 
                id, user_id, username, action, table_name, record_id, 
                created_at, ip_address
            FROM audit_logs
            WHERE created_at > DATE_SUB(NOW(), INTERVAL ? DAY)
            ORDER BY created_at DESC
        ');
        $stmt->execute([$days]);
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);
        
        $output = fopen('php://output', 'w');
        
        // Headers
        fputcsv($output, ['ID', 'User ID', 'Username', 'Action', 'Table', 'Record ID', 'Created', 'IP Address']);
        
        // Data
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            fputcsv($output, $row);
        }
        
        fclose($output);
        return true;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Archiver les logs anciens (conformité RGPD retention)
 * 
 * @param int $months Nombre de mois à conserver (défaut: 12)
 */
function archiveOldLogs($months = 12) {
    global $pdo;
    
    try {
        // Créer table archive si elle n'existe pas
        $pdo->exec('
            CREATE TABLE IF NOT EXISTS audit_logs_archive LIKE audit_logs;
        ');
        
        // Copier les logs anciens vers archive
        $stmt = $pdo->prepare('
            INSERT INTO audit_logs_archive
            SELECT * FROM audit_logs
            WHERE created_at < DATE_SUB(NOW(), INTERVAL ? MONTH)
        ');
        $stmt->execute([$months]);
        $copiedCount = $stmt->rowCount();
        
        // Supprimer les logs archivés
        $stmt = $pdo->prepare('
            DELETE FROM audit_logs
            WHERE created_at < DATE_SUB(NOW(), INTERVAL ? MONTH)
        ');
        $stmt->execute([$months]);
        
        return ['archived' => $copiedCount];
    } catch (Exception $e) {
        error_log("Archive logs error: " . $e->getMessage());
        return ['error' => $e->getMessage()];
    }
}

?>
