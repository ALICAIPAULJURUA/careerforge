# CareerForge — Traceability Matrix (Representative Sample)

> Source: CareerForge Complete Requirements Package v1.0. Companion documents: see /architecture folder for technical design

## 11. Traceability Matrix (Representative Sample)
Full coverage follows this pattern for all Functional Requirements; the rows below illustrate the method rather than repeating all 40 FRs exhaustively.

| Business Requirement | Functional Requirement | User Story | Acceptance Criteria | Test Case |
|---|---|---|---|---|
| Users must be able to create an account | FR-001 | US-001 | AC (US-001) — register success/duplicate email | TC-AUTH-01: register with valid data succeeds; TC-AUTH-02: duplicate email rejected |
| Users must be able to securely log in | FR-002 | US-002 | AC (US-002) | TC-AUTH-03: valid login succeeds; TC-AUTH-04: invalid login rejected and rate-limited |
| Users must record work history with selectable detail | FR-008, FR-009 | US-008, US-009, US-011 | — | TC-EXP-01: create experience; TC-EXP-02: add/edit/delete achievement; TC-EXP-03: edit/delete experience |
| A resume must show only chosen data, not everything | FR-021, FR-022 | US-020, US-021 | AC (US-021) | TC-RES-01: enabling/disabling sections reflects in preview; TC-RES-02: selecting 3 of 6 achievements renders exactly those 3, in order |
| Master data must never be altered by resume-specific edits | FR-024 | US-024 | AC (US-024) | TC-RES-03: override on one resume does not affect another resume or the master record |
| Users must be able to export a professional PDF | FR-031 | US-027 | AC (US-027) | TC-PDF-01: successful export downloads a valid PDF; TC-PDF-02: simulated engine failure shows error, no file offered |
| Users must be able to reuse a resume as a starting point for a new one | FR-032 | US-028 | AC (US-028) | TC-RES-04: duplicate produces independent copy; edits to copy don't affect original |
| A safe, ATS-oriented output must be available | FR-029 | US-026 | AC (US-026) | TC-ATS-01: ATS mode strips photo/columns and uses standard headings |
| Users must be able to gauge fit against a specific job posting | FR-034, FR-035, FR-036 | US-029 | AC (US-029) | TC-JDA-01: known-matching keywords are categorized "Matched"; known-absent skills categorized "Missing"; no fabricated experience appears in output |
| Users must control what becomes public | FR-037, FR-038, FR-039 | US-030, US-031 | AC (US-030/031) | TC-PUB-01: new account defaults to private; TC-PUB-02: only explicitly-included fields appear at public URL |
| No user may access another user's private data | NFR-001 | (cross-cutting) | — | TC-SEC-01: attempt to load another user's resume by ID manipulation returns 403/404 for every authenticated endpoint |

---
