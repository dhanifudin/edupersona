<?php

namespace Database\Seeders;

use App\Models\AiFeedback;
use App\Models\AiRecommendation;
use App\Models\LearningMaterial;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoRecommendationSeeder extends Seeder
{
    /**
     * Demo AI recommendations dan feedback untuk siswa demo
     */
    public function run(): void
    {
        $subject = Subject::where('code', 'MTK')->first();

        if (! $subject) {
            $this->command->warn('Subject MTK tidak ditemukan.');

            return;
        }

        // Get demo students
        $budi = User::where('email', 'budi@demo.test')->first();
        $siti = User::where('email', 'siti@demo.test')->first();
        $andi = User::where('email', 'andi@demo.test')->first();
        $dewi = User::where('email', 'dewi@demo.test')->first();

        if (! $budi || ! $siti || ! $andi) {
            $this->command->warn('Demo students tidak ditemukan.');

            return;
        }

        // === BUDI (Visual) Recommendations ===
        $this->createRecommendations($budi, $subject, [
            [
                'material_title' => 'Video: Konsep Bilangan Bulat',
                'relevance_score' => 0.95,
                'reason' => 'Materi video yang cocok dengan gaya belajar visualmu. Animasi dan grafik membantu memahami konsep bilangan bulat.',
            ],
            [
                'material_title' => 'Infografis: Operasi Bilangan Bulat',
                'relevance_score' => 0.92,
                'reason' => 'Infografis ini membantu kamu memahami operasi bilangan bulat secara visual dengan diagram yang jelas.',
            ],
            [
                'material_title' => 'Video: Pengenalan Pecahan',
                'relevance_score' => 0.88,
                'reason' => 'Lanjutkan dengan video pecahan setelah menguasai bilangan bulat. Visualisasi yang menarik!',
            ],
            [
                'material_title' => 'Dokumen: Rumus Aljabar Bergambar',
                'relevance_score' => 0.85,
                'reason' => 'Dokumen dengan banyak ilustrasi dan gambar sesuai preferensi visual kamu.',
            ],
            [
                'material_title' => 'Video: Bangun Datar dan Ruang',
                'relevance_score' => 0.82,
                'reason' => 'Geometri lebih mudah dipahami melalui visualisasi 3D dalam video ini.',
            ],
        ]);

        // Budi Feedback
        $this->createFeedback($budi, 'encouragement', $this->getBudiFeedback());

        // === SITI (Auditory) Recommendations ===
        $this->createRecommendations($siti, $subject, [
            [
                'material_title' => 'Podcast: Memahami Bilangan Bulat',
                'relevance_score' => 0.96,
                'reason' => 'Podcast ideal untuk gaya belajar auditori sepertimu. Penjelasan verbal yang mudah diikuti.',
            ],
            [
                'material_title' => 'Audio: Lagu Rumus Pecahan',
                'relevance_score' => 0.93,
                'reason' => 'Belajar dengan irama dan melodi mempermudah ingatan. Lagu ini akan membantumu mengingat rumus pecahan.',
            ],
            [
                'material_title' => 'Video: Penjelasan Verbal Aljabar',
                'relevance_score' => 0.89,
                'reason' => 'Video dengan penjelasan verbal mendalam tanpa terlalu banyak teks visual.',
            ],
            [
                'material_title' => 'Podcast: Cerita Geometri',
                'relevance_score' => 0.86,
                'reason' => 'Konsep geometri dijelaskan dalam format cerita audio yang menarik.',
            ],
            [
                'material_title' => 'Audio: Tips Mengingat Rumus',
                'relevance_score' => 0.83,
                'reason' => 'Audio tips mnemonik yang bisa didengar berulang kali untuk mengingat rumus.',
            ],
        ]);

        // Siti Feedback
        $this->createFeedback($siti, 'suggestion', $this->getSitiFeedback());

        // === ANDI (Kinesthetic) Recommendations ===
        $this->createRecommendations($andi, $subject, [
            [
                'material_title' => 'Simulasi: Garis Bilangan Interaktif',
                'relevance_score' => 0.97,
                'reason' => 'Simulasi interaktif yang cocok untuk pembelajar kinestetik. Kamu bisa menggerakkan titik langsung!',
            ],
            [
                'material_title' => 'Simulasi: Kalkulator Pecahan',
                'relevance_score' => 0.94,
                'reason' => 'Praktik langsung dengan kalkulator pecahan interaktif. Manipulasi potongan pie virtual!',
            ],
            [
                'material_title' => 'Simulasi: Puzzle Aljabar',
                'relevance_score' => 0.90,
                'reason' => 'Belajar aljabar melalui permainan puzzle yang hands-on dan menyenangkan.',
            ],
            [
                'material_title' => 'Simulasi: Bangun 3D Interaktif',
                'relevance_score' => 0.87,
                'reason' => 'Eksplorasi geometri dengan model 3D yang bisa kamu putar dan bedah.',
            ],
            [
                'material_title' => 'Video: Praktik Menghitung Statistik',
                'relevance_score' => 0.84,
                'reason' => 'Video dengan demonstrasi langkah demi langkah yang bisa kamu ikuti sambil praktik.',
            ],
        ]);

        // Andi Feedback
        $this->createFeedback($andi, 'learning_progress', $this->getAndiFeedback());

        // === DEWI (Mixed) Recommendations ===
        if ($dewi) {
            $this->createRecommendations($dewi, $subject, [
                [
                    'material_title' => 'Dokumen: Ringkasan Matematika Dasar',
                    'relevance_score' => 0.90,
                    'reason' => 'Ringkasan komprehensif cocok untuk gaya belajar campuranmu.',
                ],
                [
                    'material_title' => 'Video: Review Materi Semester',
                    'relevance_score' => 0.88,
                    'reason' => 'Video review dengan visual dan audio yang seimbang.',
                ],
                [
                    'material_title' => 'Simulasi: Puzzle Aljabar',
                    'relevance_score' => 0.85,
                    'reason' => 'Simulasi interaktif untuk variasi cara belajar.',
                ],
                [
                    'material_title' => 'Video: Konsep Bilangan Bulat',
                    'relevance_score' => 0.82,
                    'reason' => 'Video dasar yang bisa membantu memperkuat pemahaman.',
                ],
                [
                    'material_title' => 'Podcast: Memahami Bilangan Bulat',
                    'relevance_score' => 0.80,
                    'reason' => 'Podcast alternatif untuk belajar sambil beraktivitas.',
                ],
            ]);

            $this->createFeedback($dewi, 'encouragement', $this->getDewiFeedback());
        }

        $this->command->info('Created demo AI recommendations and feedback.');
    }

    private function createRecommendations(User $student, Subject $subject, array $recommendations): void
    {
        foreach ($recommendations as $recData) {
            $material = LearningMaterial::where('title', $recData['material_title'])
                ->where('subject_id', $subject->id)
                ->first();

            if (! $material) {
                $this->command->warn("Material not found: {$recData['material_title']}");

                continue;
            }

            AiRecommendation::firstOrCreate(
                [
                    'user_id' => $student->id,
                    'material_id' => $material->id,
                ],
                [
                    'reason' => $recData['reason'],
                    'relevance_score' => $recData['relevance_score'],
                    'is_viewed' => false,
                    'viewed_at' => null,
                ]
            );
        }
    }

    private function createFeedback(User $student, string $type, string $feedbackText): void
    {
        AiFeedback::firstOrCreate(
            [
                'user_id' => $student->id,
                'context_type' => 'progress',
                'feedback_type' => $type,
            ],
            [
                'context_id' => null,
                'feedback_text' => $feedbackText,
                'is_read' => false,
                'generated_at' => now()->subDays(1),
            ]
        );
    }

    private function getBudiFeedback(): string
    {
        return <<<'FEEDBACK'
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
- Catatan dengan warna-warni dan highlight

### Saran untuk Minggu Depan
Fokus pada materi pecahan berikutnya. Coba lihat infografis jenis-jenis pecahan yang sudah direkomendasikan untukmu.

Terus semangat! 🚀
FEEDBACK;
    }

    private function getSitiFeedback(): string
    {
        return <<<'FEEDBACK'
## Progress Bagus, Siti! 🎵

Kamu sudah konsisten belajar dengan gaya auditorimu!

### Statistik Belajar
- 🎧 4 materi audio selesai
- ⏱️ Total 83 menit belajar
- 📈 Progress pecahan: 78%

### Kekuatan Belajarmu
Sebagai pembelajar auditori, kamu unggul dalam:
- Memahami penjelasan verbal
- Mengingat melalui irama dan melodi
- Diskusi dan tanya jawab

### Saran untuk Minggu Depan
Lanjutkan ke materi aljabar. Coba:
- Rekam penjelasanmu sendiri dan dengarkan ulang
- Diskusikan materi dengan teman atau keluarga
- Gunakan podcast aljabar yang sudah direkomendasikan

Keep listening and learning! 🎶
FEEDBACK;
    }

    private function getAndiFeedback(): string
    {
        return <<<'FEEDBACK'
## Luar Biasa, Andi! 💪

Kamu aktif berlatih dengan simulasi interaktif!

### Aktivitas Minggu Ini
- 🎮 3 simulasi dikerjakan
- ⏱️ 110 menit praktik langsung
- 🧩 Puzzle aljabar sedang dalam progress

### Gaya Belajar Kinestetikmu
Kamu belajar paling baik dengan praktik langsung. Terus manfaatkan:
- Simulasi interaktif untuk memahami konsep
- Eksperimen dan manipulasi objek virtual
- Belajar sambil bergerak dan beraktivitas

### Rekomendasi untuk Minggu Depan
Selesaikan puzzle aljabar yang sedang kamu kerjakan. Setelah itu:
- Coba simulasi bangun 3D untuk geometri
- Jangan lupa istirahat dan gerak setiap 30 menit
- Praktikkan rumus dengan menulisnya berulang kali

Tetap aktif bergerak dan belajar! 🏃
FEEDBACK;
    }

    private function getDewiFeedback(): string
    {
        return <<<'FEEDBACK'
## Semangat, Dewi! ✨

Dengan gaya belajar campuran, kamu punya fleksibilitas yang tinggi!

### Profil Belajarmu
- 👁️ Visual: 72%
- 👂 Auditori: 70%
- 🖐️ Kinestetik: 68%

### Keuntungan Gaya Campuran
Kamu bisa belajar efektif dengan berbagai metode:
- Video untuk memahami konsep baru
- Audio untuk review saat perjalanan
- Simulasi untuk memperdalam pemahaman

### Tips untuk Minggu Ini
Variasikan cara belajarmu:
1. Mulai dengan video untuk overview
2. Dengarkan podcast untuk penguatan
3. Praktikkan dengan simulasi

Eksplorasi berbagai materi dan temukan kombinasi terbaikmu! 🌈
FEEDBACK;
    }
}
