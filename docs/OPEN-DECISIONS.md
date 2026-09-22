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
