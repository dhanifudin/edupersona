<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    /**
     * Seed demo data untuk mendemonstrasikan personalized learning
     *
     * Demo data mencakup:
     * - 4 siswa dengan profil gaya belajar berbeda (Visual, Auditori, Kinestetik, Campuran)
     * - 21 materi pembelajaran untuk setiap gaya belajar
     * - Aktivitas dan progress pembelajaran
     * - Rekomendasi AI yang dipersonalisasi
     * - Feedback AI yang sesuai gaya belajar
     *
     * Password untuk semua demo users: demo1234
     *
     * Usage:
     *   php artisan db:seed --class=DemoDataSeeder
     */
    public function run(): void
    {
        $this->command->info('');
        $this->command->info('=== EduPersona.ai Demo Data Seeder ===');
        $this->command->info('');

        // Step 1: Create demo students with learning profiles
        $this->command->info('Step 1/4: Creating demo students...');
        $this->call(DemoStudentSeeder::class);

        // Step 2: Create demo learning materials
        $this->command->info('Step 2/4: Creating learning materials...');
        $this->call(DemoMaterialSeeder::class);

        // Step 3: Create demo activities and progress
        $this->command->info('Step 3/4: Creating activities and progress...');
        $this->call(DemoActivitySeeder::class);

        // Step 4: Create demo recommendations and feedback
        $this->command->info('Step 4/4: Creating AI recommendations and feedback...');
        $this->call(DemoRecommendationSeeder::class);

        $this->command->info('');
        $this->command->info('=== Demo Data Created Successfully! ===');
        $this->command->info('');
        $this->command->info('Demo Accounts (password: demo1234):');
        $this->command->table(
            ['Name', 'Email', 'Learning Style'],
            [
                ['Budi Santoso', 'budi@demo.test', 'Visual (85%)'],
                ['Siti Rahayu', 'siti@demo.test', 'Auditori (88%)'],
                ['Andi Pratama', 'andi@demo.test', 'Kinestetik (90%)'],
                ['Dewi Lestari', 'dewi@demo.test', 'Campuran'],
            ]
        );
        $this->command->info('');
        $this->command->info('Login sebagai setiap siswa untuk melihat rekomendasi yang berbeda!');
        $this->command->info('');
    }
}
