<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { register } from '@/routes';
import { GraduationCap, Sparkles } from 'lucide-vue-next';

interface Props {
    canRegister: boolean;
}

defineProps<Props>();

const scrollToDemo = () => {
    document.getElementById('demo-section')?.scrollIntoView({ behavior: 'smooth' });
};
</script>

<template>
    <section class="relative min-h-[calc(100vh-4rem)] flex items-center justify-center px-4 pt-16">
        <!-- Background Pattern -->
        <div class="absolute inset-0 -z-10 overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-b from-primary/5 via-transparent to-transparent" />
            <div class="absolute top-20 left-10 h-72 w-72 rounded-full bg-primary/10 blur-3xl" />
            <div class="absolute bottom-20 right-10 h-72 w-72 rounded-full bg-secondary/10 blur-3xl" />
        </div>

        <div class="mx-auto max-w-4xl text-center">
            <!-- Badge -->
            <Badge variant="secondary" class="mb-6">
                <Sparkles class="mr-1 h-3 w-3" />
                Powered by Gemini AI
            </Badge>

            <!-- Icon -->
            <div class="mb-6 flex justify-center">
                <div class="flex h-20 w-20 items-center justify-center rounded-2xl bg-primary/10">
                    <GraduationCap class="h-10 w-10 text-primary" />
                </div>
            </div>

            <!-- Headline -->
            <h1 class="mb-6 text-4xl font-bold tracking-tight sm:text-5xl lg:text-6xl">
                Temukan Gaya Belajarmu,
                <br />
                <span class="text-primary">Raih Potensi Maksimal</span>
            </h1>

            <!-- Subheadline -->
            <p class="mx-auto mb-8 max-w-2xl text-lg text-muted-foreground sm:text-xl">
                Platform pembelajaran yang dipersonalisasi berdasarkan gaya belajar
                <span class="font-medium text-blue-600 dark:text-blue-400">Visual</span>,
                <span class="font-medium text-green-600 dark:text-green-400">Auditori</span>, dan
                <span class="font-medium text-orange-600 dark:text-orange-400">Kinestetik</span>
                menggunakan AI.
            </p>

            <!-- CTA Buttons -->
            <div class="flex flex-col items-center gap-4 sm:flex-row sm:justify-center">
                <Link v-if="canRegister" :href="register().url">
                    <Button size="lg" class="min-w-48">
                        Mulai Sekarang - Gratis
                    </Button>
                </Link>
                <Button size="lg" variant="outline" @click="scrollToDemo">
                    Coba Demo
                </Button>
            </div>
        </div>
    </section>
</template>
