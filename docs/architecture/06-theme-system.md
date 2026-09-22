# CareerForge — Theme System

> Source: CareerForge Complete Technical Architecture v1.0. Companion documents: see /requirements folder for product requirements

## 6. Theme System
### 6.1 Principle
No colors, fonts, or spacing values are hardcoded in any Blade template. Every visual property a template needs comes from the `theme` object passed in via `RenderableResume.meta.theme`, expressed as CSS custom properties injected once at the top of the template:

```blade
<style>
  :root {
    --primary-color: {{ $resume['meta']['theme']['primary_color'] }};
    --secondary-color: {{ $resume['meta']['theme']['secondary_color'] }};
    --text-color: {{ $resume['meta']['theme']['text_color'] }};
    --background-color: {{ $resume['meta']['theme']['background_color'] }};
    --sidebar-color: {{ $resume['meta']['theme']['sidebar_color'] }};
    --font-family: '{{ $resume['meta']['theme']['font_family'] }}', sans-serif;
    --heading-size: {{ $resume['meta']['theme']['heading_size_px'] }}px;
    --body-size: {{ $resume['meta']['theme']['body_size_px'] }}px;
  }
</style>
```
Template CSS then exclusively references `var(--primary-color)` etc. This means a new theme preset requires zero template changes, and a new template automatically supports every existing theme preset for free.

### 6.2 What's User-Controlled vs. System-Controlled
- **Theme selection** (`resumes.theme_id`): user picks from seeded presets — this is the "controlled design options" requirement in practice. Presets are seeded via a `ThemeSeeder`, not created ad hoc through the UI in MVP.
- **Font override, photo style, layout, ATS mode, page size**: stored directly on `resumes` (Section 2.5) since these are per-resume choices independent of the theme's own default font — a user can pick the "Classic Blue" theme but override just the font.
- **Section spacing / margins**: for MVP, these are fixed per-template CSS constants, not user-configurable — the brief explicitly says not to allow unrestricted freedom in v1, and spacing is the lowest-value customization to expose first. Revisit in a later version if requested.

### 6.3 Why a Table Instead of an Enum
Colors specifically don't fit cleanly into a PHP enum the way `layout` or `photo_style` do, because a theme is a *bundle* of several correlated values (a set of colors + a font that were chosen to look good together) — storing that bundle as rows in a `themes` table (rather than, say, five separate enum columns on `resumes`) keeps "add a new preset" a data change (seed a row), not a code change.

---
