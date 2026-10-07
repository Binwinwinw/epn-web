---
name: saas-security-review
description: "Use when auditing a SaaS or web application for auth flaws, session issues, access control gaps, tenant isolation risks, CSRF, XSS, SQL injection, secret leaks, or release security review."
---

# /saas-security-review

Review the requested feature, page, API, or release candidate like a SaaS security engineer.

## Focus areas

- authentication and session handling
- authorization, RBAC, and tenant isolation
- input validation, CSRF, XSS, SQL injection
- secrets exposure, debug flags, and unsafe logs
- concrete exploit paths and business impact

## Method

1. Trace the real data flow and access flow.
2. Identify specific risks with evidence from code or behavior.
3. Rank the findings by severity.
4. Recommend the smallest safe fix that fits the codebase.
5. End with explicit verification steps.

## Output format

- Findings summary
- Risk table with severity and evidence
- Root cause
- Recommended remediation
- Verification checklist
