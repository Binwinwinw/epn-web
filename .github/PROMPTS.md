# Formulaire de création de prompt personnalisé – EPN Web

Remplissez ce formulaire pour générer un prompt adapté à vos besoins (Copilot ou autre agent IA).

---

## 1. Contexte du prompt

- **But du prompt** :
  > (Décrivez l’objectif principal de votre demande)
- **Module ou fichier concerné** :
  > (Ex : src/pages/frequentations.php, src/api/, public/assets/css/, etc.)
- **Contrainte(s) spécifique(s)** :
  > (Ex : respecter le design system, validation serveur, sécurité, etc.)

## 2. Détail de la demande

- **Action attendue** :
  > (Ex : ajouter un champ, corriger un bug, générer un script, etc.)
- **Entrées ou exemples** :
  > (Ex : structure de données, exemple d’entrée utilisateur, etc.)
- **Sortie attendue** :
  > (Ex : code PHP, JSON, documentation, etc.)

## 3. Précisions optionnelles

- **Niveau de détail souhaité** :
  > (Ex : code complet, simple patch, explications, etc.)
- **Format de la réponse** :
  > (Ex : code, tableau, markdown, etc.)
- **Autres contraintes** :
  > (Ex : ne pas toucher à la base, ne pas modifier le CSS, etc.)

---

**Exemple d’utilisation** :

- Remplissez chaque section puis copiez-collez le formulaire complété dans Copilot ou un agent IA.
- L’IA générera un prompt précis et adapté à votre besoin.

---

_Ce modèle peut être adapté selon l’évolution du projet ou des besoins IA._

---

## Exemples de prompts pour Copilot/IA (EPN Web)

Voici 50 exemples concrets, détaillés et structurés, à utiliser ou adapter selon vos besoins :

---

### 1. Ajout d’un type d’utilisation

Ajoute un nouveau type d’utilisation “France Services” dans le formulaire de fréquentation :

- Ajoute le logo dans `public/assets/images/logos/`
- Mets à jour la liste dans le JS et le formulaire
- Adapte l’API si besoin

### 2. Correction d’un bug AJAX

Corrige le bug de rafraîchissement auto du tableau de bord (`src/pages/tableau-bord.php`) :

- Le JS ne met pas à jour les statuts des postes toutes les 5 secondes.

### 3. Sécurisation d’un formulaire

Vérifie que le formulaire d’inscription (`src/pages/inscriptions.php`) valide bien toutes les entrées côté serveur et côté client, selon les règles du projet.

### 4. Création d’un module

Crée un module “Gestion du matériel” :

- Page PHP dans `src/pages/`
- Endpoint API associé dans `src/api/`
- JS pour l’interface dans `public/assets/js/`

### 5. Respect du design system

Refactore le CSS de `src/pages/frequentations.php` pour garantir la cohérence avec `public/assets/css/design-system.css` (couleurs, espacements, boutons).

### 6. Migration Access → MySQL

Génère un script d’import CSV pour migrer les données Access vers la table “inscription” MySQL, en respectant le schéma existant.

### 7. Ajout d’un champ “email secondaire”

Ajoute un champ “email secondaire” dans le formulaire d’inscription et l’API :

- Modifie le formulaire HTML
- Mets à jour la base et l’API
- Ajoute la validation correspondante

### 8. Génération d’un test unitaire PHP

Écris un test unitaire PHP pour la fonction de création d’atelier (API `atelier.php`).

### 9. Documentation d’un workflow

Documente le workflow de migration Access → MySQL dans un fichier markdown détaillé (étapes, outils, pièges).

### 10. Ajout d’un captcha

Ajoute une vérification anti-robot (captcha) sur le formulaire d’inscription :

- Intègre Google reCAPTCHA ou équivalent
- Mets à jour la validation côté serveur

### 11. Exemple de requête AJAX

Génère un exemple de requête AJAX pour récupérer la liste des agents actifs via l’API.

### 12. Export PDF des usagers

Ajoute un bouton “Exporter en PDF” la liste des usagers inscrits :

- Génère le PDF côté serveur ou client
- Ajoute le bouton sur la page concernée

### 13. Correction responsive tableau de bord

Corrige l’affichage mobile du tableau de bord (CSS responsive, media queries).

### 14. Ajout d’une colonne “statut” aux ateliers

Ajoute une colonne “statut” dans la table des ateliers (front + back) :

- Modifie la base, l’API et l’interface

### 15. Anonymisation RGPD

Génère un script pour anonymiser les données usagers en base (conformité RGPD).

### 16. Documentation API fréquentation

Ajoute une documentation API pour l’endpoint `/api/frequentation.php` (méthodes, paramètres, exemples).

### 17. Correction faille XSS

Génère un patch pour corriger une faille XSS sur les champs commentaires (sanitisation côté serveur et client).

### 18. Filtre de recherche par date

Ajoute un filtre de recherche par date sur la page des fréquentations (front + back).

### 19. Test d’intégration inscription

Écris un test d’intégration pour la création d’une inscription (API, base, validation).

### 20. Pagination liste usagers

Ajoute un système de pagination sur la liste des usagers (front + back, API).

### 21. Génération d’un README module matériel

Génère un README complet pour le module “Gestion du matériel” (installation, usage, API, exemples).

### 22. Ajout d’un champ “niveau d’urgence” atelier

Ajoute un champ “niveau d’urgence” dans les ateliers (formulaire, API, base).

### 23. Script de sauvegarde MySQL

Génère un script pour sauvegarder automatiquement la base MySQL (cron, export SQL).

### 24. Vérification unicité email

Ajoute une vérification d’unicité sur le champ email à l’inscription (front + back).

### 25. Prompt refonte graphique

Propose un prompt pour demander à l’IA une refonte graphique de l’interface d’accueil.

### 26. Bouton “Imprimer” ateliers

Ajoute un bouton “Imprimer” sur la page des ateliers (JS, CSS print).

### 27. Accessibilité (contraste, ARIA)

Génère un patch pour améliorer l’accessibilité (contraste couleurs, attributs ARIA, navigation clavier).

### 28. Export CSV fréquentations

Ajoute un export CSV des fréquentations par période (front + back, API).

### 29. Vérification permissions fichiers

Génère un script pour vérifier les permissions des fichiers sensibles (config, .htaccess, etc.).

### 30. Champ “notes internes” agents

Ajoute un champ “notes internes” visible seulement par les agents (formulaire, API, base, droits).

### 31. Documentation Swagger API usagers

Génère un exemple de documentation Swagger pour l’API usagers (endpoints, schémas, exemples).

### 32. Alerte JS poste occupé

Ajoute une alerte JS si un poste est déjà occupé lors de la sélection dans le formulaire de fréquentation.

### 33. Centralisation messages d’erreur

Génère un patch pour centraliser tous les messages d’erreur dans un fichier dédié (PHP ou JS).

### 34. Réinitialisation mot de passe usager

Ajoute un bouton “Réinitialiser mot de passe” pour les usagers (front, back, sécurité).

### 35. Purge inscriptions inactives

Génère un script pour purger automatiquement les inscriptions inactives depuis 2 ans.

### 36. Photo de profil à l’inscription

Ajoute un champ “photo de profil” à l’inscription (upload sécurisé, stockage, affichage).

### 37. Test automatisé sélection agent

Écris un test automatisé pour la sélection d’agent (modal, API, JS).

### 38. Indicateur visuel site BAC/MAC

Ajoute un indicateur visuel du site (BAC/MAC) sur toutes les pages (bannière, couleur, icône).

### 39. Activation mode maintenance

Génère un patch pour activer le mode maintenance via `.htaccess` (redirection, page dédiée).

### 40. Champ “type de matériel”

Ajoute un champ “type de matériel” dans la gestion du matériel (formulaire, API, base).

### 41. Script de restauration base

Génère un script pour restaurer la base à partir d’une sauvegarde SQL.

### 42. Vérification session expirée

Ajoute une vérification de session expirée sur toutes les pages (PHP, JS, UX).

### 43. Prompt analyse sécurité

Propose un prompt pour demander à l’IA une analyse de sécurité complète du projet.

### 44. Bouton “Voir historique” usager

Ajoute un bouton “Voir historique” sur la fiche usager (affichage des actions, API, modal).

### 45. Gestion des erreurs AJAX

Génère un patch pour améliorer la gestion des erreurs AJAX (retours, affichage, logs).

### 46. Champ “structure partenaire”

Ajoute un champ “structure partenaire” à l’inscription (formulaire, API, base).

### 47. Rapport d’activité par email

Génère un script pour envoyer un rapport d’activité par email (PHP, cron, template).

### 48. Téléchargement des logos en zip

Ajoute un bouton “Télécharger tous les logos” en zip (PHP, JS, archive dynamique).

### 49. Documentation utilisateur module ateliers

Génère une documentation utilisateur pour le module ateliers (fonctionnalités, navigation, FAQ).

### 50. Système de logs actions critiques

Ajoute un système de logs pour toutes les actions critiques (PHP, stockage sécurisé, consultation).
