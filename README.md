# Edusync – A Smart Student Management System

## Project Overview
Edusync is a modern, mobile‑responsive Student Management System designed for local, final‑year demo usage at institute labs. It streamlines student registration, attendance tracking, grade management, notices, and analytics via a secure PHP/MySQL backend and a responsive Tailwind UI. The system includes an AI assistant that provides structured analyses with safe database access.

## Core Features
- Student registration and profile management
- Attendance tracking with weekly summaries and trends
- Grade management and subject-wise performance
- Notice board for announcements
- Admin dashboard with class statistics and fee insights
- AI Chat Assistant with secure tool-calling for student lookups
- Mobile-first UI with smooth animations and accessibility
- Subject Management System (regulation-based)
- Common & Department Subjects (1st year common + dept-specific)
- Student Analytics Dashboard (attendance and marks)
- Parent Information Tracking (parent_name, parent_mobile)
- PDF Report Export (single-page academic report)
- SweetAlert2 Alerts (modern confirmations and toasts)
- Static + AI Hybrid Routing (static replies + AI tools)

## Technical Architecture
- Backend: PHP 8, PDO, prepared statements, session-auth
- Database: MySQL/MariaDB with students, attendance, marks, subjects, users, departments
- Frontend: HTML5, CSS3, Tailwind CSS, Font Awesome, Chart.js, SweetAlert
- AI Integration: Groq Chat Completions (OpenAI-compatible) with 2‑phase tool-calling
- Security: Auth guard on endpoints, CSRF script, input sanitization, safe error handling

### Key Modules
- ai/ai_config.php, ai_controller.php, ai_tools.php, ai_security.php
- api/ai/chat.php (chat endpoint), api/ai/AIService.php (orchestrator), api/ai/DatabaseHelper.php (DB helper)
- public/ai-chat/chatbot.php (chat UI), public/dashboard.php (charts), public/includes/* (layout)

### Data Flow (Hybrid Routing)
1. Static intents matched from data/ai-responses.json for FAQs
2. If no match, Groq assistant runs with function tools
3. Tool fetch_student_db returns structured student data
4. Assistant formats the final analysis message for the UI

## Database Schema Overview
- students(id, reg_no, name, dept_id, year, phone, email, total_fee, paid_fee, balance_fee, status, created_at)
- attendance(id, student_id, week_date, present, percentage, created_at)
- marks(id, student_id, subject_id, marks_obtained, total_marks, created_at)
- subjects(id, name, code, dept_id, year)
- departments(id, name)
- users(id, name, email, password, role, dept_id, created_at)

Indexes & integrity: reg_no unique, attendance unique per student/date, marks unique per student/subject, foreign keys on references.

## Implementation Timeline
- Week 1: Planning, requirements, module breakdown, schema design
- Week 2: Backend scaffolding, auth, students/attendance/marks APIs
- Week 3: Admin dashboard, notices, charts, responsive layout
- Week 4: AI integration with function tools, testing, stabilization
- Week 5: Performance tuning, accessibility pass, final documentation and viva prep

## Challenges Faced
- Harmonizing legacy marks tables with new normalized schema
- Ensuring safe tool-calling and preventing prompt injection
- Handling variable student data completeness (phone, email)
- Achieving smooth performance on low‑end devices without heavy effects

## Solutions Implemented
- Strict prepared statements and defensive DB helpers
- Sanitization in ai_controller and chat endpoint
- Runtime DB schema validation in chat API for early warnings
- Tailwind-based lightweight animations; reduced motion support
- Two‑phase Groq tool-calling: first call with tools, execute, second call without tools

## Technologies Used
- PHP, MySQL, HTML5, CSS3, JavaScript
- Tailwind CSS (final UI), Font Awesome icons
- Chart.js for analytics visuals
- SweetAlert for interactive notifications
- Bootstrap/Ajax (referenced in early prototypes; final UI uses Tailwind)

## Subject Management System
- Admin-first, regulation-based subject catalog
- 1st-year common subjects available to all departments:
  - Mathematics, Physics, Chemistry, English, Tamil
- Department-specific subjects for Years 2 and 3
- Dynamic fetching via API with filters for department, year, and search
- Brief schema (subject_catalog):
  - subject_name (text), department (nullable FK to departments), year (1–3)
  - is_common (0/1), regulation_year (int), created_at (timestamp)
- Safe creation with duplicate checks and prepared statements

## Student Analytics Dashboard
- Dedicated Student Analysis page with compact, responsive design
- Displays student information:
  - Name, Register Number, Department, Year, Parent Name, Parent Mobile
- Visual analytics powered by Chart.js:
  - Attendance doughnut chart (overall%)
  - Subject-wise marks bar chart (deduped subjects)
  - Attendance trend (internal) used for insights
- Instant data loading via AJAX; no page reloads
- Mobile-friendly grid layout and tight card-based UI

## PDF Report Generation
- One-click export of a professional, single-page A4 report
- Content includes:
  - Student details, Attendance (overall and last month), Subject marks
  - Compact charts converted from Chart.js canvases to high-res images
  - Short AI performance summary (strengths, weaknesses, evaluation)
- Clean academic layout with headings, small fonts, and tight spacing
- Hides UI-only elements (dropdowns, filters, buttons, sidebar) in PDF
- Uses html2pdf with A4 portrait, 10mm margins, scale ~1.5, page-break avoidance

## Parent Information Integration
- Added parent_name and parent_mobile fields to students
- Frontend Add/Edit forms updated to capture and validate:
  - Accepts 10-digit Indian numbers; normalizes +91/91/0 prefixes
- Server-side validation with prepared statements for both create/update
- Data used in analytics and PDF report sections

## SweetAlert2 Integration
- Replaces all default alerts and confirms with modern SweetAlert2 dialogs
- Used for:
  - Success and error notifications
  - Delete confirmations
  - Form submission feedback
- Improves UX with consistent, accessible notifications

## UI & UX Improvements
- Tailwind CSS utilities for consistent spacing and responsive design
- Anime.js for subtle, performant entry animations and feedback
- Dashboard-style card layout with fixed-height chart containers
- Sidebar enhancements and mobile collapsible behavior
- Print-optimized styles for clean PDF output

## System Architecture Enhancement
- Hybrid AI routing:
  - Static reply matching for FAQs and fixed intents
  - AI fallback with secure tool-calling for database-backed answers
- Tool-calling fetches student aggregates safely (prepared statements)
- Subject-aware logic for analytics and reporting
- Scalable, modular endpoints for subjects, students, marks, attendance

## Team & Guidance
- N Syed Asrar Saqib — Backend lead, database integrity, AI integration
- N Syed Maaz — Frontend responsiveness, dashboards, animations
- M Ragul — Attendance and marks modules, testing
- K Suresh — Admin operations, notices, accessibility
- Guided by Mr. Srinivasan sir — Project planning, reviews, and final evaluation

## Development Journey
- Iterative sprints delivering backend APIs first, then UI and AI
- Static-first approach to contain costs and ensure offline demo capability
- Progressive enhancement: charts, animations, and AI added after core modules stabilized

## Key Achievements
- Stable, demo‑ready system with secure student analytics
- Clean two‑phase tool-calling architecture
- Mobile‑friendly chat and dashboard with smooth UX
- Clear documentation and viva‑ready explanation

## Screenshots
Place screenshots in public/assets/images and reference them here:
- Dashboard Overview: public/assets/images/dashboard.png
- Student List: public/assets/images/students_list.png
- Attendance Trend: public/assets/images/attendance_trend.png
- AI Chat: public/assets/images/ai_chat.png

## Setup & Deployment
- Import the database from db/edusync.sql or run api/common/setup_full_db.php
- Configure env: GROQ_API_KEY via Windows Environment Variables or Apache SetEnv
- Start XAMPP Apache/MySQL, navigate to http://localhost/edusync/public/

## Accessibility & Responsiveness
- Tailwind classes ensure flexible layout
- prefers-reduced-motion respected in chat UI
- Keyboard-friendly interactions and clear contrast in dark mode

## Future Scope
- Role-based access control with granular policies
- Multi-student analytics and cohort insights
- PDF report exports
- Admin analytics dashboard extensions
- Real-time notifications and logging
- AI confidence scoring on tool use
- Optional cloud deployment pipeline

## License
MIT
