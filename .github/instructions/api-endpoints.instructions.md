---
description: "Use when editing PHP API endpoints, AJAX handlers, CRUD logic, JSON responses, or site-aware BAC/MAC data access in src/api/."
name: "API PHP EPN"
applyTo: "src/api/**/*.php"
---

# API PHP EPN

- Valider toutes les entrées côté serveur, même si le front valide déjà.
- Utiliser uniquement PDO préparé pour les requêtes SQL.
- Préserver les conventions de réponse JSON déjà utilisées par les endpoints voisins.
- Respecter la logique de site et les variations BAC/MAC existantes.
- Ne pas modifier le schéma MySQL sans validation humaine explicite.
- En cas de nouveau endpoint, suivre le pattern du dossier `src/api/` au lieu d'ajouter une couche d'architecture nouvelle.
