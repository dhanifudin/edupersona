# Database Schema - EduPersona.ai

## Overview

Dokumen ini menjelaskan struktur database untuk platform EduPersona.ai.

## Entity Relationship

```
users
    ├── [as student]
    │   ├── class_student (N:M with classes)
    │   ├── learning_style_responses (1:N)
    │   ├── learning_style_profiles (1:1)
    │   ├── learning_activities (1:N)
    │   ├── ai_recommendations (1:N)
    │   ├── ai_feedback (1:N)
    │   └── student_progress (1:N)
    │
    └── [as teacher]
        ├── teacher_subject (N:M with subjects)
        ├── class_subject (teaches subject in class)
        └── learning_materials (1:N)

classes
    ├── class_student (1:N) → students
    ├── class_subject (1:N) → subjects + teacher
    └── homeroom_teacher → users (teacher)

subjects
    ├── teacher_subject (N:M with teachers)
    ├── class_subject (N:M with classes)
    ├── learning_materials (1:N)
    └── student_progress (1:N)

learning_style_questions
    └── learning_style_responses (1:N)

learning_materials
    ├── learning_activities (1:N)
    └── ai_recommendations (1:N)
```

## Relationship Summary

| Relationship | Type | Description |
|--------------|------|-------------|
| Student ↔ Class | N:M | Siswa terdaftar di kelas (via `class_student`) |
| Teacher ↔ Subject | N:M | Guru mengajar mata pelajaran (via `teacher_subject`) |
| Class ↔ Subject | N:M | Mata pelajaran diajarkan di kelas oleh guru tertentu (via `class_subject`) |
| Class → Teacher | N:1 | Wali kelas (homeroom teacher) |
| Material → Teacher | N:1 | Guru yang upload materi |
| Material → Subject | N:1 | Materi untuk mata pelajaran |

## Table Specifications

### 1. Users Enhancement (Modify Existing)

Tambahkan kolom ke tabel `users` yang sudah ada:

| Column | Type | Description |
|--------|------|-------------|
| role | enum('student', 'teacher', 'admin') | User role, default: student |
| student_id_number | string(50), nullable | NIS (Nomor Induk Siswa) - untuk student |
| teacher_id_number | string(50), nullable | NIP/NUPTK - untuk teacher |
| phone | string(20), nullable | Nomor telepon |
| learning_interests | text, nullable | Minat belajar siswa (JSON array) |

**Note:** `grade` dan `major` dipindahkan ke tabel `classes` untuk normalisasi.

---

### 2. Classes (Kelas)

| Column | Type | Description |
|--------|------|-------------|
| id | bigint, PK | Primary key |
| name | string(50) | Nama kelas (contoh: "X IPA 1") |
| grade_level | enum('X', 'XI', 'XII') | Tingkat kelas |
| major | string(100), nullable | Jurusan (IPA, IPS, Bahasa, dll) |
| academic_year | string(9) | Tahun ajaran (contoh: "2024/2025") |
| homeroom_teacher_id | bigint, FK, nullable | Wali kelas (relasi ke users) |
| is_active | boolean, default: true | Status aktif |
| created_at | timestamp | |
| updated_at | timestamp | |

**Indexes:**
- homeroom_teacher_id
- academic_year
- unique(name, academic_year)

---

### 3. Class Student (Pivot: Siswa di Kelas)

| Column | Type | Description |
|--------|------|-------------|
| id | bigint, PK | Primary key |
| class_id | bigint, FK | Relasi ke classes |
| student_id | bigint, FK | Relasi ke users (student) |
| enrolled_at | date | Tanggal masuk kelas |
| status | enum('active', 'transferred', 'graduated') | Status siswa di kelas |
| created_at | timestamp | |
| updated_at | timestamp | |

**Constraints:**
- unique(class_id, student_id) - Siswa hanya sekali per kelas

---

### 4. Subjects (Mata Pelajaran)

| Column | Type | Description |
|--------|------|-------------|
| id | bigint, PK | Primary key |
| name | string(100) | Nama mata pelajaran |
| code | string(20), unique | Kode mata pelajaran (contoh: "MTK", "FIS") |
| description | text, nullable | Deskripsi mata pelajaran |
| is_active | boolean, default: true | Status aktif |
| created_at | timestamp | |
| updated_at | timestamp | |

**Seeder Data (MVP):**
- Matematika (MTK)

---

### 5. Teacher Subject (Pivot: Guru Mengajar Mapel)

| Column | Type | Description |
|--------|------|-------------|
| id | bigint, PK | Primary key |
| teacher_id | bigint, FK | Relasi ke users (teacher) |
| subject_id | bigint, FK | Relasi ke subjects |
| is_primary | boolean, default: false | Apakah mapel utama guru |
| created_at | timestamp | |
| updated_at | timestamp | |

**Constraints:**
- unique(teacher_id, subject_id)

---

### 6. Class Subject (Pivot: Mapel di Kelas + Guru Pengajar)

| Column | Type | Description |
|--------|------|-------------|
| id | bigint, PK | Primary key |
| class_id | bigint, FK | Relasi ke classes |
| subject_id | bigint, FK | Relasi ke subjects |
| teacher_id | bigint, FK | Guru yang mengajar mapel ini di kelas ini |
| schedule_day | enum, nullable | Hari jadwal (monday-friday) |
| schedule_time | time, nullable | Jam pelajaran |
| created_at | timestamp | |
| updated_at | timestamp | |

**Constraints:**
- unique(class_id, subject_id) - Satu mapel per kelas

**Note:** Tabel ini menghubungkan kelas dengan mata pelajaran DAN guru yang mengajar. Sehingga guru X mengajar Matematika di kelas X IPA 1.

---

### 7. Learning Materials (Materi Pembelajaran)

| Column | Type | Description |
|--------|------|-------------|
| id | bigint, PK | Primary key |
| subject_id | bigint, FK | Relasi ke subjects |
| teacher_id | bigint, FK | Relasi ke users (guru yang upload) |
| class_id | bigint, FK, nullable | Relasi ke classes (null = untuk semua kelas) |
| title | string(255) | Judul materi |
| description | text, nullable | Deskripsi materi |
| topic | string(255), nullable | Topik/bab materi |
| type | enum | video, document, infographic, audio, simulation |
| learning_style | enum | visual, auditory, kinesthetic, all |
| difficulty_level | enum | beginner, intermediate, advanced |
| content_url | string(500), nullable | URL eksternal (YouTube, Google Drive, dll) |
| file_path | string(500), nullable | Path file yang diupload |
| is_active | boolean, default: true | Status aktif |
| created_at | timestamp | |
| updated_at | timestamp | |

**Indexes:**
- subject_id
- teacher_id
- class_id
- learning_style
- type

**Note:** Jika `class_id` null, materi tersedia untuk semua kelas yang mengambil subject tersebut.

---

### 8. Learning Style Questions (Pertanyaan Gaya Belajar)

| Column | Type | Description |
|--------|------|-------------|
| id | bigint, PK | Primary key |
| question_text | text | Teks pertanyaan dalam Bahasa Indonesia |
| style_type | enum('visual', 'auditory', 'kinesthetic') | Tipe gaya belajar |
| order | integer | Urutan pertanyaan |
| is_active | boolean, default: true | Status aktif |
| created_at | timestamp | |
| updated_at | timestamp | |

**Seeder:** 15 pertanyaan VAK (5 per gaya belajar)

---

### 9. Learning Style Responses (Jawaban Kuesioner)

| Column | Type | Description |
|--------|------|-------------|
| id | bigint, PK | Primary key |
| user_id | bigint, FK | Relasi ke users |
| question_id | bigint, FK | Relasi ke learning_style_questions |
| score | tinyint | Skor 1-5 (Likert scale) |
| created_at | timestamp | |
| updated_at | timestamp | |

**Constraints:**
- unique(user_id, question_id) - Satu jawaban per pertanyaan per user

---

### 10. Learning Style Profiles (Profil Gaya Belajar)

| Column | Type | Description |
|--------|------|-------------|
| id | bigint, PK | Primary key |
| user_id | bigint, FK, unique | Relasi ke users (1:1) |
| visual_score | decimal(5,2) | Skor visual (persentase) |
| auditory_score | decimal(5,2) | Skor auditori (persentase) |
| kinesthetic_score | decimal(5,2) | Skor kinestetik (persentase) |
| dominant_style | enum | visual, auditory, kinesthetic, mixed |
| analyzed_at | timestamp | Waktu analisis terakhir |
| created_at | timestamp | |
| updated_at | timestamp | |

---

### 11. Learning Activities (Aktivitas Belajar)

| Column | Type | Description |
|--------|------|-------------|
| id | bigint, PK | Primary key |
| user_id | bigint, FK | Relasi ke users |
| material_id | bigint, FK | Relasi ke learning_materials |
| duration_seconds | integer, default: 0 | Durasi belajar dalam detik |
| started_at | timestamp | Waktu mulai |
| completed_at | timestamp, nullable | Waktu selesai |
| created_at | timestamp | |
| updated_at | timestamp | |

---

### 12. AI Recommendations (Rekomendasi AI)

| Column | Type | Description |
|--------|------|-------------|
| id | bigint, PK | Primary key |
| user_id | bigint, FK | Relasi ke users |
| material_id | bigint, FK | Relasi ke learning_materials |
| reason | text | Alasan rekomendasi dari AI |
| relevance_score | decimal(3,2) | Skor relevansi (0.00-1.00) |
| is_viewed | boolean, default: false | Sudah dilihat user |
| viewed_at | timestamp, nullable | Waktu dilihat |
| created_at | timestamp | |
| updated_at | timestamp | |

---

### 13. AI Feedback (Umpan Balik AI)

| Column | Type | Description |
|--------|------|-------------|
| id | bigint, PK | Primary key |
| user_id | bigint, FK | Relasi ke users |
| context_type | string(50) | Tipe konteks (progress, quiz, activity) |
| context_id | bigint, nullable | ID konteks terkait |
| feedback_text | text | Teks feedback dari AI |
| feedback_type | enum | encouragement, suggestion, warning |
| is_read | boolean, default: false | Sudah dibaca user |
| generated_at | timestamp | Waktu generate |
| created_at | timestamp | |
| updated_at | timestamp | |

---

### 14. Student Progress (Progress Siswa)

| Column | Type | Description |
|--------|------|-------------|
| id | bigint, PK | Primary key |
| user_id | bigint, FK | Relasi ke users |
| subject_id | bigint, FK | Relasi ke subjects |
| class_id | bigint, FK | Relasi ke classes |
| topic | string(255) | Nama topik |
| score | decimal(5,2), nullable | Skor/nilai |
| attempts | integer, default: 0 | Jumlah percobaan |
| status | enum | not_started, in_progress, completed |
| last_activity_at | timestamp, nullable | Aktivitas terakhir |
| created_at | timestamp | |
| updated_at | timestamp | |

**Constraints:**
- unique(user_id, subject_id, class_id, topic)

---

## Migration Order

1. `add_role_columns_to_users_table`
2. `create_classes_table`
3. `create_subjects_table`
4. `create_class_student_table`
5. `create_teacher_subject_table`
6. `create_class_subject_table`
7. `create_learning_materials_table`
8. `create_learning_style_questions_table`
9. `create_learning_style_responses_table`
10. `create_learning_style_profiles_table`
11. `create_learning_activities_table`
12. `create_ai_recommendations_table`
13. `create_ai_feedback_table`
14. `create_student_progress_table`

---

## Seeders

1. `SubjectSeeder` - Mata pelajaran Matematika
2. `ClassSeeder` - Contoh kelas (X IPA 1, X IPA 2, dll)
3. `LearningStyleQuestionSeeder` - 15 pertanyaan VAK
4. `LearningMaterialSeeder` - Contoh materi matematika (opsional)

---

## Model Relationships Summary

```php
// User.php
public function classes() // as student
public function currentClass() // active class
public function teacherSubjects() // subjects teacher can teach
public function classSubjects() // classes where teacher teaches
public function learningStyleProfile()
public function learningStyleResponses()
public function learningActivities()
public function aiRecommendations()
public function aiFeedback()
public function studentProgress()
public function uploadedMaterials() // as teacher

// ClassRoom.php (Class is reserved)
public function students()
public function homeroomTeacher()
public function subjects()
public function classSubjects()
public function materials()

// Subject.php
public function teachers()
public function classes()
public function materials()
public function studentProgress()

// LearningMaterial.php
public function subject()
public function teacher()
public function classRoom()
public function activities()
public function recommendations()
```
