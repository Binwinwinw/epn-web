# Journal de bord IA – EPN Web

Ce journal consigne les interventions, décisions et problèmes rencontrés par les agents IA sur le projet EPN Web.

## Règle de tenue du journal

- Chaque session de travail IA significative doit être consignée ici.
- Chaque entrée doit préciser au minimum la date, l'objectif, les changements, les réussites, les échecs ou blocages, et la suite à donner.
- Aucune session importante ne doit se terminer sans mise à jour du journal.

---

## Historique des actions

- **14/04/2026** : Refonte du socle de personnalisation IA du dépôt. Réussites : routeur principal compacté, guide complémentaire rationalisé, prompts, skills et agent réalignés avec la structure `public/`, `src/` et `.memory/`. Échec ou point écarté : le skill de facturation a été retiré car le service reste gratuit à ce stade.
- **14/04/2026** : Vérification finale de cohérence des chemins et de la documentation. Réussite : aucune erreur détectée après contrôle des fichiers de contexte, de documentation et de mémoire.
- **14/04/2026** : Refonte de l'accueil public et de l'accueil application. Réussites : landing page publique ajoutée, page d'accueil métier modernisée, navigation unifiée via le routeur `public/index.php?page=...`, et protection d'accès plus cohérente sur les pages de `src/pages/`.
- **14/04/2026** : Stabilisation base de données et API. Réussites : correction des chemins de configuration des endpoints JSON, réparation locale XAMPP après corruption InnoDB/tablespaces orphelins, restauration des tables principales, et ajout d'une couche SQL V2 compatible pour préparer les futures statistiques et exports CSV.
- **05/03/2026** : Création du fichier `.github/workspace-instructions.md` (instructions Copilot centralisées pour EPN Web, synthèse architecture, conventions, workflows, anti-patterns). Voir ce fichier pour toute intervention IA future.
- **28/02/2026** : Initialisation des fichiers d’aide IA, création des instructions Copilot, structuration des conventions et workflows critiques.
- _(Complétez chaque intervention IA avec la date, le résumé de l’action, les difficultés et solutions)_
- _(Consigner toute modification de configuration, déploiement ou sécurité : changement d’URL, adaptation des permissions, ajout d’une variable d’environnement, etc.)_

## Problèmes et solutions

- _(À compléter lors de chaque résolution de bug ou adaptation majeure)_
- **05/03/2026** : Migration Tailwindcss -> lancer la migration Tailwindcss sur les pages principales et secondaires de l'application
