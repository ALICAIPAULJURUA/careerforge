# CareerForge — Acceptance Criteria (Key User Stories)

> Source: CareerForge Complete Requirements Package v1.0. Companion documents: see /architecture folder for technical design

## 4. Acceptance Criteria (Key User Stories)
**US-001 — Register**
- Given a visitor on the registration page, When they submit a valid unique email, matching password/confirmation, and required fields, Then an account is created and they are logged in and redirected to the dashboard.
- Given a visitor submits an email already in use, When they submit the form, Then a validation error is shown and no account is created.

**US-002 — Login**
- Given a registered user with correct credentials, When they submit the login form, Then they are authenticated and redirected to the dashboard.
- Given incorrect credentials, When submitted, Then an error is shown and no session is created; failed attempts are rate-limited (see NFR-001).

**US-009 — Add achievement to experience**
- Given a user editing an experience record, When they add an achievement with non-empty text, Then it is saved as a child record of that experience and becomes independently selectable in any resume referencing that experience.

**US-010 — Mark experience as current**
- Given a user creating/editing an experience, When they check "current position," Then the end date field is disabled/cleared and the resume renderer displays "Present" instead of an end date.

**US-019 — Create resume**
- Given a user with at least a populated Personal Info section, When they create a new resume and provide a name, Then a new resume record is created in an unpublished/draft state with no sections yet selected.

**US-021 — Select specific records for a resume section**
- Given a resume with an Experience section enabled, When the user selects 2 of 5 available experiences and, within one experience, 3 of 6 achievements, Then only the selected records render in the preview and PDF, in the order chosen, and the master records remain unmodified.

**US-024 — Per-resume text override**
- Given a resume referencing a master achievement, When the user edits that achievement's text within the resume builder (not the profile editor), Then the override is stored against that resume only; the master achievement text is unchanged; and other resumes referencing the same master achievement continue to show the original text unless they have their own override.

**US-026 — ATS mode**
- Given a user viewing a resume, When they enable ATS mode, Then the rendered output removes the photo, icons, and multi-column layout, and uses standard section headings ("Experience," "Education," "Skills") in a single-column, text-first layout.

**US-027 — PDF export**
- Given a user viewing a resume preview, When they click "Export PDF," Then a PDF is generated matching the preview's template, content, and styling, and is downloaded (or queued and made available, if generation is asynchronous — pending decision, Section 12).
- Given the PDF generation process fails (e.g., renderer timeout), When export is attempted, Then the user sees a clear error message and is not shown a corrupt/empty file.

**US-028 — Duplicate resume**
- Given an existing resume, When the user selects "Duplicate," Then a new resume is created with identical section selections, item selections, overrides, and styling, with an auto-generated name (e.g., "Copy of [original name]"), and the original resume is unchanged.

**US-029 — Job description analysis**
- Given a user pastes job description text and submits it, When the system compares it against the profile, Then it returns three distinct lists: matched requirements (evidence exists in profile), missing requirements (no evidence found), and suggested improvements — and at no point does it add or imply experience the user has not entered themselves.

**US-030 / US-031 — Public portfolio publishing**
- Given a user has never published a portfolio, When they view portfolio settings, Then visibility defaults to private/unpublished.
- Given a user enables public visibility, When they save, Then only fields explicitly marked "include in public portfolio" are rendered at the public URL; all other profile data remains inaccessible to unauthenticated visitors.

---
