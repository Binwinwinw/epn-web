---
name: debug-skill
description: "Use when: bug triage, root cause analysis, reproducible debugging workflow, React and Supabase incident diagnosis. Triggers: debug, bug, reproduce issue, health check."
---

# /debug-skill

# Systematic Debugging & Health Check Skill

This skill defines the rigorous process for identifying and resolving bugs in "Mon Coach Scolaire", ensuring a stable and reliable platform for all users.

## Debugging Philosophy

1. **Reproduce First**: Never attempt a fix without a clear reproduction path (or a plausible hypothesis based on logs).
2. **Isolate**: Determine if the issue is Frontend (React/UI), Backend (Supabase/RLS), or AI-related (Prompt mismatch/API error).
3. **Log Everything**: Use comprehensive `console.log` or dedicated monitoring tools to trace the data flow.
4. **Fix the Root, Not the Symptom**: If an error is caused by a missing null check, investigate _why_ the data was null in the first place.

## Debugging Workflow

- **React Issues**: Inspect State, Props, and use React DevTools.
- **Supabase/DB Issues**: Check RLS policies, network tabs, and the Supabase dashboard logs.
- **AI Logic**: Validate the input prompt and use a "Shadow Response" (log the raw response before processing) to identify hallucinations or formatting errors.

## Maintenance Checklist

- **Linting**: Run `npm run lint` regularly.
- **Type Safety**: Ensure all new features are fully typed with TypeScript.
- **Regression Testing**: Verify that new features don't break existing ones (especially Auth and Dashboard flows).

---

_Inspired by professional software engineering - Stability for Education_
