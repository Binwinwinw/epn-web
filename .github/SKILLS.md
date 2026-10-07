# Catalogue des skills SaaS pour ce projet

Ces skills ont été retenus après une vérification rapide des priorités SaaS sur des sources de référence :

- OWASP met l'accent sur l'authentification, le contrôle d'accès, la validation, les secrets, la journalisation et la sécurité multi-tenant.
- Microsoft souligne l'importance de l'isolation par tenant, du RBAC, du SSO et des identifiants immuables.
- Le service reste gratuit à ce stade, donc le focus porte sur la sécurité, la robustesse et l'isolement logique.

## Skills actifs

### 1. saas-security-review

Usage : audit sécurité d'une page, d'une API, d'un workflow ou d'une release.
Objectif : détecter les risques OWASP concrets et proposer les correctifs minimaux.

### 2. saas-tenancy-rbac

Usage : conception ou revue de l'isolation par tenant, des rôles, permissions et accès multi-client.
Objectif : éviter les fuites inter-tenant et clarifier le modèle d'autorisation.

### 3. saas-release-readiness

Usage : revue pré-déploiement d'une fonctionnalité ou d'une correction sensible.
Objectif : vérifier la sécurité, le rollback, les tests de fumée et les risques prod.

### 4. saas-observability-incident

Usage : incident de prod, bug intermittent, lenteur, régression ou analyse post-mortem.
Objectif : aller au root cause avec preuves et recommandations d'instrumentation.

## Emplacement réel

Les skills utilisables par Copilot sont placés dans `.github/skills/` pour rester compatibles avec le dépôt et le mode workspace.
