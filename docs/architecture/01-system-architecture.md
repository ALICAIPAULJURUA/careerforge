# CareerForge — System Architecture

> Source: CareerForge Complete Technical Architecture v1.0. Companion documents: see /requirements folder for product requirements

## 1. System Architecture
### 1.1 Layered Structure

CareerForge follows a fairly conventional layered Laravel structure, deliberately avoiding over-engineering (no CQRS, no separate microservices) while still keeping responsibilities cleanly separated — because the riskiest logic in this app (merging master data + resume selections + overrides into something renderable) needs to live in exactly one well-tested place, not scattered across controllers and Blade views.

```
Request → Route → Controller/Livewire Component → Form Request (validation)
        → Policy (authorization) → Action/Service (business logic)
        → Model/Eloquent (persistence) → back up through the same layers
        → Blade View / Template (presentation)
```

**Controllers / Livewire components** — thin. They validate input has reached them correctly (via Form Requests), check nothing themselves beyond what Laravel's route-model-binding + Policy already enforce, call exactly one Action or Service, and return a view/redirect. They contain no business rules.

**Form Requests** — one per meaningful write operation (`StoreExperienceRequest`, `UpdateResumeSectionRequest`, etc.). This is where field-level validation rules live (dates, required-if-not-current, max lengths, MIME types), keeping controllers free of `$request->validate([...])` blocks scattered everywhere.

**Policies** — one per model that has per-user ownership semantics (Experience, Education, Skill, Project, Resume, etc.). Each policy's `view`/`update`/`delete` methods check `$model->user_id === $user->id`. This is the single enforcement point for "a user must not touch another user's data" (Section 8 has the full list).

**Actions** — single-purpose, invokable classes for anything that's more than a plain Eloquent create/update (e.g., `CreateResumeAction`, `DuplicateResumeAction`, `AttachResumeItemAction`, `ApplyResumeOverrideAction`). Plain CRUD (e.g., saving a Skill) doesn't need an Action — that would be over-engineering; it can live directly in the Livewire component or a thin Form Request → Model save. Actions exist specifically where multiple models are touched together or where the operation has real business rules (duplication, section reordering, override application).

**Services** — for logic that isn't really "an action a user takes" but a capability the app depends on: `ResumeRenderingService` (the most important service in the system — turns a Resume + its sections/items/overrides into a normalized data structure templates can consume), `PdfGeneratorInterface` implementation, `JobDescriptionAnalyzerService`, and later `AiAssistantInterface` implementations. Services are injected via the container, not instantiated ad hoc, so they can be swapped/mocked in tests.

**Why this split matters here specifically:** the hardest requirement in the whole project — "a resume shows a curated, possibly-overridden subset of master data, and none of that curation should ever mutate the master data" — is exactly the kind of logic that becomes an unmaintainable mess if it's half in a Livewire component, half in a Blade `@if`, and half in an Eloquent accessor. Concentrating it in `ResumeRenderingService` means there's one place to test it and one place to change it when Section 12's open questions get resolved.

### 1.2 Events/Listeners

Not required for MVP. The workflows described in the Requirements Package are synchronous and single-user; there's no genuine cross-cutting side-effect (like "notify other users") that needs decoupling via events yet. Introducing an event for, say, `ResumeExported` is reasonable *later* if you want audit logging or usage analytics, but adding it now would be architecture for a scale this app doesn't have. **Recommendation: skip for MVP, revisit post-MVP if analytics/notifications are added.**

### 1.3 Jobs/Queues

**Conditionally required**, and this is one of the decisions carried over from the Requirements Package (open item #3/#4): if PDF generation uses Browsershot (headless Chrome), it can take a few seconds and should run as a queued Job (`GenerateResumePdfJob`) with the user polling or being notified on completion, rather than blocking the request. If Dompdf is chosen instead (fast, in-process), synchronous generation within the request lifecycle is fine and a queue is unnecessary complexity for MVP. **This is decided by the PDF engine choice in Section 10 — flagged there.**

### 1.4 Notifications

Minimal for MVP: password reset (Laravel's built-in `Illuminate\Auth\Notifications\ResetPassword`) and, if PDF generation is queued, a simple "your PDF is ready" notification (database or broadcast channel — email is unnecessary overhead for this). No other notification needs exist in the approved requirements yet.

### 1.5 File Storage

Handled by Laravel's Filesystem abstraction (`Storage::disk(...)`), not raw file handling. Full design in Section 9.

### 1.6 PDF Generation

Isolated behind a `PdfGeneratorInterface` so the resume rendering logic and the rest of the app never know which concrete engine is in use. Full design in Section 10.

### 1.7 AI Integration Boundary

Not built for MVP (Future tier, FR-040) but the boundary is designed now so it doesn't leak into core logic later. Full design in Section 11.

---
