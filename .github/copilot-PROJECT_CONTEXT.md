# Contexte du projet EPN Web

Ce projet vise à digitaliser la gestion des Espaces Publics Numériques (EPN) pour les sites BAC et MAC.

## Objectifs

- Suivi des fréquentations, ateliers, inscriptions et postes informatiques
- Interface adaptée aux agents et usagers
- Migration depuis Access/Visual Basic vers PHP/MySQL

## Utilisateurs cibles

- Agents d’accueil et référents
- Usagers des EPN
- Administrateurs techniques

## Contraintes

- Respect des horaires dynamiques
- Validation des données côté client et serveur
- Sécurité des sessions et des données personnelles
- Adaptation des couleurs et logos selon le site

## Points techniques

- Architecture PHP/MySQL avec point d'entrée dans `public/`, pages dans `src/pages/`, API dans `src/api/` et config dans `src/config/`
- Assets centralisés dans `public/assets/`
- Rafraîchissement automatique du tableau de bord
- Modals pour toutes les sélections
- Système de configuration hybride local/production via `src/config/config.php`
- Mémoire projet documentée dans `.memory/`
- Sécurité renforcée par `.gitignore` et `.htaccess` pour les fichiers sensibles
- Déploiement principal sur Hostinger, compatible XAMPP/local

## Note

Complétez ce contexte lors de toute évolution majeure du projet.
