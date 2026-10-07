# Skills Hub

Ce fichier est le HUB officiel des skills du projet EPN Web.

## Objectif

- Donner un point d'entree unique pour tous les skills.
- Eviter les doublons de declenchement.
- Definir quel skill utiliser en priorite selon la demande.
- Distinguer skills locaux EPN, skills CKM partagés, et skills SaaS generiques.

## Catalogue

### Skills EPN Project-Specific

| Skill                       | Role principal                                            | Portee | Lien                                                                                                                    |
| --------------------------- | --------------------------------------------------------- | ------ | ----------------------------------------------------------------------------------------------------------------------- |
| ai-memory                   | Memoire pedagogique eleve et contexte personnalise        | Projet | [.github/skills/ai-memory/SKILL.md](./ai-memory/SKILL.md)                                                               |
| debug-skill                 | Debug systematique et root cause analysis                 | Projet | [.github/skills/debug-skill/SKILL.md](./debug-skill/SKILL.md)                                                           |
| docs-optimisation           | Hygiene documentaire, docs/, README a la racine           | Projet | [.github/skills/docs-optimisation/SKILL.md](./docs-optimisation/SKILL.md)                                               |
| frontend-design             | Direction artistique frontend et principes premium        | Projet | [.github/skills/frontend-design/SKILL.md](./frontend-design/SKILL.md)                                                   |
| socratic-tutoring           | Tutorat socratique sans reponse directe                   | Projet | [.github/skills/socratic-tutoring/SKILL.md](./socratic-tutoring/SKILL.md)                                               |
| ui-ux-frontend-premium      | Orchestrateur UI/UX pour router vers le bon sous-skill    | Projet | [.github/skills/ui-ux-frontend-premium/SKILL.md](./ui-ux-frontend-premium/SKILL.md)                                     |
| ui-ux-design-frontend-skill | Standards UI/UX de production pour composants et layouts  | Projet | [.github/skills/ui-ux-design-frontend-skill/SKILL.md](./ui-ux-design-frontend-skill/SKILL.md)                           |
| ui-ux-pro-max               | Moteur de recommandations design data-driven avec scripts | Projet | [.github/skills/ui-ux-pro-max/SKILL.md](./ui-ux-pro-max/SKILL.md)                                                       |
| fix-customization-eval-diag | Fixes diagnostics Chat Customizations evaluations         | Projet | [.github/skills/fix-customization-evaluation-diagnostics/SKILL.md](./fix-customization-evaluation-diagnostics/SKILL.md) |

### Skills CKM Shared (Copilot Knowledge Model)

| Skill             | Role principal                                                           | Scope  | Lien                                                              | Note                                                                                           |
| ----------------- | ------------------------------------------------------------------------ | ------ | ----------------------------------------------------------------- | ---------------------------------------------------------------------------------------------- |
| ckm:design        | **Large scope:** Logo, CIP, banners, icons, social photos, design system | Global | [.github/skills/design/SKILL.md](./design/SKILL.md)               | Utiliser pour travaux design generiques; deleguer aux skills specialisees pour cas specifiques |
| ckm:brand         | Brand voice, visual identity, messaging, assets                          | Global | [.github/skills/brand/SKILL.md](./brand/SKILL.md)                 | Branding subset de ckm:design                                                                  |
| ckm:design-system | Token architecture, component specs (+ slide generation)                 | Global | [.github/skills/design-system/SKILL.md](./design-system/SKILL.md) | **Specialise** pour architecture tokens et components; inclut aussi slide generation           |
| ckm:banner-design | **Specialise:** Banners pour social, ads, hero, print                    | Global | [.github/skills/banner-design/SKILL.md](./banner-design/SKILL.md) | **Specialise** banner design avec 20+ art directions; subset de ckm:design                     |
| ckm:slides        | **Specialise:** HTML presentations avec Chart.js                         | Global | [.github/skills/slides/SKILL.md](./slides/SKILL.md)               | **Specialise** pour presentations; subset overlap avec ckm:design-system et ckm:design         |
| ckm:ui-styling    | shadcn/ui, Tailwind, accessible UI, dark mode                            | Global | [.github/skills/ui-styling/SKILL.md](./ui-styling/SKILL.md)       | UI implementation (orthogonal aux 5 autres CKM)                                                |

### Skills SaaS Generic (Multi-project)

| Skill                       | Role principal                                            | Portee  | Lien                                                                                          |
| --------------------------- | --------------------------------------------------------- | ------- | --------------------------------------------------------------------------------------------- |
| saas-observability-incident | Debug SaaS incident, production bug, latency spike        | Partagé | [.github/skills/saas-observability-incident/SKILL.md](./saas-observability-incident/SKILL.md) |
| saas-release-readiness      | Review release safety, config drift, rollback, smoke test | Partagé | [.github/skills/saas-release-readiness/SKILL.md](./saas-release-readiness/SKILL.md)           |
| saas-security-review        | Audit auth, access control, CSRF, XSS, SQL injection      | Partagé | [.github/skills/saas-security-review/SKILL.md](./saas-security-review/SKILL.md)               |

## Routage recommande

Utiliser cet ordre pour choisir le bon skill quand plusieurs semblent proches.

1. **Bug / incident** → debug-skill (local) ou saas-observability-incident (si SaaS)
2. **Sécurité** → saas-security-review (audit) ou saas-tenancy-rbac (si multitenant)
3. **Deploy / release** → saas-release-readiness
4. **Tutorat / eleve** → socratic-tutoring puis ai-memory
5. **UI/UX large ou ambiguë** → ui-ux-frontend-premium
6. **Architecture visuelle high-level** → frontend-design
7. **Implementation UI concrete** → ui-ux-design-frontend-skill ou ckm:ui-styling
8. **Exploration design system avec scripts** → ui-ux-pro-max
9. **Brand / messaging / tone** → ckm:brand
10. **Logo / banners / icons / corporate design** → ckm:design
11. **Presentations / slides** → ckm:slides
12. **Design tokens / component specs** → ckm:design-system
13. **Organisation docs** → docs-optimisation
14. **Fix customization diagnostics** → fix-customization-eval-diag

### Note: Pas de doublon CKM, hiérarchie intentionnelle

Certains skills CKM semblent se chevaucher, mais c'est une **spécialisation graduée**, pas un doublon:

- `ckm:design` = large scope (logos, banners, icons, presentations, design system)
- `ckm:banner-design` = specialise banners uniquement (20+ art directions)
- `ckm:design-system` = specialise tokens & components (subset overlap avec design)
- `ckm:slides` = specialise presentations HTML (subset overlap avec design-system)
- `ckm:brand` = subset: brand voice & messaging uniquement

**Règle**: Travail large ou ambigu → `ckm:design`. Cas spécifique → skill specialise.

Aussi: `debug-skill` vs `saas-observability-incident` = deux contextes, pas un doublon (local vs SaaS).

## Conventions de normalisation

Chaque skill doit respecter:

1. Un fichier SKILL.md dans son dossier dedie: `.github/skills/[skill-name]/SKILL.md`
2. Frontmatter YAML avec name et description (premières 5 lignes)
3. **Prefixe dans name** selon categorie:
   - CKM Shared: `ckm:` (ex: `ckm:brand`, `ckm:design`, `ckm:ui-styling`)
   - SaaS Generic: `saas-` (ex: `saas-security-review`, `saas-tenancy-rbac`)
   - Project-Specific: aucun prefixe (ex: `socratic-tutoring`, `ui-ux-frontend-premium`)
4. Nom du dossier doit matcher le nom du skill (sans prefixe dans le dossier)
   - Exception CKM: dossier `brand/` mais skill name = `ckm:brand`
5. Description commencant par "Use when:" ou equivalent
6. Triggers explicites dans description (French ou English selon public)

## Maintenance du Hub

Quand un skill est ajoute, renomme, ou supprime:

1. Mettre a jour le tableau Catalogue
2. Mettre a jour Routage recommande
3. Verifier les conflits de declenchement avec les skills existants
4. Revalider name/description/frontmatter du nouveau skill

## Note de gouvernance

Ce HUB est la source de verite pour la gouvernance des skills de ce repository.

## Strategie hybride global/local/partagé

Objectif: ne pas dupliquer inutilement les skills, equilibrer partage et specialisation.

### Repartition decidee

| Skill                       | Portee  | Raison                                        |
| --------------------------- | ------- | --------------------------------------------- |
| **CKM Shared** (6 skills)   |         |                                               |
| ckm:brand                   | Global  | Brand voice applicable a tous les projets     |
| ckm:design                  | Global  | Logo, CIP, assets reutilisables               |
| ckm:design-system           | Global  | Token architecture transversale               |
| ckm:banner-design           | Global  | Workflow banniere multi-plateforme generique  |
| ckm:slides                  | Global  | Presentations HTML reutilisables              |
| ckm:ui-styling              | Global  | shadcn/ui + Tailwind communs a 5+ projets     |
| **SaaS Generic** (4 skills) |         |                                               |
| saas-observability-incident | Partagé | Debug SaaS applicable a tous les services     |
| saas-release-readiness      | Partagé | Checklist deploy pour tout SaaS               |
| saas-security-review        | Partagé | Audit securite multi-secteur                  |
| saas-tenancy-rbac           | Partagé | Architecture multitenant generique            |
| **EPN Project** (8 skills)  |         |                                               |
| ai-memory                   | Projet  | Memoire eleve, domaine pedagogique specifique |
| debug-skill                 | Projet  | Methode debug customisee pour EPN             |
| docs-optimisation           | Projet  | Organisation docs projet                      |
| frontend-design             | Projet  | Direction artistique EPN Web                  |
| socratic-tutoring           | Projet  | Pedagogie socratique EPN                      |
| ui-ux-frontend-premium      | Projet  | Routeur UI/UX local EPN                       |
| ui-ux-design-frontend-skill | Projet  | Standards UI/UX cibles EPN                    |
| ui-ux-pro-max               | Projet  | Recommandations design EPN + assets locaux    |
| fix-customization-eval-diag | Projet  | Tooling vscode customization locale           |

## Regles de placement

1. **CKM Shared** (prefixe `ckm:`): Skills sans dependance metier, reutilisables sur 5+ projets.
   - Placement: .github/skills/[skill-name]/
   - Triggers multilingues (fr/en)
   - Exemples: brand, design, design-system, banner-design, slides, ui-styling

2. **SaaS Generic** (sans prefixe, suffixe `saas-*`): Patterns SaaS multi-domaine, 3+ secteurs.
   - Placement: .github/skills/[saas-skill-name]/
   - Triggers anglais (secteur SaaS standard)
   - Exemples: saas-observability-incident, saas-release-readiness, saas-security-review, saas-tenancy-rbac

3. **Project-Specific** (sans prefixe, vocabulaire metier): Logique specifique a EPN ou Tutoring.
   - Placement: .github/skills/[skill-name]/
   - Triggers multilingues (contexte projet)
   - Exemples: ai-memory, socratic-tutoring, ui-ux-frontend-premium, debug-skill

4. **Eviter les doublons**: Un skill existe une seule fois, dans l'une des 3 categories.
   - Si un skill projet devient recurrent sur 3+ projets → promouvoir en SaaS ou CKM
   - Si un CKM skill devient trop specialise → extraire une version projet dedicatee

## Convention de nommage

1. Utiliser kebab-case strict pour le dossier et le champ name.
2. Forme recommandee: domaine-action, exemple: docs-optimisation.
3. Eviter les suffixes vagues comme final, v2, new.
4. Si variation necessaire: domaine-action-contexte, exemple: ui-ux-design-frontend-skill.
5. Le champ name doit toujours etre identique au nom du dossier.

## Conventions de normalisation

Chaque skill doit respecter:

1. Un fichier SKILL.md dans son dossier dedie: `.github/skills/[skill-name]/SKILL.md`
2. Frontmatter YAML avec name et description (premières 5 lignes)
3. **Prefixe dans name** selon categorie:
   - CKM Shared: `ckm:` (ex: `ckm:brand`, `ckm:design`, `ckm:ui-styling`)
   - SaaS Generic: `saas-` (ex: `saas-security-review`, `saas-tenancy-rbac`)
   - Project-Specific: aucun prefixe (ex: `socratic-tutoring`, `ui-ux-frontend-premium`)
4. Nom du dossier doit matcher le nom du skill (sans prefixe dans le dossier)
   - Exception CKM: dossier `brand/` mais skill name = `ckm:brand`
5. Description commencant par "Use when:" ou equivalent
6. Triggers explicites dans description (French ou English selon public)

## Maintenance du Hub

Quand un skill est ajoute, renomme, ou supprime:

1. Mettre a jour le tableau Catalogue (section appropriee: CKM, SaaS, Project)
2. Mettre a jour Routage recommande (ajouter priority si necessaire)
3. Mettre a jour Strategie hybride (la repartition decidee)
4. Verifier les conflits de declenchement (triggers doublons)
5. Revalider name/description/frontmatter du nouveau skill
6. Si migration: ex. Project → SaaS, mettre a jour nom du skill avec prefixe

## Note de gouvernance

Ce HUB est la source de verite pour la gouvernance des skills de ce repository.

- CKM skills: maintenus par equipe design/ux globale
- SaaS skills: maintenus par equipe SaaS architecture
- Project skills: maintenus par equipe EPN Web

## Statut de normalisation

| Element                | Statut     | Notes                                              |
| ---------------------- | ---------- | -------------------------------------------------- |
| Classification 3-tiers | ✅ TERMINE | CKM (6) + SaaS (4) + Project (8) = 18 skills       |
| Repartition decidee    | ✅ TERMINE | Placement clair pour chaque skill dans son tier    |
| Convention de nommage  | ✅ TERMINE | Prefixes `ckm:`, `saas-`, no prefix project        |
| Frontmatter YAML       | ✅ TERMINE | Tous les skills ont name et description normalises |
| Triggers multilingues  | ✅ TERMINE | Francais pour project/tutorat, English pour SaaS   |
| Documentation inline   | ✅ TERMINE | Chaque skill a son .md avec "Use when" et triggers |

## Statut des 3 taches

1. Classification global/local: TERMINEE
2. Regles de placement: TERMINEES
3. Convention de nommage: TERMINEE
