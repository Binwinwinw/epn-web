# 🚀 Checklist Déploiement - Système de Login EPN Web

## Phase 1 : Installation (5 min)

- [ ] **Exécuter la migration SQL**

  ```bash
  mysql -u root epn_gestion < database/migration_001_users.sql
  ```

- [ ] **Vérifier la création des tables**

  ```bash
  php bin/manage-users.php list
  ```

- [ ] **Accéder au login**
  - Naviguer vers : `http://localhost/epn-web/?page=login`
  - Entrer : `admin` / `changeme123`

## Phase 2 : Configuration (10 min)

- [ ] **Changer le mot de passe admin**

  ```bash
  php bin/reset-passwords.php admin "VotreNouveauMotDePasse"
  ```

- [ ] **Créer les utilisateurs métier**

  ```bash
  # Agent BAC
  php bin/manage-users.php create agent1 "Password1" --role=agent --sites=BAC

  # Agent BAC+MAC
  php bin/manage-users.php create agent2 "Password2" --role=agent --sites=BAC,MAC

  # Administrateur
  php bin/manage-users.php create admin2 "Password3" --role=admin --sites=BAC,MAC
  ```

- [ ] **Tester les logins**
  - Logout avec le compte admin
  - Login avec un nouvel agent
  - Vérifier l'accès aux pages

## Phase 3 : Sécurité (2 h)

### Configuration

- [ ] **Mettre DEBUG_MODE = false en production**

  ```php
  // src/config/config.prod.php
  'debug' => false,
  ```

- [ ] **HTTPS activé et forcé**
  - Vérifier le certificat SSL (2048+ bits)
  - Configurer redirection HTTP → HTTPS
  - Mettre à jour session.secure = On dans php.ini

- [ ] **Session PHP sécurisée** (php.ini)
  ```ini
  session.httponly = On          # Empêche access via JavaScript
  session.secure = On            # HTTPS only (production)
  session.name = PHPSESSIDEPN    # Nom personnalisé
  session.use_strict_mode = On   # Session fixation prevention
  ```

### Remédiation d'Audit

✅ **Les vulnérabilités suivantes ont été résolues** (voir docs/AUDIT_SECURITE_COMPLET.md):

1. **SQL Injection** → Whitelist stricte `getTableForSite()` + PDO prepared statements
2. **XSS JavaScript** → DOM API refactorisation (pas innerHTML)
3. **Pas d'Authentification** → Session + Bcrypt + CSRF token framework
4. **Pas de Rate Limiting** → IP+Username rate limiting (5/IP/h, 10/user/h)
5. **Validation insuffisante** → 12 validators strictes (email, date, enum, etc.)
6. **Headers HTTP manquants** → HSTS, CSP, X-Frame-Options, X-XSS-Protection

**Fichiers de sécurité activés**:

- ✅ `src/includes/auth.php` - Authentification + CSRF
- ✅ `src/includes/validation.php` - 12 validators
- ✅ `src/includes/rate-limit.php` - Brute force protection
- ✅ `src/includes/security-headers.php` - HTTP security headers
- ✅ `src/api/auth.php` - Endpoints avec doLogin() renforcé
- ✅ `src/api/frequentation.php` - Validation + CSRF check
- ✅ `src/api/atelier.php` - Validation + CSRF check
- ✅ `src/api/inscription.php` - Validation + CSRF check
- ✅ `src/api/postes.php` - Validation + CSRF check
- ✅ `public/index.php` - Global security headers

### Tests de Sécurité

- [ ] **Test de login/logout**

  ```bash
  curl -X POST http://localhost/epn-web/src/api/auth.php \
    -d '{"username":"admin","password":"changeme123"}' \
    -H "Content-Type: application/json"
  ```

  Résultat attendu: 200 OK + session cookie

- [ ] **Test de protection API (authentification)**

  ```bash
  # Sans session (doit retourner 401)
  curl -X GET http://localhost/epn-web/src/api/frequentation.php
  ```

  Résultat attendu: 401 Unauthorized

- [ ] **Test de protection CSRF**

  ```bash
  # POST sans CSRF token (doit retourner 403)
  curl -X POST http://localhost/epn-web/src/api/frequentation.php \
    -d 'date_frequentation=2025-01-01'
  ```

  Résultat attendu: 403 Forbidden (CSRF_VALIDATION_FAILED)

- [ ] **Test de rate limiting**

  ```bash
  # Tenter 6 logins échoués avec mauvais password
  for i in {1..6}; do
    curl -X POST http://localhost/epn-web/src/api/auth.php \
      -d '{"username":"admin","password":"wrongpassword"}'
    sleep 1
  done
  ```

  Résultat attendu (tentative 6+): 429 Too Many Requests

- [ ] **Test de validation d'entrée**

  ```bash
  # Tenter une date invalide (doit retourner 400)
  curl -X POST http://localhost/epn-web/src/api/frequentation.php \
    -d 'date_frequentation=invalid&csrf_token=xxx'
  ```

  Résultat attendu: 400 Bad Request (VALIDATION_ERROR)

---

## Phase 4 : Sauvegardes et Monitoring (1 h)

- [ ] **Sauvegarder la base de données**

  ```bash
  mysqldump -u root epn_gestion > epn_backup_$(date +%Y%m%d).sql
  ```

- [ ] **Configurer les logs**
  - Vérifier que les logs d'erreur PHP sont activés
  - Configurer la rotation des logs

---

## 📞 Support

Voir la documentation complète : [docs/CONFIGURATION.md](CONFIGURATION.md)
