---
description: "Create or extend an EPN module with a PHP page in src/pages, a matching endpoint in src/api, and minimal public/assets changes while respecting project conventions."
name: "Créer un module EPN"
argument-hint: "Nom du module et besoin métier"
agent: "agent"
---

Crée ou complète un module EPN pour la demande fournie par l'utilisateur.

Contraintes à respecter :

- suivre l'architecture réelle du dépôt : page PHP dans `src/pages/` + endpoint dans `src/api/` si nécessaire ;
- laisser les assets front dans `public/assets/` ;
- rester en PHP, HTML, CSS et JavaScript simple ;
- utiliser des requêtes PDO préparées ;
- garder la logique BAC/MAC existante ;
- ajouter la validation serveur ;
- limiter les changements au strict nécessaire.

Réponse attendue :

1. résumé du besoin ;
2. fichiers à modifier ;
3. implémentation ;
4. vérifications manuelles à faire.
