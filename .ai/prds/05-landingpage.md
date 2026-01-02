# Landing Page - EduPersona.ai

## Overview

Landing page untuk EduPersona.ai yang menampilkan fitur utama platform, demo interaktif kuesioner VAK, dan preview profil gaya belajar. Halaman ini dirancang dengan tema pendidikan/akademik yang profesional.

## Features Checklist

- [ ] Hero Section dengan tagline dan CTA
- [ ] Features Section dengan 3-4 fitur utama
- [ ] Interactive Demo Section (Mini Kuesioner VAK)
- [ ] Learning Profile Preview (Radar Chart)
- [ ] Navigation Header dengan Login/Register
- [ ] Footer dengan informasi singkat
- [ ] Responsive Design (Mobile-first)
- [ ] Dark Mode Support

---

## 1. Navigation Header

### Description
Header navigasi sederhana dengan logo, nama aplikasi, dan tombol autentikasi.

### User Stories
- Sebagai pengunjung, saya ingin melihat nama dan logo aplikasi agar tahu platform apa ini
- Sebagai pengunjung, saya ingin mengakses halaman login/register dengan mudah

### UI Requirements

**Layout:**
- Fixed header di atas (sticky on scroll)
- Logo + Nama "EduPersona.ai" di kiri
- Tombol "Masuk" dan "Daftar" di kanan
- Jika sudah login: Tombol "Dashboard" saja

**Komponen:**
```
[Logo] EduPersona.ai                    [Masuk] [Daftar]
```

**Styling:**
- Background: transparent atau blur on scroll
- Logo: AppLogoIcon dengan warna primary
- Tombol Masuk: variant outline
- Tombol Daftar: variant default (primary)

---

## 2. Hero Section

### Description
Section utama yang menjelaskan value proposition EduPersona.ai secara singkat dan menarik.

### User Stories
- Sebagai pengunjung, saya ingin memahami apa itu EduPersona.ai dalam 5 detik pertama
- Sebagai pengunjung, saya ingin langsung bisa mendaftar jika tertarik

### UI Requirements

**Layout:**
```
┌─────────────────────────────────────────────────────────────┐
│                                                             │
│     🎓  [Icon pendidikan/buku]                              │
│                                                             │
│     Temukan Gaya Belajarmu,                                 │
│     Raih Potensi Maksimal                                   │
│                                                             │
│     Platform pembelajaran yang dipersonalisasi              │
│     berdasarkan gaya belajar Visual, Auditori,              │
│     dan Kinestetik menggunakan AI.                          │
│                                                             │
│     [Mulai Sekarang - Gratis]    [Pelajari Lebih Lanjut]   │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

**Content:**
- Headline: "Temukan Gaya Belajarmu, Raih Potensi Maksimal"
- Subheadline: "Platform pembelajaran yang dipersonalisasi berdasarkan gaya belajar Visual, Auditori, dan Kinestetik menggunakan AI."
- CTA Primary: "Mulai Sekarang - Gratis" → link ke /register
- CTA Secondary: "Pelajari Lebih Lanjut" → scroll ke features

**Visual Elements:**
- Icon: GraduationCap, BookOpen, atau ilustrasi siswa belajar
- Background: Gradient subtle atau pattern akademik
- Badge: "Powered by Gemini AI" (opsional)

---

## 3. Features Section

### Description
Menampilkan 3-4 fitur utama EduPersona.ai dalam format card.

### User Stories
- Sebagai pengunjung, saya ingin mengetahui fitur-fitur yang ditawarkan
- Sebagai pengunjung, saya ingin memahami manfaat menggunakan platform ini

### UI Requirements

**Layout:**
```
┌─────────────────────────────────────────────────────────────┐
│                    Kenapa EduPersona.ai?                    │
│                                                             │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐         │
│  │ 📋         │  │ 🤖         │  │ 📊         │          │
│  │ Kuesioner  │  │ Rekomendasi │  │ Lacak      │          │
│  │ VAK        │  │ AI         │  │ Progress   │          │
│  │            │  │            │  │            │          │
│  │ Identifikasi│  │ Materi yang│  │ Pantau     │          │
│  │ gaya belajar│  │ sesuai gaya│  │ perkembangan│          │
│  │ dalam 5    │  │ belajarmu  │  │ belajarmu  │          │
│  │ menit      │  │            │  │            │          │
│  └─────────────┘  └─────────────┘  └─────────────┘         │
└─────────────────────────────────────────────────────────────┘
```

**Features List:**

| Fitur | Icon | Judul | Deskripsi |
|-------|------|-------|-----------|
| 1 | ClipboardList | Kuesioner VAK | Identifikasi gaya belajar Visual, Auditori, atau Kinestetik dalam 5 menit |
| 2 | Sparkles | Rekomendasi AI | Materi pembelajaran yang dipersonalisasi berdasarkan profilmu |
| 3 | TrendingUp | Lacak Progress | Pantau perkembangan dan statistik belajarmu secara real-time |
| 4 | Users | Multi-Role | Fitur lengkap untuk Siswa, Guru, dan Admin |

**Styling:**
- Grid: 3 kolom desktop, 1 kolom mobile
- Card: dengan shadow subtle, hover effect
- Icon: dalam circle background primary/10

---

## 4. Interactive Demo Section

### Description
Demo interaktif yang memungkinkan pengunjung mencoba mini kuesioner VAK dan melihat contoh hasil profil gaya belajar.

### User Stories
- Sebagai pengunjung, saya ingin mencoba kuesioner sebelum mendaftar
- Sebagai pengunjung, saya ingin melihat seperti apa hasil analisis gaya belajar

### UI Requirements

**Layout:**
```
┌─────────────────────────────────────────────────────────────┐
│                   Coba Demo Gratis                          │
│                                                             │
│  ┌─────────────────────────┐  ┌─────────────────────────┐  │
│  │   Mini Kuesioner VAK    │  │   Contoh Profil Belajar │  │
│  │                         │  │                         │  │
│  │ Pertanyaan 1/3          │  │      [Radar Chart]      │  │
│  │ ───────────────         │  │                         │  │
│  │ Saya lebih mudah        │  │   Visual: 45%           │  │
│  │ memahami materi jika    │  │   Auditori: 30%         │  │
│  │ ada gambar atau diagram │  │   Kinestetik: 25%       │  │
│  │                         │  │                         │  │
│  │ ○ Sangat Tidak Setuju   │  │   Gaya Dominan:         │  │
│  │ ○ Tidak Setuju          │  │   🎨 VISUAL             │  │
│  │ ○ Netral                │  │                         │  │
│  │ ● Setuju                │  │   "Kamu belajar paling  │  │
│  │ ○ Sangat Setuju         │  │   efektif dengan materi │  │
│  │                         │  │   visual seperti video  │  │
│  │ [Selanjutnya →]         │  │   dan infografis"       │  │
│  └─────────────────────────┘  └─────────────────────────┘  │
│                                                             │
│              [Daftar untuk Hasil Lengkap]                   │
└─────────────────────────────────────────────────────────────┘
```

### 4.1 Mini Kuesioner VAK

**Sample Questions (3 pertanyaan):**

| No | Pertanyaan | Tipe |
|----|------------|------|
| 1 | Saya lebih mudah memahami materi jika ada gambar atau diagram | Visual |
| 2 | Saya lebih suka mendengarkan penjelasan daripada membaca sendiri | Auditory |
| 3 | Saya lebih mudah mengingat sesuatu jika langsung mempraktikkannya | Kinesthetic |

**Interaction Flow:**
1. Tampilkan pertanyaan 1 dengan progress indicator "1/3"
2. User memilih jawaban (Likert 1-5)
3. Klik "Selanjutnya" → animasi slide ke pertanyaan berikutnya
4. Setelah pertanyaan 3: Tampilkan hasil di panel kanan
5. CTA: "Daftar untuk Hasil Lengkap"

**State Management:**
```typescript
interface DemoState {
  currentQuestion: number; // 0-2
  answers: number[]; // [1-5, 1-5, 1-5]
  showResult: boolean;
}
```

### 4.2 Learning Profile Preview (Radar Chart)

**Display:**
- Radar chart dengan 3 sumbu: Visual, Auditori, Kinestetik
- Nilai default: V:45%, A:30%, K:25%
- Update berdasarkan jawaban demo (simplified calculation)
- Badge gaya dominan dengan warna:
  - Visual: Biru (#3B82F6)
  - Auditori: Hijau (#22C55E)
  - Kinestetik: Orange (#F97316)

**Simplified Scoring (untuk demo):**
```
Visual Score = answer[0] * 20
Auditory Score = answer[1] * 20
Kinesthetic Score = answer[2] * 20
Dominant = highest score
```

**Components Needed:**
- `LandingRadarChart.vue` - Simplified radar chart (bisa pakai CSS/SVG)
- Atau gunakan library ringan seperti Chart.js

---

## 5. CTA Section

### Description
Section call-to-action final untuk mendorong pengunjung mendaftar.

### User Stories
- Sebagai pengunjung yang sudah yakin, saya ingin mendaftar dengan cepat

### UI Requirements

**Layout:**
```
┌─────────────────────────────────────────────────────────────┐
│                                                             │
│           Siap Menemukan Gaya Belajarmu?                    │
│                                                             │
│     Bergabung dengan ribuan siswa yang sudah                │
│     meningkatkan cara belajar mereka.                       │
│                                                             │
│              [Daftar Sekarang - Gratis]                     │
│                                                             │
│         Sudah punya akun? [Masuk di sini]                   │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

**Styling:**
- Background: Primary color dengan opacity rendah atau gradient
- Text: Centered, kontras tinggi
- CTA Button: Large, prominent

---

## 6. Footer

### Description
Footer sederhana dengan informasi copyright dan links.

### UI Requirements

**Content:**
```
───────────────────────────────────────────────────────────────
EduPersona.ai - Platform Pembelajaran Personalisasi

© 2025 EduPersona.ai. Hak cipta dilindungi.
───────────────────────────────────────────────────────────────
```

---

## Technical Specification

### File Structure

```
resources/js/
├── pages/
│   └── Welcome.vue              # Landing page (redesign)
├── components/
│   └── landing/
│       ├── LandingHeader.vue    # Navigation header
│       ├── LandingHero.vue      # Hero section
│       ├── LandingFeatures.vue  # Features grid
│       ├── LandingDemo.vue      # Interactive demo container
│       ├── MiniQuestionnaire.vue # Mini VAK quiz
│       ├── DemoRadarChart.vue   # Simplified radar chart
│       ├── LandingCTA.vue       # Call-to-action section
│       └── LandingFooter.vue    # Footer
```

### Routes

| Method | Route | Description |
|--------|-------|-------------|
| GET | / | Landing page (existing) |

Tidak perlu route baru - Welcome.vue sudah di-render di route `/`.

### Props dari Backend

```php
// routes/web.php (existing)
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');
```

### Dependencies

**Existing (no new dependencies needed):**
- Lucide icons: `lucide-vue-next`
- Inertia Vue: `@inertiajs/vue3`
- Tailwind CSS v4

**Optional untuk Radar Chart:**
- CSS/SVG custom (recommended - no dependency)
- Atau `chart.js` + `vue-chartjs` jika sudah ada

---

## Responsive Breakpoints

| Breakpoint | Layout |
|------------|--------|
| Mobile (< 768px) | Single column, stacked sections |
| Tablet (768px - 1024px) | 2 columns untuk demo section |
| Desktop (> 1024px) | Full layout dengan sidebar demo |

---

## Dark Mode Support

Semua komponen harus mendukung dark mode menggunakan class `dark:`:
- Background: `bg-background dark:bg-background`
- Text: `text-foreground dark:text-foreground`
- Cards: `bg-card dark:bg-card`

---

## Implementation Order

1. **Phase 1: Structure**
   - [ ] Buat folder `resources/js/components/landing/`
   - [ ] Redesign `Welcome.vue` dengan section placeholders
   - [ ] Implementasi `LandingHeader.vue`

2. **Phase 2: Static Sections**
   - [ ] Implementasi `LandingHero.vue`
   - [ ] Implementasi `LandingFeatures.vue`
   - [ ] Implementasi `LandingCTA.vue`
   - [ ] Implementasi `LandingFooter.vue`

3. **Phase 3: Interactive Demo**
   - [ ] Implementasi `MiniQuestionnaire.vue` dengan state management
   - [ ] Implementasi `DemoRadarChart.vue` (SVG-based)
   - [ ] Implementasi `LandingDemo.vue` sebagai container

4. **Phase 4: Polish**
   - [ ] Responsive testing
   - [ ] Dark mode verification
   - [ ] Animation dan micro-interactions
   - [ ] Accessibility (keyboard navigation, screen readers)

5. **Phase 5: Testing**
   - [ ] Feature test untuk landing page accessibility
   - [ ] Visual regression test (optional)

---

## Related Documents

- [00-overview.md](./00-overview.md) - Project overview
- [03-vak-questionnaire.md](./03-vak-questionnaire.md) - VAK questionnaire specification
- [02-phase1-student.md](./02-phase1-student.md) - Student features reference
