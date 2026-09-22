# CareerForge — User Workflows (Step-by-Step)

> Source: CareerForge Complete Requirements Package v1.0. Companion documents: see /architecture folder for technical design

## 8. User Workflows (Step-by-Step)
**Registration**
1. Visitor navigates to Register.
2. Enters name, email, password, password confirmation.
3. Submits; system validates uniqueness and password rules.
4. Account created, session started, redirect to Dashboard (empty state).

**Creating Career Profile**
1. From Dashboard, user opens Career Profile.
2. Fills Personal Info (required fields enforced).
3. Optionally uploads photo.
4. Navigates to each section (Experience, Education, Skills, Projects, etc.) and adds records via "Add" forms.
5. Each record save returns to the section list view, showing the new record.

**Adding Experience**
1. User opens Experience section → "Add Experience."
2. Enters job title, organization, location, start date, (end date or "current"), description.
3. Saves the base experience record.
4. Adds one or more achievements as separate line items under that experience.
5. Record appears in the Experience list, available for selection in any resume.

**Creating a Resume**
1. From Resume List, user clicks "New Resume."
2. Names the resume and optionally sets a target role.
3. Chooses a template.
4. System presents section toggles (Summary, Experience, Education, Skills, Projects, etc.).
5. For each enabled section, user selects which specific master records (and which achievements within an experience) to include.
6. User reorders sections and items via drag-and-drop or up/down controls.
7. Resume is saved in draft state, viewable anytime from Resume List.

**Customizing a Resume**
1. From an open resume, user opens Style settings.
2. Selects color theme and font from the constrained option list.
3. Toggles photo display on/off.
4. Changes apply live to the preview pane.

**Previewing a Resume**
1. User opens the Preview tab/panel for a resume.
2. System renders the current template + selections + overrides as a formatted page view, matching what the PDF will produce.

**Exporting PDF**
1. From Preview, user clicks "Export PDF."
2. System renders the resume through the PDF engine.
3. On success, file is offered for download (or queued, per NFR-002/Section 12 decision).
4. On failure, user sees an explicit error and can retry.

**Duplicating a Resume**
1. From Resume List, user selects "Duplicate" on an existing resume.
2. System creates a new resume record copying all section/item selections, overrides, and style settings.
3. New resume appears in the list with a default name the user can rename.

**Creating an ATS Resume**
1. From an existing (or new) resume, user toggles "ATS Mode."
2. System switches rendering to the ATS-safe layout (no photo, single column, standard headings), keeping the same underlying section/item selections.
3. User can export this as a separate PDF without affecting the non-ATS version (implies ATS is a per-resume flag, not a separate resume, unless the user explicitly duplicates first — see Section 12 for the exact behavior to confirm).

**Analyzing a Job Description**
1. User opens the Job Description Analyzer (as a standalone tool or attached to a specific resume).
2. Pastes job description text.
3. Submits for analysis.
4. System extracts candidate requirements/keywords.
5. System compares against the user's profile data.
6. Results are shown in three groups: Matched, Missing, Suggested — with no fabricated claims of experience.

**Publishing a Portfolio**
1. User opens Portfolio Settings (disabled by default).
2. Selects which sections/fields to expose publicly.
3. Chooses a unique URL slug.
4. Previews the public-facing page before publishing.
5. Enables "Public" toggle and saves.
6. Portfolio becomes reachable at the public URL; user can disable at any time.

---
