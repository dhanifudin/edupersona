<?php

namespace Database\Seeders;

use App\Models\LearningStyleQuestion;
use Illuminate\Database\Seeder;

class LearningStyleQuestionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $questions = [
            // Visual (V) - 5 Pertanyaan
            [
                'question_text' => 'Saya lebih mudah memahami materi yang disajikan dalam bentuk gambar, diagram, atau grafik',
                'style_type' => 'visual',
                'order' => 1,
            ],
            [
                'question_text' => 'Saya suka mencatat dengan menggunakan warna-warna berbeda dan membuat mind map',
                'style_type' => 'visual',
                'order' => 2,
            ],
            [
                'question_text' => 'Saya lebih suka membaca instruksi tertulis daripada mendengarkan penjelasan lisan',
                'style_type' => 'visual',
                'order' => 3,
            ],
            [
                'question_text' => 'Saya mudah mengingat wajah orang tetapi sering lupa namanya',
                'style_type' => 'visual',
                'order' => 4,
            ],
            [
                'question_text' => 'Saya lebih suka menonton video pembelajaran daripada mendengarkan podcast',
                'style_type' => 'visual',
                'order' => 5,
            ],

            // Auditory (A) - 5 Pertanyaan
            [
                'question_text' => 'Saya lebih mudah memahami materi ketika dijelaskan secara lisan oleh guru',
                'style_type' => 'auditory',
                'order' => 6,
            ],
            [
                'question_text' => 'Saya suka belajar sambil mendengarkan musik atau dalam suasana yang ada suaranya',
                'style_type' => 'auditory',
                'order' => 7,
            ],
            [
                'question_text' => 'Saya sering membaca dengan suara keras atau menggerakkan bibir saat membaca',
                'style_type' => 'auditory',
                'order' => 8,
            ],
            [
                'question_text' => 'Saya mudah mengingat nama orang tetapi sering lupa wajahnya',
                'style_type' => 'auditory',
                'order' => 9,
            ],
            [
                'question_text' => 'Saya lebih suka mendengarkan podcast atau audio book daripada membaca buku',
                'style_type' => 'auditory',
                'order' => 10,
            ],

            // Kinesthetic (K) - 5 Pertanyaan
            [
                'question_text' => 'Saya lebih suka belajar dengan melakukan praktik langsung atau eksperimen',
                'style_type' => 'kinesthetic',
                'order' => 11,
            ],
            [
                'question_text' => 'Saya sulit duduk diam dalam waktu lama dan sering bergerak saat belajar',
                'style_type' => 'kinesthetic',
                'order' => 12,
            ],
            [
                'question_text' => 'Saya suka menggunakan tangan saat berbicara atau menjelaskan sesuatu',
                'style_type' => 'kinesthetic',
                'order' => 13,
            ],
            [
                'question_text' => 'Saya lebih mudah mengingat sesuatu yang pernah saya lakukan sendiri',
                'style_type' => 'kinesthetic',
                'order' => 14,
            ],
            [
                'question_text' => 'Saya lebih suka bermain peran atau simulasi daripada membaca atau mendengarkan',
                'style_type' => 'kinesthetic',
                'order' => 15,
            ],
        ];

        foreach ($questions as $question) {
            LearningStyleQuestion::firstOrCreate(
                [
                    'question_text' => $question['question_text'],
                ],
                [
                    'style_type' => $question['style_type'],
                    'order' => $question['order'],
                    'is_active' => true,
                ]
            );
        }
    }
}
