# CareerForge — Security Requirements

> Source: CareerForge Complete Requirements Package v1.0. Companion documents: see /architecture folder for technical design

## 10. Security Requirements
- **Authentication:** Laravel's built-in hashing (bcrypt/argon2) for passwords; no custom crypto. Session-based auth for the web app.
- **Authorization:** Every model action (view/edit/delete) is checked through a Laravel Policy tied to the authenticated user; route-model binding used to prevent ID-guessing from bypassing checks.
- **CSRF:** Laravel's default CSRF middleware enabled on all state-changing forms/Livewire actions; never disabled for convenience.
- **XSS:** All user-supplied text is output through Blade's default escaping (`{{ }}`); any rich-text fields (if introduced later) must be sanitized server-side before storage or render.
- **SQL Injection:** Exclusively Eloquent/query builder with parameter binding; no raw string-concatenated queries.
- **Mass Assignment:** Explicit `$fillable` (not blanket `$guarded = []`) on every model; sensitive fields (user_id, ownership) never accepted from request input, always set server-side from the authenticated user.
- **File Upload Security:** Server-side MIME-type validation (not trusting client extension), re-encoding of images on upload, randomized storage filenames, storage outside the public webroot unless a file is explicitly meant to be public.
- **Image Validation:** Enforced max dimensions and file size; rejected files never reach permanent storage.
- **Password Security:** Minimum complexity rules enforced via Form Request validation; reset tokens are single-use and time-limited (Laravel default behavior).
- **Session Security:** Session regenerated on login; secure/httponly cookies in production; session invalidated on logout.
- **Data Privacy:** Career data private by default; no cross-user data exposure through any endpoint, including PDF generation and previews.
- **Public Portfolio Privacy:** Explicit opt-in only; explicit per-field allow-list (never "public unless marked private"); slug should not be sequential/guessable in a way that exposes unpublished portfolios (only published ones are ever reachable, but slugs should still be validated as intentional choices, not auto-incrementing IDs).
- **Rate Limiting:** Login, registration, and password-reset endpoints rate-limited via Laravel's throttle middleware; job description analyzer and any AI-backed endpoint also rate-limited to control cost and abuse.
- **AI Data Privacy:** If/when AI assistance (FR-040) is implemented, only the minimum necessary text (e.g., a single achievement or summary) is sent to the AI provider — never the user's full profile — and this must be disclosed to the user in-product before first use.

---
