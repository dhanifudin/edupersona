# PRD 07: Demo Data untuk Personalized Learning

## Tujuan

Menyediakan data demo yang menunjukkan bagaimana sistem EduPersona.ai memberikan pengalaman pembelajaran yang dipersonalisasi berdasarkan gaya belajar siswa (Visual, Auditori, Kinestetik).

## Scope

Data demo mencakup:
1. Siswa dengan profil gaya belajar berbeda
2. Materi pembelajaran untuk setiap gaya belajar
3. Rekomendasi AI yang menunjukkan personalisasi
4. Progress dan aktivitas pembelajaran
5. Feedback AI yang relevan

---

## 1. Demo Users (Siswa)

### 1.1 Profil Siswa Demo

| Username | Email | Gaya Dominan | Visual | Auditori | Kinestetik |
|----------|-------|--------------|--------|----------|------------|
| Budi Visual | budi@demo.test | visual | 85% | 45% | 35% |
| Siti Auditori | siti@demo.test | auditory | 40% | 88% | 42% |
| Andi Kinestetik | andi@demo.test | kinesthetic | 35% | 40% | 90% |
| Dewi Campuran | dewi@demo.test | mixed | 72% | 70% | 68% |

### 1.2 Password Default
Semua user demo menggunakan password: `demo1234`

---

## 2. Subjects & Topics (Matematika)

### 2.1 Struktur Topik

Subject: **Matematika** (MTK) - sudah ada di seeder

Topics yang perlu ditambahkan:
1. Bilangan Bulat
2. Pecahan
3. Aljabar Dasar
4. Geometri Dasar
5. Statistika Dasar

---

## 3. Learning Materials

### 3.1 Materi per Gaya Belajar

#### Visual (6 materi)
| Judul | Tipe | Topik | Tingkat |
|-------|------|-------|---------|
| Video: Konsep Bilangan Bulat | video | Bilangan Bulat | beginner |
| Infografis: Operasi Bilangan Bulat | infographic | Bilangan Bulat | beginner |
| Video: Pengenalan Pecahan | video | Pecahan | beginner |
| Infografis: Jenis-jenis Pecahan | infographic | Pecahan | intermediate |
| Dokumen: Rumus Aljabar Bergambar | document | Aljabar Dasar | intermediate |
| Video: Bangun Datar dan Ruang | video | Geometri Dasar | beginner |

#### Auditori (6 materi)
| Judul | Tipe | Topik | Tingkat |
|-------|------|-------|---------|
| Podcast: Memahami Bilangan Bulat | audio | Bilangan Bulat | beginner |
| Audio: Lagu Rumus Pecahan | audio | Pecahan | beginner |
| Video: Penjelasan Verbal Aljabar | video | Aljabar Dasar | intermediate |
| Podcast: Cerita Geometri | audio | Geometri Dasar | beginner |
| Audio: Tips Mengingat Rumus | audio | Aljabar Dasar | intermediate |
| Video: Diskusi Statistika | video | Statistika Dasar | beginner |

#### Kinestetik (6 materi)
| Judul | Tipe | Topik | Tingkat |
|-------|------|-------|---------|
| Simulasi: Garis Bilangan Interaktif | simulation | Bilangan Bulat | beginner |
| Simulasi: Kalkulator Pecahan | simulation | Pecahan | beginner |
| Simulasi: Puzzle Aljabar | simulation | Aljabar Dasar | intermediate |
| Simulasi: Bangun 3D Interaktif | simulation | Geometri Dasar | intermediate |
| Video: Praktik Menghitung Statistik | video | Statistika Dasar | beginner |
| Simulasi: Grafik Statistika | simulation | Statistika Dasar | intermediate |

#### Universal (3 materi - untuk semua gaya)
| Judul | Tipe | Topik | Tingkat |
|-------|------|-------|---------|
| Dokumen: Ringkasan Matematika Dasar | document | Bilangan Bulat | beginner |
| Video: Review Materi Semester | video | Aljabar Dasar | advanced |
| Dokumen: Latihan Soal Campuran | document | Statistika Dasar | intermediate |

**Total: 21 materi pembelajaran**

---

## 4. Demo Recommendations

### 4.1 Rekomendasi untuk Budi (Visual)

| Materi | Relevance Score | Alasan |
|--------|-----------------|--------|
| Video: Konsep Bilangan Bulat | 0.95 | Materi video yang cocok dengan gaya belajar visualmu |
| Infografis: Operasi Bilangan Bulat | 0.92 | Infografis membantu kamu memahami konsep secara visual |
| Video: Pengenalan Pecahan | 0.88 | Lanjutkan dengan video pecahan setelah menguasai bilangan bulat |
| Dokumen: Rumus Aljabar Bergambar | 0.85 | Dokumen dengan banyak gambar sesuai preferensi visualmu |
| Video: Bangun Datar dan Ruang | 0.82 | Geometri lebih mudah dipahami melalui visualisasi |

### 4.2 Rekomendasi untuk Siti (Auditori)

| Materi | Relevance Score | Alasan |
|--------|-----------------|--------|
| Podcast: Memahami Bilangan Bulat | 0.96 | Podcast ideal untuk gaya belajar auditori sepertimu |
| Audio: Lagu Rumus Pecahan | 0.93 | Belajar dengan irama dan melodi mempermudah ingatan |
| Video: Penjelasan Verbal Aljabar | 0.89 | Video dengan penjelasan verbal mendalam |
| Podcast: Cerita Geometri | 0.86 | Konsep geometri dalam format cerita audio |
| Audio: Tips Mengingat Rumus | 0.83 | Audio tips yang bisa didengar berulang kali |

### 4.3 Rekomendasi untuk Andi (Kinestetik)

| Materi | Relevance Score | Alasan |
|--------|-----------------|--------|
| Simulasi: Garis Bilangan Interaktif | 0.97 | Simulasi interaktif cocok untuk pembelajar kinestetik |
| Simulasi: Kalkulator Pecahan | 0.94 | Praktik langsung dengan kalkulator pecahan |
| Simulasi: Puzzle Aljabar | 0.90 | Belajar aljabar melalui permainan puzzle |
| Simulasi: Bangun 3D Interaktif | 0.87 | Eksplorasi geometri dengan model 3D |
| Video: Praktik Menghitung Statistik | 0.84 | Video dengan demonstrasi langkah demi langkah |

---

## 5. Demo Learning Activities

### 5.1 Aktivitas Budi (Visual)

| Materi | Durasi | Status | Tanggal |
|--------|--------|--------|---------|
| Video: Konsep Bilangan Bulat | 25 menit | completed | 3 hari lalu |
| Infografis: Operasi Bilangan Bulat | 15 menit | completed | 2 hari lalu |
| Video: Pengenalan Pecahan | 30 menit | in_progress | hari ini |

### 5.2 Aktivitas Siti (Auditori)

| Materi | Durasi | Status | Tanggal |
|--------|--------|--------|---------|
| Podcast: Memahami Bilangan Bulat | 20 menit | completed | 4 hari lalu |
| Audio: Lagu Rumus Pecahan | 10 menit | completed | 3 hari lalu |
| Video: Penjelasan Verbal Aljabar | 35 menit | completed | 2 hari lalu |
| Podcast: Cerita Geometri | 18 menit | in_progress | hari ini |

### 5.3 Aktivitas Andi (Kinestetik)

| Materi | Durasi | Status | Tanggal |
|--------|--------|--------|---------|
| Simulasi: Garis Bilangan Interaktif | 40 menit | completed | 5 hari lalu |
| Simulasi: Kalkulator Pecahan | 25 menit | completed | 3 hari lalu |
| Simulasi: Puzzle Aljabar | 45 menit | in_progress | hari ini |

---

## 6. Demo Student Progress

### 6.1 Progress per Topik

| Siswa | Topik | Score | Status |
|-------|-------|-------|--------|
| Budi | Bilangan Bulat | 85 | completed |
| Budi | Pecahan | 60 | in_progress |
| Siti | Bilangan Bulat | 90 | completed |
| Siti | Pecahan | 78 | completed |
| Siti | Aljabar Dasar | 45 | in_progress |
| Andi | Bilangan Bulat | 75 | completed |
| Andi | Pecahan | 70 | completed |
| Andi | Aljabar Dasar | 50 | in_progress |

---

## 7. Demo AI Feedback

### 7.1 Feedback untuk Budi (Visual)

**Type: encouragement**
```
## Hebat, Budi! 🌟

Kamu sudah menyelesaikan 2 materi dalam minggu ini dengan sangat baik!

### Pencapaian Minggu Ini
- ✅ Menguasai konsep bilangan bulat (85%)
- 📺 Menonton 2 video pembelajaran
- ⏱️ Total waktu belajar: 40 menit

### Tips untuk Gaya Belajar Visualmu
Kamu adalah pembelajar visual! Manfaatkan:
- Diagram dan mind map saat belajar
- Video dengan ilustrasi yang jelas
- Catatan dengan warna-warni

Terus semangat! 🚀
```

### 7.2 Feedback untuk Siti (Auditori)

**Type: suggestion**
```
## Progress Bagus, Siti! 🎵

Kamu sudah konsisten belajar dengan gaya auditorimu!

### Statistik Belajar
- 🎧 4 materi audio selesai
- ⏱️ Total 83 menit belajar
- 📈 Progress pecahan: 78%

### Saran untuk Minggu Depan
Sebagai pembelajar auditori, coba:
- Rekam penjelasanmu sendiri dan dengarkan ulang
- Diskusikan materi dengan teman atau keluarga
- Gunakan audio materi aljabar untuk topik berikutnya

Keep listening and learning! 🎶
```

### 7.3 Feedback untuk Andi (Kinestetik)

**Type: learning_progress**
```
## Luar Biasa, Andi! 💪

Kamu aktif berlatih dengan simulasi interaktif!

### Aktivitas Minggu Ini
- 🎮 3 simulasi dikerjakan
- ⏱️ 110 menit praktik langsung
- 🧩 Puzzle aljabar sedang dalam progress

### Rekomendasi untuk Pembelajar Kinestetik
Kamu belajar paling baik dengan praktik langsung:
- Coba simulasi bangun 3D untuk geometri
- Jangan lupa istirahat dan gerak setiap 30 menit
- Praktikkan rumus dengan menulisnya berulang kali

Tetap aktif bergerak dan belajar! 🏃
```

---

## 8. Implementasi

### 8.1 File yang Dibuat

```
database/seeders/
├── DemoDataSeeder.php          # Main seeder untuk demo data
├── DemoStudentSeeder.php       # Seeder untuk siswa demo
├── DemoMaterialSeeder.php      # Seeder untuk materi pembelajaran
├── DemoActivitySeeder.php      # Seeder untuk aktivitas & progress
└── DemoRecommendationSeeder.php # Seeder untuk rekomendasi & feedback
```

### 8.2 Perintah Artisan

```bash
# Seed demo data (fresh)
php artisan db:seed --class=DemoDataSeeder

# Atau tambahkan ke database yang sudah ada
php artisan db:seed --class=DemoDataSeeder
```

### 8.3 Dependensi

- Subject "Matematika" harus sudah ada (dari SubjectSeeder)
- LearningStyleQuestions harus sudah ada (dari LearningStyleQuestionSeeder)
- User dengan role 'teacher' harus ada untuk assign materials

---

## 9. Catatan Penting

1. **Password demo**: Semua user demo menggunakan password `demo1234`
2. **Bahasa**: Semua konten dalam Bahasa Indonesia
3. **Konsistensi**: Data demo harus konsisten dengan profil gaya belajar
4. **Realistis**: Score dan progress harus realistis (tidak sempurna)
5. **Variasi**: Tunjukkan berbagai status (completed, in_progress)

---

## 10. Validasi Demo

### Checklist Demonstrasi

- [ ] Login sebagai Budi → Lihat rekomendasi visual
- [ ] Login sebagai Siti → Lihat rekomendasi audio
- [ ] Login sebagai Andi → Lihat rekomendasi simulasi
- [ ] Bandingkan rekomendasi antar siswa → Harus berbeda
- [ ] Lihat feedback AI → Sesuai gaya belajar masing-masing
- [ ] Lihat progress dashboard → Data aktivitas terlihat
- [ ] Filter materi by gaya belajar → Hasil sesuai filter
