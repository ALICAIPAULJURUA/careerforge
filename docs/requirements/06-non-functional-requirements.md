# CareerForge — Non-Functional Requirements

> Source: CareerForge Complete Requirements Package v1.0. Companion documents: see /architecture folder for technical design

## 6. Non-Functional Requirements
| ID | Category | Requirement | Measure |
|---|---|---|---|
| NFR-001 | Security | All authenticated endpoints enforce per-user authorization (no IDOR); passwords hashed with bcrypt/argon2; login/reset endpoints rate-limited | 0 unauthorized cross-user data access incidents; max 5 login attempts per minute per IP before throttling |
| NFR-002 | Performance | Resume builder interactions (add/remove/reorder) render within perceptible-instant time; PDF generation completes or is queued within a bounded time | UI interaction < 300ms; PDF generation < 10s synchronous, or queued with status feedback if longer |
| NFR-003 | Availability | Application recovers from PDF-engine or AI-provider failures without data loss | Failed PDF/AI operations never corrupt or partially save resume state |
| NFR-004 | Accessibility | Application UI meets WCAG 2.1 AA for forms, navigation, and interactive controls | Automated accessibility check (e.g., axe) passes with no critical violations on core screens |
| NFR-005 | Maintainability | Business logic isolated from controllers/views (Actions/Services pattern); templates share one data contract | New template addable without modifying resume data-fetching logic |
| NFR-006 | Scalability | Queries for resume rendering avoid N+1 patterns even with large profiles | Rendering a resume with 20+ experiences/achievements issues a bounded, eager-loaded query set, not one query per record |
| NFR-007 | Privacy | Career data is private by default; public portfolio requires explicit opt-in and explicit per-field inclusion | Default state for every new user: portfolio disabled, zero fields marked public |
| NFR-008 | Usability | First-time users can complete profile + first resume + PDF export without external help | ≥ 90% task completion in informal usability testing with 1 sample user (you) per the SRS objective in Section 1.6 |

---
