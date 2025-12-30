# EduPersona.ai - Product Requirements Document

## Project Overview

**EduPersona.ai** adalah platform pembelajaran yang dipersonalisasi berbasis AI dan analitik data. Sistem ini mengidentifikasi gaya belajar siswa (Visual, Auditori, Kinestetik) dan memberikan rekomendasi materi pembelajaran yang sesuai.

## Technology Stack

- **Backend**: Laravel 12, PHP 8.5
- **Frontend**: Vue 3, Inertia v2, Tailwind CSS v4
- **AI**: Google Gemini API
- **Database**: SQLite (development), MySQL/PostgreSQL (production)
- **Authentication**: Laravel Fortify
- **Routing**: Laravel Wayfinder

## MVP Scope

- **Fokus**: Student role terlebih dahulu
- **Mata Pelajaran**: Matematika (teachers dapat menambah lainnya)
- **Bahasa**: Bahasa Indonesia untuk UI dan respons AI
- **Gaya Belajar**: Standard VAK (Visual, Auditory, Kinesthetic) questionnaire
- **Materi**: External links + file uploads
- **Fitur AI**: Recommendations + Feedback generation

## User Roles

| Role | Deskripsi |
|------|-----------|
| Student (Siswa) | Mengisi profil, kuesioner gaya belajar, melihat rekomendasi materi |
| Teacher (Guru) | Upload materi, lihat analitik kelas, kelola siswa |
| Admin | Monitoring sekolah, manajemen user, konfigurasi sistem |

## Development Phases

### Phase 1: Foundation & Student Core (MVP)
- Database schema & models
- User role system
- VAK questionnaire
- Learning style analyzer
- Learning profile page
- Gemini AI integration
- Material recommendations
- AI feedback system
- Progress tracking

### Phase 2: Teacher Features
- Teacher dashboard
- Material management (upload/edit)
- Class analytics
- Student progress view
- Intervention tools

### Phase 3: Admin Features
- School-wide analytics
- User management
- System configuration
- Report generation (PDF/Excel)

## Process Flow

```
Input Data → AI Analysis → Personal Recommendations → Learning → Evaluation → Continuous Improvement
```

## Related Documents

- [01-database-schema.md](./01-database-schema.md) - Database design
- [02-phase1-student.md](./02-phase1-student.md) - Phase 1 MVP details
- [03-vak-questionnaire.md](./03-vak-questionnaire.md) - VAK questionnaire specification
- [04-ai-integration.md](./04-ai-integration.md) - Gemini AI integration
