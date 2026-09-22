# CareerForge — Authorization

> Source: CareerForge Complete Technical Architecture v1.0. Companion documents: see /requirements folder for product requirements

## 8. Authorization
Every model with per-user ownership gets a Policy. Pattern is identical across all of them:

```
class ExperiencePolicy {
    public function view(User $user, Experience $experience): bool {
        return $experience->user_id === $user->id;
    }
    public function update(User $user, Experience $experience): bool { return $this->view($user, $experience); }
    public function delete(User $user, Experience $experience): bool { return $this->view($user, $experience); }
}
```

**Policies required:** `ProfilePolicy`, `SummaryPolicy`, `ExperiencePolicy`, `ExperienceAchievementPolicy` (checks via parent experience's `user_id`), `EducationPolicy`, `SkillPolicy`, `ProjectPolicy`, `CertificationPolicy`, `AwardPolicy`, `LeadershipPolicy`, `LanguagePolicy`, `ReferencePolicy`, `ResumePolicy`, `ResumeSectionPolicy` (via parent resume), `ResumeItemPolicy` (via parent resume section → resume), `ResumeOverridePolicy` (via parent resume), `PortfolioSettingsPolicy`.

**Enforcement points:**
- Controllers call `$this->authorize('update', $experience)` (or Livewire components call `$this->authorize(...)` in `mount()`/actions).
- Route-model binding ensures `$experience` is a real model instance before the policy even runs — combined, an attempt to access another user's `experiences/5` returns 403/404 before any data is exposed, satisfying NFR-001 and the "unauthorized access" edge case.
- The public portfolio route (`/u/{slug}`) is the one deliberate exception — it has no policy check because it's meant to be publicly readable, but it must **only** ever query `portfolio_settings` where `is_published = true`, and must filter the underlying profile data through `included_sections` — this is a data-filtering concern in the controller/service, not a policy, since there's no authenticated user to check ownership against.
- Admin routes (if/when built) would use a separate `admin` middleware checking a `users.is_admin` flag, not a Policy, since admin capability isn't about owning a specific record.

---
