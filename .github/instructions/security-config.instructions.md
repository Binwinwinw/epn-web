---
description: "Use when editing configuration, deployment docs, env files, test utilities, or any file that could expose secrets or production settings."
name: "Config et sécurité EPN"
applyTo:
  - "src/config/**/*.php"
  - "docs/**/*.md"
  - "README.md"
  - ".env*"
  - "test-config.php"
---

# Config et sécurité EPN

- Ne jamais versionner de vrais identifiants, mots de passe ou secrets dans la documentation ou les exemples.
- Utiliser des valeurs factices ou des placeholders dans tout exemple de configuration.
- Garder le mode debug désactivé en production.
- Tout script de test ou diagnostic doit rester temporaire et être retiré après usage.
- Respecter la séparation locale / production déjà en place via `src/config/config.php`, `src/config/config.local.php` et `src/config/config.prod.php` quand il existe.
- Pour les procédures, pointer vers la doc existante au lieu de la recopier.
