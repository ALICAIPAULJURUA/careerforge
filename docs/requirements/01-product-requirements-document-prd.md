# CareerForge — Product Requirements Document (PRD)

> Source: CareerForge Complete Requirements Package v1.0. Companion documents: see /architecture folder for technical design

## 1. Product Requirements Document (PRD)
### 1.1 Product Vision
CareerForge treats a person's career history as structured, reusable data rather than a static document. One **Career Profile** feeds any number of purpose-built **Resumes**, each an independent presentation (template, section selection, wording) generated from — but never destructive of — the underlying master data.

### 1.2 Problem Statement
Job seekers maintain multiple, drifting copies of their resume in Word/PDF files. Updating one job or skill means manually editing every version. Formatting professional, ATS-compatible documents is time-consuming and error-prone, and people forget achievements or projects they haven't reused in a while. There is no tool that treats career data as a single source of truth with resumes as generated views over it.

### 1.3 Target Users
- University students building their first resume
- Recent graduates applying broadly with limited experience
- Experienced professionals with long, multi-role histories
- Creative professionals needing visually distinctive resumes alongside ATS-safe versions
- Active job seekers maintaining several role-targeted resumes simultaneously

(Full personas in Section 2.)

### 1.4 User Needs
- A single place to store everything career-related, once
- The ability to produce different resumes for different roles without duplicating data entry
- Confidence that a resume will pass through Applicant Tracking Systems when needed
- Fast, reliable PDF export with professional visual quality
- Assurance that editing a resume never corrupts the master record

### 1.5 Product Goals
1. Eliminate duplicate data entry across resume versions.
2. Make producing a new targeted resume fast (minutes, not hours).
3. Support both visually distinctive and ATS-safe output from the same data.
4. Keep the system understandable and maintainable for a solo/small team (this is also a learning project).

### 1.6 Product Objectives (Measurable)
- A user can go from an empty account to a downloaded PDF resume in under 15 minutes on first use.
- A second resume, built from an already-populated profile, can be created and exported in under 3 minutes.
- Zero incidents of one user's private career data being readable by another user.
- Core resume-builder page interactions (add/remove/reorder section) respond in under 300ms perceived latency.

### 1.7 Success Criteria
- MVP (Section 7) is fully functional end-to-end: register → build profile → build resume → export PDF.
- At least one real resume (yours, per the original brief) is successfully recreated inside the system as a validation case.
- No critical or high-severity security findings in the security requirements checklist (Section 10) at MVP release.

### 1.8 Product Scope
Career data management, resume building with section/record selection, template rendering, PDF export, ATS-oriented output, job description keyword comparison, optional AI-assisted text suggestions, optional public portfolio page. Single-user-owned data; no employer-facing or ATS-for-recruiters features.

### 1.9 Out-of-Scope Features
- Employer/recruiter-facing applicant tracking
- Team or organization accounts / shared editing
- Payments, subscriptions, paywalled features
- Automated job applications or job-board integration
- Resume translation/localization into other languages
- LinkedIn or third-party profile scraping/import
- Real-time collaborative editing

### 1.10 Assumptions
- The user has a modern browser; no legacy browser support required.
- Single-tenant-per-user data model (no shared/organization data).
- Deployment target is a standard LAMP/managed-hosting-style environment unless later specified otherwise (affects PDF engine choice — see Section 12).
- English-only UI and resume content for MVP.

### 1.11 Constraints
- Backend must be Laravel; frontend must stay within Blade/Livewire/Alpine (no React/Vue) per project brief.
- Developer is learning Laravel — architecture must stay approachable, not maximally "enterprise."
- Solo-developer bandwidth — MVP must be genuinely minimal (see Section 7).

### 1.12 Risks
See SRS Section 4 for the full risk register; carried forward and still applicable in full. Highest-priority items remain: master/presentation data coupling (R1), PDF engine lock-in (R2), and AI fabrication risk (R4).

---
