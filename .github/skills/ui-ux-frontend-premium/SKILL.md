---
name: ui-ux-frontend-premium
description: "Use when: a UI/UX request is broad or ambiguous and you need to route to the right frontend skill. Triggers: ui ux premium global, design routing, choose frontend skill, orchestration ui ux."
---

# /ui-ux-frontend-premium

# UI UX Frontend Premium Orchestrator

This skill is the entry point for UI/UX frontend requests.
It routes work to the right specialized skill to avoid overlap and conflicting guidance.

## Routing Rules

1. Use frontend-design for art direction, visual tone, typography moodboards, and layout language.
2. Use ui-ux-design-frontend-skill for concrete implementation choices in product UI (components, states, responsiveness, shadcn customization).
3. Use ui-ux-pro-max for data-backed exploration, scripted recommendations, design-system generation, and benchmark-style options.

## Decision Tree

1. If the user asks "what style should we adopt" or "give a strong visual direction": use frontend-design.
2. If the user asks "build this screen/component" or "polish this existing UI": use ui-ux-design-frontend-skill.
3. If the user asks "compare options", "generate a design system", or needs structured search outputs: use ui-ux-pro-max.
4. If the request includes multiple intents, run in this order: frontend-design, then ui-ux-design-frontend-skill, then ui-ux-pro-max only if needed.

## Output Contract

- State which sub-skill(s) were selected and why.
- Keep recommendations consistent with project constraints and mobile responsiveness.
- Do not duplicate the same advice across sub-skills.

## Guardrails

- Do not run ui-ux-pro-max for simple UI tweaks.
- Do not skip frontend-design when visual direction is undecided.
- Do not bypass ui-ux-design-frontend-skill when implementation detail is requested.
