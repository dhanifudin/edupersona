# Database Schema - EduPersona.ai

## Overview

Dokumen ini menjelaskan struktur database untuk platform EduPersona.ai.

## Entity Relationship

```
users (enhanced)
    ├── learning_style_responses (1:N)
    ├── learning_style_profiles (1:1)
    ├── learning_activities (1:N)
    ├── ai_recommendations (1:N)
    ├── ai_feedback (1:N)
    ├── student_progress (1:N)
    └── learning_materials (1:N, as teacher)

subjects
    ├── learning_materials (1:N)
    └── student_progress (1:N)

learning_style_questions
    └── learning_style_responses (1:N)

learning_materials
    ├── learning_activities (1:N)
    └── ai_recommendations (1:N)
```

## Table Specifications

### 1. Users Enhancement (Modify Existing)

Tambahkan kolom ke tabel `users` yang sudah ada:

| Column | Type | Description |
|--------|------|-------------|
| role | enum('student', 'teacher', 'admin') | User role, default: student |
| grade | string(50), nullable | Kelas siswa (contoh: "X IPA 1") |
| major | string(100), nullable | Jurusan/program studi |
| learning_interests | text, nullable | Minat belajar siswa (JSON array) |

### 2. Subjects (Mata Pelajaran)

| Column | Type | Description |
|--------|------|-------------|
| id | bigint, PK | Primary key |
| name | string(100) | Nama mata pelajaran |
| description | text, nullable | Deskripsi mata pelajaran |
| is_active | boolean, default: true | Status aktif |
| created_at | timestamp | |
| updated_at | timestamp | |

**Seeder Data (MVP):**
- Matematika

### 3. Learning Materials (Materi Pembelajaran)

| Column | Type | Description |
|--------|------|-------------|
| id | bigint, PK | Primary key |
| subject_id | bigint, FK | Relasi ke subjects |
| teacher_id | bigint, FK | Relasi ke users (guru yang upload) |
| title | string(255) | Judul materi |
| description | text, nullable | Deskripsi materi |
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
- learning_style
- type

### 4. Learning Style Questions (Pertanyaan Gaya Belajar)

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

### 5. Learning Style Responses (Jawaban Kuesioner)

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

### 6. Learning Style Profiles (Profil Gaya Belajar)

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

### 7. Learning Activities (Aktivitas Belajar)

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

### 8. AI Recommendations (Rekomendasi AI)

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

### 9. AI Feedback (Umpan Balik AI)

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

### 10. Student Progress (Progress Siswa)

| Column | Type | Description |
|--------|------|-------------|
| id | bigint, PK | Primary key |
| user_id | bigint, FK | Relasi ke users |
| subject_id | bigint, FK | Relasi ke subjects |
| topic | string(255) | Nama topik |
| score | decimal(5,2), nullable | Skor/nilai |
| attempts | integer, default: 0 | Jumlah percobaan |
| status | enum | not_started, in_progress, completed |
| last_activity_at | timestamp, nullable | Aktivitas terakhir |
| created_at | timestamp | |
| updated_at | timestamp | |

**Constraints:**
- unique(user_id, subject_id, topic)

## Migration Order

1. `add_role_columns_to_users_table`
2. `create_subjects_table`
3. `create_learning_materials_table`
4. `create_learning_style_questions_table`
5. `create_learning_style_responses_table`
6. `create_learning_style_profiles_table`
7. `create_learning_activities_table`
8. `create_ai_recommendations_table`
9. `create_ai_feedback_table`
10. `create_student_progress_table`

## Seeders

1. `SubjectSeeder` - Mata pelajaran Matematika
2. `LearningStyleQuestionSeeder` - 15 pertanyaan VAK
3. `LearningMaterialSeeder` - Contoh materi matematika (opsional)
