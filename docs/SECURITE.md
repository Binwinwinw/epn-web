# Guide de Sécurité - EPN Web

## Phase 1 : Corrections Immédiatement Appliquées (6 mai 2026)

### 1. ✅ SQL Injection dans les endpoints

**Problème**: Les noms de tables étaient interpolés directement dans les requêtes SQL.

**Fichiers affectés**:

- `src/api/frequentation.php`
- `src/api/atelier.php`

**Solution appliquée**:

- Créé une fonction `getTableForSite()` dans `src/includes/auth.php`
- Cette fonction utilise une **whitelist** de tables autorisées
- Les noms de table sont maintenant sélectionnés via un dictionnaire, jamais interpolés

```php
// ❌ AVANT (vulnérable)
$tableFrequentation = $site === 'BAC' ? 'frequentation1' : 'frequentation2';
$stmt = $pdo->prepare("SELECT * FROM $tableFrequentation WHERE ...");

// ✅ APRÈS (sécurisé)
$tableFrequentation = getTableForSite('frequentation', $site);
// getTableForSite() utilise une whitelist interne
```

**Impact**: SQL injection via le paramètre `site` est maintenant impossible.

---

### 2. ✅ XSS JavaScript dans frequentations.js

**Problème**: Les données utilisateur étaient insérées dans des attributs `onclick` sans échappement.

```javascript
// ❌ AVANT (vulnérable)
html += `<li onclick="choisirUsager(${usager.NUM_INSCRIPTION}, '${usager.NOMS_PRENOMS}', ...)">
```

**Solution appliquée**:

- Remplacé la construction de HTML par des éléments DOM
- Utilisation de `setAttribute()` et `textContent` au lieu d'innerHTML
- Event listeners au lieu d'onclick inline
- Les données sensibles sont stockées en attributs `data-*`

```javascript
// ✅ APRÈS (sécurisé)
const li = document.createElement("li");
li.setAttribute("data-noms", noms);
li.textContent = noms; // Échappe automatiquement
li.addEventListener("click", choisirUsagerFromElement);
```

**Impact**: XSS via injection de quotes dans les noms d'usagers est maintenant impossible.

---

### 3. ✅ Infrastructure d'authentification créée

**Nouveau fichier**: `src/includes/auth.php`

**Fonctionnalités disponibles**:

- `isUserAuthenticated()` — Vérifie si un utilisateur est connecté
- `requireAuth()` — Termine la requête si non authentifié
- `getTableForSite()` — Whitelist des tables BAC/MAC
- `generateCSRFToken()` / `validateCSRFToken()` — Protection CSRF
- `getCurrentSite()` / `changeSite()` — Gestion du site actif
- `simulateAuthForDevelopment()` — Authentification de développement (temporaire)

**Mode développement**:

- Les endpoints acceptent les requêtes sans authentification si `DEBUG_MODE` est activé
- Une session de dev est créée automatiquement pour tester
- ⚠️ **À désactiver en production**

**Mode production**:

- Ajouter `requireAuth()` en début de chaque endpoint
- Implémenter un vrai système de login
- Valider les CSRF tokens sur les POST/PUT/DELETE

---

### 4. ✅ .gitignore amélioré

**Ajouts**:

```
.env
.env.local
.env.production
.env.*.local
database/backups/
```

**Raison**: Éviter de committer des secrets ou fichiers sensibles.

---

### 5. ✅ Fichier d'exemple d'environnement

**Nouveau fichier**: `.env.example`

Contient un template de toutes les variables d'environnement nécessaires.

---

## Phase 2 : À Faire (court terme)

### 1. Implémenter un vrai système de login

**Où**: `src/pages/login.php` (nouvelle page)

**Minimal**:

```php
// Vérifier username/password
if (hash_equals(AUTH_PASSWORD_HASH, password_hash($password, PASSWORD_DEFAULT))) {
    $_SESSION['user_id'] = 1;
    $_SESSION['username'] = $username;
    $_SESSION['role'] = 'admin';
}
```

**Recommandé**:

- Table `users` avec hash des mots de passe
- Rôles dans la base : ADMIN, AGENT, REFERENT
- Audit des logins

---

### 2. Ajouter CSRF tokens sur tous les POST/PUT

**Dans les pages**:

```php
<input type="hidden" name="csrf_token" value="<?= getCSRFToken() ?>">
```

**Dans les API endpoints**:

```php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!validateCSRFToken($token)) {
        http_response_code(403);
        die(json_encode(['success' => false, 'message' => 'CSRF token invalide']));
    }
}
```

---

### 3. Valider les enums et formats

**Exemple**: Valider le statut

```php
$statuts_valides = ['Public', 'Privé', 'Confirmé'];
if (!in_array($_POST['statut'], $statuts_valides)) {
    throw new Exception('Statut invalide');
}
```

**Exemple**: Valider les emails

```php
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    throw new Exception('Email invalide');
}
```

---

### 4. Corriger les autres endpoints

Ajouter `requireAuth()` et CSRF validation dans:

- `src/api/inscription.php`
- `src/api/postes.php`
- `src/api/session.php` — remplacer `setSite` par un endpoint d'authentification
- `src/api/usagers.php`
- `src/api/statistiques.php`

---

## Phase 3 : À Faire (moyen terme)

### 1. Tests de sécurité

- [ ] Scanner OWASP (ZAP, Burp Suite)
- [ ] Audit de pénétration manuel
- [ ] Tests de validation d'input
- [ ] Tests de CORS

### 2. Logging et monitoring

- [ ] Enregistrer les tentatives échouées de login
- [ ] Enregistrer les modifications critiques (INSERT/UPDATE/DELETE)
- [ ] Alerter sur les erreurs 403/401

### 3. Documentation

- [ ] README pour le déploiement sécurisé
- [ ] Manuel d'administration pour les rôles et permissions
- [ ] Procédure de gestion des secrets (rotation de clés, etc.)

---

## Utilisation de l'authentification

### En développement

```php
<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';

// Créer une session de dev automatiquement
if (DEBUG_MODE && !isUserAuthenticated()) {
    simulateAuthForDevelopment();
}

// Accéder à l'utilisateur actuel
$user = getCurrentUser();
echo $user['username']; // 'dev_user'
echo $user['site'];     // 'BAC'
?>
```

### En production

```php
<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';

// Exiger l'authentification (arrête la requête si non auth)
requireAuth();

// Maintenant on peut accéder à l'utilisateur
$user = getCurrentUser();
// ...
?>
```

---

## Checklist d'audit sécurité

- [x] SQL injection via noms de table — Corrigé
- [x] XSS JavaScript — Corrigé
- [ ] Authentification réelle — À faire
- [ ] CSRF tokens — À faire
- [ ] Validation des inputs — Partiellement fait
- [ ] Logging des actions — À faire
- [ ] Rate limiting — À faire
- [ ] Sessions avec expiration — À faire
- [ ] HTTPS en production — À vérifier
- [ ] HSTS headers — À ajouter
- [ ] Content-Security-Policy — À ajouter

---

## Ressources

- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [PDO prepared statements](https://www.php.net/manual/en/pdo.prepared-statements.php)
- [Password hashing PHP](https://www.php.net/manual/en/function.password-hash.php)
- [CSRF protection](https://owasp.org/www-community/attacks/csrf)
