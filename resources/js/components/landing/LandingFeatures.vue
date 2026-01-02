<script setup lang="ts">
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { ClipboardList, Sparkles, TrendingUp, Users } from 'lucide-vue-next';

const features = [
    {
        icon: ClipboardList,
        title: 'Kuesioner VAK',
        description: 'Identifikasi gaya belajar Visual, Auditori, atau Kinestetik dalam 5 menit',
        color: 'text-blue-600 bg-blue-100 dark:text-blue-400 dark:bg-blue-900/30',
    },
    {
        icon: Sparkles,
        title: 'Rekomendasi AI',
        description: 'Materi pembelajaran yang dipersonalisasi berdasarkan profilmu menggunakan Gemini AI',
        color: 'text-purple-600 bg-purple-100 dark:text-purple-400 dark:bg-purple-900/30',
    },
    {
        icon: TrendingUp,
        title: 'Lacak Progress',
        description: 'Pantau perkembangan dan statistik belajarmu secara real-time',
        color: 'text-green-600 bg-green-100 dark:text-green-400 dark:bg-green-900/30',
    },
    {
        icon: Users,
        title: 'Multi-Role',
        description: 'Fitur lengkap untuk Siswa, Guru, dan Admin dalam satu platform',
        color: 'text-orange-600 bg-orange-100 dark:text-orange-400 dark:bg-orange-900/30',
    },
];
</script>

<template>
    <section class="py-20 px-4">
        <div class="mx-auto max-w-6xl">
            <!-- Section Header -->
            <div class="mb-12 text-center">
                <h2 class="mb-4 text-3xl font-bold tracking-tight sm:text-4xl">
                    Kenapa EduPersona.ai?
                </h2>
                <p class="mx-auto max-w-2xl text-muted-foreground">
                    Tingkatkan efektivitas belajar dengan pendekatan yang sesuai karakteristikmu
                </p>
            </div>

            <!-- Features Grid -->
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <Card v-for="feature in features" :key="feature.title" class="transition-all hover:shadow-lg">
                    <CardHeader>
                        <div :class="['mb-4 flex h-12 w-12 items-center justify-center rounded-lg', feature.color]">
                            <component :is="feature.icon" class="h-6 w-6" />
                        </div>
                        <CardTitle class="text-lg">{{ feature.title }}</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <CardDescription>{{ feature.description }}</CardDescription>
                    </CardContent>
                </Card>
            </div>
        </div>
    </section>
</template>
