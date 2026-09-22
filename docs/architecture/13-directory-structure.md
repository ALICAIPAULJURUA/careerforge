# CareerForge — Directory Structure

> Source: CareerForge Complete Technical Architecture v1.0. Companion documents: see /requirements folder for product requirements

## 13. Directory Structure
```
app/
├── Actions/
│   ├── Resume/
│   │   ├── CreateResumeAction.php
│   │   ├── DuplicateResumeAction.php
│   │   ├── AttachResumeItemAction.php
│   │   ├── ReorderResumeItemsAction.php
│   │   └── ApplyResumeOverrideAction.php
│   └── CareerProfile/
│       └── DeleteMasterRecordAction.php   // handles the "referenced by resumes" warning/cascade
├── DataTransferObjects/
│   └── RenderableResume.php
├── Http/
│   ├── Controllers/
│   │   ├── DashboardController.php
│   │   ├── ProfileController.php
│   │   ├── ExperienceController.php
│   │   ├── ... (one per master data type)
│   │   ├── ResumeController.php
│   │   ├── ResumeSectionController.php
│   │   ├── ResumeItemController.php
│   │   ├── ResumeOverrideController.php
│   │   ├── JobAnalyzerController.php
│   │   └── PortfolioController.php        // public-facing
│   └── Requests/
│       ├── StoreExperienceRequest.php
│       ├── UpdateExperienceRequest.php
│       ├── StoreResumeRequest.php
│       ├── ... (mirrors controllers)
├── Livewire/
│   ├── ResumeBuilder/
│   │   ├── SectionManager.php
│   │   ├── ItemSelector.php
│   │   └── StylePanel.php
│   └── CareerProfile/
│       └── (per-section interactive editors, if Livewire is used for these rather than plain controllers)
├── Models/
│   ├── User.php
│   ├── Profile.php
│   ├── Summary.php
│   ├── Experience.php
│   ├── ExperienceAchievement.php
│   ├── Education.php
│   ├── Skill.php
│   ├── Project.php
│   ├── ProjectTechnology.php
│   ├── ProjectImage.php
│   ├── Certification.php
│   ├── Award.php
│   ├── Leadership.php
│   ├── Language.php
│   ├── Reference.php
│   ├── Resume.php
│   ├── ResumeSection.php
│   ├── ResumeItem.php
│   ├── ResumeOverride.php
│   ├── Template.php
│   ├── Theme.php
│   └── PortfolioSettings.php
├── Policies/
│   └── (one per owned model, per Section 8)
├── Services/
│   ├── ResumeRendering/
│   │   └── ResumeRenderingService.php
│   ├── Pdf/
│   │   ├── PdfGeneratorInterface.php
│   │   ├── DompdfGenerator.php
│   │   └── BrowsershotGenerator.php       // added only if/when adopted
│   ├── JobAnalysis/
│   │   └── JobDescriptionAnalyzerService.php
│   └── Ai/                                // added only in the AI phase
│       ├── AiAssistantInterface.php
│       └── AnthropicAiAssistant.php
├── Jobs/
│   └── GenerateResumePdfJob.php           // added only if PDF generation is queued
└── Providers/
    └── PdfServiceProvider.php             // binds PdfGeneratorInterface to config-selected driver

resources/
├── views/
│   ├── templates/
│   │   ├── modern.blade.php
│   │   ├── classic.blade.php
│   │   ├── creative.blade.php
│   │   ├── ats.blade.php
│   │   └── partials/
│   │       ├── section-heading.blade.php
│   │       ├── date-range.blade.php
│   │       └── contact-line.blade.php
│   ├── career-profile/
│   ├── resumes/
│   ├── portfolio/
│   └── auth/
└── css/, js/ (Alpine.js components, Tailwind or plain CSS)

database/
├── migrations/
├── seeders/
│   ├── TemplateSeeder.php
│   └── ThemeSeeder.php
└── factories/
    └── (one per model, for testing)

tests/
├── Feature/
│   ├── ResumeBuilderTest.php
│   ├── ResumeRenderingTest.php
│   ├── AuthorizationTest.php
│   └── PdfExportTest.php
└── Unit/
    └── ResumeRenderingServiceTest.php
```

---
