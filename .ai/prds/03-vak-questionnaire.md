# VAK Questionnaire Specification

## Overview

Kuesioner VAK (Visual, Auditory, Kinesthetic) untuk mengidentifikasi gaya belajar dominan siswa. Menggunakan 15 pertanyaan dengan skala Likert 1-5.

## Likert Scale

| Skor | Label |
|------|-------|
| 1 | Sangat Tidak Setuju |
| 2 | Tidak Setuju |
| 3 | Netral |
| 4 | Setuju |
| 5 | Sangat Setuju |

## Questions (Bahasa Indonesia)

### Visual (V) - 5 Pertanyaan

| No | Pertanyaan |
|----|------------|
| V1 | Saya lebih mudah memahami materi yang disajikan dalam bentuk gambar, diagram, atau grafik |
| V2 | Saya suka mencatat dengan menggunakan warna-warna berbeda dan membuat mind map |
| V3 | Saya lebih suka membaca instruksi tertulis daripada mendengarkan penjelasan lisan |
| V4 | Saya mudah mengingat wajah orang tetapi sering lupa namanya |
| V5 | Saya lebih suka menonton video pembelajaran daripada mendengarkan podcast |

### Auditory (A) - 5 Pertanyaan

| No | Pertanyaan |
|----|------------|
| A1 | Saya lebih mudah memahami materi ketika dijelaskan secara lisan oleh guru |
| A2 | Saya suka belajar sambil mendengarkan musik atau dalam suasana yang ada suaranya |
| A3 | Saya sering membaca dengan suara keras atau menggerakkan bibir saat membaca |
| A4 | Saya mudah mengingat nama orang tetapi sering lupa wajahnya |
| A5 | Saya lebih suka mendengarkan podcast atau audio book daripada membaca buku |

### Kinesthetic (K) - 5 Pertanyaan

| No | Pertanyaan |
|----|------------|
| K1 | Saya lebih suka belajar dengan melakukan praktik langsung atau eksperimen |
| K2 | Saya sulit duduk diam dalam waktu lama dan sering bergerak saat belajar |
| K3 | Saya suka menggunakan tangan saat berbicara atau menjelaskan sesuatu |
| K4 | Saya lebih mudah mengingat sesuatu yang pernah saya lakukan sendiri |
| K5 | Saya lebih suka bermain peran atau simulasi daripada membaca atau mendengarkan |

## Scoring Algorithm

### Step 1: Calculate Raw Scores

```php
$visualTotal = sum of scores for V1-V5 (range: 5-25)
$auditoryTotal = sum of scores for A1-A5 (range: 5-25)
$kinestheticTotal = sum of scores for K1-K5 (range: 5-25)
```

### Step 2: Calculate Percentages

```php
$grandTotal = $visualTotal + $auditoryTotal + $kinestheticTotal;

$visualPercentage = ($visualTotal / $grandTotal) * 100;
$auditoryPercentage = ($auditoryTotal / $grandTotal) * 100;
$kinestheticPercentage = ($kinestheticTotal / $grandTotal) * 100;
```

### Step 3: Determine Dominant Style

```php
$scores = [
    'visual' => $visualPercentage,
    'auditory' => $auditoryPercentage,
    'kinesthetic' => $kinestheticPercentage,
];

$maxScore = max($scores);
$dominantStyles = array_keys($scores, $maxScore);

if (count($dominantStyles) > 1) {
    $dominantStyle = 'mixed';
} else {
    $dominantStyle = $dominantStyles[0];
}
```

### Step 4: Threshold for Mixed

Jika selisih tertinggi dengan yang lain < 5%, maka dianggap "mixed":

```php
$sorted = arsort($scores);
$highest = array_values($scores)[0];
$secondHighest = array_values($scores)[1];

if (($highest - $secondHighest) < 5) {
    $dominantStyle = 'mixed';
}
```

## Learning Style Profiles

### Visual Learner

**Karakteristik:**
- Belajar terbaik melalui gambar, diagram, dan visualisasi
- Suka mencatat dengan warna dan mind map
- Mudah mengingat apa yang dilihat

**Rekomendasi Materi:**
- Video pembelajaran
- Infografis
- Diagram dan flowchart
- Presentasi dengan banyak gambar
- Mind map

**Tips Belajar:**
- Gunakan highlighter warna-warni
- Buat catatan visual dan diagram
- Tonton video sebelum membaca teks
- Visualisasikan konsep dalam pikiran

### Auditory Learner

**Karakteristik:**
- Belajar terbaik melalui mendengarkan
- Suka diskusi dan penjelasan lisan
- Mudah mengingat apa yang didengar

**Rekomendasi Materi:**
- Podcast pendidikan
- Audio book
- Rekaman penjelasan guru
- Diskusi kelompok

**Tips Belajar:**
- Rekam penjelasan dan dengarkan ulang
- Jelaskan materi ke orang lain
- Gunakan mnemonic berbasis suara
- Belajar dalam kelompok diskusi

### Kinesthetic Learner

**Karakteristik:**
- Belajar terbaik melalui praktik langsung
- Suka bergerak dan melakukan eksperimen
- Mudah mengingat apa yang dilakukan

**Rekomendasi Materi:**
- Simulasi interaktif
- Proyek hands-on
- Eksperimen laboratorium
- Role playing
- Games edukatif

**Tips Belajar:**
- Praktikkan langsung setiap konsep
- Ambil jeda untuk bergerak
- Gunakan model fisik atau manipulatif
- Belajar sambil berjalan atau bergerak

### Mixed Learner

**Karakteristik:**
- Kombinasi dari dua atau lebih gaya belajar
- Fleksibel dalam menerima berbagai format materi
- Dapat beradaptasi dengan berbagai metode

**Rekomendasi Materi:**
- Kombinasi berbagai format
- Materi yang menggabungkan visual, audio, dan praktik

**Tips Belajar:**
- Variasikan metode belajar
- Kombinasikan berbagai pendekatan
- Temukan keseimbangan yang cocok

## Seeder Data

```php
// database/seeders/LearningStyleQuestionSeeder.php

$questions = [
    // Visual
    ['question_text' => 'Saya lebih mudah memahami materi yang disajikan dalam bentuk gambar, diagram, atau grafik', 'style_type' => 'visual', 'order' => 1],
    ['question_text' => 'Saya suka mencatat dengan menggunakan warna-warna berbeda dan membuat mind map', 'style_type' => 'visual', 'order' => 2],
    ['question_text' => 'Saya lebih suka membaca instruksi tertulis daripada mendengarkan penjelasan lisan', 'style_type' => 'visual', 'order' => 3],
    ['question_text' => 'Saya mudah mengingat wajah orang tetapi sering lupa namanya', 'style_type' => 'visual', 'order' => 4],
    ['question_text' => 'Saya lebih suka menonton video pembelajaran daripada mendengarkan podcast', 'style_type' => 'visual', 'order' => 5],

    // Auditory
    ['question_text' => 'Saya lebih mudah memahami materi ketika dijelaskan secara lisan oleh guru', 'style_type' => 'auditory', 'order' => 6],
    ['question_text' => 'Saya suka belajar sambil mendengarkan musik atau dalam suasana yang ada suaranya', 'style_type' => 'auditory', 'order' => 7],
    ['question_text' => 'Saya sering membaca dengan suara keras atau menggerakkan bibir saat membaca', 'style_type' => 'auditory', 'order' => 8],
    ['question_text' => 'Saya mudah mengingat nama orang tetapi sering lupa wajahnya', 'style_type' => 'auditory', 'order' => 9],
    ['question_text' => 'Saya lebih suka mendengarkan podcast atau audio book daripada membaca buku', 'style_type' => 'auditory', 'order' => 10],

    // Kinesthetic
    ['question_text' => 'Saya lebih suka belajar dengan melakukan praktik langsung atau eksperimen', 'style_type' => 'kinesthetic', 'order' => 11],
    ['question_text' => 'Saya sulit duduk diam dalam waktu lama dan sering bergerak saat belajar', 'style_type' => 'kinesthetic', 'order' => 12],
    ['question_text' => 'Saya suka menggunakan tangan saat berbicara atau menjelaskan sesuatu', 'style_type' => 'kinesthetic', 'order' => 13],
    ['question_text' => 'Saya lebih mudah mengingat sesuatu yang pernah saya lakukan sendiri', 'style_type' => 'kinesthetic', 'order' => 14],
    ['question_text' => 'Saya lebih suka bermain peran atau simulasi daripada membaca atau mendengarkan', 'style_type' => 'kinesthetic', 'order' => 15],
];
```

## Service Class

```php
// app/Services/LearningStyleAnalyzer.php

class LearningStyleAnalyzer
{
    public function analyze(User $user): LearningStyleProfile
    {
        $responses = $user->learningStyleResponses()
            ->with('question')
            ->get();

        $scores = $this->calculateScores($responses);
        $percentages = $this->calculatePercentages($scores);
        $dominantStyle = $this->determineDominantStyle($percentages);

        return LearningStyleProfile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'visual_score' => $percentages['visual'],
                'auditory_score' => $percentages['auditory'],
                'kinesthetic_score' => $percentages['kinesthetic'],
                'dominant_style' => $dominantStyle,
                'analyzed_at' => now(),
            ]
        );
    }

    private function calculateScores(Collection $responses): array
    {
        return [
            'visual' => $responses->where('question.style_type', 'visual')->sum('score'),
            'auditory' => $responses->where('question.style_type', 'auditory')->sum('score'),
            'kinesthetic' => $responses->where('question.style_type', 'kinesthetic')->sum('score'),
        ];
    }

    private function calculatePercentages(array $scores): array
    {
        $total = array_sum($scores);

        return [
            'visual' => round(($scores['visual'] / $total) * 100, 2),
            'auditory' => round(($scores['auditory'] / $total) * 100, 2),
            'kinesthetic' => round(($scores['kinesthetic'] / $total) * 100, 2),
        ];
    }

    private function determineDominantStyle(array $percentages): string
    {
        arsort($percentages);
        $sorted = array_values($percentages);

        // If difference between top 2 is less than 5%, consider it mixed
        if (($sorted[0] - $sorted[1]) < 5) {
            return 'mixed';
        }

        return array_search($sorted[0], $percentages);
    }
}
```
