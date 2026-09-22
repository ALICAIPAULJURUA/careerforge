# CareerForge — Database Design

> Source: CareerForge Complete Technical Architecture v1.0. Companion documents: see /requirements folder for product requirements

## 2. Database Design
Every table below states purpose, and — per your instruction — the requested table list has been evaluated rather than adopted wholesale. See the "Necessity Analysis" callouts.

### 2.1 Necessity Analysis (requested tables vs. what's actually built)

| Requested table | Decision | Reasoning |
|---|---|---|
| `skill_categories` | **Not a separate table.** Use an `ENUM`/string column `category` on `skills`. | The category set (Technical, IT, Software/Tools, Soft, Digital Media, Leadership, Other) is small, fixed, and not user-extended in the approved requirements. A lookup table would add a join for no real benefit. If custom categories become a real need later, this is a cheap migration to revisit. |
| `project_technologies` | **Kept, but as a simple child table**, not a shared/normalized tags table. | Technologies differ per project and aren't currently required to be individually selectable per resume (unlike achievements), so a lightweight `project_technologies(project_id, name, order)` table is enough — simpler than a many-to-many tag system, and still normalized enough to query/display cleanly. |
| `resume_experience`, `resume_education`, `resume_skills`, `resume_projects` (one pivot per type) | **Replaced by a single polymorphic `resume_items` table.** | Four-plus near-identical pivot tables (one per master-data type, and more if certifications/awards/leadership/languages are also selectable) is exactly the kind of duplication the whole product concept is designed to avoid. A single polymorphic table (`itemable_type`, `itemable_id`) lets `ResumeRenderingService` and the resume-builder UI use one consistent pattern for "which records are selected, in what order" across every section type — including ones added later — without a schema change. |
| `themes` | **Kept, but as curated presets**, not free-form per-user themes. | The requirements explicitly call for "controlled design options first," not unrestricted customization. A `themes` table of a handful of admin/seed-defined presets (colors, fonts, spacing) is a lookup table users pick from, not a place where arbitrary CSS is stored. |
| `media`/`files` (generic polymorphic media table) | **Not built for MVP.** | Only two concrete file-upload needs exist in approved scope: one profile photo (single, 1:1 — a column is enough) and optional project images (Should/Could tier — a small dedicated `project_images` table if/when built). A generic Spatie-style polymorphic media table is genuine future-proofing but is unjustified complexity until a third or fourth file type shows up. |
| `references` | **Kept**, structured, per Requirements Package assumption (open item #8, still pending your final field confirmation). | |

### 2.2 Core User & Profile Tables

**`users`**
| Column | Type | Nullable | Default | Index/FK | Purpose |
|---|---|---|---|---|---|
| id | bigint unsigned, PK | no | auto | PK | |
| name | varchar(255) | no | — | | Display name |
| email | varchar(255) | no | — | unique | Login identifier |
| email_verified_at | timestamp | yes | null | | |
| password | varchar(255) | no | — | | Hashed |
| remember_token | varchar(100) | yes | null | | |
| created_at / updated_at | timestamp | yes | null | | |

**`profiles`**
| Column | Type | Nullable | Default | Index/FK | Purpose |
|---|---|---|---|---|---|
| id | bigint unsigned, PK | no | auto | PK | |
| user_id | bigint unsigned | no | — | unique FK → users.id, cascade delete | 1:1 with user |
| full_name | varchar(255) | yes | null | | Overrides users.name for display if set |
| professional_title | varchar(255) | yes | null | | |
| phone | varchar(50) | yes | null | | |
| location | varchar(255) | yes | null | | |
| website_url | varchar(255) | yes | null | | |
| linkedin_url | varchar(255) | yes | null | | |
| github_url | varchar(255) | yes | null | | |
| photo_path | varchar(255) | yes | null | | Storage disk path, not public URL |
| created_at / updated_at | timestamp | yes | null | | |

**`summaries`**
| Column | Type | Nullable | Default | Index/FK | Purpose |
|---|---|---|---|---|---|
| id | bigint unsigned, PK | no | auto | PK | |
| user_id | bigint unsigned | no | — | FK → users.id, cascade delete, index | |
| label | varchar(100) | no | — | | e.g. "IT Support summary" |
| content | text | no | — | | |
| created_at / updated_at | timestamp | yes | null | | |

### 2.3 Master Career Data Tables

**`experiences`**
| Column | Type | Nullable | Default | Index/FK | Purpose |
|---|---|---|---|---|---|
| id | bigint unsigned, PK | no | auto | PK | |
| user_id | bigint unsigned | no | — | FK → users.id, cascade delete, index | |
| job_title | varchar(255) | no | — | | |
| organization | varchar(255) | no | — | | |
| location | varchar(255) | yes | null | | |
| start_date | date | no | — | | |
| end_date | date | yes | null | | Null when is_current = true |
| is_current | boolean | no | false | | |
| description | text | yes | null | | Free-text role summary |
| created_at / updated_at | timestamp | yes | null | | |

**`experience_achievements`**
| Column | Type | Nullable | Default | Index/FK | Purpose |
|---|---|---|---|---|---|
| id | bigint unsigned, PK | no | auto | PK | |
| experience_id | bigint unsigned | no | — | FK → experiences.id, cascade delete, index | |
| content | text | no | — | | One bullet point |
| sort_order | smallint unsigned | no | 0 | | Master ordering (resume-specific order is separate — see 2.5) |
| created_at / updated_at | timestamp | yes | null | | |

**`educations`**
| Column | Type | Nullable | Default | Index/FK | Purpose |
|---|---|---|---|---|---|
| id | bigint unsigned, PK | no | auto | PK | |
| user_id | bigint unsigned | no | — | FK → users.id, cascade delete, index | |
| institution | varchar(255) | no | — | | |
| qualification | varchar(255) | no | — | | |
| field_of_study | varchar(255) | yes | null | | |
| start_date | date | no | — | | |
| end_date | date | yes | null | | |
| is_current | boolean | no | false | | |
| description | text | yes | null | | |
| created_at / updated_at | timestamp | yes | null | | |

**`skills`**
| Column | Type | Nullable | Default | Index/FK | Purpose |
|---|---|---|---|---|---|
| id | bigint unsigned, PK | no | auto | PK | |
| user_id | bigint unsigned | no | — | FK → users.id, cascade delete, index | |
| name | varchar(150) | no | — | | |
| category | enum('technical','it','software_tools','soft','digital_media','leadership','other') | no | 'other' | index | See 2.1 |
| proficiency | tinyint unsigned | yes | null | | Optional 1–5 scale, Could-tier |
| created_at / updated_at | timestamp | yes | null | | |

**`projects`**
| Column | Type | Nullable | Default | Index/FK | Purpose |
|---|---|---|---|---|---|
| id | bigint unsigned, PK | no | auto | PK | |
| user_id | bigint unsigned | no | — | FK → users.id, cascade delete, index | |
| name | varchar(255) | no | — | | |
| description | text | yes | null | | |
| role | varchar(255) | yes | null | | |
| start_date | date | yes | null | | |
| end_date | date | yes | null | | |
| project_url | varchar(255) | yes | null | | |
| github_url | varchar(255) | yes | null | | |
| created_at / updated_at | timestamp | yes | null | | |

**`project_technologies`**
| Column | Type | Nullable | Default | Index/FK | Purpose |
|---|---|---|---|---|---|
| id | bigint unsigned, PK | no | auto | PK | |
| project_id | bigint unsigned | no | — | FK → projects.id, cascade delete, index | |
| name | varchar(100) | no | — | | e.g. "Laravel" |
| sort_order | smallint unsigned | no | 0 | | |

**`project_images`** *(Should/Could tier, not MVP-blocking)*
| Column | Type | Nullable | Default | Index/FK | Purpose |
|---|---|---|---|---|---|
| id | bigint unsigned, PK | no | auto | PK | |
| project_id | bigint unsigned | no | — | FK → projects.id, cascade delete, index | |
| path | varchar(255) | no | — | | Storage disk path |
| caption | varchar(255) | yes | null | | |
| sort_order | smallint unsigned | no | 0 | | |

**`certifications`**
| Column | Type | Nullable | Default | Index/FK | Purpose |
|---|---|---|---|---|---|
| id | bigint unsigned, PK | no | auto | PK | |
| user_id | bigint unsigned | no | — | FK → users.id, cascade delete, index | |
| name | varchar(255) | no | — | | |
| issuing_organization | varchar(255) | no | — | | |
| issue_date | date | yes | null | | |
| expiry_date | date | yes | null | | |
| credential_id | varchar(255) | yes | null | | |
| credential_url | varchar(255) | yes | null | | |
| created_at / updated_at | timestamp | yes | null | | |

**`awards`**
| Column | Type | Nullable | Default | Index/FK | Purpose |
|---|---|---|---|---|---|
| id | bigint unsigned, PK | no | auto | PK | |
| user_id | bigint unsigned | no | — | FK → users.id, cascade delete, index | |
| name | varchar(255) | no | — | | |
| organization | varchar(255) | yes | null | | |
| date | date | yes | null | | |
| description | text | yes | null | | |
| created_at / updated_at | timestamp | yes | null | | |

**`leaderships`**
| Column | Type | Nullable | Default | Index/FK | Purpose |
|---|---|---|---|---|---|
| id | bigint unsigned, PK | no | auto | PK | |
| user_id | bigint unsigned | no | — | FK → users.id, cascade delete, index | |
| position | varchar(255) | no | — | | |
| organization | varchar(255) | no | — | | |
| start_date | date | yes | null | | |
| end_date | date | yes | null | | |
| is_current | boolean | no | false | | |
| responsibilities | text | yes | null | | |
| created_at / updated_at | timestamp | yes | null | | |

**`languages`**
| Column | Type | Nullable | Default | Index/FK | Purpose |
|---|---|---|---|---|---|
| id | bigint unsigned, PK | no | auto | PK | |
| user_id | bigint unsigned | no | — | FK → users.id, cascade delete, index | |
| name | varchar(100) | no | — | | |
| proficiency | varchar(50) | yes | null | | e.g. "Native", "Conversational" — free text, not enum, since proficiency frameworks vary |
| created_at / updated_at | timestamp | yes | null | | |

**`references`** *(Could tier — pending your field confirmation, Requirements Package item #8)*
| Column | Type | Nullable | Default | Index/FK | Purpose |
|---|---|---|---|---|---|
| id | bigint unsigned, PK | no | auto | PK | |
| user_id | bigint unsigned | no | — | FK → users.id, cascade delete, index | |
| name | varchar(255) | no | — | | |
| relationship | varchar(150) | yes | null | | |
| organization | varchar(255) | yes | null | | |
| email | varchar(255) | yes | null | | |
| phone | varchar(50) | yes | null | | |
| created_at / updated_at | timestamp | yes | null | | |

### 2.4 Template & Theme Tables

**`templates`**
| Column | Type | Nullable | Default | Index/FK | Purpose |
|---|---|---|---|---|---|
| id | bigint unsigned, PK | no | auto | PK | |
| key | varchar(50) | no | — | unique | Matches Blade view name, e.g. `modern` |
| name | varchar(100) | no | — | | Display name |
| description | varchar(255) | yes | null | | |
| supports_photo | boolean | no | true | | Used to gray out photo toggle for ATS-only templates |
| is_active | boolean | no | true | | Allows retiring a template without deleting history |
| created_at / updated_at | timestamp | yes | null | | |

**`themes`**
| Column | Type | Nullable | Default | Index/FK | Purpose |
|---|---|---|---|---|---|
| id | bigint unsigned, PK | no | auto | PK | |
| name | varchar(100) | no | — | | e.g. "Classic Blue" |
| primary_color | varchar(7) | no | — | | Hex |
| secondary_color | varchar(7) | no | — | | Hex |
| text_color | varchar(7) | no | — | | Hex |
| background_color | varchar(7) | no | — | | Hex |
| sidebar_color | varchar(7) | yes | null | | Hex, only relevant for sidebar layouts |
| font_family | varchar(100) | no | 'Inter' | | Constrained list enforced at app level, not DB |
| heading_size | varchar(10) | no | 'md' | | Enum-like string: sm/md/lg |
| body_size | varchar(10) | no | 'md' | | |
| is_system | boolean | no | true | | Seeded preset vs. (future) user-created |
| created_at / updated_at | timestamp | yes | null | | |

### 2.5 Resume Presentation Layer (the critical part)

**`resumes`**
| Column | Type | Nullable | Default | Index/FK | Purpose |
|---|---|---|---|---|---|
| id | bigint unsigned, PK | no | auto | PK | |
| user_id | bigint unsigned | no | — | FK → users.id, cascade delete, index | |
| name | varchar(255) | no | — | | e.g. "Junior IT Support Resume" |
| target_role | varchar(255) | yes | null | | |
| template_id | bigint unsigned | no | — | FK → templates.id | |
| theme_id | bigint unsigned | no | — | FK → themes.id | |
| font_override | varchar(100) | yes | null | | Overrides theme's default font if user picks a different one |
| photo_enabled | boolean | no | true | | Ignored at render time if template.supports_photo = false |
| photo_style | enum('none','circle','square','rounded') | no | 'circle' | | |
| layout | enum('one_column','two_column','sidebar') | no | 'one_column' | | Only meaningful for templates that support it |
| ats_mode | boolean | no | false | | Per-resume toggle, per approved assumption |
| page_size | enum('a4','letter') | no | 'a4' | | |
| slug | varchar(255) | yes | null | | Internal reference only, not the public portfolio slug |
| created_at / updated_at | timestamp | yes | null | | |

**`resume_sections`**
| Column | Type | Nullable | Default | Index/FK | Purpose |
|---|---|---|---|---|---|
| id | bigint unsigned, PK | no | auto | PK | |
| resume_id | bigint unsigned | no | — | FK → resumes.id, cascade delete, index | |
| section_type | enum('summary','experience','education','skills','projects','certifications','awards','leadership','languages','references') | no | — | | |
| is_visible | boolean | no | true | | Toggling off hides the section without deleting its item selections |
| sort_order | smallint unsigned | no | 0 | | Determines section order on the rendered resume |
| created_at / updated_at | timestamp | yes | null | | |
| | | | | unique(resume_id, section_type) | A resume has at most one section instance per type |

**`resume_items`** — the polymorphic table that replaces `resume_experience`/`resume_education`/`resume_skills`/`resume_projects` etc.
| Column | Type | Nullable | Default | Index/FK | Purpose |
|---|---|---|---|---|---|
| id | bigint unsigned, PK | no | auto | PK | |
| resume_section_id | bigint unsigned | no | — | FK → resume_sections.id, cascade delete, index | Which section this item belongs to |
| itemable_type | varchar(255) | no | — | index (with itemable_id) | Morph class, e.g. `App\Models\Experience`, `App\Models\ExperienceAchievement`, `App\Models\Skill` |
| itemable_id | bigint unsigned | no | — | index (with itemable_type) | ID of the master record |
| sort_order | smallint unsigned | no | 0 | | Resume-specific order, independent of master `sort_order` |
| created_at / updated_at | timestamp | yes | null | | |
| | | | | unique(resume_section_id, itemable_type, itemable_id) | Prevents duplicate selection of the same record |

*Note on achievements:* an Experience's achievements are selected as their own `resume_items` rows (`itemable_type = ExperienceAchievement`) nested under the same section as the parent Experience `resume_items` row — the `ResumeRenderingService` is responsible for grouping achievement items under their parent experience item at render time using `experience_achievements.experience_id`, rather than the schema trying to express that nesting directly. This keeps `resume_items` a single flat, simple table instead of needing a self-referencing parent_id, which would only be needed for this one case.

**`resume_overrides`** — per-resume field-level text overrides (Should-tier, FR-024)
| Column | Type | Nullable | Default | Index/FK | Purpose |
|---|---|---|---|---|---|
| id | bigint unsigned, PK | no | auto | PK | |
| resume_id | bigint unsigned | no | — | FK → resumes.id, cascade delete, index | |
| overridable_type | varchar(255) | no | — | index (with overridable_id) | Morph class of the master record being overridden |
| overridable_id | bigint unsigned | no | — | index (with overridable_type) | |
| field_name | varchar(100) | no | — | | e.g. `content`, `description` |
| override_value | text | no | — | | |
| created_at / updated_at | timestamp | yes | null | | |
| | | | | unique(resume_id, overridable_type, overridable_id, field_name) | One override per field per record per resume |

### 2.6 Public Portfolio (Could tier)

**`portfolio_settings`**
| Column | Type | Nullable | Default | Index/FK | Purpose |
|---|---|---|---|---|---|
| id | bigint unsigned, PK | no | auto | PK | |
| user_id | bigint unsigned | no | — | unique FK → users.id, cascade delete | 1:1 |
| slug | varchar(255) | yes | null | unique | Public URL segment |
| is_published | boolean | no | false | | Defaults private (NFR-007) |
| included_sections | json | no | '[]' | | Explicit allow-list of section types, per FR-038 |
| created_at / updated_at | timestamp | yes | null | | |

---
