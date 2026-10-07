# Copilot Workspace Instructions – EPN Web

## Rôle

Ce fichier complète `.github/copilot-instructions.md`. Il apporte le contexte de travail utile sans dupliquer le routeur principal.

## Structure du projet

- Point d'entrée web dans `public/`.
- Pages métier PHP dans `src/pages/`.
- Endpoints AJAX et CRUD dans `src/api/`.
- CSS, JS et logos dans `public/assets/`.
- Schéma DB dans `database/schema.sql`.
- Configuration via `src/config/config.php`, `src/config/config.local.php` et `src/config/config.prod.php` quand présent.

## Règles de travail

- Faire des changements petits, lisibles et cohérents avec les fichiers voisins.
- Utiliser PDO préparé et validation serveur sur toute entrée utilisateur.
- Respecter la logique BAC/MAC portée par la session.
- Préserver les modals, le rafraîchissement du tableau de bord et les patterns UI existants.
- Ne jamais exposer de secrets ni laisser le debug actif en production.
- Ne pas modifier le schéma de base sans validation humaine.

## Routage pratique

- Pages PHP : `.github/instructions/php-pages.instructions.md`
- API et AJAX : `.github/instructions/api-endpoints.instructions.md`
- UI, CSS, JS : `.github/instructions/ui-assets.instructions.md`
- Config et sécurité : `.github/instructions/security-config.instructions.md`
- Prompts métier : `.github/prompts/` et `.github/PROMPTS.md`
- Agent dédié : `.github/agents/epn-maintainer.agent.md`
- Skills SaaS : `.github/skills/` et `.github/SKILLS.md`
- Mémoire projet : `.memory/`

## Documentation à privilégier

- Vue d'ensemble : `README.md`
- Contexte projet : `.github/copilot-PROJECT_CONTEXT.md`
- Règles IA : `.github/copilot-REGLES_IA.md`
- Journal : `.github/copilot-JOURNAL_DE_BORD.md`
- Procédures : `docs/CONFIGURATION.md`, `docs/DEPLOIEMENT.md`, `docs/TROUBLESHOOTING.md`

## Rappel

Chercher d'abord la source la plus proche du sujet, puis intervenir avec le plus petit changement sûr possible.

<!-- hacklm-memory:start -->

## Memory-Augmented Context

Read memory files on-demand — not all at once.

| File                    | When to read                        |
| ----------------------- | ----------------------------------- |
| .memory/instructions.md | How to behave                       |
| .memory/quirks.md       | When something breaks unexpectedly  |
| .memory/preferences.md  | Style/design/naming choices         |
| .memory/decisions.md    | Architectural changes               |
| .memory/security.md     | **ALWAYS — before any code change** |

### Memory Tools

Call `queryMemory` before answering anything about architecture, conventions, or style.

Call `storeMemory` (with a kebab-case `slug`) when:

1. User states a preference or rule → store as Instruction or Preference **before** acting
2. User corrects you → store the correction
3. A command or build fails → store root cause and fix
4. After completing any implementation task → store each architectural decision, convention, or pattern applied that is not already in memory. Do this **before ending the turn**.

Same slug = update, not duplicate.

### Writing Style for Memory Entries

Hemingway style. Short sentences. No jargon. No filler. Be blunt.
Bad: "The system employs an asynchronous locking mechanism to serialise concurrent write operations."
Good: "Use a lock before writing. One write at a time."

### Categories

| Category    | Use for                         |
| ----------- | ------------------------------- |
| Instruction | How to behave                   |
| Quirk       | Project-specific weirdness      |
| Preference  | Style/design/naming             |
| Decision    | Architectural commitments       |
| Security    | Rules that must NEVER be broken |

<!-- hacklm-memory:end -->