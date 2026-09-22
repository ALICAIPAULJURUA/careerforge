# CareerForge — Build Instructions for the Coding Agent

You are implementing CareerForge, a Laravel career-profile and resume-builder application. This file tells you how to work. The `requirements/` and `architecture/` folders in this same directory are your specification — they are authoritative. Do not invent product behavior or database structure that isn't in them; if something is genuinely missing, stop and ask rather than guessing.

## 0. Before you write a single line of code

1. Read `README.md` for the map of this folder.
2. Read `OPEN-DECISIONS.md` in full. Several items there have a stated recommended default — use those defaults unless the project owner has told you otherwise in the conversation. Do not silently pick a different answer.
3. Read `architecture/14-development-phases.md` in full. This is your build order. Do not skip ahead or build multiple phases at once.
4. Read `architecture/02-database-design.md` and `architecture/03-relationships-eloquent-terms.md` before writing any migration or model. The schema in these files is the schema — do not add, remove, or rename columns/tables without flagging the change and explaining why before proceeding.

## 1. Working process — one phase at a time

For every phase in `architecture/14-development-phases.md`:

1. State which phase you're starting and its objective, in one line.
2. Build only what that phase lists under "Components," "DB changes," and "UI changes." Do not pull in work from a later phase, even if it seems convenient (e.g., don't start theme customization while building Phase 2's Experience CRUD, even though it might seem natural — it belongs to Phase 9).
3. Write the tests listed under that phase's "Tests" entry as part of the same phase, not as a follow-up. A phase without its tests is not done.
4. Before moving to the next phase, check the phase's "Definition of done" line explicitly and confirm it's met. If it isn't, stay on the current phase.
5. If a phase's work touches an item still open in `OPEN-DECISIONS.md`, apply that item's recommended default, note in your summary to the user that you did so, and continue — don't block the whole phase on a decision that has a stated fallback.

## 2. Code standards — how this codebase should be written

These follow directly from `architecture/01-system-architecture.md` and `architecture/13-directory-structure.md`. Don't deviate from this structure:

- **Controllers and Livewire components are thin.** They call a Form Request for validation, a Policy for authorization, and exactly one Action or Service for business logic. If you find yourself writing more than a few lines of actual logic in a controller method, that logic belongs in an Action or Service instead.
- **One Form Request per meaningful write operation**, holding all field-level validation rules (required-if-not-current dates, MIME types, max lengths). Never call `$request->validate([...])` inline in a controller for anything beyond the most trivial case.
- **One Policy per model with per-user ownership.** Every `view`/`update`/`delete` check goes through the policy, invoked via `$this->authorize(...)`. Never write an ad hoc `if ($model->user_id !== auth()->id())` check outside a policy — that's how authorization bugs happen.
- **Actions** are single-purpose, invokable classes for anything touching multiple models or carrying real business rules (creating a resume, duplicating a resume, attaching a resume item, applying an override). Plain single-model CRUD doesn't need an Action.
- **`ResumeRenderingService` is the only place** that merges master data + resume section/item selections + overrides into renderable output. No Blade template, controller, or Livewire component should query `resume_items` or `resume_overrides` directly — they only ever receive the already-merged `RenderableResume` output. This rule is the single most important one in the whole codebase; the entire product concept depends on this boundary being respected. If you're tempted to add a conditional to a Blade template that checks selection/override state directly, stop — that logic belongs in the service.
- **PDF generation goes through `PdfGeneratorInterface`.** No controller or service should reference Dompdf or Browsershot classes directly.
- **No AI SDK calls anywhere outside `AiAssistantInterface` implementations**, even once that phase is reached.
- **Use the exact table/column names from `architecture/02-database-design.md`.** If you believe a column is missing something needed for a phase, flag it and propose the addition — don't add it silently.
- **Every uploaded file goes through the validation described in `architecture/09-file-storage.md`** (server-side MIME check, size limit, re-encoding, randomized filename, private disk). Don't take a shortcut on file handling even for early phases.

## 3. Testing expectations

- Feature tests cover the full request/response cycle for each phase's components, including at least one authorization-failure case (user A cannot touch user B's record) per new model introduced.
- The single highest-priority test in the project is around Phase 5–6: confirming that selecting/reordering/overriding resume content never mutates the underlying master records. Write this test thoroughly, with explicit assertions on the master table's unchanged state after resume operations.
- Don't skip a phase's listed tests to "come back to them later." A phase isn't complete without them, per Section 1.4 of this file.

## 4. When you're unsure

- If a requirement is ambiguous and `OPEN-DECISIONS.md` doesn't cover it, stop and ask the project owner rather than choosing an interpretation and continuing silently.
- If you believe the architecture document has a mistake or a better approach exists for something not yet built, say so explicitly and explain the tradeoff — don't just build it differently without flagging the deviation.
- If a phase seems too large to complete in one working session, it's fine to pause partway and report exactly what's done and what's left against that phase's component list — just don't mark a phase "done" until its Definition of Done is actually met.

## 5. Getting started

Begin with **Phase 0 — Project Scaffolding** from `architecture/14-development-phases.md`. Confirm the Laravel version and starter kit approach (Breeze with Blade + Livewire is the recommended fit per the architecture doc) before scaffolding, then proceed phase by phase as described above.
