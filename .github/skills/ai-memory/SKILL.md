---
name: ai-memory
description: "Use when: student profiling, memory extraction from sessions, personalized tutoring context, strengths and weaknesses tracking. Triggers: memory eleve, profil eleve, pedagogical memory, student context."
---

# /ai-memory

# AI Student Memory Skill

This skill enables the AI Coach to maintain a long-term, evolving memory of each student, inspired by the "Supermemory" architecture. It focuses on extracting pedagogical facts, tracking progress, and identifying learning patterns.

## Concept

Instead of just relying on past scores, we extract **semantic facts** from every interaction.
Example: "L'élève a du mal avec les fractions mais comprend bien les pourcentages."

## Key Responsibilities

1. **Fact Extraction**: After each chat session or exercise, identify new "memories" about the student.
2. **Context Retrieval**: Before starting a session, retrieve relevant memories to personalize the approach.
3. **Fact Updating**: If a new interaction contradicts an old memory (e.g., the student now masters fractions), update the memory state.

## Memory Categories

- **Strengths**: Concepts the student masters well.
- **Weaknesses**: Recurring errors or explicitly mentioned difficulties.
- **Interests**: Topics or themes the student enjoys (for creating relatable exercises).
- **Learning Style**: Prefers short exercises, needs a lot of encouragement, likes visual explanations, etc.

## Instructions for the AI

- **Observe**: Monitor student input for clues about their state of mind and understanding.
- **Record**: Log significant pedagogical milestones.
- **Synthesize**: Periodically summarize a student's profile to provide a high-level view for parents/teachers.

## Usage in Workflows

1. **Session Start**: `get_student_context(student_id)` -> Inject memories into System Prompt.
2. **Session End**: `analyze_session(history)` -> `upsert_student_memories(facts)`.

---

_Inspired by supermemory.ai - Personalized Learning Intelligence_
