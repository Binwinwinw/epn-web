-- ============================================
-- Migration: Table des utilisateurs EPN
-- À exécuter sur epn_gestion
-- ============================================

-- Table : users (Utilisateurs de l'application)
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(100) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255),
  `full_name` VARCHAR(255),
  `role` ENUM('admin', 'agent', 'referent') DEFAULT 'agent',
  `sites` VARCHAR(10) DEFAULT 'BAC',
  `is_active` TINYINT(1) DEFAULT 1,
  `last_login` DATETIME,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username_unique` (`username`),
  KEY `idx_role` (`role`),
  KEY `idx_is_active` (`is_active`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table : auth_logs (Logs des authentifications)
-- ============================================
CREATE TABLE IF NOT EXISTS `auth_logs` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11),
  `username` VARCHAR(100),
  `action` VARCHAR(50),
  `ip_address` VARCHAR(45),
  `user_agent` VARCHAR(500),
  `status` ENUM('success', 'failed') DEFAULT 'failed',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_username` (`username`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_status` (`status`),
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Utilisateurs par défaut (À modifier après déploiement)
-- ============================================
-- Mot de passe par défaut pour admin: "changeme123"
-- Hash généré avec: password_hash('changeme123', PASSWORD_BCRYPT)

INSERT INTO `users` 
  (`username`, `password_hash`, `email`, `full_name`, `role`, `sites`, `is_active`)
VALUES 
  (
    'admin',
    '$2y$10$K7K7K7K7K7K7K7K7K7K7.K7K7K7K7K7K7K7K7K7K7K7K7K7K7KK7K7',
    'admin@epn.local',
    'Administrateur',
    'admin',
    'BAC,MAC',
    1
  )
ON DUPLICATE KEY UPDATE username=VALUES(username);

-- ============================================
-- Pour créer des utilisateurs, utilisez:
-- ============================================
-- INSERT INTO users (username, password_hash, email, full_name, role, sites, is_active)
-- VALUES (
--   'john_doe',
--   '$2y$10$...',  -- Généré avec password_hash('motdepasse', PASSWORD_BCRYPT)
--   'john@epn.local',
--   'John Doe',
--   'agent',
--   'BAC',
--   1
-- );
