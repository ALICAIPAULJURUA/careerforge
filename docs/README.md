# CareerForge — Project Documentation

This folder is the single source of truth for the CareerForge project. It should be placed at the root of the codebase (e.g., `/docs`) so any developer or AI coding agent working on the project can read it before writing code.

## How to read this folder

Read in this order:

1. **`OPEN-DECISIONS.md`** — read this first. It lists every unresolved product/technical decision and the recommended default for each. Nothing should be built against an open item without checking this file.
2. **`requirements/`** — the *what* and *why*: product requirements, personas, user stories, acceptance criteria, functional and non-functional requirements, MVP scope, workflows, edge cases, security requirements, and a traceability matrix.
3. **`architecture/`** — the *how*: system architecture, full database schema, relationships, the resume data model (the core mechanic of the whole product), template/theme systems, routing, authorization, file storage, PDF generation, the AI integration boundary, ERD, directory structure, and the phased development plan.
4. **`BUILD-INSTRUCTIONS.md`** — the instructions for an AI coding agent (or a human developer) to actually implement the project, phase by phase, using everything above as the specification.

## Folder contents

### `/requirements`
| File | Contents |
|---|---|
| 01-product-requirements-document-prd.md | Vision, problem statement, target users, goals, scope, assumptions, constraints, risks |
| 02-user-personas.md | Five personas with goals and frustrations |
| 03-user-stories.md | All user stories, grouped by feature area |
| 04-acceptance-criteria-key-user-stories.md | Given/When/Then criteria for key stories |
| 05-functional-requirements.md | FR-001 through FR-040, each with priority and related story |
| 06-non-functional-requirements.md | NFR-001 through NFR-008, measurable |
| 07-mvp-prioritization-moscow.md | Must/Should/Could/Future breakdown |
| 08-user-workflows-step-by-step.md | Step-by-step flows for every major workflow |
| 09-edge-cases.md | Edge cases and expected handling |
| 10-security-requirements.md | Full security requirement checklist |
| 11-traceability-matrix-representative-sample.md | Business requirement → FR → story → AC → test case |

### `/architecture`
| File | Contents |
|---|---|
| 01-system-architecture.md | Layers, models, services, actions, policies, jobs, notifications |
| 02-database-design.md | Every table, every column, types, nullability, indexes, FKs, purpose |
| 03-relationships-eloquent-terms.md | Eloquent relationship definitions |
| 04-resume-data-model-how-one-profile-produces-many-resumes.md | The core master-data-vs-presentation mechanic |
| 05-template-architecture.md | How templates coexist without duplicating logic |
| 06-theme-system.md | Theme/customization system |
| 07-routing.md | Proposed routes and route groups |
| 08-authorization.md | Policies and enforcement points |
| 09-file-storage.md | Upload validation, storage strategy, private/public handling |
| 10-pdf-architecture.md | PDF generation pipeline, engine comparison |
| 11-future-ai-architecture-boundary-only-not-built-now.md | AI integration boundary (not built yet) |
| 12-erd.md | Textual ERD + Mermaid ERD |
| 13-directory-structure.md | Full proposed Laravel directory layout |
| 14-development-phases.md | Phase 0–16, each with objective/components/DB/UI/tests/DoD |

### Root files
| File | Contents |
|---|---|
| OPEN-DECISIONS.md | Every unresolved decision across both documents, consolidated |
| BUILD-INSTRUCTIONS.md | How an AI coding agent should implement this project |

## Project identity

- **Name:** CareerForge
- **Stack:** Laravel, PHP, MySQL, Blade, Livewire, Alpine.js — no React/Vue
- **Core idea:** one master career profile produces many independent resumes, each a curated, reorderable, optionally-overridden view over the same underlying data
