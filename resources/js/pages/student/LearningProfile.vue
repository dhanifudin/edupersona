<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { type BreadcrumbItem, type LearningStyleProfile } from '@/types';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Progress } from '@/components/ui/progress';
import { index as dashboardIndex } from '@/actions/App/Http/Controllers/Student/DashboardController';
import { Brain, Eye, Ear, Hand, Lightbulb, BookOpen, ArrowRight } from 'lucide-vue-next';

interface LearningRecommendation {
    title: string;
    description: string;
    tips: string[];
    materials: string[];
}

interface Props {
    learningProfile: LearningStyleProfile;
    recommendations: LearningRecommendation;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/student/dashboard',
    },
    {
        title: 'Profil Gaya Belajar',
        href: '/student/learning-profile',
    },
];

const getStyleIcon = (style: string) => {
    const icons: Record<string, typeof Eye> = {
        visual: Eye,
        auditory: Ear,
        kinesthetic: Hand,
    };
    return icons[style] || Brain;
};

const getStyleLabel = (style: string): string => {
    const labels: Record<string, string> = {
        visual: 'Visual',
        auditory: 'Auditori',
        kinesthetic: 'Kinestetik',
    };
    return labels[style] || style;
};

const getStyleColor = (style: string): string => {
    const colors: Record<string, string> = {
        visual: 'bg-blue-500',
        auditory: 'bg-green-500',
        kinesthetic: 'bg-orange-500',
    };
    return colors[style] || 'bg-gray-500';
};

const getStyleColorLight = (style: string): string => {
    const colors: Record<string, string> = {
        visual: 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300',
        auditory: 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300',
        kinesthetic: 'bg-orange-100 text-orange-700 dark:bg-orange-900 dark:text-orange-300',
    };
    return colors[style] || 'bg-gray-100 text-gray-700';
};

const getMaterialTypeLabel = (type: string): string => {
    const labels: Record<string, string> = {
        video: 'Video',
        document: 'Dokumen',
        infographic: 'Infografis',
        audio: 'Audio',
        simulation: 'Simulasi',
    };
    return labels[type] || type;
};

const learningStyles = [
    { key: 'visual', label: 'Visual', score: props.learningProfile.visual_score, icon: Eye, color: 'blue' },
    { key: 'auditory', label: 'Auditori', score: props.learningProfile.auditory_score, icon: Ear, color: 'green' },
    { key: 'kinesthetic', label: 'Kinestetik', score: props.learningProfile.kinesthetic_score, icon: Hand, color: 'orange' },
];
</script>

<template>
    <Head title="Profil Gaya Belajar" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <!-- Header -->
            <div class="flex items-center gap-3">
                <div class="rounded-full bg-primary/10 p-3">
                    <Brain class="h-6 w-6 text-primary" />
                </div>
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Profil Gaya Belajar</h1>
                    <p class="text-muted-foreground">
                        Hasil analisis preferensi gaya belajarmu
                    </p>
                </div>
            </div>

            <!-- Dominant Style Card -->
            <Card :class="getStyleColorLight(learningProfile.dominant_style)">
                <CardContent class="pt-6">
                    <div class="flex flex-col items-center gap-4 md:flex-row md:gap-6">
                        <div :class="['rounded-full p-4', getStyleColor(learningProfile.dominant_style)]">
                            <component :is="getStyleIcon(learningProfile.dominant_style)" class="h-12 w-12 text-white" />
                        </div>
                        <div class="text-center md:text-left">
                            <div class="text-sm font-medium opacity-75">Gaya Belajar Dominanmu</div>
                            <h2 class="text-3xl font-bold">{{ recommendations.title }}</h2>
                            <p class="mt-2 max-w-xl">{{ recommendations.description }}</p>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <div class="grid gap-6 lg:grid-cols-2">
                <!-- Score Breakdown -->
                <Card>
                    <CardHeader>
                        <CardTitle>Distribusi Skor</CardTitle>
                        <CardDescription>
                            Persentase preferensi untuk setiap gaya belajar
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-6">
                            <div v-for="style in learningStyles" :key="style.key" class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <component :is="style.icon" class="h-5 w-5 text-muted-foreground" />
                                        <span class="font-medium">{{ style.label }}</span>
                                        <Badge
                                            v-if="style.key === learningProfile.dominant_style"
                                            :class="getStyleColor(style.key)"
                                        >
                                            Dominan
                                        </Badge>
                                    </div>
                                    <span class="font-bold">{{ style.score }}%</span>
                                </div>
                                <div class="relative">
                                    <Progress
                                        :model-value="style.score"
                                        :class="[
                                            'h-3',
                                            style.color === 'blue' ? '[&>div]:bg-blue-500' : '',
                                            style.color === 'green' ? '[&>div]:bg-green-500' : '',
                                            style.color === 'orange' ? '[&>div]:bg-orange-500' : '',
                                        ]"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Radar Chart Placeholder -->
                        <div class="mt-8 flex items-center justify-center">
                            <div class="relative">
                                <svg viewBox="0 0 200 200" class="h-48 w-48">
                                    <!-- Background circles -->
                                    <circle cx="100" cy="100" r="80" fill="none" stroke="currentColor" class="text-muted/20" stroke-width="1" />
                                    <circle cx="100" cy="100" r="60" fill="none" stroke="currentColor" class="text-muted/20" stroke-width="1" />
                                    <circle cx="100" cy="100" r="40" fill="none" stroke="currentColor" class="text-muted/20" stroke-width="1" />
                                    <circle cx="100" cy="100" r="20" fill="none" stroke="currentColor" class="text-muted/20" stroke-width="1" />

                                    <!-- Axes -->
                                    <line x1="100" y1="20" x2="100" y2="180" stroke="currentColor" class="text-muted/30" stroke-width="1" />
                                    <line x1="30" y1="140" x2="170" y2="60" stroke="currentColor" class="text-muted/30" stroke-width="1" />
                                    <line x1="30" y1="60" x2="170" y2="140" stroke="currentColor" class="text-muted/30" stroke-width="1" />

                                    <!-- Data polygon -->
                                    <polygon
                                        :points="`
                                            100,${100 - (learningProfile.visual_score * 0.8)}
                                            ${100 + (learningProfile.kinesthetic_score * 0.693)},${100 + (learningProfile.kinesthetic_score * 0.4)}
                                            ${100 - (learningProfile.auditory_score * 0.693)},${100 + (learningProfile.auditory_score * 0.4)}
                                        `"
                                        class="fill-primary/20 stroke-primary"
                                        stroke-width="2"
                                    />

                                    <!-- Labels -->
                                    <text x="100" y="10" text-anchor="middle" class="fill-current text-xs font-medium">V</text>
                                    <text x="180" y="150" text-anchor="middle" class="fill-current text-xs font-medium">K</text>
                                    <text x="20" y="150" text-anchor="middle" class="fill-current text-xs font-medium">A</text>
                                </svg>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Learning Tips -->
                <Card>
                    <CardHeader>
                        <div class="flex items-center gap-2">
                            <Lightbulb class="h-5 w-5" />
                            <CardTitle>Tips Belajar</CardTitle>
                        </div>
                        <CardDescription>
                            Strategi belajar yang cocok untukmu
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <ul class="space-y-3">
                            <li
                                v-for="(tip, index) in recommendations.tips"
                                :key="index"
                                class="flex items-start gap-3"
                            >
                                <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-primary/10 text-xs font-medium text-primary">
                                    {{ index + 1 }}
                                </div>
                                <span class="text-sm">{{ tip }}</span>
                            </li>
                        </ul>
                    </CardContent>
                </Card>
            </div>

            <!-- Recommended Material Types -->
            <Card>
                <CardHeader>
                    <div class="flex items-center gap-2">
                        <BookOpen class="h-5 w-5" />
                        <CardTitle>Jenis Materi yang Disarankan</CardTitle>
                    </div>
                    <CardDescription>
                        Tipe konten pembelajaran yang paling efektif untuk gaya belajarmu
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="flex flex-wrap gap-3">
                        <div
                            v-for="materialType in recommendations.materials"
                            :key="materialType"
                            class="flex items-center gap-2 rounded-lg border bg-muted/50 px-4 py-2"
                        >
                            <Badge :class="getStyleColor(learningProfile.dominant_style)">
                                {{ getMaterialTypeLabel(materialType) }}
                            </Badge>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- CTA -->
            <Card class="border-primary/20 bg-primary/5">
                <CardContent class="pt-6">
                    <div class="flex flex-col items-center gap-4 text-center md:flex-row md:text-left">
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold">Siap Mulai Belajar?</h3>
                            <p class="text-sm text-muted-foreground">
                                Lihat materi pembelajaran yang dipersonalisasi berdasarkan gaya belajarmu
                            </p>
                        </div>
                        <Link :href="dashboardIndex().url">
                            <Badge variant="default" class="cursor-pointer px-4 py-2 text-sm">
                                Kembali ke Dashboard
                                <ArrowRight class="ml-2 h-4 w-4" />
                            </Badge>
                        </Link>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
