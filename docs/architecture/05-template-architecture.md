# CareerForge — Template Architecture

> Source: CareerForge Complete Technical Architecture v1.0. Companion documents: see /requirements folder for product requirements

## 5. Template Architecture
### 5.1 Goal
`modern.blade.php`, `classic.blade.php`, `creative.blade.php`, and `ats.blade.php` must all be able to render a resume without any of them containing data-fetching, selection, or override-merging logic. Adding `executive.blade.php` later should require **zero changes** outside `resources/views/templates/` and a new `templates` table row.

### 5.2 The Contract
Every template Blade view receives exactly one variable: a `RenderableResume` (a plain array or a lightweight DTO/`Illuminate\Support\Fluent` object — a DTO class, e.g. `App\DataTransferObjects\RenderableResume`, is the cleaner choice for a student-maintainable codebase since it gives IDE autocomplete over raw array keys). Its shape is fixed regardless of resume:

```
RenderableResume
  meta: { name, target_role, template_key, theme, photo_enabled, photo_style, layout, ats_mode, page_size }
  personal: { full_name, title, contact fields, photo_url|null }
  sections: [
    {
      type: 'experience' | 'education' | 'skills' | ... ,
      title: string,               // e.g. "Experience" (or ATS-standard heading)
      items: [
        {
          // fields vary by type, but always pre-merged with overrides already applied
          // e.g. for an experience item: job_title, organization, dates, achievements: [...]
        }
      ]
    },
    ...
  ]
```

Because override-merging already happened in `ResumeRenderingService`, **no template ever queries `resume_overrides` or `resume_items` directly** — templates are purely presentational.

### 5.3 How a Template Is Selected and Rendered
```
ResumeController@preview / @export
  → $data = app(ResumeRenderingService::class)->render($resume)
  → return view("templates.{$resume->template->key}", ['resume' => $data])
```
The controller doesn't know or care which template it's rendering — it resolves the Blade view name from `templates.key`, which is exactly why the `templates` table stores that key rather than the app hardcoding a `match()` statement per template.

### 5.4 Shared Partials
Even though each template is visually distinct, common structural fragments (a section heading renderer, a date-range formatter, a contact-info line) live in `resources/views/templates/partials/` and are `@include`d by each template — this is where actual duplication (not business logic, just markup patterns) is avoided.

### 5.5 ATS Template Specifics
`ats.blade.php` is a template like any other from the architecture's point of view, but by convention it ignores `photo_enabled`/`layout`/theme sidebar colors entirely and always renders single-column, plain-text-forward markup with standard section headings — this satisfies FR-029 without needing an `if (ats_mode)` branch scattered through the other three templates. If `ats_mode` is toggled on a resume, the rendering controller simply resolves to the `ats` template key instead of the resume's normal `template_id` for that specific render — meaning ATS mode temporarily substitutes the template rather than requiring every visual template to also have an ATS branch. **This refines the earlier assumption slightly: ATS mode is a per-resume toggle, but mechanically it works by swapping which template is used at render time, not by adding conditionals inside the visual templates.**

---
