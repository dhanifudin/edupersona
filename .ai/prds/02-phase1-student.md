# Phase 1: Student Core Features (MVP)

## Overview

Phase 1 fokus pada fitur-fitur untuk siswa sebagai pengguna utama platform.

## Features Checklist

- [ ] Student Registration & Profile
- [ ] VAK Questionnaire System
- [ ] Learning Style Profile Display
- [ ] Material Recommendations (AI)
- [ ] AI Feedback System
- [ ] Progress Dashboard

---

## 1. Student Registration & Profile

### Description
Siswa dapat mendaftar dan melengkapi profil dengan informasi kelas, jurusan, dan minat belajar.

### User Stories
- Sebagai siswa, saya ingin mendaftar akun agar dapat mengakses platform
- Sebagai siswa, saya ingin melengkapi profil agar sistem memahami konteks saya

### UI Requirements

**Registration Form (extend existing):**
- Name (existing)
- Email (existing)
- Password (existing)
- Kelas (dropdown: X, XI, XII)
- Jurusan (dropdown: IPA, IPS, Bahasa, dll)
- Minat Belajar (multi-select tags)

**Profile Page:**
- Display profil lengkap
- Edit profil
- Link ke kuesioner gaya belajar (jika belum diisi)

### API Endpoints

| Method | Route | Controller | Description |
|--------|-------|------------|-------------|
| GET | /student/profile | ProfileController@show | Lihat profil |
| PUT | /student/profile | ProfileController@update | Update profil |

---

## 2. VAK Questionnaire System

### Description
Kuesioner 15 pertanyaan untuk mengidentifikasi gaya belajar siswa.

### User Stories
- Sebagai siswa, saya ingin mengisi kuesioner gaya belajar agar mendapat rekomendasi yang tepat
- Sebagai siswa, saya ingin melihat progress pengisian kuesioner

### UI Requirements

**Questionnaire Page:**
- Progress indicator (1/15, 2/15, dst)
- Satu pertanyaan per halaman (atau scrollable list)
- Radio buttons untuk Likert scale 1-5:
  - 1 = Sangat Tidak Setuju
  - 2 = Tidak Setuju
  - 3 = Netral
  - 4 = Setuju
  - 5 = Sangat Setuju
- Tombol Sebelumnya/Selanjutnya
- Tombol Submit di akhir

### API Endpoints

| Method | Route | Controller | Description |
|--------|-------|------------|-------------|
| GET | /student/questionnaire | QuestionnaireController@index | Lihat kuesioner |
| POST | /student/questionnaire | QuestionnaireController@store | Simpan jawaban |
| GET | /student/questionnaire/result | QuestionnaireController@result | Lihat hasil |

### Business Logic

Lihat [03-vak-questionnaire.md](./03-vak-questionnaire.md) untuk detail pertanyaan dan perhitungan skor.

---

## 3. Learning Style Profile Display

### Description
Menampilkan hasil analisis gaya belajar siswa dalam bentuk visual.

### User Stories
- Sebagai siswa, saya ingin melihat gaya belajar dominan saya
- Sebagai siswa, saya ingin memahami karakteristik gaya belajar saya

### UI Requirements

**Profile Display:**
- Radar chart (Visual, Auditory, Kinesthetic scores)
- Dominant style badge dengan ikon
- Penjelasan karakteristik gaya belajar dominan
- Tips belajar sesuai gaya belajar
- Tombol "Isi Ulang Kuesioner" (jika ingin update)

### Visualizations
- Radar Chart: vue-chartjs atau Chart.js
- Warna: Visual (biru), Auditory (hijau), Kinesthetic (orange)

---

## 4. Material Recommendations (AI)

### Description
Rekomendasi materi pembelajaran berdasarkan gaya belajar dari Gemini AI.

### User Stories
- Sebagai siswa, saya ingin melihat rekomendasi materi sesuai gaya belajar saya
- Sebagai siswa, saya ingin memahami mengapa materi ini direkomendasikan

### UI Requirements

**Recommendations Page:**
- List of recommended materials
- Filter by type (video, document, audio, simulation)
- Filter by difficulty
- Card untuk setiap materi:
  - Thumbnail/icon
  - Title
  - Type badge
  - Learning style match indicator
  - AI reason snippet
  - "Mulai Belajar" button

### API Endpoints

| Method | Route | Controller | Description |
|--------|-------|------------|-------------|
| GET | /student/recommendations | RecommendationController@index | Lihat rekomendasi |
| POST | /student/recommendations/{id}/view | RecommendationController@markViewed | Tandai sudah dilihat |

---

## 5. AI Feedback System

### Description
Feedback personalisasi dari Gemini AI berdasarkan progress dan aktivitas siswa.

### User Stories
- Sebagai siswa, saya ingin mendapat feedback tentang progress belajar saya
- Sebagai siswa, saya ingin mendapat saran untuk meningkatkan pembelajaran

### UI Requirements

**Feedback Display:**
- Notification bell dengan unread count
- Dropdown/panel feedback terbaru
- Feedback page dengan list semua feedback
- Feedback types dengan ikon berbeda:
  - Encouragement (green, thumbs up)
  - Suggestion (blue, lightbulb)
  - Warning (yellow, alert)

### API Endpoints

| Method | Route | Controller | Description |
|--------|-------|------------|-------------|
| GET | /student/feedback | FeedbackController@index | Lihat semua feedback |
| POST | /student/feedback/{id}/read | FeedbackController@markRead | Tandai sudah dibaca |

---

## 6. Progress Dashboard

### Description
Dashboard untuk melihat progress dan aktivitas belajar siswa.

### User Stories
- Sebagai siswa, saya ingin melihat ringkasan progress belajar saya
- Sebagai siswa, saya ingin melihat statistik aktivitas belajar

### UI Requirements

**Dashboard Components:**
- Welcome message dengan nama siswa
- Learning style badge (jika sudah diisi)
- Quick stats:
  - Total waktu belajar (jam/menit)
  - Materi yang dipelajari
  - Progress per topik
- Recent activities list
- Recommendations preview (3-5 items)
- Unread feedback indicator

### API Endpoints

| Method | Route | Controller | Description |
|--------|-------|------------|-------------|
| GET | /dashboard | DashboardController@index | Dashboard utama |

---

## File Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   └── Student/
│   │       ├── DashboardController.php
│   │       ├── ProfileController.php
│   │       ├── QuestionnaireController.php
│   │       ├── MaterialController.php
│   │       ├── RecommendationController.php
│   │       ├── FeedbackController.php
│   │       └── ProgressController.php
│   ├── Requests/
│   │   └── Student/
│   │       ├── UpdateProfileRequest.php
│   │       └── StoreQuestionnaireRequest.php
│   └── Middleware/
│       └── EnsureUserIsStudent.php (via bootstrap/app.php)
├── Services/
│   ├── LearningStyleAnalyzer.php
│   ├── GeminiAiService.php
│   └── RecommendationEngine.php

resources/js/
├── Pages/
│   └── Student/
│       ├── Dashboard.vue
│       ├── Profile/
│       │   ├── Show.vue
│       │   └── Edit.vue
│       ├── Questionnaire/
│       │   ├── Index.vue
│       │   └── Result.vue
│       ├── Materials/
│       │   ├── Index.vue
│       │   └── Show.vue
│       ├── Recommendations.vue
│       ├── Feedback.vue
│       └── Progress.vue
├── Components/
│   └── Student/
│       ├── LearningStyleRadar.vue
│       ├── MaterialCard.vue
│       ├── FeedbackItem.vue
│       └── ProgressStats.vue
```

## Routes

```php
// routes/web.php
Route::middleware(['auth', 'role:student'])->prefix('student')->name('student.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::get('/questionnaire', [QuestionnaireController::class, 'index'])->name('questionnaire.index');
    Route::post('/questionnaire', [QuestionnaireController::class, 'store'])->name('questionnaire.store');
    Route::get('/questionnaire/result', [QuestionnaireController::class, 'result'])->name('questionnaire.result');

    Route::get('/materials', [MaterialController::class, 'index'])->name('materials.index');
    Route::get('/materials/{material}', [MaterialController::class, 'show'])->name('materials.show');

    Route::get('/recommendations', [RecommendationController::class, 'index'])->name('recommendations.index');
    Route::post('/recommendations/{recommendation}/view', [RecommendationController::class, 'markViewed'])->name('recommendations.view');

    Route::get('/feedback', [FeedbackController::class, 'index'])->name('feedback.index');
    Route::post('/feedback/{feedback}/read', [FeedbackController::class, 'markRead'])->name('feedback.read');

    Route::get('/progress', [ProgressController::class, 'index'])->name('progress.index');
});
```

## Implementation Order

1. Database migrations & models
2. User role middleware
3. Profile pages (show/edit)
4. Questionnaire system
5. Learning style analyzer service
6. Learning profile page with chart
7. Gemini AI service
8. Materials listing
9. Recommendation engine
10. Feedback system
11. Dashboard aggregation
