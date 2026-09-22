# CareerForge — Development Phases

> Source: CareerForge Complete Technical Architecture v1.0. Companion documents: see /requirements folder for product requirements

## 14. Development Phases
Each phase is deliberately small, ends in a working/testable state, and maps back to the MVP scope from the Requirements Package.

### Phase 0 — Project Scaffolding
- **Objective:** Working Laravel install with auth scaffolding, database connection, base layout.
- **Components:** Laravel installer, Breeze (Blade + Livewire starter kit is the natural fit here) or hand-built auth views, base `layout.blade.php`.
- **DB changes:** default `users`, `password_reset_tokens`, `sessions` migrations (Laravel defaults).
- **UI changes:** base layout shell, nav placeholder.
- **Tests:** app boots, default routes return 200.
- **Definition of done:** `php artisan serve` runs; register/login/logout work via default scaffolding.

### Phase 1 — Career Profile Core (Personal Info + Photo)
- **Objective:** FR-005, FR-006.
- **Components:** `Profile` model/migration, `ProfileController`, `ProfilePolicy`, photo upload handling (Section 9).
- **DB changes:** `profiles` table.
- **UI changes:** Profile edit form with photo upload.
- **Tests:** create/update profile; photo validation rejects bad MIME/size; unauthorized access blocked.
- **Definition of done:** a logged-in user can fill and persist their personal info and photo.

### Phase 2 — Experience & Achievements
- **Objective:** FR-008, FR-009, FR-010, FR-011.
- **Components:** `Experience`, `ExperienceAchievement` models/migrations, controllers, policies, Form Requests (including "end date required unless current" validation).
- **DB changes:** `experiences`, `experience_achievements`.
- **UI changes:** Experience list + create/edit form with nested achievement inputs.
- **Tests:** CRUD success/failure paths, current-flag validation, achievement ordering.
- **Definition of done:** user can build a full multi-role work history with individually listed achievements.

### Phase 3 — Remaining Master Data (Education, Skills, Projects)
- **Objective:** FR-011 (Education), FR-012 (Skills), FR-014 (Projects, + FR technologies).
- **Components:** corresponding models/migrations/controllers/policies.
- **DB changes:** `educations`, `skills`, `projects`, `project_technologies`.
- **UI changes:** three more section editors following the same pattern established in Phase 2.
- **Tests:** CRUD + authorization for each.
- **Definition of done:** all MVP master-data sections exist and are populated for a real test user.

### Phase 4 — Templates & Themes Seed Data
- **Objective:** Lay groundwork so Phase 5 has something to render against.
- **Components:** `Template`, `Theme` models/migrations, `TemplateSeeder`, `ThemeSeeder` (one template, one theme minimum for MVP).
- **DB changes:** `templates`, `themes`.
- **UI changes:** none user-facing yet.
- **Tests:** seeders run and produce expected rows.
- **Definition of done:** at least one template key and one theme preset exist in the database.

### Phase 5 — Resume Skeleton (Create, Sections, Items)
- **Objective:** FR-020, FR-021, FR-022, FR-023 — the core mechanic from Section 4.
- **Components:** `Resume`, `ResumeSection`, `ResumeItem` models/migrations, `CreateResumeAction`, `AttachResumeItemAction`, `ReorderResumeItemsAction`, resume builder Livewire components.
- **DB changes:** `resumes`, `resume_sections`, `resume_items`.
- **UI changes:** Resume creation flow, section toggles, record picker per section, drag/reorder UI.
- **Tests:** creating a resume, selecting specific records, reordering, and confirming master data is untouched (this is the highest-value test in the whole project — write it carefully).
- **Definition of done:** a user can build a resume that references a curated subset of their master data.

### Phase 6 — Rendering Service & Preview
- **Objective:** Turn Phase 5's data into visible output; FR-030.
- **Components:** `RenderableResume` DTO, `ResumeRenderingService`, `modern.blade.php` (first template only).
- **DB changes:** none.
- **UI changes:** Preview screen.
- **Tests:** `ResumeRenderingServiceTest` — verifies section ordering, item ordering, achievement grouping under parent experience, empty-section handling (edge cases from the Requirements Package).
- **Definition of done:** the preview accurately reflects a resume's configuration.

### Phase 7 — PDF Export
- **Objective:** FR-031.
- **Components:** `PdfGeneratorInterface`, `DompdfGenerator` (per Section 10.2 recommendation), `PdfServiceProvider`.
- **DB changes:** none (unless queued — deferred).
- **UI changes:** Export button + download flow; failure-state messaging.
- **Tests:** successful export produces a valid PDF; simulated failure shows an error and never serves a corrupt file.
- **Definition of done:** a resume built in Phase 5–6 can be downloaded as a PDF matching the preview.

### Phase 8 — Resume Duplication & Deletion
- **Objective:** FR-032, FR-033.
- **Components:** `DuplicateResumeAction`.
- **DB changes:** none.
- **UI changes:** Duplicate/Delete controls on Resume List.
- **Tests:** duplication produces a fully independent copy; deleting a resume leaves master data untouched.
- **Definition of done:** MVP is functionally complete end-to-end (register → profile → resume → PDF → duplicate).

### Phase 9 (Post-MVP) — Additional Templates + Theme/Font Customization
- FR-025 (remaining templates), FR-026, FR-027. Adds `classic.blade.php`, `creative.blade.php`, theme picker UI, font override UI.

### Phase 10 (Post-MVP) — ATS Mode
- FR-029. Adds `ats.blade.php` and the template-substitution logic from Section 5.5.

### Phase 11 (Post-MVP) — Remaining Career Sections
- FR-007, FR-015–FR-019 (Summaries, Certifications, Awards, Leadership, Languages, References).

### Phase 12 (Post-MVP) — Per-Resume Overrides
- FR-024. Adds `resume_overrides` table and `ApplyResumeOverrideAction`, plus the override-checking step in `ResumeRenderingService` (designed for from the start in Section 4, but not activated until this phase).

### Phase 13 (Post-MVP) — Job Description Analyzer
- FR-034–036, keyword-based per the approved assumption.

### Phase 14 (Post-MVP) — Public Portfolio
- FR-037–039, `portfolio_settings` table, public `PortfolioController`.

### Phase 15 (Future) — AI Assistance
- FR-040, `AiAssistantInterface` and first concrete implementation, per Section 11's boundary design.

### Phase 16 — Testing, Security & Accessibility Hardening
- Full pass against Section 10 (security) and NFR-004 (accessibility) of the Requirements Package before considering the project "production-ready," even though this is a portfolio project.

---
