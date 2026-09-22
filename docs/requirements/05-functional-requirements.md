# CareerForge — Functional Requirements

> Source: CareerForge Complete Requirements Package v1.0. Companion documents: see /architecture folder for technical design

## 5. Functional Requirements
Each requirement is uniquely identified, prioritized (MoSCoW, aligned with Section 7), and linked to its originating user story.

| ID | Name | Description | Priority | Related Story |
|---|---|---|---|---|
| FR-001 | User registration | System allows account creation with email + password, with server-side validation | Must | US-001 |
| FR-002 | User login | System authenticates via email + password with session management | Must | US-002 |
| FR-003 | Password reset | System sends a reset link and allows setting a new password | Must | US-003 |
| FR-004 | Logout | System invalidates the active session on logout | Must | US-004 |
| FR-005 | Personal info management | User can create/edit personal info (name, title, contact, links) | Must | US-005 |
| FR-006 | Photo upload | User can upload, replace, and remove a profile photo | Must | US-006 |
| FR-007 | Multiple named summaries | User can create, edit, delete multiple professional summaries | Should | US-007 |
| FR-008 | Experience CRUD | User can create/edit/delete experience records with all specified fields | Must | US-008, US-011 |
| FR-009 | Experience achievements CRUD | User can add/edit/delete individual achievement records under an experience | Must | US-009 |
| FR-010 | Current position flag | Experience/Education/Leadership records support a "current" boolean that suppresses end date | Must | US-010 |
| FR-011 | Education CRUD | User can create/edit/delete education records | Must | US-012 |
| FR-012 | Skills CRUD with categories | User can create/edit/delete skills, each assigned a category | Must | US-013 |
| FR-013 | Skill proficiency (optional) | Skill records support an optional proficiency indicator | Could | US-014 |
| FR-014 | Projects CRUD | User can create/edit/delete project records including links | Must | US-015 |
| FR-015 | Certifications CRUD | User can create/edit/delete certification records | Should | US-016 |
| FR-016 | Awards CRUD | User can create/edit/delete award records | Should | US-017 |
| FR-017 | Leadership CRUD | User can create/edit/delete leadership records | Should | (Section 4 of original brief) |
| FR-018 | Languages CRUD | User can create/edit/delete language + proficiency records | Should | US-018 |
| FR-019 | References CRUD (optional) | User can create/edit/delete reference records; resumes may omit this section entirely | Could | (Section 4 of original brief) |
| FR-020 | Resume creation | User can create a new resume with a name and (optional) target role | Must | US-019 |
| FR-021 | Section selection | User can enable/disable which career profile sections appear per resume | Must | US-020 |
| FR-022 | Record selection per section | User can choose specific master records (which experiences, achievements, skills, projects) per resume section | Must | US-021 |
| FR-023 | Section/item reordering | User can reorder sections and selected items within a resume | Must | US-022 |
| FR-024 | Per-resume text override | User can override the displayed text of a specific master record field for one resume only, without altering the master record | Should | US-024 |
| FR-025 | Template selection | User can choose from a list of available templates for a resume | Must | US-025 |
| FR-026 | Theme/color customization | User can select from a constrained set of color themes per resume | Should | US-023 |
| FR-027 | Font customization | User can select from a constrained set of fonts per resume | Should | US-023 |
| FR-028 | Photo display toggle | User can enable/disable photo display per resume regardless of profile photo existing | Must | US-023 |
| FR-029 | ATS mode toggle | User can enable ATS-oriented rendering for a resume | Should | US-026 |
| FR-030 | Resume preview | User can view a live/near-live preview of the resume as configured | Must | (implied by US-019–024) |
| FR-031 | PDF export | User can export the current resume state as a downloadable PDF | Must | US-027 |
| FR-032 | Resume duplication | User can duplicate a resume, copying all selections/overrides/styling into a new independent resume | Must | US-028 |
| FR-033 | Resume deletion | User can delete a resume without affecting master career data | Must | (implied) |
| FR-034 | Job description input | User can paste job description text for analysis | Should | US-029 |
| FR-035 | Requirement extraction | System extracts candidate skills/keywords from pasted job description text | Should | US-029 |
| FR-036 | Profile comparison | System compares extracted requirements against the user's profile and categorizes as matched/missing/suggested | Should | US-029 |
| FR-037 | Public portfolio toggle | User can enable/disable a public portfolio, defaulting to disabled | Could | US-030 |
| FR-038 | Public portfolio field selection | User can choose which profile fields/sections are included in the public portfolio | Could | US-031 |
| FR-039 | Public portfolio URL/slug | User can set a unique slug for their public portfolio URL | Could | US-030 |
| FR-040 | AI-assisted text suggestions | System can generate suggested rewrites for summary/achievement text via an AI provider, requiring explicit user acceptance before saving | Future | (Phase 13, original brief) |

---
