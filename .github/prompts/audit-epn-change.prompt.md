---
description: "Audit a page, endpoint, or release candidate for PDO safety, validation, BAC/MAC logic, config exposure, and deployment risks."
name: "Auditer un changement EPN"
argument-hint: "Zone, fichier ou fonctionnalité à auditer"
agent: "agent"
---

Analyse la zone demandée dans ce dépôt EPN Web et fais un audit ciblé sur `src/pages/`, `src/api/`, `src/config/` ou `public/assets/` selon le cas.

Vérifie en priorité :

- la validation côté serveur ;
- l'usage de requêtes PDO préparées ;
- la cohérence BAC/MAC et session ;
- l'exposition éventuelle de secrets ou de modes debug ;
- les risques de régression UI ou AJAX.

Donne une réponse courte avec :

1. points conformes ;
2. risques trouvés ;
3. correctifs recommandés ;
4. étapes de vérification.
