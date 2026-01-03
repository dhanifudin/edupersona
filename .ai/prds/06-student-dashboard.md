# Student Dashboard - EduPersona.ai

## Overview

Redesign dashboard siswa dengan fokus pada mata pelajaran. Siswa dapat mendaftar ke mata pelajaran (assigned + elective), memilih topik, dan belajar dengan learning path yang dipandu.

## Features Checklist

- [ ] Subject Enrollment System (assigned + elective)
- [ ] Subject-focused Dashboard Redesign
- [ ] Topic Selection & Navigation
- [ ] Guided Learning Path
- [ ] Topic Materials Display
- [ ] AI Recommendations per Topic
- [ ] Progress Tracking per Topic/Subject

---

## 1. Subject Enrollment System

### Description
Siswa dapat terdaftar di mata pelajaran melalui dua cara:
1. **Assigned**: Otomatis dari kelas (via class_subject)
2. **Elective**: Dipilih sendiri oleh siswa

### User Stories
- Sebagai siswa, saya ingin melihat mata pelajaran yang sudah assigned ke kelas saya
- Sebagai siswa, saya ingin mendaftar ke mata pelajaran elective tambahan
- Sebagai siswa, saya ingin membatalkan enrollment dari mata pelajaran elective

### Database: `student_subject_enrollments`

| Column | Type | Description |
|--------|------|-------------|
| id | bigint, PK | Primary key |
| user_id | bigint, FK | Relasi ke users |
| subject_id | bigint, FK | Relasi ke subjects |
| enrollment_type | enum('assigned', 'elective') | Tipe enrollment |
| enrolled_at | timestamp | Waktu enrollment |
| status | enum('active', 'completed', 'dropped') | Status |
| created_at | timestamp | |
| updated_at | timestamp | |

**Constraints:** unique(user_id, subject_id)

### API Endpoints

| Method | Route | Controller | Description |
|--------|-------|------------|-------------|
| GET | /student/subjects | SubjectEnrollmentController@index | List enrolled subjects |
| GET | /student/subjects/available | SubjectEnrollmentController@available | List available electives |
| POST | /student/subjects/{subject}/enroll | SubjectEnrollmentController@enroll | Enroll elective |
| DELETE | /student/subjects/{subject}/unenroll | SubjectEnrollmentController@unenroll | Unenroll elective |

---

## 2. Dashboard Redesign

### Description
Dashboard baru dengan fokus pada mata pelajaran dan topik.

### UI Layout

```
┌─────────────────────────────────────────────────────────────┐
│ Dashboard Siswa                                [Profile] [⚙]│
├─────────────────────────────────────────────────────────────┤
│ Welcome Section                                             │
│ ┌─────────────┐ ┌─────────────┐ ┌─────────────┐            │
│ │ Gaya Belajar│ │ Total Waktu │ │ Topik       │            │
│ │ 🎨 Visual   │ │ 12.5 jam    │ │ 8/24 selesai│            │
│ └─────────────┘ └─────────────┘ └─────────────┘            │
├─────────────────────────────────────────────────────────────┤
│ Mata Pelajaran Saya                    [+ Tambah Elective] │
│                                                             │
│ ┌───────────────┐ ┌───────────────┐ ┌───────────────┐      │
│ │ 📐 Matematika │ │ ⚗️ Fisika     │ │ 🧪 Kimia      │      │
│ │ Assigned      │ │ Assigned      │ │ Elective      │      │
│ │ ████████░░ 80%│ │ ███░░░░░░ 30% │ │ █░░░░░░░░ 10% │      │
│ │ 8/10 topics   │ │ 3/10 topics   │ │ 1/10 topics   │      │
│ │ [Lanjutkan]   │ │ [Mulai]       │ │ [Mulai]       │      │
│ └───────────────┘ └───────────────┘ └───────────────┘      │
└─────────────────────────────────────────────────────────────┘
```

### Subject Card Component

**Props:**
- subject: Subject
- progress: { completedTopics: number, totalTopics: number, percentage: number }
- enrollmentType: 'assigned' | 'elective'

**Actions:**
- Click → Navigate to subject learning page

---

## 3. Subject Learning Page

### Description
Halaman detail mata pelajaran dengan daftar topik dan learning path.

### UI Layout

```
┌─────────────────────────────────────────────────────────────┐
│ ← Back to Dashboard          Matematika                     │
├─────────────────────────────────────────────────────────────┤
│ ┌─────────────────────┐ ┌─────────────────────────────────┐ │
│ │ Daftar Topik        │ │ Topik: Aljabar                  │ │
│ │                     │ │                                 │ │
│ │ ✅ 1. Bilangan      │ │ Progress: ████████░░ 80%        │ │
│ │ 🔵 2. Aljabar ←     │ │                                 │ │
│ │ ⚪ 3. Geometri      │ │ ┌─────────────────────────────┐ │ │
│ │ ⚪ 4. Statistika    │ │ │ Materi Pembelajaran         │ │ │
│ │ ⚪ 5. Peluang       │ │ │ 📹 Video: Pengantar Aljabar │ │ │
│ │                     │ │ │ 📄 Doc: Rumus Aljabar       │ │ │
│ │                     │ │ │ 🎮 Sim: Latihan Aljabar     │ │ │
│ │                     │ │ └─────────────────────────────┘ │ │
│ │                     │ │                                 │ │
│ │                     │ │ ┌─────────────────────────────┐ │ │
│ │                     │ │ │ 🤖 Rekomendasi AI           │ │ │
│ │                     │ │ │ Berdasarkan gaya belajarmu: │ │ │
│ │                     │ │ │ • Video Animasi Aljabar     │ │ │
│ │                     │ │ │ • Infografis Rumus          │ │ │
│ │                     │ │ └─────────────────────────────┘ │ │
│ └─────────────────────┘ └─────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────┘
```

### Topics Status
- ✅ Completed (all materials finished)
- 🔵 In Progress (current topic)
- ⚪ Not Started

### API Endpoints

| Method | Route | Controller | Description |
|--------|-------|------------|-------------|
| GET | /student/subjects/{subject}/learn | SubjectLearningController@show | Subject learning page |
| GET | /student/subjects/{subject}/topics | SubjectLearningController@topics | List topics with progress |
| GET | /student/subjects/{subject}/topics/{topic} | SubjectLearningController@topic | Topic detail with materials |
| POST | /student/subjects/{subject}/topics/{topic}/start | SubjectLearningController@startTopic | Start learning a topic |
| POST | /student/subjects/{subject}/topics/{topic}/complete | SubjectLearningController@completeTopic | Mark topic complete |

---

## 4. Topic Learning Flow

### Description
Alur belajar topik dengan materi dan tracking progress.

### Flow
1. Student selects topic from list
2. System shows materials for that topic (filtered by learning style if available)
3. Student clicks material → opens material page, creates learning activity
4. System tracks duration and completion
5. AI generates recommendations based on topic + learning style
6. When all materials completed → topic marked as complete

### Progress Tracking (using existing `student_progress` table)

| Field | Usage |
|-------|-------|
| user_id | Student |
| subject_id | Subject |
| topic | Topic name (from material.topic) |
| status | not_started, in_progress, completed |
| score | Optional: quiz score |
| attempts | Material views count |
| last_activity_at | Last activity timestamp |

---

## 5. Elective Enrollment Modal

### Description
Modal untuk mendaftar ke mata pelajaran elective.

### UI Layout

```
┌─────────────────────────────────────────────┐
│ Tambah Mata Pelajaran Elective          [X] │
├─────────────────────────────────────────────┤
│ Pilih mata pelajaran yang ingin kamu        │
│ pelajari:                                   │
│                                             │
│ ┌─────────────────────────────────────────┐ │
│ │ ☐ Bahasa Inggris                        │ │
│ │   12 topik • 45 materi                  │ │
│ ├─────────────────────────────────────────┤ │
│ │ ☐ Seni Budaya                           │ │
│ │   8 topik • 24 materi                   │ │
│ ├─────────────────────────────────────────┤ │
│ │ ☐ Informatika                           │ │
│ │   15 topik • 60 materi                  │ │
│ └─────────────────────────────────────────┘ │
│                                             │
│                    [Batal] [Daftar Sekarang]│
└─────────────────────────────────────────────┘
```

---

## File Structure

```
app/
├── Http/Controllers/Student/
│   ├── DashboardController.php          # MODIFY
│   ├── SubjectEnrollmentController.php  # NEW
│   └── SubjectLearningController.php    # NEW
├── Models/
│   ├── User.php                         # MODIFY (add relationship)
│   └── StudentSubjectEnrollment.php     # NEW

database/migrations/
└── xxxx_create_student_subject_enrollments_table.php  # NEW

resources/js/
├── pages/student/
│   ├── Dashboard.vue                    # MODIFY (redesign)
│   └── SubjectLearning.vue              # NEW
├── components/student/
│   ├── SubjectCard.vue                  # NEW
│   ├── TopicList.vue                    # NEW
│   ├── TopicDetail.vue                  # NEW
│   ├── MaterialList.vue                 # NEW (or reuse existing)
│   └── EnrollmentModal.vue              # NEW

tests/Feature/Student/
├── SubjectEnrollmentControllerTest.php  # NEW
├── SubjectLearningControllerTest.php    # NEW
└── DashboardControllerTest.php          # MODIFY
```

---

## Routes

```php
Route::middleware(['auth', 'role:student'])->prefix('student')->name('student.')->group(function () {
    // Existing routes...

    // NEW: Subject Enrollment
    Route::get('/subjects', [SubjectEnrollmentController::class, 'index'])->name('subjects.index');
    Route::get('/subjects/available', [SubjectEnrollmentController::class, 'available'])->name('subjects.available');
    Route::post('/subjects/{subject}/enroll', [SubjectEnrollmentController::class, 'enroll'])->name('subjects.enroll');
    Route::delete('/subjects/{subject}/unenroll', [SubjectEnrollmentController::class, 'unenroll'])->name('subjects.unenroll');

    // NEW: Subject Learning
    Route::get('/subjects/{subject}/learn', [SubjectLearningController::class, 'show'])->name('subjects.learn');
    Route::get('/subjects/{subject}/topics', [SubjectLearningController::class, 'topics'])->name('subjects.topics');
    Route::get('/subjects/{subject}/topics/{topic}', [SubjectLearningController::class, 'topic'])->name('subjects.topic');
    Route::post('/subjects/{subject}/topics/{topic}/start', [SubjectLearningController::class, 'startTopic'])->name('subjects.topic.start');
    Route::post('/subjects/{subject}/topics/{topic}/complete', [SubjectLearningController::class, 'completeTopic'])->name('subjects.topic.complete');
});
```

---

## Implementation Order

### Phase 1: Database & Models
1. [ ] Create `student_subject_enrollments` migration
2. [ ] Create `StudentSubjectEnrollment` model
3. [ ] Update `User` model with `enrolledSubjects()` relationship
4. [ ] Create factory and seeder

### Phase 2: Enrollment Backend
5. [ ] Create `SubjectEnrollmentController`
6. [ ] Implement index, available, enroll, unenroll methods
7. [ ] Add auto-enrollment for class subjects
8. [ ] Write enrollment tests

### Phase 3: Learning Backend
9. [ ] Create `SubjectLearningController`
10. [ ] Implement show, topics, topic methods
11. [ ] Implement startTopic, completeTopic methods
12. [ ] Integrate with AI recommendations
13. [ ] Write learning tests

### Phase 4: Dashboard Frontend
14. [ ] Create `SubjectCard.vue` component
15. [ ] Create `EnrollmentModal.vue` component
16. [ ] Redesign `Dashboard.vue`
17. [ ] Add enrollment functionality

### Phase 5: Learning Frontend
18. [ ] Create `SubjectLearning.vue` page
19. [ ] Create `TopicList.vue` component
20. [ ] Create `TopicDetail.vue` component
21. [ ] Implement learning flow

### Phase 6: Testing & Polish
22. [ ] Run all tests
23. [ ] Run Pint
24. [ ] Generate Wayfinder
25. [ ] Build frontend

---

## Related Documents
- [01-database-schema.md](./01-database-schema.md) - Database design
- [02-phase1-student.md](./02-phase1-student.md) - Student features
- [04-ai-integration.md](./04-ai-integration.md) - AI recommendations
