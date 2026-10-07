# Scripts d'Administration EPN Web

## Sécurité

Ces scripts sont destinés au terminal uniquement. Ils refusent toute exécution depuis le navigateur.

## `install.php`

Script d’installation sécurisé pour créer la base de données et un compte administrateur principal.

### Utilisation

```bash
php bin/install.php --admin-password "MotDePasseFort123!" --admin-user admin
```

### Exemple sécurisé

```bash
php bin/install.php --admin-user admin --admin-password "T0pS3curePassword!2025"
```

Aucun compte par défaut faible n’est créé en mode sécurisé.

## `manage-users.php`

Script de gestion des utilisateurs en ligne de commande.

### Utilisation

```bash
php bin/manage-users.php <command> [options]
```

### Commandes

#### Créer un utilisateur

```bash
php bin/manage-users.php create <username> <password> [--role=admin|agent|referent] [--sites=BAC,MAC]
```

#### Lister les utilisateurs

```bash
php bin/manage-users.php list
```

#### Réinitialiser le mot de passe

```bash
php bin/manage-users.php reset-password <username> <new_password>
```

#### Changer le rôle

```bash
php bin/manage-users.php set-role <username> <role>
```

#### Changer les sites autorisés

```bash
php bin/manage-users.php set-sites <username> <sites>
```

#### Supprimer un utilisateur

```bash
php bin/manage-users.php delete <username>
```

### Notes importantes

- ⚠️ L'utilisateur `admin` ne peut pas être supprimé
- ✅ Les mots de passe sont hashés avec bcrypt
- 🔒 Le script ne sauvegarde pas les mots de passe en clair
- 📝 Toutes les modifications sont enregistrées dans la base de données

## `reset-passwords.php`

Script de réinitialisation contrôlée. Il refuse son exécution sans confirmation explicite.

```bash
php bin/reset-passwords.php --force --admin-password "MotDePasseFort123!" --demo-password "AutreMotDePasseFort456!"
```

Le dossier [bin](bin) ne doit jamais être exposé publiquement. Il doit rester hors du dossier web accessible.
