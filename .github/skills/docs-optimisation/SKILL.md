---
name: docs-optimisation
description: "Use when: documentation cleanup, move root markdown docs to docs/, keep README at root, maintain markdown as source of truth, and create/update html navigation views. Triggers: optimise docs, sceller optimisation docs, doc workflow, markdown to html docs."
---

# /docs-optimisation

## Purpose

Standardize project documentation workflow:

- Markdown files are the source of truth.
- HTML is used as an optional reading and navigation layer.
- Keep project root clean.

## Rules

1. Keep README.md at project root.
2. Move root-level documentation files (except README.md) into docs/.
3. Prefer .md for authoring, reviews, diffs, and maintenance.
4. Use HTML only for discovery/navigation and better readability.
5. Update internal links after any move/rename.
6. Do not modify source code unless explicitly requested.

## Execution Checklist

1. Scan root for doc files: \*.md except README.md.
2. Ensure docs/ exists.
3. Move target files into docs/.
4. Verify moved files exist in docs/ and are absent from root.
5. Update links in README.md and doc index files.
6. If requested, create/update docs/index.html as documentation portal.
7. Provide final report with moved files and changed links.

## Output Format

- Moved files list.
- Updated files list.
- Remaining root docs check.
- Optional next step: generate or refine HTML doc portal.

## Safety

- Never delete documentation content.
- Never overwrite unrelated files.
- If ambiguity exists about a file role, ask before moving.
