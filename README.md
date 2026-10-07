# Application Web - Gestion des EPN

Migration de l'application Windows Forms Visual Basic vers une application web PHP et MySQL.

## Installation avec XAMPP

### 1. Copier les fichiers

Copiez le dossier du projet dans le répertoire `htdocs` de XAMPP :

```text
C:\xampp\htdocs\epn-web\
```

### 2. Créer la base de données

1. Ouvrez [phpMyAdmin](http://localhost/phpmyadmin)
2. Importez le fichier `database/schema.sql`
3. Ou exécutez manuellement le script SQL

### 3. Configuration

Ajustez la configuration locale dans `src/config/config.local.php` si nécessaire :

- `DB_HOST` : généralement `localhost`
- `DB_NAME` : `epn_gestion`
- `DB_USER` : généralement `root`
- `DB_PASS` : laissez vide par défaut avec XAMPP

### 4. Accéder à l'application

Ouvrez votre navigateur et allez à :

```text
http://localhost/epn-web/
```

## Structure des fichiers

```text
epn-web/
├── public/                     # Point d'entrée web, routeur public et assets
│   ├── index.php               # Routeur principal via ?page=
│   └── assets/
├── src/
│   ├── pages/                  # Pages métier PHP servies via le routeur
│   ├── api/                    # Endpoints AJAX et CRUD JSON
│   ├── config/                 # Configuration applicative
│   └── includes/               # Includes et helpers communs
├── database/
│   └── schema.sql              # Schéma de base de données + couche v2 compatible
├── docs/                       # Documentation projet
├── .github/                    # Instructions, prompts, agents et skills
└── .memory/                    # Mémoire documentaire du dépôt
```

## Architecture de navigation

- `index.php` redirige vers `public/index.php`
- `public/index.php` centralise la navigation via un routeur whitelisté
- les pages de `src/pages/` passent par ce routeur pour un accès plus cohérent et plus sûr
- la page publique d'entrée est `src/pages/landingpage.php`
- la page d'accueil de l'application est `src/pages/index.php`

## Migration des données depuis Access

1. Exportez chaque table Access en CSV
2. Importez les CSV dans MySQL via phpMyAdmin
3. Vérifiez la cohérence des colonnes avec `database/schema.sql`

## Fonctionnalités implémentées

### Modules principaux

- Page d'accueil avec sélection du site BAC ou MAC
- Accueil site avec horaires dynamiques
- Tableau de bord temps réel des postes
- Gestion des inscriptions
- Gestion des fréquentations
- Gestion des ateliers

### API REST

- `src/api/session.php` : gestion des sessions
- `src/api/postes.php` : gestion des postes
- `src/api/inscription.php` : CRUD inscriptions
- `src/api/frequentation.php` : CRUD fréquentations
- `src/api/atelier.php` : CRUD ateliers
- `src/api/agents.php` : liste des agents
- `src/api/usagers.php` : recherche d'usagers

### Interface utilisateur

- Design moderne et responsive
- Couleurs BAC et MAC différenciées
- Modals pour les sélections complexes
- Formulaires avec validation
- Rafraîchissement automatique du tableau de bord

## Build Tailwind local

Une vraie migration Tailwind est maintenant préparée localement pour les pages premium sans dépendre du CDN.

### Commandes utiles

```text
npm install
npm run tailwind:build
npm run tailwind:watch
```

Le fichier source est `public/assets/css/tailwind.input.css` et le CSS généré sort dans `public/assets/css/tailwind.generated.css`.

## Notes

- L'application utilise les sessions PHP pour gérer l'état
- Les horaires sont calculés selon le jour de la semaine
- Les assets front sont centralisés dans `public/assets/`
- Le schéma SQL intègre désormais une couche V2 compatible pour préparer les requêtes, statistiques graphiques et exports CSV sans casser l'existant
- Des vues unifiées sont prévues pour faciliter les futurs écrans de reporting (`vue_inscriptions`, `vue_frequentations`, `vue_ateliers`)
