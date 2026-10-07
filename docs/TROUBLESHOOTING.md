# Guide de Dépannage - Configuration

## Erreur : accès refusé à la base locale

### Symptôme

L’application tente d’utiliser les identifiants locaux au lieu de la configuration de production.

### Solutions

#### 1. Vérifier le fichier de production

- Confirmez que `src/config/config.prod.php` existe sur le serveur
- Vérifiez ses permissions

#### 2. Forcer l’environnement de production

Dans `.htaccess`, vous pouvez définir :

```apache
SetEnv EPN_ENVIRONMENT production
```

#### 3. Vérifier le contenu de la configuration

Exemple minimal :

```php
return [
    'database' => [
        'host' => 'localhost',
        'name' => 'votre_base',
        'user' => 'votre_utilisateur',
        'pass' => 'votre_mot_de_passe',
        'charset' => 'utf8mb4'
    ]
];
```

#### 4. Activer temporairement le debug

- Activez `debug => true` uniquement le temps du diagnostic
- Vérifiez ensuite les logs PHP
- Désactivez le debug immédiatement après

## Vérification rapide

Le fichier `test-config.php` peut aider à confirmer :

- quelle configuration est chargée
- si la connexion fonctionne
- si les tables existent

⚠️ Supprimez ce fichier après usage.

## Erreur MySQL 1813 ou 1932

### Symptômes fréquents

- `Table 'epn_gestion.postes' doesn't exist in engine`
- `Tablespace for table 'epn_gestion.inscription' exists`
- les tables semblent présentes dans MySQL mais sont illisibles dans phpMyAdmin ou via PDO

### Cause probable

Sous XAMPP, des fichiers `.ibd` orphelins peuvent rester dans `C:\xampp\mysql\data\epn_gestion\` après une corruption locale ou une réinitialisation incomplète.

### Procédure de reprise locale

1. sauvegarder les fichiers `.ibd` concernés
2. supprimer les fichiers orphelins du dossier MySQL local
3. réimporter `database/schema.sql`
4. vérifier les tables avec `CHECK TABLE`

### Important

- cette procédure est destinée à l'environnement local XAMPP
- toujours faire une sauvegarde avant suppression d'un fichier `.ibd`
- ne jamais appliquer cette manipulation en production sans validation humaine

## Checklist

- [ ] `src/config/config.prod.php` existe sur le serveur
- [ ] les identifiants de base sont corrects
- [ ] les permissions sont correctes
- [ ] la base de données existe
- [ ] `.htaccess` est présent

## Si rien ne fonctionne

- Vérifiez les logs PHP côté hébergeur
- Testez les identifiants via phpMyAdmin
- Vérifiez que MySQL accepte bien les connexions attendues
