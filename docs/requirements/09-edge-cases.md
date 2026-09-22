# CareerForge — Edge Cases

> Source: CareerForge Complete Requirements Package v1.0. Companion documents: see /architecture folder for technical design

## 9. Edge Cases
| Scenario | Expected Handling |
|---|---|
| User has no experience entries | Experience section is simply unavailable/empty in the resume builder; resume can still be created from Education/Skills/Projects alone |
| User has no photo | Photo-enabled templates render a placeholder or gracefully omit the photo slot; no broken layout |
| User has many experiences (e.g., 15+) | Resume builder record-selection UI must support scrolling/searching, not just a flat unpaginated list |
| Very long experience description/achievement text | Input has a sensible max length (defined at implementation); templates truncate or wrap gracefully, never breaking PDF layout |
| Missing end date without "current" flag | Validation requires either an end date or "current" checked; cannot save an ambiguous open-ended non-current record |
| Deleted career record still referenced by a resume | Soft-delete master records referenced by any resume_item, OR on hard delete, cascade-remove the corresponding resume_item and show the user a warning listing affected resumes before confirming deletion |
| User deletes a skill used by several resumes | Same as above — deletion triggers a warning listing every resume that references it, requiring explicit confirmation |
| PDF generation failure (timeout/engine error) | User sees a clear, specific error message; no partial/corrupt file is ever offered for download; failure is logged for diagnosis |
| Unsupported image upload (wrong file type) | Rejected at validation with a clear message before any file touches storage |
| Very large image upload | Rejected or auto-resized/compressed server-side before storage; hard upper size limit enforced (e.g., 5MB) |
| Invalid input generally | Server-side Form Request validation is authoritative; client-side validation is a UX convenience only, never trusted alone |
| Unauthorized access attempt (e.g., editing another user's resume via URL/ID manipulation) | Policy-based authorization returns 403/404 (prefer 404 to avoid confirming record existence) and the attempt is logged |

---
