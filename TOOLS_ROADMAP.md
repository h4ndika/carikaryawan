# CariKaryawan Tools - Development Roadmap

**Last Updated:** 2026-10-12 (rebrand dari DISEKITAR; nama kelas `DisekitarAgent` sengaja tidak diganti)

## Status: Completed Tools

### 1. ✅ ATS CV & Resume Builder
- **Route:** `/karir/cv-builder`
- **Status:** Live & Production Ready
- **Features:**
  - Real-time ATS score & 8-item checklist
  - Multi-section form: Personal, Summary, Experience, Education, Skills
  - AI Polish for summary & achievement bullets
  - Live preview with print-ready layout
  - Export: PDF, TXT, Print
  - LocalStorage auto-save
  - Dark mode support
- **Tech Stack:** Livewire, Alpine.js, Tailwind CSS, OpenAI API
- **Model:** `cv-generate` (via `DisekitarAgent`, nama kelas legacy)

### 2. ✅ AI Cover Letter Generator
- **Route:** Modal on job post pages
- **Status:** Live & Production Ready
- **Features:**
  - 1-click generation from job post context
  - Customizable: name, phone, email, experience, skills, tone, language (ID/EN)
  - AI-powered professional cover letter
  - Export: Copy, Download TXT, Print
- **Tech Stack:** Blade Component (Modal), Alpine.js, OpenAI API
- **Model:** `cover-letter-generate`

### 3. ✅ Interview Prep AI
- **Route:** `/karir/interview-prep`
- **Status:** Live & Production Ready
- **Features:**
  - Generate 15-25 interview questions in 4 categories:
    - General (perkenalan, motivasi)
    - Behavioral (STAR method)
    - Technical/Functional
    - Company-Specific
  - Each question has collapsible "Tips Jawab"
  - Customizable: level (fresh/mid/senior), focus, language
  - Right sidebar: STAR Method guide, Interview Tips, Common Mistakes
  - Export: Copy All, Download TXT, Print/PDF
- **Tech Stack:** Livewire, Alpine.js, OpenAI API
- **Model:** `cv-generate`

### 4. ✅ Tools Hub Landing Page
- **Route:** `/tools`
- **Status:** Live & Production Ready
- **Features:**
  - Clean 3-column grid layout
  - 2 active tool cards + 1 placeholder
  - Coming Soon section (3 tools)
  - CTA banner (Kirim Saran Tool)
  - Fully responsive & dark mode

---

## Coming Soon Tools (Roadmap)

### 1. 🔜 Email Writer AI
**Priority:** Medium  
**Estimated Effort:** 2-3 hours  
**Target Release:** Q4 2026

#### Description
Generate email profesional untuk berbagai kebutuhan bisnis dengan tone yang tepat.

#### Features (MVP)
- **Email Types:**
  - Follow-up email setelah interview
  - Thank you email ke interviewer
  - Job application email
  - Networking/cold outreach email
  - Resignation email (profesional)
  - Request for recommendation letter
  
- **Customization:**
  - Recipient name & role
  - Sender name & context
  - Email purpose/type (dropdown)
  - Tone: Formal / Friendly / Concise
  - Language: Bahasa Indonesia / English
  - Context/background (textarea)

- **Output:**
  - Email subject line
  - Email body (properly formatted)
  - Copy to clipboard
  - Download as .txt
  
#### Technical Specs
- **Route:** `/tools/email-writer`
- **Backend:**
  - `App\Livewire\EmailWriter.php`
  - `App\Services\EmailWriterService.php`
  - `App\Http\Controllers\EmailWriterController.php`
  - POST `/tools/email-writer/generate`
- **AI Prompt Pattern:**
  ```
  Generate professional email for [purpose]
  From: [sender context]
  To: [recipient name/role]
  Tone: [formal/friendly/concise]
  Language: [id/en]
  Context: [user background]
  
  Output JSON:
  {
    "subject": "...",
    "body": "..."
  }
  ```
- **Model:** `cv-generate` or dedicated `email-writer` model
- **Rate Limit:** 20 requests/minute

#### UI/UX
- Theme color: **Blue** (`from-blue-50 to-sky-50`)
- Icon: `fa-solid fa-envelope`
- Layout: Left form + Right preview (like CV Builder)
- Real-time preview of generated email

---

### 2. 🔜 Salary Calculator
**Priority:** Medium  
**Estimated Effort:** 4-5 hours  
**Target Release:** Q1 2027

#### Description
Hitung estimasi gaji yang adil berdasarkan posisi, pengalaman, dan lokasi di Indonesia.

#### Features (MVP)
- **Input Parameters:**
  - Job title/position
  - Industry/sector (dropdown: Tech, Finance, Marketing, etc.)
  - Years of experience (0-20+)
  - Education level (SMA, D3, S1, S2, S3)
  - City/location (Jakarta, Bandung, Surabaya, etc.)
  - Company size (Startup <50, SME 50-200, Enterprise 200+)
  - Employment type (Full-time, Contract, Freelance)

- **Output:**
  - Salary range estimate (min-max)
  - Median salary
  - Percentile breakdown (25th, 50th, 75th, 90th)
  - Comparison chart vs national average
  - Regional cost of living adjustment
  - Negotiation tips based on experience level

- **Data Source Options:**
  1. **Static lookup table** (manual curated data from surveys)
  2. **AI estimation** (GPT-4 with context from job market data)
  3. **Hybrid:** Static base + AI adjustment

#### Technical Specs
- **Route:** `/tools/salary-calculator`
- **Backend:**
  - `App\Livewire\SalaryCalculator.php`
  - `App\Services\SalaryEstimationService.php`
  - `database/seeders/SalaryDataSeeder.php` (static data)
  - OR AI-powered estimation via OpenAI
- **Frontend:**
  - Multi-step form wizard OR single-page calculator
  - Chart.js or ApexCharts for visualization
  - Range slider for years of experience
  - City autocomplete dropdown

#### UI/UX
- Theme color: **Amber/Orange** (`from-amber-50 to-orange-50`)
- Icon: `fa-solid fa-calculator`
- Layout: Form left, Results right (with chart)

#### Notes
- Requires periodic data updates (quarterly)
- Consider adding "Compare with similar roles" feature
- Privacy: No personal data collection, all client-side calculation

---

### 3. 🔜 Career Path Planner
**Priority:** High  
**Estimated Effort:** 6-8 hours  
**Target Release:** Q2 2027

#### Description
Rencanakan roadmap karir Anda dengan AI guidance dan milestone yang jelas. Bantu user membuat career plan 3-5 tahun ke depan.

#### Features (MVP)
- **Input:**
  - Current role/position
  - Current industry
  - Years of experience
  - Dream role/target position
  - Skills you have (tags/autocomplete)
  - Skills you need to learn
  - Time horizon (1/3/5 years)
  - Work preference (individual contributor vs leadership track)

- **AI-Generated Output:**
  - **Career Roadmap:** Visual timeline dengan milestones
  - **Step-by-step plan:**
    - Phase 1 (0-12 months): Short-term goals
    - Phase 2 (1-2 years): Mid-term goals
    - Phase 3 (3-5 years): Long-term goals
  - **Skill Development Plan:**
    - Critical skills to acquire
    - Learning resources (courses, certifications)
    - Estimated time to learn each skill
  - **Potential Career Paths:**
    - Path A (Technical Track)
    - Path B (Management Track)
    - Path C (Specialist/Consultant)
  - **Actionable Next Steps** (top 3 immediate actions)

- **Export:**
  - Download as PDF (formatted roadmap)
  - Save to account (future feature: user accounts)
  - Share link (public/private)

#### Technical Specs
- **Route:** `/tools/career-planner`
- **Backend:**
  - `App\Livewire\CareerPlanner.php`
  - `App\Services\CareerPlannerService.php`
  - POST `/tools/career-planner/generate`
- **AI Prompt:**
  ```
  You are a senior career coach with 20+ years experience.
  
  Current role: [role]
  Target role: [dream role]
  Experience: [years]
  Skills: [current skills]
  Timeline: [years]
  
  Generate a detailed career roadmap with:
  1. Phased milestones (JSON timeline)
  2. Skill development plan
  3. 3 alternative career paths
  4. Top 3 immediate actions
  
  Output as JSON.
  ```
- **Frontend:**
  - Timeline visualization (ApexCharts timeline or custom CSS)
  - Accordion for each phase
  - Skill tags with progress indicators
- **Model:** GPT-4 (requires more complex reasoning)

#### UI/UX
- Theme color: **Rose/Pink** (`from-rose-50 to-pink-50`)
- Icon: `fa-solid fa-chart-line`
- Layout: Full-width timeline + collapsible sections below
- Interactive: Click milestone to expand details

#### Advanced Features (V2+)
- Progress tracking (if user accounts implemented)
- Reminders/notifications for milestones
- Integration with LinkedIn profile
- Compare your plan with industry benchmarks

---

## Future Tool Ideas (Backlog)

### 4. 📝 LinkedIn Profile Optimizer
- Analyze LinkedIn profile
- Suggest improvements for headline, about, experience sections
- Keyword optimization for recruiter searches

### 5. 🎯 Job Match Score
- Upload CV + job description
- AI calculates match score (%)
- Highlights gaps and strengths

### 6. 📊 Portfolio Website Generator
- Generate simple portfolio site from CV data
- Export as static HTML
- Host on subdomain or download

### 7. 💬 Mock Interview Simulator
- Real-time voice conversation with AI interviewer
- Speech-to-text + AI response
- Post-interview feedback report

---

## Technical Architecture

### Shared Infrastructure
- **Framework:** Laravel 11 + Livewire 3
- **Frontend:** Alpine.js + Tailwind CSS
- **AI Provider:** OpenAI API (via `DisekitarAgent`)
- **Rate Limiting:** Per-tool throttling (15-30 req/min)
- **Dark Mode:** Full support required
- **Mobile:** Fully responsive

### Model Usage
- **Simple generation (Cover Letter, Email):** `cv-generate` model
- **Complex reasoning (Career Planner):** GPT-4 or equivalent
- **Cost optimization:** Use caching for similar requests

### Common Patterns
1. **Service Layer:** `App\Services\{Tool}Service.php`
2. **Livewire Component:** Full-page at `/tools/{slug}`
3. **API Endpoint:** POST `/tools/{slug}/generate` (for AJAX)
4. **Export:** Copy, TXT, PDF/Print (minimum)
5. **Banner on Tools Hub:** Add card when tool goes live

---

## Integration Checklist (Per New Tool)

- [ ] Create Livewire component + view
- [ ] Create Service class with AI logic
- [ ] Create API controller + route
- [ ] Add to `/tools` hub page (move from Coming Soon to Active)
- [ ] Add navbar link if needed (or keep under Tools hub only)
- [ ] Update footer links
- [ ] Add route to `routes/web.php`
- [ ] Add banner/link on relevant pages (e.g., job posts)
- [ ] Write tests (basic)
- [ ] Deploy to production
- [ ] Update this roadmap doc

---

## Resource Links

- **Design Reference:** https://afdi-visuals.com/work (for UI inspiration)
- **AI Models:** Check `.env` for `OPENAI_MODEL_*` config
- **Codebase Tools Location:**
  - Services: `app/Services/`
  - Livewire: `app/Livewire/`
  - Views: `resources/views/livewire/`
  - Components: `resources/views/components/`

---

## Notes

- All tools harus mobile-friendly dan dark mode support
- Setiap tool baru harus ada banner card di `/tools` hub
- Gunakan consistent design pattern (seperti Interview Prep banner)
- Rate limiting wajib untuk semua AI endpoints
- Export minimal: Copy + TXT. PDF/Print bonus.
- LocalStorage auto-save jika form kompleks (seperti CV Builder)

---

**Maintainer:** Development Team  
**Last Review:** 2026-09-18
