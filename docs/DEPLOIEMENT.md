# Guide de Déploiement en Production

## Fichiers à déposer sur le serveur

### Obligatoires

1. **Le dossier `public/`**
   - `public/index.php`
   - `public/assets/css/`
   - `public/assets/js/`
   - `public/assets/images/`

2. **Le dossier `src/`**
   - `src/pages/`
   - `src/api/`
   - `src/config/`
   - `src/includes/`

3. **La racine utile**
   - `.htaccess`
   - `database/schema.sql` pour l'import initial
   - `test-config.php` uniquement pour un contrôle temporaire

### À ne pas laisser en production

- `test-config.php` après vérification
- les fichiers de documentation
- les exemples de configuration non utilisés

## Base de données

### Étape 1 : créer la base

1. Connectez-vous au panneau Hostinger
2. Ouvrez la gestion MySQL
3. Créez la base si nécessaire
4. Notez les identifiants réels côté hébergeur

### Étape 2 : importer le schéma

- Importez `database/schema.sql` via phpMyAdmin
- Ou utilisez la ligne de commande suivante si SSH est disponible

```bash
mysql -u votre_utilisateur -p votre_base < database/schema.sql
```

### Étape 3 : vérifier les tables

Vérifiez la présence des tables attendues comme `inscription`, `frequentation1`, `frequentation2`, `ateliers1`, `ateliers2`, `postes` et `agents`.

## Configuration finale

Le fichier `src/config/config.prod.php` doit contenir vos valeurs de production.

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

## Vérification post-déploiement

- Vérifiez que `public/` et `src/` sont bien présents sur le serveur
- Vérifiez que `.htaccess` est actif
- Testez l’accès à l’application
- Utilisez `test-config.php` seulement si nécessaire, puis supprimez-le

## Checklist

- [ ] Base de données créée
- [ ] Schéma SQL importé
- [ ] `src/config/config.prod.php` renseigné
- [ ] `public/` déployé
- [ ] `src/` déployé
- [ ] `.htaccess` présent
- [ ] Test de connexion validé
- [ ] Fichier de test supprimé

## Sécurité

- `debug` doit rester à `false` en production
- `src/config/config.prod.php` doit rester hors versionnement
- les permissions sensibles doivent être restreintes
- utilisez HTTPS

## Support

En cas de problème, consultez les logs PHP et les journaux fournis par l’hébergeur.
