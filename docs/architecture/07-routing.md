# CareerForge — Routing

> Source: CareerForge Complete Technical Architecture v1.0. Companion documents: see /requirements folder for product requirements

## 7. Routing
```
// Public (guest) routes
Route::middleware('guest')->group(function () {
    Route::get('/login', ...)->name('login');
    Route::post('/login', ...);
    Route::get('/register', ...)->name('register');
    Route::post('/register', ...);
    Route::get('/forgot-password', ...)->name('password.request');
    Route::post('/forgot-password', ...)->name('password.email');
    Route::get('/reset-password/{token}', ...)->name('password.reset');
    Route::post('/reset-password', ...)->name('password.update');
});

// Public portfolio routes (no auth required, read-only)
Route::get('/u/{slug}', PortfolioController::class . '@show')->name('portfolio.show');

// Authenticated routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::post('/logout', ...)->name('logout');

    Route::get('/dashboard', DashboardController::class . '@index')->name('dashboard');

    // Career profile — resourceful controllers or Livewire full-page components per section
    Route::get('/profile', ProfileController::class . '@edit')->name('profile.edit');
    Route::resource('experiences', ExperienceController::class)->except(['show']);
    Route::resource('experiences.achievements', ExperienceAchievementController::class)
        ->shallow()->except(['show']);
    Route::resource('educations', EducationController::class)->except(['show']);
    Route::resource('skills', SkillController::class)->except(['show']);
    Route::resource('projects', ProjectController::class)->except(['show']);
    Route::resource('certifications', CertificationController::class)->except(['show']);
    Route::resource('awards', AwardController::class)->except(['show']);
    Route::resource('leaderships', LeadershipController::class)->except(['show']);
    Route::resource('languages', LanguageController::class)->except(['show']);
    Route::resource('references', ReferenceController::class)->except(['show']);

    // Resumes
    Route::resource('resumes', ResumeController::class);
    Route::post('/resumes/{resume}/duplicate', ResumeController::class . '@duplicate')->name('resumes.duplicate');
    Route::get('/resumes/{resume}/preview', ResumeController::class . '@preview')->name('resumes.preview');
    Route::post('/resumes/{resume}/export', ResumeController::class . '@export')->name('resumes.export');
    Route::put('/resumes/{resume}/sections/reorder', ResumeSectionController::class . '@reorder')->name('resumes.sections.reorder');
    Route::post('/resumes/{resume}/items', ResumeItemController::class . '@store')->name('resumes.items.store');
    Route::delete('/resume-items/{resumeItem}', ResumeItemController::class . '@destroy')->name('resume-items.destroy');
    Route::put('/resume-items/reorder', ResumeItemController::class . '@reorder')->name('resume-items.reorder');
    Route::put('/resumes/{resume}/overrides', ResumeOverrideController::class . '@update')->name('resumes.overrides.update');

    // Job description analyzer
    Route::get('/job-analyzer', JobAnalyzerController::class . '@create')->name('job-analyzer.create');
    Route::post('/job-analyzer', JobAnalyzerController::class . '@analyze')->name('job-analyzer.analyze');

    // Public portfolio settings (distinct from the public-facing /u/{slug} route above)
    Route::get('/portfolio-settings', PortfolioSettingsController::class . '@edit')->name('portfolio-settings.edit');
    Route::put('/portfolio-settings', PortfolioSettingsController::class . '@update')->name('portfolio-settings.update');
});

// Admin routes — not built for MVP; namespaced and gated behind an 'admin' middleware
// if/when template management or moderation tooling is introduced (Requirements Package, open item re: admin role).
```

Livewire full-page components can sit behind these same route definitions (`Route::get(...)->name(...)` returning a Livewire component instead of a controller method) — the routing structure doesn't change based on that implementation choice.

---
