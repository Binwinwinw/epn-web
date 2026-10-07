---
name: saas-release-readiness
description: "Use before deploying a SaaS or web application change to review release safety, config drift, migrations, rollback, smoke tests, observability, and production risks."
---

# /saas-release-readiness

Run a disciplined pre-release review for product changes that touch live users.

## Review checklist

- config and secret safety
- schema or data migration risk
- backward compatibility and feature flags
- smoke tests for critical user journeys
- monitoring, logging, and alert coverage
- rollback path and operator notes

## Rules

- No release is considered ready without evidence.
- Prefer small, reversible changes.
- Flag blockers clearly instead of assuming deploy safety.

## Output format

- Go or no-go recommendation
- Blocking issues
- Release checklist
- Post-deploy verification steps
