---
description: "Use when editing PHP pages in src/pages, public entry files, dashboards, forms, modal workflows, or BAC/MAC user interfaces in EPN Web."
name: "Pages PHP EPN"
applyTo:
  - "src/pages/**/*.php"
  - "public/**/*.php"
  - "index.php"
---

# Pages PHP EPN

- Garder les pages métier dans `src/pages/` et le point d'entrée web dans `public/`.
- Laisser les écritures de données et le CRUD dans le endpoint correspondant de `src/api/`.
- Préserver la logique BAC/MAC portée par la session utilisateur.
- Réutiliser les modals, tableaux et patterns déjà présents avant d'inventer une nouvelle UI.
- Préférer de petites améliorations en PHP, HTML et JavaScript simple.
- Pour le contexte projet, voir [../copilot-PROJECT_CONTEXT.md](../copilot-PROJECT_CONTEXT.md) et [../copilot-REGLES_IA.md](../copilot-REGLES_IA.md).
