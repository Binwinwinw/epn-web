# Guide de Configuration Hybride

L'application utilise un système de configuration hybride qui détecte automatiquement si l'environnement est local ou de production.

## Fonctionnement

Le fichier `src/config/config.php` gère la détection d’environnement :

- **Local** : accès via `localhost` ou `127.0.0.1`
- **Production** : autres domaines ou configuration forcée côté serveur

## Configuration locale

Le fichier `src/config/config.local.php` contient les paramètres par défaut pour le développement local avec XAMPP.

## Configuration de production

### Étape 1 : créer le fichier

Le fichier `src/config/config.prod.php` doit être créé sur le serveur et rester hors versionnement.

### Étape 2 : renseigner les informations

Exemple de structure :

```php
return [
    'database' => [
        'host' => 'localhost',
        'name' => 'votre_base',
        'user' => 'votre_utilisateur',
        'pass' => 'votre_mot_de_passe',
        'charset' => 'utf8mb4'
    ],
    'app' => [
        'name' => 'Gestion des EPN 2025',
        'url' => 'https://votre-domaine.com',
        'environment' => 'production',
        'debug' => false
    ],
    'timezone' => 'America/Martinique'
];
```

## Vérification

- En local, ouvrez `http://localhost/epn-web/`
- En production, ouvrez votre URL publique et vérifiez que la connexion à la base fonctionne

## Sécurité

- `src/config/config.prod.php` contient des informations sensibles
- Ce fichier ne doit jamais être versionné
- Le mode debug doit rester désactivé en production
- Utilisez uniquement des placeholders dans la documentation

## Dépannage rapide

### Fichier introuvable

- Vérifiez que `src/config/config.prod.php` existe bien sur le serveur
- Vérifiez les permissions du fichier

### Erreur de connexion DB

- Vérifiez les identifiants de base de données
- Vérifiez les droits de l’utilisateur MySQL
- Vérifiez l’accessibilité du serveur MySQL

### Debug actif en production

- Assurez-vous que `debug` vaut `false` dans `src/config/config.prod.php`
- Vérifiez que les erreurs PHP ne sont pas affichées publiquement

## Support

Pour toute question, consultez les logs du serveur ou contactez l’administrateur système.
