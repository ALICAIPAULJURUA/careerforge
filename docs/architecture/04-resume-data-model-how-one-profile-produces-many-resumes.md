# CareerForge — Resume Data Model — How One Profile Produces Many Resumes

> Source: CareerForge Complete Technical Architecture v1.0. Companion documents: see /requirements folder for product requirements

## 4. Resume Data Model — How One Profile Produces Many Resumes
This is the core mechanic, so it's worth walking through concretely.

**Step 1 — Master data exists independently of any resume.** A user's `experiences`, `skills`, `projects`, etc. are created once, owned by `user_id`, and never reference a resume.

**Step 2 — A Resume is a thin configuration object.** Creating a `Resume` row just records template/theme/style choices. It has zero content until sections are configured.

**Step 3 — Sections declare structure.** For each section type the user enables, one `resume_sections` row is created, holding visibility and order.

**Step 4 — Items declare selection and order.** For each master record the user wants included, one `resume_items` row is created under the relevant section, pointing at the master record via the polymorphic `itemable` relationship, with its own `sort_order`. This is how "8 achievements exist, but this resume shows 4, in this specific order" is expressed — four `resume_items` rows, not four copies of the achievement text.

**Step 5 — Overrides declare resume-specific wording (optional).** If the user edits the *displayed text* of a specific field for this resume only, a `resume_overrides` row is created (or updated) keyed to that exact record + field + resume. Nothing is written back to the master table.

**Step 6 — Rendering merges all four layers.** `ResumeRenderingService::render(Resume $resume)`:
1. Loads the resume's `resume_sections`, ordered, filtered to `is_visible`.
2. For each section, loads its `resume_items`, ordered, eager-loading each `itemable` morph target.
3. For each loaded item, checks `resume_overrides` for a matching row and substitutes the override value for that field if present, otherwise uses the master value.
4. Groups achievement items under their parent experience item (the one schema-level special case noted in 2.5).
5. Returns a single normalized `RenderableResume` array/DTO — the *only* thing templates ever receive (Section 5).

**Deleting master data:** because `resume_items`/`resume_overrides` reference master records via `itemable_id`/`overridable_id` rather than copying data, deleting a master record (say, an Experience) that's referenced by any `resume_items` row must be intercepted before deletion — the delete action checks for existing references (via the `morphMany` relationship noted in Section 3) and either blocks the action with a warning listing affected resumes, or cascades the deletion of those `resume_items` rows and informs the user which resumes were affected (per the Edge Cases table — this exact behavior is one of the items still pending your confirmation).

---
