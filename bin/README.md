# Scripts d'Administration EPN Web

## `manage-users.php`

Script de gestion des utilisateurs en ligne de commande.

### Installation

```bash
# Le script est déjà en place
# Rendre exécutable (optionnel sur Linux/Mac)
chmod +x manage-users.php
```

### Utilisation

```bash
php manage-users.php <command> [options]
```

### Commandes

#### Créer un utilisateur

```bash
php manage-users.php create <username> <password> [--role=admin|agent|referent] [--sites=BAC,MAC]

# Exemples
php manage-users.php create john_doe "MyPassword123" --role=agent --sites=BAC
php manage-users.php create jane_smith "AnotherPassword" --role=admin --sites=BAC,MAC
```

#### Lister les utilisateurs

```bash
php manage-users.php list
```

#### Réinitialiser le mot de passe

```bash
php manage-users.php reset-password <username> <new_password>

# Exemple
php manage-users.php reset-password john_doe "NewPassword123"
```

#### Changer le rôle

```bash
php manage-users.php set-role <username> <role>

# Exemple
php manage-users.php set-role john_doe admin
```

#### Changer les sites autorisés

```bash
php manage-users.php set-sites <username> <sites>

# Exemple
php manage-users.php set-sites john_doe BAC,MAC
```

#### Désactiver/Activer un utilisateur

```bash
php manage-users.php disable <username>
php manage-users.php enable <username>
```

#### Supprimer un utilisateur

```bash
php manage-users.php delete <username>
```

### Notes importantes

- ⚠️ L'utilisateur `admin` ne peut pas être supprimé
- ✅ Les mots de passe sont hashés avec bcrypt
- 🔒 Le script ne sauvegarde pas les mots de passe en clair
- 📝 Toutes les modifications sont enregistrées dans la base de données

### Accès de développement

Un utilisateur `admin` avec le mot de passe `changeme123` est créé par défaut.

**À CHANGER IMMÉDIATEMENT après le déploiement** :

```bash
php manage-users.php reset-password admin "VotreNouveauMotDePasse"
```
