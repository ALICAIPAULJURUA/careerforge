# CareerForge — PDF Architecture

> Source: CareerForge Complete Technical Architecture v1.0. Companion documents: see /requirements folder for product requirements

## 10. PDF Architecture
### 10.1 Separation of Concerns
```
PdfGeneratorInterface
    generate(string $html): string  // returns binary PDF content or a storage path

DompdfGenerator implements PdfGeneratorInterface   // Option A
BrowsershotGenerator implements PdfGeneratorInterface  // Option B
```
`ResumeController@export` calls `app(PdfGeneratorInterface::class)->generate($renderedHtml)` — it has no idea which engine is bound. The concrete implementation is chosen in a service provider binding (`config('pdf.driver')`), so swapping engines later is a one-line config change plus writing the new class, not a rewrite of controller/business logic.

### 10.2 The Two Real Options (Decision Still Needed — carried over from prior documents)
| | Dompdf | Browsershot |
|---|---|---|
| CSS support | Limited (no flexbox/grid, weak on modern layout) | Full (real Chrome rendering engine) |
| Server requirements | Pure PHP, no extra dependencies | Requires Node.js + Puppeteer/headless Chromium installed on the server |
| Speed | Fast, synchronous-friendly | Slower, benefits from a queued Job |
| Hosting fit | Works on almost any shared/managed PHP host | Needs a host that permits installing/running Chromium (VPS, Docker, or specific managed platforms) |
| Visual fidelity for "Modern/Creative" templates | Templates must be deliberately written in a Dompdf-safe CSS subset (table-based layouts, avoid flexbox) | Templates can use normal modern CSS freely |

**Recommendation for a Laravel-student, portfolio-hosting context:** start with **Dompdf** for MVP — it removes a whole class of deployment risk while you're learning, and the four MVP templates can be authored within its CSS subset. Revisit Browsershot post-MVP if visual fidelity becomes a real constraint once you have real hosting decided. This decision determines whether Section 1.3's Jobs/Queues are needed at all for MVP (Dompdf likely doesn't need a queue; Browsershot likely does).

### 10.3 Generation Flow
```
1. ResumeRenderingService::render($resume)         → RenderableResume DTO
2. View::make("templates.{$key}", [...])->render() → raw HTML string (same HTML used for the in-app Preview)
3. PdfGeneratorInterface::generate($html)           → PDF bytes
4. Response::streamDownload(...) or queued Job + Notification, per engine choice
```
Using the *exact same* rendered HTML for both the live in-browser Preview and the PDF export (rather than maintaining two separate rendering paths) guarantees the PDF always matches what the user previewed — a common and easily-avoided source of bugs in resume builders.

---
