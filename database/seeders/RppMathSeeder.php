<?php

namespace Database\Seeders;

use App\Models\LearningMaterial;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeder untuk materi pembelajaran Matematika Kelas XI
 * Berdasarkan RPP SMA Negeri 9 Malang Tahun Pelajaran 2025/2026
 *
 * BAB 1: Komposisi Fungsi dan Fungsi Invers (8 pertemuan)
 * BAB 2: Lingkaran (8 pertemuan)
 * BAB 3: Statistika (6 pertemuan)
 */
class RppMathSeeder extends Seeder
{
    public function run(): void
    {
        $subject = Subject::where('code', 'MTK')->first();
        $teacher = User::where('email', 'guru@edupersona.test')->first();

        if (! $subject || ! $teacher) {
            $this->command->warn('Subject MTK atau Teacher tidak ditemukan. Jalankan SubjectSeeder dan DemoStudentSeeder terlebih dahulu.');

            return;
        }

        $materials = array_merge(
            $this->getBab1Materials(),
            $this->getBab2Materials(),
            $this->getBab3Materials()
        );

        $count = 0;
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
            $count++;
        }

        $this->command->info("Created {$count} RPP-based learning materials for Matematika Kelas XI.");
    }

    /**
     * BAB 1: Komposisi Fungsi dan Fungsi Invers
     * 8 Pertemuan (16 JP @ 45 menit)
     */
    private function getBab1Materials(): array
    {
        return [
            // === KONSEP DASAR FUNGSI (Pertemuan 1-2) ===
            // Visual
            [
                'title' => 'Video: Pengertian dan Karakteristik Fungsi',
                'description' => 'Video pembelajaran yang menjelaskan definisi fungsi, domain, kodomain, dan range dengan visualisasi diagram panah yang menarik.',
                'topic' => 'Konsep Dasar Fungsi',
                'type' => 'video',
                'learning_style' => 'visual',
                'difficulty_level' => 'beginner',
                'content_url' => 'https://www.youtube.com/watch?v=rpp_fungsi_dasar',
            ],
            [
                'title' => 'Infografis: Domain, Kodomain, dan Range',
                'description' => 'Infografis lengkap yang menampilkan perbedaan domain, kodomain, dan range dalam berbagai representasi fungsi.',
                'topic' => 'Konsep Dasar Fungsi',
                'type' => 'infographic',
                'learning_style' => 'visual',
                'difficulty_level' => 'beginner',
                'file_path' => 'materials/rpp/infografis-domain-range.png',
            ],
            // Auditory
            [
                'title' => 'Podcast: Pengenalan Konsep Fungsi',
                'description' => 'Podcast audio yang menjelaskan konsep fungsi dengan analogi mesin dan proses dalam kehidupan sehari-hari.',
                'topic' => 'Konsep Dasar Fungsi',
                'type' => 'audio',
                'learning_style' => 'auditory',
                'difficulty_level' => 'beginner',
                'file_path' => 'materials/rpp/podcast-konsep-fungsi.mp3',
            ],
            [
                'title' => 'Video: Penjelasan Verbal Diagram Panah',
                'description' => 'Video dengan penjelasan verbal yang mendalam tentang cara membaca dan membuat diagram panah untuk fungsi.',
                'topic' => 'Konsep Dasar Fungsi',
                'type' => 'video',
                'learning_style' => 'auditory',
                'difficulty_level' => 'beginner',
                'content_url' => 'https://www.youtube.com/watch?v=rpp_diagram_panah',
            ],
            // Kinesthetic
            [
                'title' => 'Simulasi: Diagram Panah Interaktif',
                'description' => 'Simulasi interaktif di mana siswa dapat membuat dan memanipulasi diagram panah untuk memahami konsep fungsi.',
                'topic' => 'Konsep Dasar Fungsi',
                'type' => 'simulation',
                'learning_style' => 'kinesthetic',
                'difficulty_level' => 'beginner',
                'content_url' => 'https://www.geogebra.org/m/rpp_diagram_panah',
            ],
            [
                'title' => 'Simulasi: Eksplorasi Fungsi dengan GeoGebra',
                'description' => 'Eksplorasi fungsi menggunakan GeoGebra untuk menentukan domain dan range secara visual dan interaktif.',
                'topic' => 'Konsep Dasar Fungsi',
                'type' => 'simulation',
                'learning_style' => 'kinesthetic',
                'difficulty_level' => 'beginner',
                'content_url' => 'https://www.geogebra.org/m/rpp_eksplorasi_fungsi',
            ],

            // === KOMPOSISI FUNGSI (Pertemuan 3-4) ===
            // Visual
            [
                'title' => 'Video: Konsep Komposisi Fungsi',
                'description' => 'Video animasi yang menjelaskan komposisi fungsi sebagai gabungan dua fungsi dengan visualisasi dua mesin berurutan.',
                'topic' => 'Komposisi Fungsi',
                'type' => 'video',
                'learning_style' => 'visual',
                'difficulty_level' => 'intermediate',
                'content_url' => 'https://www.youtube.com/watch?v=rpp_komposisi_fungsi',
            ],
            [
                'title' => 'Infografis: Langkah-langkah Komposisi f o g',
                'description' => 'Infografis step-by-step cara menentukan hasil komposisi fungsi (f o g)(x) = f(g(x)) dengan contoh.',
                'topic' => 'Komposisi Fungsi',
                'type' => 'infographic',
                'learning_style' => 'visual',
                'difficulty_level' => 'intermediate',
                'file_path' => 'materials/rpp/infografis-komposisi-fungsi.png',
            ],
            // Auditory
            [
                'title' => 'Podcast: Analogi Mesin untuk Komposisi',
                'description' => 'Podcast yang menjelaskan komposisi fungsi menggunakan analogi dua mesin berurutan: mesin pertama menghasilkan output, lalu menjadi input mesin kedua.',
                'topic' => 'Komposisi Fungsi',
                'type' => 'audio',
                'learning_style' => 'auditory',
                'difficulty_level' => 'intermediate',
                'file_path' => 'materials/rpp/podcast-analogi-mesin.mp3',
            ],
            [
                'title' => 'Video: Diskusi Sifat Komposisi Fungsi',
                'description' => 'Video diskusi tentang sifat-sifat komposisi fungsi: apakah komutatif, asosiatif, dan syarat agar komposisi bisa dilakukan.',
                'topic' => 'Komposisi Fungsi',
                'type' => 'video',
                'learning_style' => 'auditory',
                'difficulty_level' => 'intermediate',
                'content_url' => 'https://www.youtube.com/watch?v=rpp_sifat_komposisi',
            ],
            // Kinesthetic
            [
                'title' => 'Simulasi: Komposisi Fungsi Interaktif',
                'description' => 'Simulasi interaktif untuk bereksperimen dengan komposisi fungsi, melihat bagaimana output satu fungsi menjadi input fungsi lain.',
                'topic' => 'Komposisi Fungsi',
                'type' => 'simulation',
                'learning_style' => 'kinesthetic',
                'difficulty_level' => 'intermediate',
                'content_url' => 'https://www.geogebra.org/m/rpp_komposisi_interaktif',
            ],
            [
                'title' => 'Lembar Kerja: Latihan Komposisi Fungsi',
                'description' => 'Lembar kerja praktikum dengan berbagai soal komposisi fungsi dari yang sederhana hingga kompleks.',
                'topic' => 'Komposisi Fungsi',
                'type' => 'document',
                'learning_style' => 'kinesthetic',
                'difficulty_level' => 'intermediate',
                'file_path' => 'materials/rpp/lkpd-komposisi-fungsi.pdf',
            ],

            // === FUNGSI INVERS (Pertemuan 5-6) ===
            // Visual
            [
                'title' => 'Video: Konsep Fungsi Invers',
                'description' => 'Video yang menjelaskan konsep fungsi invers sebagai kebalikan dari suatu fungsi dengan visualisasi proses terbalik.',
                'topic' => 'Fungsi Invers',
                'type' => 'video',
                'learning_style' => 'visual',
                'difficulty_level' => 'intermediate',
                'content_url' => 'https://www.youtube.com/watch?v=rpp_fungsi_invers',
            ],
            [
                'title' => 'Infografis: Syarat dan Rumus Fungsi Invers',
                'description' => 'Infografis tentang syarat fungsi memiliki invers (bijektif) dan langkah-langkah menentukan fungsi invers.',
                'topic' => 'Fungsi Invers',
                'type' => 'infographic',
                'learning_style' => 'visual',
                'difficulty_level' => 'intermediate',
                'file_path' => 'materials/rpp/infografis-fungsi-invers.png',
            ],
            // Auditory
            [
                'title' => 'Podcast: Memahami Kebalikan dalam Fungsi',
                'description' => 'Podcast yang menjelaskan konsep kebalikan dalam fungsi dengan analogi membuka-menutup pintu, menambah-mengurangi.',
                'topic' => 'Fungsi Invers',
                'type' => 'audio',
                'learning_style' => 'auditory',
                'difficulty_level' => 'intermediate',
                'file_path' => 'materials/rpp/podcast-kebalikan-fungsi.mp3',
            ],
            [
                'title' => 'Video: Penjelasan Verbal Fungsi Bijektif',
                'description' => 'Video dengan penjelasan verbal tentang fungsi bijektif (korespondensi satu-satu) sebagai syarat fungsi invers.',
                'topic' => 'Fungsi Invers',
                'type' => 'video',
                'learning_style' => 'auditory',
                'difficulty_level' => 'intermediate',
                'content_url' => 'https://www.youtube.com/watch?v=rpp_fungsi_bijektif',
            ],
            // Kinesthetic
            [
                'title' => 'Simulasi: Grafik Fungsi dan Inversnya',
                'description' => 'Simulasi GeoGebra untuk melihat simetri grafik fungsi dan inversnya terhadap garis y = x.',
                'topic' => 'Fungsi Invers',
                'type' => 'simulation',
                'learning_style' => 'kinesthetic',
                'difficulty_level' => 'intermediate',
                'content_url' => 'https://www.geogebra.org/m/rpp_grafik_invers',
            ],
            [
                'title' => 'Lembar Kerja: Menentukan Fungsi Invers',
                'description' => 'Lembar kerja praktikum untuk berlatih menentukan fungsi invers dari fungsi linear dan kuadrat.',
                'topic' => 'Fungsi Invers',
                'type' => 'document',
                'learning_style' => 'kinesthetic',
                'difficulty_level' => 'intermediate',
                'file_path' => 'materials/rpp/lkpd-fungsi-invers.pdf',
            ],

            // === APLIKASI DAN PENGUATAN (Pertemuan 7-8) ===
            // Visual
            [
                'title' => 'Video: Studi Kasus Diskon dan Pajak',
                'description' => 'Video studi kasus aplikasi komposisi fungsi dalam perhitungan diskon ganda dan pajak berlapis.',
                'topic' => 'Aplikasi Komposisi dan Invers',
                'type' => 'video',
                'learning_style' => 'visual',
                'difficulty_level' => 'advanced',
                'content_url' => 'https://www.youtube.com/watch?v=rpp_aplikasi_diskon',
            ],
            // Auditory
            [
                'title' => 'Video: Presentasi Aplikasi Fungsi',
                'description' => 'Video presentasi yang menjelaskan berbagai aplikasi fungsi dalam fisika, ekonomi, dan teknologi.',
                'topic' => 'Aplikasi Komposisi dan Invers',
                'type' => 'video',
                'learning_style' => 'auditory',
                'difficulty_level' => 'advanced',
                'content_url' => 'https://www.youtube.com/watch?v=rpp_presentasi_aplikasi',
            ],
            // Kinesthetic
            [
                'title' => 'Simulasi: Kalkulator Biaya Perjalanan',
                'description' => 'Simulasi interaktif untuk menghitung biaya perjalanan ojek online menggunakan komposisi fungsi jarak dan tarif.',
                'topic' => 'Aplikasi Komposisi dan Invers',
                'type' => 'simulation',
                'learning_style' => 'kinesthetic',
                'difficulty_level' => 'advanced',
                'content_url' => 'https://www.geogebra.org/m/rpp_kalkulator_biaya',
            ],
            // All
            [
                'title' => 'Dokumen: Ringkasan BAB 1 dan Latihan',
                'description' => 'Dokumen ringkasan lengkap materi BAB 1 (Komposisi Fungsi dan Fungsi Invers) beserta bank soal latihan.',
                'topic' => 'Aplikasi Komposisi dan Invers',
                'type' => 'document',
                'learning_style' => 'all',
                'difficulty_level' => 'advanced',
                'file_path' => 'materials/rpp/ringkasan-bab1-fungsi.pdf',
            ],
        ];
    }

    /**
     * BAB 2: Lingkaran
     * 8 Pertemuan (2 x 45 menit JP)
     */
    private function getBab2Materials(): array
    {
        return [
            // === PERSAMAAN LINGKARAN DAN UNSUR-UNSURNYA (Pertemuan 1-2) ===
            // Visual
            [
                'title' => 'Video: Unsur-unsur Lingkaran',
                'description' => 'Video animasi yang menjelaskan unsur-unsur lingkaran: pusat, jari-jari, diameter, busur, tali busur, dan juring.',
                'topic' => 'Persamaan Lingkaran',
                'type' => 'video',
                'learning_style' => 'visual',
                'difficulty_level' => 'beginner',
                'content_url' => 'https://www.youtube.com/watch?v=rpp_unsur_lingkaran',
            ],
            [
                'title' => 'Infografis: Persamaan Lingkaran (0,0) dan (a,b)',
                'description' => 'Infografis yang menampilkan rumus persamaan lingkaran dengan pusat di titik asal dan pusat di (a,b).',
                'topic' => 'Persamaan Lingkaran',
                'type' => 'infographic',
                'learning_style' => 'visual',
                'difficulty_level' => 'beginner',
                'file_path' => 'materials/rpp/infografis-persamaan-lingkaran.png',
            ],
            // Auditory
            [
                'title' => 'Podcast: Pengenalan Konsep Lingkaran',
                'description' => 'Podcast yang menjelaskan definisi lingkaran dan relevansinya dalam kehidupan sehari-hari seperti roda, jam, dan satelit.',
                'topic' => 'Persamaan Lingkaran',
                'type' => 'audio',
                'learning_style' => 'auditory',
                'difficulty_level' => 'beginner',
                'file_path' => 'materials/rpp/podcast-konsep-lingkaran.mp3',
            ],
            [
                'title' => 'Video: Penjelasan Verbal Rumus Lingkaran',
                'description' => 'Video dengan penjelasan verbal mendalam tentang cara menurunkan dan menggunakan rumus persamaan lingkaran.',
                'topic' => 'Persamaan Lingkaran',
                'type' => 'video',
                'learning_style' => 'auditory',
                'difficulty_level' => 'beginner',
                'content_url' => 'https://www.youtube.com/watch?v=rpp_rumus_lingkaran',
            ],
            // Kinesthetic
            [
                'title' => 'Simulasi: Menggambar Lingkaran di GeoGebra',
                'description' => 'Tutorial interaktif menggambar lingkaran di GeoGebra dengan berbagai pusat dan jari-jari.',
                'topic' => 'Persamaan Lingkaran',
                'type' => 'simulation',
                'learning_style' => 'kinesthetic',
                'difficulty_level' => 'beginner',
                'content_url' => 'https://www.geogebra.org/m/rpp_gambar_lingkaran',
            ],
            [
                'title' => 'Simulasi: Eksplorasi Pusat dan Jari-jari',
                'description' => 'Simulasi interaktif untuk bereksperimen dengan perubahan pusat dan jari-jari lingkaran serta melihat perubahan persamaannya.',
                'topic' => 'Persamaan Lingkaran',
                'type' => 'simulation',
                'learning_style' => 'kinesthetic',
                'difficulty_level' => 'beginner',
                'content_url' => 'https://www.geogebra.org/m/rpp_eksplorasi_lingkaran',
            ],

            // === LINGKARAN DAN GARIS SINGGUNG (Pertemuan 3-4) ===
            // Visual
            [
                'title' => 'Video: Sifat Garis Singgung Lingkaran',
                'description' => 'Video animasi yang menunjukkan sifat-sifat garis singgung lingkaran dan hubungannya dengan jari-jari.',
                'topic' => 'Garis Singgung Lingkaran',
                'type' => 'video',
                'learning_style' => 'visual',
                'difficulty_level' => 'intermediate',
                'content_url' => 'https://www.youtube.com/watch?v=rpp_garis_singgung',
            ],
            [
                'title' => 'Infografis: Persamaan Garis Singgung',
                'description' => 'Infografis tentang rumus persamaan garis singgung lingkaran yang melalui titik pada lingkaran.',
                'topic' => 'Garis Singgung Lingkaran',
                'type' => 'infographic',
                'learning_style' => 'visual',
                'difficulty_level' => 'intermediate',
                'file_path' => 'materials/rpp/infografis-garis-singgung.png',
            ],
            // Auditory
            [
                'title' => 'Podcast: Memahami Garis Singgung',
                'description' => 'Podcast yang menjelaskan konsep garis singgung dengan analogi roda yang menyentuh permukaan jalan.',
                'topic' => 'Garis Singgung Lingkaran',
                'type' => 'audio',
                'learning_style' => 'auditory',
                'difficulty_level' => 'intermediate',
                'file_path' => 'materials/rpp/podcast-garis-singgung.mp3',
            ],
            [
                'title' => 'Video: Diskusi Sifat-sifat Garis Singgung',
                'description' => 'Video diskusi kelompok yang membahas sifat-sifat garis singgung dan pembuktiannya.',
                'topic' => 'Garis Singgung Lingkaran',
                'type' => 'video',
                'learning_style' => 'auditory',
                'difficulty_level' => 'intermediate',
                'content_url' => 'https://www.youtube.com/watch?v=rpp_diskusi_singgung',
            ],
            // Kinesthetic
            [
                'title' => 'Simulasi: Menggambar Garis Singgung',
                'description' => 'Simulasi GeoGebra untuk menggambar garis singgung lingkaran dan mengamati sifat tegak lurusnya dengan jari-jari.',
                'topic' => 'Garis Singgung Lingkaran',
                'type' => 'simulation',
                'learning_style' => 'kinesthetic',
                'difficulty_level' => 'intermediate',
                'content_url' => 'https://www.geogebra.org/m/rpp_simulasi_singgung',
            ],
            [
                'title' => 'Lembar Kerja: Latihan Garis Singgung',
                'description' => 'Lembar kerja praktikum dengan soal-soal penentuan persamaan garis singgung dan masalah kontekstual.',
                'topic' => 'Garis Singgung Lingkaran',
                'type' => 'document',
                'learning_style' => 'kinesthetic',
                'difficulty_level' => 'intermediate',
                'file_path' => 'materials/rpp/lkpd-garis-singgung.pdf',
            ],

            // === LINGKARAN DAN TALI BUSUR (Pertemuan 5-6) ===
            // Visual
            [
                'title' => 'Video: Definisi dan Sifat Tali Busur',
                'description' => 'Video animasi yang menjelaskan definisi tali busur, sifat-sifatnya, dan hubungannya dengan diameter.',
                'topic' => 'Tali Busur Lingkaran',
                'type' => 'video',
                'learning_style' => 'visual',
                'difficulty_level' => 'intermediate',
                'content_url' => 'https://www.youtube.com/watch?v=rpp_tali_busur',
            ],
            [
                'title' => 'Infografis: Hubungan Tali Busur dan Pusat',
                'description' => 'Infografis tentang hubungan tali busur dengan pusat lingkaran, termasuk jarak tali busur ke pusat.',
                'topic' => 'Tali Busur Lingkaran',
                'type' => 'infographic',
                'learning_style' => 'visual',
                'difficulty_level' => 'intermediate',
                'file_path' => 'materials/rpp/infografis-tali-busur.png',
            ],
            // Auditory
            [
                'title' => 'Podcast: Konsep Tali Busur dan Juring',
                'description' => 'Podcast yang menjelaskan konsep tali busur, busur, dan juring dengan contoh aplikasi dalam kehidupan.',
                'topic' => 'Tali Busur Lingkaran',
                'type' => 'audio',
                'learning_style' => 'auditory',
                'difficulty_level' => 'intermediate',
                'file_path' => 'materials/rpp/podcast-tali-busur.mp3',
            ],
            [
                'title' => 'Video: Penjelasan Sudut Pusat dan Keliling',
                'description' => 'Video dengan penjelasan verbal tentang sudut pusat, sudut keliling, dan hubungannya dengan tali busur.',
                'topic' => 'Tali Busur Lingkaran',
                'type' => 'video',
                'learning_style' => 'auditory',
                'difficulty_level' => 'intermediate',
                'content_url' => 'https://www.youtube.com/watch?v=rpp_sudut_lingkaran',
            ],
            // Kinesthetic
            [
                'title' => 'Simulasi: Eksplorasi Tali Busur Interaktif',
                'description' => 'Simulasi GeoGebra untuk mengeksplorasi sifat-sifat tali busur dengan menggerakkan titik-titik pada lingkaran.',
                'topic' => 'Tali Busur Lingkaran',
                'type' => 'simulation',
                'learning_style' => 'kinesthetic',
                'difficulty_level' => 'intermediate',
                'content_url' => 'https://www.geogebra.org/m/rpp_eksplorasi_tali_busur',
            ],
            [
                'title' => 'Lembar Kerja: Perhitungan Tali Busur',
                'description' => 'Lembar kerja praktikum untuk menghitung panjang tali busur dan jarak tali busur ke pusat.',
                'topic' => 'Tali Busur Lingkaran',
                'type' => 'document',
                'learning_style' => 'kinesthetic',
                'difficulty_level' => 'intermediate',
                'file_path' => 'materials/rpp/lkpd-tali-busur.pdf',
            ],

            // === APLIKASI KONSEP LINGKARAN (Pertemuan 7-8) ===
            // Visual
            [
                'title' => 'Video: Desain Logo dengan Lingkaran',
                'description' => 'Video tutorial membuat desain logo yang memanfaatkan konsep lingkaran, garis singgung, dan tali busur.',
                'topic' => 'Aplikasi Lingkaran',
                'type' => 'video',
                'learning_style' => 'visual',
                'difficulty_level' => 'advanced',
                'content_url' => 'https://www.youtube.com/watch?v=rpp_desain_logo',
            ],
            // Auditory
            [
                'title' => 'Video: Presentasi Proyek Aplikasi Lingkaran',
                'description' => 'Video presentasi siswa tentang proyek aplikasi lingkaran dalam kehidupan nyata seperti roda gigi dan orbit planet.',
                'topic' => 'Aplikasi Lingkaran',
                'type' => 'video',
                'learning_style' => 'auditory',
                'difficulty_level' => 'advanced',
                'content_url' => 'https://www.youtube.com/watch?v=rpp_proyek_lingkaran',
            ],
            // Kinesthetic
            [
                'title' => 'Simulasi: Pemodelan Roda Gigi',
                'description' => 'Simulasi interaktif untuk membuat model roda gigi menggunakan konsep lingkaran dan garis singgung.',
                'topic' => 'Aplikasi Lingkaran',
                'type' => 'simulation',
                'learning_style' => 'kinesthetic',
                'difficulty_level' => 'advanced',
                'content_url' => 'https://www.geogebra.org/m/rpp_roda_gigi',
            ],
            // All
            [
                'title' => 'Dokumen: Ringkasan BAB 2 dan Latihan',
                'description' => 'Dokumen ringkasan lengkap materi BAB 2 (Lingkaran) beserta bank soal latihan dan rubrik penilaian.',
                'topic' => 'Aplikasi Lingkaran',
                'type' => 'document',
                'learning_style' => 'all',
                'difficulty_level' => 'advanced',
                'file_path' => 'materials/rpp/ringkasan-bab2-lingkaran.pdf',
            ],
        ];
    }

    /**
     * BAB 3: Statistika
     * 6 Pertemuan (12 JP @ 45 menit)
     */
    private function getBab3Materials(): array
    {
        return [
            // === DIAGRAM PENCAR (Pertemuan 1-2) ===
            // Visual
            [
                'title' => 'Video: Membuat Diagram Pencar',
                'description' => 'Video tutorial membuat diagram pencar menggunakan spreadsheet dengan data bivariat dari kehidupan nyata.',
                'topic' => 'Diagram Pencar',
                'type' => 'video',
                'learning_style' => 'visual',
                'difficulty_level' => 'beginner',
                'content_url' => 'https://www.youtube.com/watch?v=rpp_diagram_pencar',
            ],
            [
                'title' => 'Infografis: Pola Hubungan dalam Diagram Pencar',
                'description' => 'Infografis yang menunjukkan berbagai pola hubungan: positif, negatif, tidak ada hubungan, linear, dan non-linear.',
                'topic' => 'Diagram Pencar',
                'type' => 'infographic',
                'learning_style' => 'visual',
                'difficulty_level' => 'beginner',
                'file_path' => 'materials/rpp/infografis-pola-diagram-pencar.png',
            ],
            // Auditory
            [
                'title' => 'Podcast: Pengenalan Data Bivariat',
                'description' => 'Podcast yang menjelaskan konsep data bivariat dan pentingnya statistika dalam memahami hubungan antarvariabel.',
                'topic' => 'Diagram Pencar',
                'type' => 'audio',
                'learning_style' => 'auditory',
                'difficulty_level' => 'beginner',
                'file_path' => 'materials/rpp/podcast-data-bivariat.mp3',
            ],
            [
                'title' => 'Video: Tutorial Excel untuk Diagram Pencar',
                'description' => 'Video tutorial verbal step-by-step membuat diagram pencar di Microsoft Excel atau Google Sheets.',
                'topic' => 'Diagram Pencar',
                'type' => 'video',
                'learning_style' => 'auditory',
                'difficulty_level' => 'beginner',
                'content_url' => 'https://www.youtube.com/watch?v=rpp_excel_pencar',
            ],
            // Kinesthetic
            [
                'title' => 'Simulasi: Diagram Pencar Interaktif',
                'description' => 'Simulasi Desmos untuk membuat dan memanipulasi diagram pencar dengan data yang bisa diubah-ubah.',
                'topic' => 'Diagram Pencar',
                'type' => 'simulation',
                'learning_style' => 'kinesthetic',
                'difficulty_level' => 'beginner',
                'content_url' => 'https://www.desmos.com/calculator/rpp_pencar',
            ],
            [
                'title' => 'Lembar Kerja: Praktikum Diagram Pencar',
                'description' => 'Lembar kerja praktikum untuk mengumpulkan data dan membuat diagram pencar dari fenomena sekitar.',
                'topic' => 'Diagram Pencar',
                'type' => 'document',
                'learning_style' => 'kinesthetic',
                'difficulty_level' => 'beginner',
                'file_path' => 'materials/rpp/lkpd-diagram-pencar.pdf',
            ],

            // === REGRESI LINEAR SEDERHANA (Pertemuan 3-4) ===
            // Visual
            [
                'title' => 'Video: Konsep Regresi Linear',
                'description' => 'Video animasi yang menjelaskan konsep regresi linear sebagai garis terbaik yang memodelkan hubungan data.',
                'topic' => 'Regresi Linear',
                'type' => 'video',
                'learning_style' => 'visual',
                'difficulty_level' => 'intermediate',
                'content_url' => 'https://www.youtube.com/watch?v=rpp_regresi_linear',
            ],
            [
                'title' => 'Infografis: Rumus dan Langkah Regresi',
                'description' => 'Infografis step-by-step menghitung koefisien regresi (a dan b) dan menyusun persamaan garis regresi.',
                'topic' => 'Regresi Linear',
                'type' => 'infographic',
                'learning_style' => 'visual',
                'difficulty_level' => 'intermediate',
                'file_path' => 'materials/rpp/infografis-rumus-regresi.png',
            ],
            // Auditory
            [
                'title' => 'Podcast: Memahami Regresi dalam Kehidupan',
                'description' => 'Podcast yang menjelaskan aplikasi regresi dalam memprediksi penjualan, harga saham, dan fenomena lainnya.',
                'topic' => 'Regresi Linear',
                'type' => 'audio',
                'learning_style' => 'auditory',
                'difficulty_level' => 'intermediate',
                'file_path' => 'materials/rpp/podcast-aplikasi-regresi.mp3',
            ],
            [
                'title' => 'Video: Studi Kasus Regresi di Ekonomi',
                'description' => 'Video studi kasus penggunaan regresi linear dalam menganalisis hubungan harga dan permintaan.',
                'topic' => 'Regresi Linear',
                'type' => 'video',
                'learning_style' => 'auditory',
                'difficulty_level' => 'intermediate',
                'content_url' => 'https://www.youtube.com/watch?v=rpp_regresi_ekonomi',
            ],
            // Kinesthetic
            [
                'title' => 'Simulasi: Kalkulator Regresi Linear',
                'description' => 'Simulasi interaktif untuk menghitung regresi linear dan melihat garis regresi pada diagram pencar.',
                'topic' => 'Regresi Linear',
                'type' => 'simulation',
                'learning_style' => 'kinesthetic',
                'difficulty_level' => 'intermediate',
                'content_url' => 'https://www.geogebra.org/m/rpp_kalkulator_regresi',
            ],
            [
                'title' => 'Lembar Kerja: Menghitung Regresi Manual',
                'description' => 'Lembar kerja praktikum untuk menghitung koefisien regresi secara manual dan menginterpretasi hasilnya.',
                'topic' => 'Regresi Linear',
                'type' => 'document',
                'learning_style' => 'kinesthetic',
                'difficulty_level' => 'intermediate',
                'file_path' => 'materials/rpp/lkpd-regresi-manual.pdf',
            ],

            // === ANALISIS KORELASI (Pertemuan 5-6) ===
            // Visual
            [
                'title' => 'Video: Konsep Korelasi Pearson',
                'description' => 'Video yang menjelaskan konsep koefisien korelasi Pearson (r) dan cara menghitungnya.',
                'topic' => 'Analisis Korelasi',
                'type' => 'video',
                'learning_style' => 'visual',
                'difficulty_level' => 'intermediate',
                'content_url' => 'https://www.youtube.com/watch?v=rpp_korelasi_pearson',
            ],
            [
                'title' => 'Infografis: Interpretasi Koefisien Korelasi',
                'description' => 'Infografis tentang interpretasi nilai koefisien korelasi: sangat kuat, kuat, sedang, lemah, dan tidak ada korelasi.',
                'topic' => 'Analisis Korelasi',
                'type' => 'infographic',
                'learning_style' => 'visual',
                'difficulty_level' => 'intermediate',
                'file_path' => 'materials/rpp/infografis-interpretasi-korelasi.png',
            ],
            // Auditory
            [
                'title' => 'Podcast: Perbedaan Regresi dan Korelasi',
                'description' => 'Podcast yang menjelaskan perbedaan mendasar antara regresi (pemodelan) dan korelasi (kekuatan hubungan).',
                'topic' => 'Analisis Korelasi',
                'type' => 'audio',
                'learning_style' => 'auditory',
                'difficulty_level' => 'intermediate',
                'file_path' => 'materials/rpp/podcast-regresi-vs-korelasi.mp3',
            ],
            [
                'title' => 'Video: Diskusi Kekuatan Hubungan Data',
                'description' => 'Video diskusi tentang bagaimana menginterpretasi kekuatan dan arah hubungan berdasarkan nilai r.',
                'topic' => 'Analisis Korelasi',
                'type' => 'video',
                'learning_style' => 'auditory',
                'difficulty_level' => 'intermediate',
                'content_url' => 'https://www.youtube.com/watch?v=rpp_diskusi_korelasi',
            ],
            // Kinesthetic
            [
                'title' => 'Simulasi: Kalkulator Korelasi Interaktif',
                'description' => 'Simulasi interaktif untuk menghitung koefisien korelasi dan mengeksplorasi hubungan antara nilai r dan pola data.',
                'topic' => 'Analisis Korelasi',
                'type' => 'simulation',
                'learning_style' => 'kinesthetic',
                'difficulty_level' => 'intermediate',
                'content_url' => 'https://www.geogebra.org/m/rpp_kalkulator_korelasi',
            ],
            [
                'title' => 'Lembar Kerja: Proyek Analisis Data',
                'description' => 'Lembar kerja proyek akhir untuk mengumpulkan data, membuat diagram pencar, menghitung regresi dan korelasi.',
                'topic' => 'Analisis Korelasi',
                'type' => 'document',
                'learning_style' => 'kinesthetic',
                'difficulty_level' => 'intermediate',
                'file_path' => 'materials/rpp/lkpd-proyek-statistika.pdf',
            ],
            // All
            [
                'title' => 'Dokumen: Ringkasan BAB 3 dan Latihan',
                'description' => 'Dokumen ringkasan lengkap materi BAB 3 (Statistika) beserta bank soal dan rubrik penilaian proyek.',
                'topic' => 'Analisis Korelasi',
                'type' => 'document',
                'learning_style' => 'all',
                'difficulty_level' => 'advanced',
                'file_path' => 'materials/rpp/ringkasan-bab3-statistika.pdf',
            ],
        ];
    }
}
