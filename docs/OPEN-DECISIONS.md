# CareerForge — Open Decisions Log

> This file consolidates every unresolved decision raised across the requirements and architecture documents. Nothing in this project should be built against an item below until it is marked RESOLVED with the owner's chosen answer. This is the single file an implementer (human or AI coding agent) should check before starting any phase that touches an open item.

## From the Requirements Package

1. **Per-resume overrides:** Defaulted to Should-have (not MVP). Confirm or promote to Must-have.
2. **ATS mode mechanism:** Defaulted to a per-resume toggle. (Refined in the Architecture doc: implemented as a template *substitution* at render time — see architecture item 15.4 below, which narrows this question.)
3. **PDF generation: synchronous vs. queued:** Depends on item 4 below.
4. **PDF engine choice (Dompdf vs. Browsershot):** Recommended default is Dompdf for MVP. **STATUS: still open — see architecture item 15.1, this is the same decision, repeated because it blocks multiple phases.**
5. **Deleted-record handling:** Recommended default is "warn + cascade-remove the resume_items reference," not a hard block. Confirm.
6. **Public portfolio slug uniqueness scope:** Defaulted to globally unique, user-chosen slugs.
7. **AI provider and scope:** Deferred to Future tier (not in MVP scope at all).
8. **References model fields:** Defaulted to Name, Relationship, Organization, Email, Phone. Confirm.
9. **Job description analyzer approach:** Defaulted to simple keyword/skill matching (no AI dependency) for its Should-have tier.

## From the Technical Architecture

10. **PDF engine (same as item 4):** Recommendation is Dompdf for MVP, given hosting simplicity for a learner context. **This is the single highest-priority open decision — it affects Phase 7, the Jobs/Queues architecture, and template CSS authoring constraints (Dompdf requires a restricted CSS subset).**
11. **Queued vs. synchronous PDF export:** Direct consequence of item 10.
12. **`RenderableResume` as a DTO class vs. plain array:** Recommendation is a DTO class for IDE support given the developer is learning Laravel. Confirm.
13. **Delete-with-reference behavior (same as item 5):** Recommendation is warn + cascade-remove.
14. **Admin capability:** Not currently scoped into any development phase. Confirm whether needed at all, and if so, whether `users.is_admin` should be added as early as Phase 0.

## How to Handle Open Items During Implementation

- If a phase's work depends on an item still marked open above, the recommended default stated in that item should be used **unless the project owner has since resolved it explicitly in conversation or in this file**.
- Any implementer (including an AI coding agent) that encounters a genuinely new ambiguity not covered above should add it to this file rather than silently choosing an interpretation, and should flag it to the project owner before proceeding on that specific piece of work.

## Resolved / Operational Decisions (post-Phase 8)

### 15. Destructive artisan commands must not wipe the real local SQLite file (2026-09-22 — RESOLVED)

**Context.** After the Projects fix (`190ca24` — nullable `technologies.*.name`), a manual `migrate:fresh`/`db:wipe` run against the real `database/database.sqlite` silently replaced all real user rows (and all career data: profiles, experiences, educations, skills, projects, resumes) with factory/seed data (see diagnosis 2026-09-22). `php artisan test` was already correctly isolated:

```xml
<!-- phpunit.xml:20-26 — confirmed correct, do not change without review -->
<env name="APP_ENV" value="testing"/>
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE" value=":memory:"/>
```

That config ensures `php artisan test` uses an in-memory SQLite DB, never the file at `database/database.sqlite` (`config/database.php:37` → `env('DB_DATABASE', database_path('database.sqlite'))`). Real data is therefore safe during `php artisan test`.

**Decision.**

1. **Guard in code — `app/Providers/AppServiceProvider.php:31`.** Any manual `migrate:fresh`, `migrate:reset`, `migrate:refresh`, `migrate:rollback`, or `db:wipe` run **outside** the `testing` + `:memory:` context now requires an explicit `--force` flag. Without it the command aborts with exit 1 and prints:

   ```
   Refusing to run "migrate:fresh" without --force.
   WARNING: "migrate:fresh" targets the real local database "…/database/database.sqlite" (connection "sqlite", APP_ENV="local").
   Re-run with --force to confirm you have a backup and intend to wipe "…".
   ```

   With `--force` outside testing, the same warning naming the resolved `DB_DATABASE` path is still printed before execution continues, so the target is never an accidental keystroke. In `APP_ENV=testing` the guard is bypassed so `RefreshDatabase` / `php artisan test` remain frictionless.

2. **Backup discipline for local dev.** Before any further destructive-command testing against the real file, the local dev database **must** be backed up. Accepted forms:
   - `Copy-Item database/database.sqlite database/database.sqlite.bak` (or `cp`) refreshed periodically — `database/.gitignore:1` (`*.sqlite*`) already git-ignores `*.bak`, so the backup never commits.
   - Or a committed seed/backup script that can reconstruct representative data without depending on the file backup.
   - Never commit the real `database.sqlite` itself; never run a destructive command without verifying the backup exists.

3. **If a schema fix is needed, it must be a new additive migration only** — never `migrate:rollback`/`migrate:fresh`/`db:wipe` against the real DB. Rollbacks are only safe in `:memory:` tests.

**Alternatives considered.** Git pre-hook, shell alias, or documentation-only warning — rejected because they do not survive across machines/shells. In-app `CommandStarting` guard travels with the repo and fails closed.

**Verification.** `php artisan migrate:fresh` (no flag) → abort 1 + warning naming DB; `php artisan migrate:fresh --force` → warning + proceeds; `php artisan test` → 136 tests pass unchanged, still uses `:memory:` (see `php artisan test --filter` output).

**Owner action if data was already wiped.** Do not reconstruct by guessing / re-seeding `users`. Restore `database.sqlite` / `database.sqlite.bak` from a pre-`2026-09-22T09:26:00Z` backup; if no backup exists, treat as data loss and re-create accounts via normal registration / `FR-003` password-reset flow — never `UPDATE users SET password=…` manually.
