# Copilot Instructions for EPN Web

## Rôle d'orchestrateur

Router léger vers les sources les plus pertinentes. Ne pas dupliquer ; consulter workspace-instructions.md pour contexte, règles et documentation détaillée.

## Priorité (en cas de conflit)

Sécurité > patterns d'implémentation > limite de lignes. Exemple : si sécurité et patterns entrent en conflit, appliquer sécurité en priorité mais documenter la déviation.

## Sources principales

**Contexte et règles** : workspace-instructions.md, copilot-PROJECT_CONTEXT.md, copilot-REGLES_IA.md, copilot-JOURNAL_DE_BORD.md

**Instructions par domaine** : .github/instructions/php-pages.instructions.md, .github/instructions/api-endpoints.instructions.md, .github/instructions/ui-assets.instructions.md, .github/instructions/security-config.instructions.md

**Mémoire projet** : .memory/ (instructions, quirks, preferences, decisions, security)

**Prompts et skills** : .github/PROMPTS.md, .github/SKILLS.md, .github/agents/epn-maintainer.agent.md

## Règles critiques

- Sécurité : ne jamais exposer de secrets, ne jamais laisser un debug actif en production.
- Schéma DB : ne jamais appliquer de modification sans accord humain; documenter la décision en mémoire n'exige pas d'accord préalable.
- Utiliser PDO préparé, validation serveur, logique BAC/MAC par session.

## Workflow

1. Consulter la source la plus proche du sujet (instruction, prompt, memory file).
2. Appliquer la plus petite modification sûre possible.
3. Documenter les décisions dans .memory/ via storeMemory().
4. Consigner l'intervention dans le journal de bord si significative.

---

Aim for 40 to 60 lines. Critical routing rules (access control, authentication flow, data protection) take priority; adjust other content accordingly and move less critical details to referenced files.



<!-- hacklm-memory:start -->
## Memory-Augmented Context

Read memory files on-demand — not all at once.

| File | When to read |
|------|-------------|
| [.memory/instructions.md](.memory/instructions.md) | How to behave |
| [.memory/quirks.md](.memory/quirks.md) | When something breaks unexpectedly |
| [.memory/preferences.md](.memory/preferences.md) | Style/design/naming choices |
| [.memory/decisions.md](.memory/decisions.md) | Architectural changes |
| [.memory/security.md](.memory/security.md) | **ALWAYS — before any code change** |

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
| Category | Use for |
|----------|---------|
| Instruction | How to behave |
| Quirk | Project-specific weirdness |
| Preference | Style/design/naming |
| Decision | Architectural commitments |
| Security | Rules that must NEVER be broken |
<!-- hacklm-memory:end -->
