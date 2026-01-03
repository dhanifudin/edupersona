<?php

namespace Database\Seeders;

use App\Models\LearningMaterial;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoMaterialSeeder extends Seeder
{
    /**
     * Demo learning materials untuk setiap gaya belajar
     */
    public function run(): void
    {
        $subject = Subject::where('code', 'MTK')->first();
        $teacher = User::where('email', 'guru@edupersona.test')->first();

        if (! $subject || ! $teacher) {
            $this->command->warn('Subject MTK atau Teacher tidak ditemukan.');

            return;
        }

        $materials = [
            // === VISUAL MATERIALS (6) ===
            [
                'title' => 'Video: Konsep Bilangan Bulat',
                'description' => 'Video pembelajaran interaktif tentang konsep dasar bilangan bulat dengan animasi visual yang menarik.',
                'topic' => 'Bilangan Bulat',
                'type' => 'video',
                'learning_style' => 'visual',
                'difficulty_level' => 'beginner',
                'content_url' => 'https://www.youtube.com/watch?v=demo_bilangan_bulat',
            ],
            [
                'title' => 'Infografis: Operasi Bilangan Bulat',
                'description' => 'Infografis lengkap tentang operasi penjumlahan, pengurangan, perkalian, dan pembagian bilangan bulat.',
                'topic' => 'Bilangan Bulat',
                'type' => 'infographic',
                'learning_style' => 'visual',
                'difficulty_level' => 'beginner',
                'content_url' => null,
                'file_path' => 'materials/infografis-operasi-bilangan-bulat.png',
            ],
            [
                'title' => 'Video: Pengenalan Pecahan',
                'description' => 'Video animasi yang menjelaskan konsep pecahan dengan visualisasi pizza dan kue.',
                'topic' => 'Pecahan',
                'type' => 'video',
                'learning_style' => 'visual',
                'difficulty_level' => 'beginner',
                'content_url' => 'https://www.youtube.com/watch?v=demo_pecahan',
            ],
            [
                'title' => 'Infografis: Jenis-jenis Pecahan',
                'description' => 'Diagram visual yang menampilkan berbagai jenis pecahan: biasa, campuran, desimal, dan persen.',
                'topic' => 'Pecahan',
                'type' => 'infographic',
                'learning_style' => 'visual',
                'difficulty_level' => 'intermediate',
                'content_url' => null,
                'file_path' => 'materials/infografis-jenis-pecahan.png',
            ],
            [
                'title' => 'Dokumen: Rumus Aljabar Bergambar',
                'description' => 'Dokumen PDF dengan rumus-rumus aljabar dilengkapi ilustrasi dan contoh soal bergambar.',
                'topic' => 'Aljabar Dasar',
                'type' => 'document',
                'learning_style' => 'visual',
                'difficulty_level' => 'intermediate',
                'content_url' => null,
                'file_path' => 'materials/rumus-aljabar-bergambar.pdf',
            ],
            [
                'title' => 'Video: Bangun Datar dan Ruang',
                'description' => 'Video 3D yang menampilkan berbagai bangun datar dan ruang beserta sifat-sifatnya.',
                'topic' => 'Geometri Dasar',
                'type' => 'video',
                'learning_style' => 'visual',
                'difficulty_level' => 'beginner',
                'content_url' => 'https://www.youtube.com/watch?v=demo_geometri',
            ],

            // === AUDITORY MATERIALS (6) ===
            [
                'title' => 'Podcast: Memahami Bilangan Bulat',
                'description' => 'Podcast audio yang menjelaskan konsep bilangan bulat dengan bahasa sederhana dan contoh kehidupan sehari-hari.',
                'topic' => 'Bilangan Bulat',
                'type' => 'audio',
                'learning_style' => 'auditory',
                'difficulty_level' => 'beginner',
                'content_url' => null,
                'file_path' => 'materials/podcast-bilangan-bulat.mp3',
            ],
            [
                'title' => 'Audio: Lagu Rumus Pecahan',
                'description' => 'Lagu edukatif yang membantu mengingat rumus-rumus pecahan dengan irama yang catchy.',
                'topic' => 'Pecahan',
                'type' => 'audio',
                'learning_style' => 'auditory',
                'difficulty_level' => 'beginner',
                'content_url' => null,
                'file_path' => 'materials/lagu-rumus-pecahan.mp3',
            ],
            [
                'title' => 'Video: Penjelasan Verbal Aljabar',
                'description' => 'Video dengan penjelasan verbal mendalam tentang konsep aljabar tanpa terlalu banyak teks.',
                'topic' => 'Aljabar Dasar',
                'type' => 'video',
                'learning_style' => 'auditory',
                'difficulty_level' => 'intermediate',
                'content_url' => 'https://www.youtube.com/watch?v=demo_aljabar_verbal',
            ],
            [
                'title' => 'Podcast: Cerita Geometri',
                'description' => 'Podcast bercerita tentang sejarah dan penerapan geometri dalam kehidupan sehari-hari.',
                'topic' => 'Geometri Dasar',
                'type' => 'audio',
                'learning_style' => 'auditory',
                'difficulty_level' => 'beginner',
                'content_url' => null,
                'file_path' => 'materials/podcast-cerita-geometri.mp3',
            ],
            [
                'title' => 'Audio: Tips Mengingat Rumus',
                'description' => 'Audio panduan dengan tips mnemonik untuk mengingat berbagai rumus matematika.',
                'topic' => 'Aljabar Dasar',
                'type' => 'audio',
                'learning_style' => 'auditory',
                'difficulty_level' => 'intermediate',
                'content_url' => null,
                'file_path' => 'materials/tips-mengingat-rumus.mp3',
            ],
            [
                'title' => 'Video: Diskusi Statistika',
                'description' => 'Video diskusi kelompok yang membahas konsep statistika dasar dengan dialog interaktif.',
                'topic' => 'Statistika Dasar',
                'type' => 'video',
                'learning_style' => 'auditory',
                'difficulty_level' => 'beginner',
                'content_url' => 'https://www.youtube.com/watch?v=demo_statistika_diskusi',
            ],

            // === KINESTHETIC MATERIALS (6) ===
            [
                'title' => 'Simulasi: Garis Bilangan Interaktif',
                'description' => 'Simulasi interaktif di mana siswa dapat menggerakkan titik pada garis bilangan.',
                'topic' => 'Bilangan Bulat',
                'type' => 'simulation',
                'learning_style' => 'kinesthetic',
                'difficulty_level' => 'beginner',
                'content_url' => 'https://phet.colorado.edu/id/simulations/number-line',
            ],
            [
                'title' => 'Simulasi: Kalkulator Pecahan',
                'description' => 'Kalkulator pecahan interaktif dengan visualisasi potongan pie yang bisa dimanipulasi.',
                'topic' => 'Pecahan',
                'type' => 'simulation',
                'learning_style' => 'kinesthetic',
                'difficulty_level' => 'beginner',
                'content_url' => 'https://phet.colorado.edu/id/simulations/fractions',
            ],
            [
                'title' => 'Simulasi: Puzzle Aljabar',
                'description' => 'Game puzzle di mana siswa menyusun potongan untuk membentuk persamaan aljabar.',
                'topic' => 'Aljabar Dasar',
                'type' => 'simulation',
                'learning_style' => 'kinesthetic',
                'difficulty_level' => 'intermediate',
                'content_url' => 'https://phet.colorado.edu/id/simulations/area-model-algebra',
            ],
            [
                'title' => 'Simulasi: Bangun 3D Interaktif',
                'description' => 'Eksplorasi 3D di mana siswa dapat memutar, memperbesar, dan membedah bangun ruang.',
                'topic' => 'Geometri Dasar',
                'type' => 'simulation',
                'learning_style' => 'kinesthetic',
                'difficulty_level' => 'intermediate',
                'content_url' => 'https://www.geogebra.org/3d',
            ],
            [
                'title' => 'Video: Praktik Menghitung Statistik',
                'description' => 'Video tutorial step-by-step menghitung mean, median, modus dengan contoh langsung.',
                'topic' => 'Statistika Dasar',
                'type' => 'video',
                'learning_style' => 'kinesthetic',
                'difficulty_level' => 'beginner',
                'content_url' => 'https://www.youtube.com/watch?v=demo_statistika_praktik',
            ],
            [
                'title' => 'Simulasi: Grafik Statistika',
                'description' => 'Simulasi pembuatan grafik statistika interaktif dengan data yang bisa diubah-ubah.',
                'topic' => 'Statistika Dasar',
                'type' => 'simulation',
                'learning_style' => 'kinesthetic',
                'difficulty_level' => 'intermediate',
                'content_url' => 'https://www.desmos.com/calculator',
            ],

            // === UNIVERSAL MATERIALS (3) - for all learning styles ===
            [
                'title' => 'Dokumen: Ringkasan Matematika Dasar',
                'description' => 'Dokumen ringkasan lengkap materi matematika dasar untuk semua gaya belajar.',
                'topic' => 'Bilangan Bulat',
                'type' => 'document',
                'learning_style' => 'all',
                'difficulty_level' => 'beginner',
                'content_url' => null,
                'file_path' => 'materials/ringkasan-matematika-dasar.pdf',
            ],
            [
                'title' => 'Video: Review Materi Semester',
                'description' => 'Video komprehensif yang merangkum seluruh materi aljabar dalam satu semester.',
                'topic' => 'Aljabar Dasar',
                'type' => 'video',
                'learning_style' => 'all',
                'difficulty_level' => 'advanced',
                'content_url' => 'https://www.youtube.com/watch?v=demo_review_semester',
            ],
            [
                'title' => 'Dokumen: Latihan Soal Campuran',
                'description' => 'Kumpulan latihan soal statistika dari berbagai tingkat kesulitan.',
                'topic' => 'Statistika Dasar',
                'type' => 'document',
                'learning_style' => 'all',
                'difficulty_level' => 'intermediate',
                'content_url' => null,
                'file_path' => 'materials/latihan-soal-statistika.pdf',
            ],
        ];

        foreach ($materials as $materialData) {
            LearningMaterial::firstOrCreate(
                [
                    'title' => $materialData['title'],
                    'subject_id' => $subject->id,
                ],
                [
                    'teacher_id' => $teacher->id,
                    'description' => $materialData['description'],
                    'topic' => $materialData['topic'],
                    'type' => $materialData['type'],
                    'learning_style' => $materialData['learning_style'],
                    'difficulty_level' => $materialData['difficulty_level'],
                    'content_url' => $materialData['content_url'] ?? null,
                    'file_path' => $materialData['file_path'] ?? null,
                    'is_active' => true,
                ]
            );
        }

        $this->command->info('Created 21 demo learning materials.');
    }
}
