-- Migration Audit Logging
-- Crée les tables et indexes pour la traçabilité complète

-- Table pour enregistrer toutes les modifications de données
CREATE TABLE IF NOT EXISTS audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    username VARCHAR(255),
    action VARCHAR(50),
    table_name VARCHAR(100),
    record_id INT,
    old_values JSON,
    new_values JSON,
    ip_address VARCHAR(45),
    user_agent VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Indexes pour performance
    INDEX idx_user (user_id),
    INDEX idx_table (table_name),
    INDEX idx_action (action),
    INDEX idx_created (created_at),
    INDEX idx_user_date (user_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table pour audit d'accès aux rôles (tentatives denied)
CREATE TABLE IF NOT EXISTS access_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    username VARCHAR(255),
    required_permission VARCHAR(100),
    required_role VARCHAR(50),
    ip_address VARCHAR(45),
    user_agent VARCHAR(500),
    status VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_user (user_id),
    INDEX idx_permission (required_permission),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Augmenter auth_logs pour inclure plus de détails
ALTER TABLE auth_logs ADD COLUMN IF NOT EXISTS failed_attempts INT DEFAULT 1;
ALTER TABLE auth_logs ADD COLUMN IF NOT EXISTS success_after_failed BOOLEAN DEFAULT FALSE;

-- Vue pour audit rapide
CREATE OR REPLACE VIEW v_recent_changes AS
SELECT 
    a.id,
    a.user_id,
    a.username,
    a.action,
    a.table_name,
    a.record_id,
    a.created_at,
    JSON_EXTRACT(a.new_values, '$.NOMS_PRENOMS') as record_description,
    a.ip_address
FROM audit_logs a
ORDER BY a.created_at DESC
LIMIT 100;
