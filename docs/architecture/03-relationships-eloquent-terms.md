# CareerForge — Relationships (Eloquent Terms)

> Source: CareerForge Complete Technical Architecture v1.0. Companion documents: see /requirements folder for product requirements

## 3. Relationships (Eloquent Terms)
- `User` **hasOne** `Profile`; `Profile` **belongsTo** `User`.
- `User` **hasOne** `PortfolioSettings`.
- `User` **hasMany** `Summary`, `Experience`, `Education`, `Skill`, `Project`, `Certification`, `Award`, `Leadership`, `Language`, `Reference`, `Resume`.
- `Experience` **hasMany** `ExperienceAchievement`; `ExperienceAchievement` **belongsTo** `Experience`.
- `Project` **hasMany** `ProjectTechnology` and **hasMany** `ProjectImage`.
- `Resume` **belongsTo** `User`, **belongsTo** `Template`, **belongsTo** `Theme`.
- `Resume` **hasMany** `ResumeSection`; `ResumeSection` **belongsTo** `Resume`.
- `ResumeSection` **hasMany** `ResumeItem`; `ResumeItem` **belongsTo** `ResumeSection` and **morphTo** `itemable` (polymorphic — resolves to Experience, ExperienceAchievement, Education, Skill, Project, Certification, Award, Leadership, Language, or Reference).
- `Resume` **hasMany** `ResumeOverride`; `ResumeOverride` **belongsTo** `Resume` and **morphTo** `overridable`.
- Any master model (Experience, Skill, etc.) can define **morphMany** `ResumeItem::class, 'itemable'` if you need "which resumes reference this record" lookups (used for the delete-warning edge case).
- `Template` **hasMany** `Resume`; `Theme` **hasMany** `Resume`. These are effectively many-to-one from Resume's side — many resumes can share one template/theme, so no pivot table is needed (a resume has exactly one template and one theme at a time).

No genuine many-to-many relationship exists in this schema outside the polymorphic `resume_items`/`resume_overrides` pattern — which is a deliberate simplification: a naive design might reach for `experience_resume` and `skill_resume` pivot tables (a real many-to-many), but the polymorphic single-table approach was chosen specifically to avoid one pivot table per master-data type (see Section 2.1).

---
