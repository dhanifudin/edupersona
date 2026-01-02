<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { register } from '@/routes';
import MiniQuestionnaire from './MiniQuestionnaire.vue';
import DemoRadarChart from './DemoRadarChart.vue';
import { ClipboardList, BarChart3, ArrowRight, RotateCcw } from 'lucide-vue-next';

interface Props {
    canRegister: boolean;
}

defineProps<Props>();

interface Scores {
    visual: number;
    auditory: number;
    kinesthetic: number;
}

const scores = ref<Scores>({ visual: 60, auditory: 40, kinesthetic: 50 });
const isCompleted = ref(false);

const handleComplete = (newScores: Scores) => {
    scores.value = newScores;
    isCompleted.value = true;
};

const resetDemo = () => {
    isCompleted.value = false;
    scores.value = { visual: 60, auditory: 40, kinesthetic: 50 };
};

const dominantDescription = computed(() => {
    const { visual, auditory, kinesthetic } = scores.value;
    if (visual >= auditory && visual >= kinesthetic) {
        return 'Kamu belajar paling efektif dengan materi visual seperti video, diagram, dan infografis.';
    } else if (auditory >= visual && auditory >= kinesthetic) {
        return 'Kamu belajar paling efektif dengan mendengarkan penjelasan, diskusi, dan materi audio.';
    }
    return 'Kamu belajar paling efektif dengan praktik langsung, eksperimen, dan aktivitas hands-on.';
});
</script>

<template>
    <section id="demo-section" class="bg-muted/30 py-20 px-4">
        <div class="mx-auto max-w-6xl">
            <!-- Section Header -->
            <div class="mb-12 text-center">
                <h2 class="mb-4 text-3xl font-bold tracking-tight sm:text-4xl">
                    Coba Demo Gratis
                </h2>
                <p class="mx-auto max-w-2xl text-muted-foreground">
                    Jawab 3 pertanyaan singkat dan lihat bagaimana EduPersona.ai menganalisis gaya belajarmu
                </p>
            </div>

            <!-- Demo Cards -->
            <div class="grid gap-6 lg:grid-cols-2">
                <!-- Questionnaire Card -->
                <Card>
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary/10">
                                    <ClipboardList class="h-5 w-5 text-primary" />
                                </div>
                                <div>
                                    <CardTitle>Mini Kuesioner VAK</CardTitle>
                                    <CardDescription>3 pertanyaan singkat</CardDescription>
                                </div>
                            </div>
                            <Button v-if="isCompleted" variant="ghost" size="sm" @click="resetDemo">
                                <RotateCcw class="mr-1 h-4 w-4" />
                                Ulang
                            </Button>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div v-if="!isCompleted">
                            <MiniQuestionnaire @complete="handleComplete" />
                        </div>
                        <div v-else class="space-y-4 text-center py-8">
                            <div class="flex h-16 w-16 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/30 mx-auto">
                                <svg class="h-8 w-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold">Kuesioner Selesai!</h3>
                            <p class="text-sm text-muted-foreground">
                                Lihat hasil analisismu di panel sebelah
                            </p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Result Card -->
                <Card>
                    <CardHeader>
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary/10">
                                <BarChart3 class="h-5 w-5 text-primary" />
                            </div>
                            <div>
                                <CardTitle>Profil Gaya Belajar</CardTitle>
                                <CardDescription>Hasil analisis VAK</CardDescription>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-6">
                            <DemoRadarChart
                                :visual="scores.visual"
                                :auditory="scores.auditory"
                                :kinesthetic="scores.kinesthetic"
                            />

                            <div v-if="isCompleted" class="rounded-lg bg-muted p-4 text-center">
                                <p class="text-sm text-muted-foreground">
                                    {{ dominantDescription }}
                                </p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- CTA -->
            <div class="mt-8 text-center">
                <Link v-if="canRegister" :href="register().url">
                    <Button size="lg">
                        Daftar untuk Hasil Lengkap
                        <ArrowRight class="ml-2 h-4 w-4" />
                    </Button>
                </Link>
                <p class="mt-3 text-sm text-muted-foreground">
                    Akses kuesioner lengkap (15 pertanyaan) dan rekomendasi AI personal
                </p>
            </div>
        </div>
    </section>
</template>
