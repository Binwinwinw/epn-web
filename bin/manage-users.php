#!/usr/bin/env php
<?php
/**
 * Script de gestion des utilisateurs EPN
 *
 * Usage:
 *   php bin/manage-users.php create <username> <password> [--role=admin|agent|referent] [--sites=BAC,MAC]
 *   php bin/manage-users.php list
 *   php bin/manage-users.php reset-password <username> <new_password>
 *   php bin/manage-users.php delete <username>
 *   php bin/manage-users.php set-role <username> <role>
 *   php bin/manage-users.php set-sites <username> <sites>
 *   php bin/manage-users.php disable <username>
 *   php bin/manage-users.php enable <username>
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("This script must be run from the command line.\n");
}

require_once __DIR__ . '/../src/config/config.php';

$command = $argv[1] ?? 'help';
$args = array_slice($argv, 2);

switch ($command) {
    case 'create':
        cmdCreate($pdo, $args);
        break;

    case 'list':
        cmdList($pdo);
        break;

    case 'reset-password':
        cmdResetPassword($pdo, $args);
        break;

    case 'delete':
        cmdDelete($pdo, $args);
        break;

    case 'set-role':
        cmdSetRole($pdo, $args);
        break;

    case 'set-sites':
        cmdSetSites($pdo, $args);
        break;

    case 'disable':
        cmdDisable($pdo, $args);
        break;

    case 'enable':
        cmdEnable($pdo, $args);
        break;

    default:
        showHelp();
        break;
}

function cmdCreate($pdo, $args)
{
    if (count($args) < 2) {
        echo "❌ Usage: create <username> <password> [--role=admin|agent|referent] [--sites=BAC,MAC]\n";
        exit(1);
    }

    $username = $args[0];
    $password = $args[1];
    $role = 'agent';
    $sites = 'BAC';

    // Parses options
    for ($i = 2; $i < count($args); $i++) {
        if (strpos($args[$i], '--role=') === 0) {
            $role = substr($args[$i], 7);
        } elseif (strpos($args[$i], '--sites=') === 0) {
            $sites = substr($args[$i], 8);
        }
    }

    // Valider le rôle
    if (!in_array($role, ['admin', 'agent', 'referent'])) {
        echo "❌ Rôle invalide: $role\n";
        exit(1);
    }

    // Vérifier l'existence
    $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ?');
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        echo "❌ L'utilisateur '$username' existe déjà\n";
        exit(1);
    }

    // Créer le hash
    $passwordHash = password_hash($password, PASSWORD_BCRYPT);

    // Insérer
    try {
        $stmt = $pdo->prepare('
            INSERT INTO users (username, password_hash, role, sites, is_active)
            VALUES (?, ?, ?, ?, 1)
        ');
        $stmt->execute([$username, $passwordHash, $role, $sites]);

        echo "✅ Utilisateur créé avec succès\n";
        echo "   Utilisateur: $username\n";
        echo "   Rôle: $role\n";
        echo "   Sites: $sites\n";
    } catch (Exception $e) {
        echo "❌ Erreur: " . $e->getMessage() . "\n";
        exit(1);
    }
}

function cmdList($pdo)
{
    try {
        $stmt = $pdo->query('
            SELECT id, username, role, sites, is_active, last_login, created_at
            FROM users
            ORDER BY created_at DESC
        ');
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($users)) {
            echo "Aucun utilisateur trouvé\n";
            return;
        }

        echo "\n📋 Utilisateurs enregistrés:\n";
        echo str_repeat("─", 100) . "\n";
        printf(
            "%-4s %-20s %-12s %-12s %-8s %-20s %-20s\n",
            "ID",
            "Username",
            "Role",
            "Sites",
            "Actif",
            "Dernier login",
            "Créé"
        );
        echo str_repeat("─", 100) . "\n";

        foreach ($users as $user) {
            printf(
                "%-4d %-20s %-12s %-12s %-8s %-20s %-20s\n",
                $user['id'],
                $user['username'],
                $user['role'],
                $user['sites'],
                $user['is_active'] ? 'Oui' : 'Non',
                $user['last_login'] ?? '─',
                substr($user['created_at'], 0, 10)
            );
        }
        echo str_repeat("─", 100) . "\n";
    } catch (Exception $e) {
        echo "❌ Erreur: " . $e->getMessage() . "\n";
        exit(1);
    }
}

function cmdResetPassword($pdo, $args)
{
    if (count($args) < 2) {
        echo "❌ Usage: reset-password <username> <new_password>\n";
        exit(1);
    }

    $username = $args[0];
    $newPassword = $args[1];

    $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ?');
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if (!$user) {
        echo "❌ Utilisateur '$username' non trouvé\n";
        exit(1);
    }

    $passwordHash = password_hash($newPassword, PASSWORD_BCRYPT);

    try {
        $stmt = $pdo->prepare('UPDATE users SET password_hash = ? WHERE username = ?');
        $stmt->execute([$passwordHash, $username]);

        echo "✅ Mot de passe réinitialisé pour '$username'\n";
    } catch (Exception $e) {
        echo "❌ Erreur: " . $e->getMessage() . "\n";
        exit(1);
    }
}

function cmdDelete($pdo, $args)
{
    if (count($args) < 1) {
        echo "❌ Usage: delete <username>\n";
        exit(1);
    }

    $username = $args[0];

    if ($username === 'admin') {
        echo "⚠️  Impossible de supprimer l'utilisateur 'admin'\n";
        exit(1);
    }

    $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ?');
    $stmt->execute([$username]);
    if (!$stmt->fetch()) {
        echo "❌ Utilisateur '$username' non trouvé\n";
        exit(1);
    }

    try {
        $stmt = $pdo->prepare('DELETE FROM users WHERE username = ?');
        $stmt->execute([$username]);

        echo "✅ Utilisateur '$username' supprimé\n";
    } catch (Exception $e) {
        echo "❌ Erreur: " . $e->getMessage() . "\n";
        exit(1);
    }
}

function cmdSetRole($pdo, $args)
{
    if (count($args) < 2) {
        echo "❌ Usage: set-role <username> <role>\n";
        exit(1);
    }

    $username = $args[0];
    $role = $args[1];

    if (!in_array($role, ['admin', 'agent', 'referent'])) {
        echo "❌ Rôle invalide: $role\n";
        exit(1);
    }

    $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ?');
    $stmt->execute([$username]);
    if (!$stmt->fetch()) {
        echo "❌ Utilisateur '$username' non trouvé\n";
        exit(1);
    }

    try {
        $stmt = $pdo->prepare('UPDATE users SET role = ? WHERE username = ?');
        $stmt->execute([$role, $username]);

        echo "✅ Rôle de '$username' défini à '$role'\n";
    } catch (Exception $e) {
        echo "❌ Erreur: " . $e->getMessage() . "\n";
        exit(1);
    }
}

function cmdSetSites($pdo, $args)
{
    if (count($args) < 2) {
        echo "❌ Usage: set-sites <username> <sites>\n";
        echo "   Sites: BAC, MAC, ou BAC,MAC\n";
        exit(1);
    }

    $username = $args[0];
    $sites = $args[1];

    $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ?');
    $stmt->execute([$username]);
    if (!$stmt->fetch()) {
        echo "❌ Utilisateur '$username' non trouvé\n";
        exit(1);
    }

    try {
        $stmt = $pdo->prepare('UPDATE users SET sites = ? WHERE username = ?');
        $stmt->execute([$sites, $username]);

        echo "✅ Sites de '$username' défini à '$sites'\n";
    } catch (Exception $e) {
        echo "❌ Erreur: " . $e->getMessage() . "\n";
        exit(1);
    }
}

function cmdDisable($pdo, $args)
{
    if (count($args) < 1) {
        echo "❌ Usage: disable <username>\n";
        exit(1);
    }

    $username = $args[0];

    try {
        $stmt = $pdo->prepare('UPDATE users SET is_active = 0 WHERE username = ?');
        $stmt->execute([$username]);

        echo "✅ Utilisateur '$username' désactivé\n";
    } catch (Exception $e) {
        echo "❌ Erreur: " . $e->getMessage() . "\n";
        exit(1);
    }
}

function cmdEnable($pdo, $args)
{
    if (count($args) < 1) {
        echo "❌ Usage: enable <username>\n";
        exit(1);
    }

    $username = $args[0];

    try {
        $stmt = $pdo->prepare('UPDATE users SET is_active = 1 WHERE username = ?');
        $stmt->execute([$username]);

        echo "✅ Utilisateur '$username' activé\n";
    } catch (Exception $e) {
        echo "❌ Erreur: " . $e->getMessage() . "\n";
        exit(1);
    }
}

function showHelp()
{
    echo <<<'EOH'

🔐 EPN Web - Gestion des utilisateurs

Usage:
  php bin/manage-users.php <command> [options]

Commands:
  create <username> <password>          Créer un nouvel utilisateur
                                        Options: --role=admin|agent|referent
                                                 --sites=BAC|MAC|BAC,MAC
  
  list                                  Lister tous les utilisateurs
  
  reset-password <username> <password>  Réinitialiser le mot de passe
  
  delete <username>                     Supprimer un utilisateur
  
  set-role <username> <role>            Changer le rôle
  
  set-sites <username> <sites>          Changer les sites autorisés
  
  disable <username>                    Désactiver un utilisateur
  
  enable <username>                     Activer un utilisateur

Examples:
  php bin/manage-users.php create john_doe "MyPassword123" --role=agent --sites=BAC
  php bin/manage-users.php list
  php bin/manage-users.php reset-password john_doe "NewPassword456"
  php bin/manage-users.php set-role john_doe admin
  php bin/manage-users.php disable john_doe
  php bin/manage-users.php enable john_doe

EOH;
}
?>