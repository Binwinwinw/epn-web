# Guide d'Implémentation du Système de Login - EPN Web

## ✅ Statut d'Implémentation

- [x] **Infrastructure d'authentification** créée (`src/includes/auth.php`)
- [x] **Migration SQL** pour la table `users` et `auth_logs`
- [x] **Page de login** (`src/pages/login.php`)
- [x] **API d'authentification** (`src/api/auth.php`)
- [x] **Routeur mis à jour** pour protéger les pages
- [x] **Tous les endpoints API sécurisés** avec vérification d'authentification
- [x] **Script de gestion des utilisateurs** (`bin/manage-users.php`)

---

## 1. Installation de la Base de Données

### Étape 1 : Exécuter la migration SQL

```bash
# Via phpMyAdmin ou MySQL client
mysql -u root epn_gestion < database/migration_001_users.sql

# Ou manuellement via phpMyAdmin :
# 1. Ouvrir https://localhost/phpmyadmin
# 2. Sélectionner la base epn_gestion
# 3. Aller dans l'onglet "SQL"
# 4. Copier-coller le contenu de database/migration_001_users.sql
# 5. Exécuter
```

### Étape 2 : Vérifier la création

```bash
# Vérifie que les tables sont créées
php bin/manage-users.php list
```

**Résultat attendu** :

```
📋 Utilisateurs enregistrés:
─────────────────────────────────────────────────────────────────────────
ID Username             Role         Sites        Actif    Dernier login    Créé
─────────────────────────────────────────────────────────────────────────
1  admin               admin        BAC,MAC      Oui      ─                2026-05-06
─────────────────────────────────────────────────────────────────────────
```

---

## 2. Premier Login et Configuration

### Accès de Démonstration (à changer immédiatement après)

- **URL** : `http://localhost/epn-web/?page=login`
- **Utilisateur** : `admin`
- **Mot de passe** : `changeme123`

### Changer le mot de passe d'administration

```bash
# Via le script de gestion
php bin/manage-users.php reset-password admin "VotreNouveauMotDePasse"

# Vérifier que ça fonctionne
# 1. Accéder à http://localhost/epn-web/?page=login
# 2. Entrer : admin / VotreNouveauMotDePasse
```

---

## 3. Créer de Nouveaux Utilisateurs

### Via le script de gestion (recommandé)

```bash
# Créer un agent pour le site BAC
php bin/manage-users.php create john_doe "SecurePassword123" --role=agent --sites=BAC

# Créer un agent avec accès à BAC et MAC
php bin/manage-users.php create jane_smith "AnotherPassword456" --role=agent --sites=BAC,MAC

# Créer un administrateur
php bin/manage-users.php create admin_user "AdminPassword789" --role=admin --sites=BAC,MAC

# Créer un référent
php bin/manage-users.php create referent_user "RefPassword101112" --role=referent --sites=BAC
```

### Via SQL (avancé)

```sql
INSERT INTO users (username, password_hash, email, full_name, role, sites, is_active)
VALUES (
  'john_doe',
  '$2y$10$...',  -- Hash généré avec password_hash('motdepasse', PASSWORD_BCRYPT)
  'john@epn.local',
  'John Doe',
  'agent',
  'BAC',
  1
);
```

---

## 4. Gestion des Utilisateurs

### Lister tous les utilisateurs

```bash
php bin/manage-users.php list
```

### Réinitialiser un mot de passe

```bash
php bin/manage-users.php reset-password john_doe "NewPassword"
```

### Changer le rôle

```bash
php bin/manage-users.php set-role john_doe admin
```

### Changer les sites autorisés

```bash
# Donner accès aux deux sites
php bin/manage-users.php set-sites john_doe BAC,MAC

# Restreindre à BAC uniquement
php bin/manage-users.php set-sites john_doe BAC
```

### Désactiver/Activer un utilisateur

```bash
php bin/manage-users.php disable john_doe
php bin/manage-users.php enable john_doe
```

### Supprimer un utilisateur

```bash
php bin/manage-users.php delete john_doe
```

---

## 5. Architecture et Points Clés

### Session Utilisateur

Après un login réussi, la session contient :

```php
$_SESSION['user_id']      // ID unique
$_SESSION['username']     // Nom d'utilisateur
$_SESSION['role']         // admin | agent | referent
$_SESSION['email']        // Email
$_SESSION['full_name']    // Nom complet
$_SESSION['sites']        // Array de sites autorisés ['BAC'] ou ['BAC', 'MAC']
$_SESSION['site']         // Site actif actuel (le premier de la liste par défaut)
```

### Fonctions Disponibles dans `src/includes/auth.php`

```php
// Vérifier l'authentification
isUserAuthenticated()       // bool
getCurrentUser()            // Array avec user_id, username, role, site
requireAuth()              // Arrête la requête si non authentifié

// Gestion du CSRF
generateCSRFToken()        // Génère/récupère le token
validateCSRFToken($token)  // Vérifie le token
getCSRFToken()             // Alias pour generateCSRFToken()

// Gestion du site
getCurrentSite()           // Retourne le site actif
changeSite($newSite)       // Change le site (avec validation)
getTableForSite($base, $site) // Retourne le nom de la table sécurisé

// Gestion de session
simulateAuthForDevelopment() // Crée une session de dev (dev mode seulement)
logout()                   // Détruit la session
```

### Endpoints API d'Authentification

#### Login

```http
POST /src/api/auth.php?action=login
Content-Type: application/json

{
  "username": "admin",
  "password": "changeme123"
}
```

**Réponse (succès 200)**:

```json
{
  "success": true,
  "message": "Connexion réussie",
  "user": {
    "id": 1,
    "username": "admin",
    "role": "admin",
    "full_name": "Administrateur",
    "email": "admin@epn.local",
    "sites": ["BAC", "MAC"],
    "current_site": "BAC"
  }
}
```

**Réponse (échec 401)**:

```json
{
  "success": false,
  "message": "Identifiants invalides"
}
```

#### Logout

```http
POST /src/api/auth.php?action=logout
```

**Réponse**:

```json
{
  "success": true,
  "message": "Déconnexion réussie"
}
```

#### Vérifier la session

```http
GET /src/api/auth.php?action=check
```

**Réponse (connecté)**:

```json
{
  "success": true,
  "authenticated": true,
  "user": { ... }
}
```

**Réponse (non connecté 401)**:

```json
{
  "success": false,
  "authenticated": false
}
```

#### Changer de site

```http
POST /src/api/auth.php?action=changeSite
Content-Type: application/json

{
  "site": "MAC"
}
```

---

## 6. Protection des Pages et Endpoints

### Pages Publiques (pas de login requis)

- `?page=landingpage` — Page d'accueil publique
- `?page=login` — Page de connexion
- `?page=help` — Aide

### Pages Protégées (login requis)

- `?page=accueil` — Accueil application
- `?page=accueil-site` — Accueil site
- `?page=inscriptions` — Gestion des inscriptions
- `?page=frequentations` — Gestion des fréquentations
- `?page=ateliers` — Gestion des ateliers
- `?page=materiel` — Gestion du matériel
- `?page=tableau-bord` — Tableau de bord
- `?page=statistiques` — Statistiques

### Endpoints API (tous protégés)

Tous les endpoints API vérifient l'authentification :

```php
// En développement (DEBUG_MODE = true) :
// Crée automatiquement une session de dev si non connecté

// En production (DEBUG_MODE = false) :
// Retourne 401 Unauthorized si non connecté
```

**Endpoints protégés**:

- `/src/api/frequentation.php`
- `/src/api/atelier.php`
- `/src/api/inscription.php`
- `/src/api/postes.php`
- `/src/api/usagers.php`
- `/src/api/statistiques.php`
- `/src/api/agents.php`
- `/src/api/session.php`

**Endpoints d'authentification** (publics pour le login) :

- `/src/api/auth.php?action=login`
- `/src/api/auth.php?action=logout`
- `/src/api/auth.php?action=check`
- `/src/api/auth.php?action=changeSite`

---

## 7. Logs d'Authentification

La table `auth_logs` enregistre :

- Tentatives de login (succès et échecs)
- Adresse IP
- User Agent (navigateur)
- Horodatage

Consultez les logs :

```sql
SELECT * FROM auth_logs
ORDER BY created_at DESC
LIMIT 100;
```

### Requêtes utiles

```sql
-- Dernières tentatives de connexion
SELECT * FROM auth_logs
WHERE action = 'login'
ORDER BY created_at DESC
LIMIT 20;

-- Connexions échouées
SELECT * FROM auth_logs
WHERE action = 'login' AND status = 'failed'
ORDER BY created_at DESC;

-- Tentatives de connexion par IP
SELECT ip_address, COUNT(*) as tentatives
FROM auth_logs
WHERE action = 'login' AND status = 'failed'
AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
GROUP BY ip_address;
```

---

## 8. Rôles et Permissions

### Définition des rôles

| Rôle         | Permissions             | Exemples                                                                                        |
| ------------ | ----------------------- | ----------------------------------------------------------------------------------------------- |
| **admin**    | Accès complet           | - Gérer les utilisateurs<br>- Voir tous les sites (BAC/MAC)<br>- Exporter les données           |
| **agent**    | Opérationnel complet    | - Enregistrer des fréquentations<br>- Gérer les ateliers<br>- Voir les statistiques de son site |
| **referent** | Lecture + Saisie limité | - Voir les données<br>- Ajouter des usagers<br>- Pas de suppression                             |

### À implémenter (Phase 3)

- Vérification du rôle dans les endpoints
- Restrictions d'accès par site (BAC/MAC)
- Audit des actions par utilisateur

Exemple :

```php
// Dans un endpoint sensible
$user = getCurrentUser();

if ($user['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Accès refusé']);
    exit;
}
```

---

## 9. Mode Développement vs Production

### Développement (DEBUG_MODE = true)

- ✅ Les endpoints créent automatiquement une session dev si non connecté
- ✅ Les pages peuvent être visitées sans authentification via API
- ✅ Permet de tester la logique métier sans login

### Production (DEBUG_MODE = false)

- ❌ Tous les endpoints retournent 401 si non authentifié
- ❌ Toutes les pages protégées redirigent vers login
- ✅ Sécurité stricte

### Configuration

```php
// src/config/config.local.php
'debug' => true,  // Développement

// src/config/config.prod.php
'debug' => false, // Production
```

---

## 10. Configuration en Production

### .env.production

Créer un fichier `.env.production` (ignoré par Git) :

```bash
DEBUG=false
DB_HOST=prodserver.example.com
DB_NAME=epn_prod
DB_USER=epn_user
DB_PASS=VerySecurePassword123
```

### Étapes de déploiement

1. Exécuter la migration SQL
2. Créer le premier utilisateur admin :
   ```bash
   php bin/manage-users.php create admin "VerySecureAdminPassword"
   ```
3. Vérifier le login avec admin
4. Créer les utilisateurs métier
5. Activer DEBUG_MODE = false
6. Configurer HTTPS (obligatoire pour cookies sécurisés)
7. Configurer les headers de sécurité (HSTS, CSP, etc.)

---

## 11. Dépannage

### Erreur 401 en développement

**Problème** : Même avec DEBUG_MODE = true, j'ai une erreur 401.

**Solutions** :

1. Vérifier que `src/config/config.local.php` a `'debug' => true`
2. Vérifier que `DEBUG_MODE` est défini et vaut `true`
3. Vérifier que la session est bien démarrée dans `auth.php`

### Les utilisateurs ne peuvent pas changer de site

**Problème** : L'appel à changeSite() échoue.

**Solutions** :

1. Vérifier que l'utilisateur a les sites dans `$_SESSION['sites']`
2. Vérifier que le site demandé est bien dans la liste autorisée
3. Vérifier que le site est 'BAC' ou 'MAC' (case-sensitive après strtoupper)

### Erreur "Action inconnue" dans session.php

**Problème** : L'ancienne API session.php reçoit une action inconnue.

**Solution** : Utiliser la nouvelle API auth.php à la place :

```javascript
// ❌ ANCIEN
fetch("src/api/session.php", {
  method: "POST",
  body: JSON.stringify({ action: "setSite", site: "MAC" }),
});

// ✅ NOUVEAU
fetch("src/api/auth.php?action=changeSite", {
  method: "POST",
  body: JSON.stringify({ site: "MAC" }),
});
```

### Problème de cookies de session

**Problème** : Les sessions ne persistent pas après un reload.

**Solutions** :

1. Vérifier que `session.cookie_httponly = 1` est activé
2. Vérifier que `session.use_only_cookies = 1` est activé
3. En HTTPS, vérifier que `session.cookie_secure = 1`
4. Vérifier que le navigateur accepte les cookies (pas en mode privé)

---

## 12. Ressources et Références

- [RFC 7231 - HTTP Status Codes](https://tools.ietf.org/html/rfc7231#section-6.3.1)
- [OWASP - Authentication Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/Authentication_Cheat_Sheet.html)
- [PHP password_hash()](https://www.php.net/manual/en/function.password-hash.php)
- [PHP Sessions](https://www.php.net/manual/en/intro.session.php)

---

**Dernier mise à jour** : 6 mai 2026
