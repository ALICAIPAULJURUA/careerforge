# CareerForge — File Storage

> Source: CareerForge Complete Technical Architecture v1.0. Companion documents: see /requirements folder for product requirements

## 9. File Storage
### 9.1 What's Stored
- Profile photo (one per user)
- Project images (optional, multiple per project, Should/Could tier)
- Generated PDFs — **not stored by default**; generated on demand and streamed to the user, to avoid storage growth and staleness (a PDF generated from an old resume state shouldn't linger). If US-027's "queued generation" path is used, the PDF is stored temporarily and deleted after download/expiry, not kept indefinitely.

### 9.2 Validation
- **Allowed MIME types (images):** `image/jpeg`, `image/png`, `image/webp` only — enforced via Laravel's `mimes:`/`mimetypes:` validation rule in the relevant Form Request, checked against actual file content, not the client-supplied extension.
- **File size limits:** profile photo max 2MB; project images max 5MB each, max 6 images per project (soft cap enforced at the Form Request/Action level).
- **Dimension handling:** images are re-encoded and resized server-side on upload (e.g., via Intervention Image) to a sane max dimension (e.g., 1200px longest side) before storage — this both normalizes file size and strips embedded EXIF/metadata as a privacy/security measure.
- **Filename strategy:** stored filenames are randomly generated (e.g., UUID + original extension), never the user-supplied filename, avoiding path traversal and collision issues entirely.

### 9.3 Storage Strategy
- **Disk:** a dedicated `private` disk (local `storage/app/private` in development, S3-compatible bucket with private ACLs in production) for all uploaded images — **not** the `public` disk directly.
- **Serving images:** a signed, time-limited URL (`Storage::temporaryUrl(...)` on S3, or a controller route that checks the Policy before streaming the file for local disk) — this ensures a photo isn't accessible just because someone guesses/finds its storage path, and lets private profile photos stay private even though they're "just an image."
- **Public portfolio exception:** when a user publishes their portfolio with photo included, the public portfolio controller is the one place permitted to generate a public-readable (but still signed/expiring, or served through a dedicated public-image proxy route) URL for that specific photo — access is still mediated by checking `portfolio_settings.is_published` and `included_sections`, not by making the file world-readable outright.

---
