---
name: saas-tenancy-rbac
description: "Use when designing or reviewing multitenant SaaS architecture, tenant isolation, RBAC, per-tenant permissions, entitlement checks, SSO, user onboarding, or tenant switching flows."
---

# /saas-tenancy-rbac

Design or review SaaS tenant and authorization behavior with a strict isolation mindset.

## What to check

- tenant boundaries in database access and APIs
- role model and permission mapping
- tenant-aware session and token claims
- self-signup, invitation, and offboarding flows
- entitlements separated cleanly from identity

## Principles

- Prefer established identity providers over building auth from scratch.
- Use immutable tenant and user identifiers for authorization and audit.
- Ensure users can only see or change resources inside their allowed tenant scope.
- Make tenant switching explicit and safe.

## Output format

- Current model
- Isolation or RBAC risks
- Recommended design adjustments
- Rollout and testing checklist
