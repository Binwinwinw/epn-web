# Règles pour les agents IA – EPN Web

## Règles générales

- Ne jamais modifier la structure de la base de données sans validation humaine
- Respecter la convention de nommage des logos et assets
- Ne pas exposer de données sensibles dans les logs ou commits
- Toujours valider les formulaires côté client et côté serveur
- Utiliser les fichiers de configuration adaptés à l’environnement
- Documenter toute intervention dans le journal de bord
- Consigner chaque session de travail, les réussites, les échecs, les blocages et les décisions dans le journal de bord
- Ne jamais versionner ni exposer `src/config/config.prod.php` ou tout fichier listé dans `.gitignore`
- Respecter les permissions recommandées : `644` pour les fichiers PHP, `755` pour les dossiers, `600` pour `src/config/config.prod.php`
- Toujours supprimer les fichiers de test comme `test-config.php` après vérification
- Ne jamais activer le mode debug en production
- Vérifier la présence et la protection des fichiers `.htaccess`

## Règles spécifiques

- Les couleurs et styles doivent rester cohérents avec le design system
- Les modals sont obligatoires pour toute sélection complexe
- Les workflows critiques doivent suivre les instructions du README et des guides dans `docs/`
- Ne pas intervenir sur les fichiers gérés par des workflows externes sans demande explicite

## Note

Complétez ou adaptez ces règles selon l’évolution du projet.
