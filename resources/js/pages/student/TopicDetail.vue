<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { type BreadcrumbItem, type Subject, type LearningMaterial, type LearningStyleProfile, type AiRecommendation } from '@/types';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { index as dashboardIndex } from '@/actions/App/Http/Controllers/Student/DashboardController';
import { show as learnShow } from '@/actions/App/Http/Controllers/Student/SubjectLearningController';
import { show as materialShow } from '@/actions/App/Http/Controllers/Student/MaterialController';
import { ArrowLeft, BookOpen, FileText, Headphones, Image, Play, Sparkles, Video } from 'lucide-vue-next';

interface Progress {
    id: number;
    status: 'not_started' | 'in_progress' | 'completed';
    score?: number;
    attempts: number;
    last_activity_at?: string;
}

interface Props {
    subject: Subject;
    topic: string;
    materials: LearningMaterial[];
    progress?: Progress;
    recommendations?: AiRecommendation[];
    learningProfile?: LearningStyleProfile;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboardIndex.url(),
    },
    {
        title: props.subject.name,
        href: learnShow.url(props.subject.id),
    },
    {
        title: props.topic,
        href: '#',
    },
];

const getMaterialIcon = (type: string) => {
    switch (type) {
        case 'video':
            return Video;
        case 'document':
            return FileText;
        case 'infographic':
            return Image;
        case 'audio':
            return Headphones;
        case 'simulation':
            return Play;
        default:
            return BookOpen;
    }
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

const getLearningStyleLabel = (style: string): string => {
    const labels: Record<string, string> = {
        visual: 'Visual',
        auditory: 'Auditori',
        kinesthetic: 'Kinestetik',
        general: 'Umum',
    };
    return labels[style] || style;
};

const getLearningStyleColor = (style: string): string => {
    const colors: Record<string, string> = {
        visual: 'bg-blue-500',
        auditory: 'bg-green-500',
        kinesthetic: 'bg-orange-500',
        general: 'bg-gray-500',
    };
    return colors[style] || 'bg-gray-500';
};

</script>

<template>
    <Head :title="`${topic} - ${subject.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <!-- Header -->
            <div class="flex items-start justify-between">
                <div class="flex items-center gap-4">
                    <Link :href="learnShow.url(subject.id)">
                        <Button variant="ghost" size="icon">
                            <ArrowLeft class="h-5 w-5" />
                        </Button>
                    </Link>
                    <div>
                        <p class="text-sm text-muted-foreground">{{ subject.name }}</p>
                        <h1 class="text-2xl font-bold">{{ topic }}</h1>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <Badge v-if="!progress || progress.status === 'not_started'" variant="secondary">
                        Belum Dimulai
                    </Badge>
                    <Badge v-else-if="progress.status === 'in_progress'" class="bg-blue-500">
                        Sedang Belajar
                    </Badge>
                    <Badge v-else-if="progress.status === 'completed'" class="bg-green-500">
                        Selesai
                    </Badge>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="grid gap-6 lg:grid-cols-3">
                <!-- Materials List -->
                <div class="lg:col-span-2 space-y-4">
                    <Card>
                        <CardHeader>
                            <CardTitle>Materi Pembelajaran</CardTitle>
                            <CardDescription>
                                {{ materials.length }} materi tersedia untuk topik ini
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-3">
                            <Link
                                v-for="material in materials"
                                :key="material.id"
                                :href="materialShow.url(material.id)"
                                class="flex items-center gap-4 p-4 rounded-lg border hover:bg-muted/50 transition-colors"
                            >
                                <div
                                    class="flex h-12 w-12 items-center justify-center rounded-lg"
                                    :class="getLearningStyleColor(material.learning_style)"
                                >
                                    <component :is="getMaterialIcon(material.type)" class="h-6 w-6 text-white" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium truncate">{{ material.title }}</p>
                                    <div class="flex items-center gap-2 mt-1">
                                        <Badge variant="outline" class="text-xs">
                                            {{ getMaterialTypeLabel(material.type) }}
                                        </Badge>
                                        <Badge variant="secondary" class="text-xs">
                                            {{ getLearningStyleLabel(material.learning_style) }}
                                        </Badge>
                                    </div>
                                </div>
                            </Link>

                            <div v-if="materials.length === 0" class="flex flex-col items-center justify-center py-8 text-center">
                                <BookOpen class="h-12 w-12 text-muted-foreground/50" />
                                <p class="mt-2 text-muted-foreground">
                                    Belum ada materi untuk topik ini.
                                </p>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- AI Recommendations Sidebar -->
                <div class="space-y-4">
                    <Card v-if="learningProfile">
                        <CardHeader class="pb-3">
                            <div class="flex items-center gap-2">
                                <Sparkles class="h-5 w-5 text-primary" />
                                <CardTitle class="text-lg">Rekomendasi AI</CardTitle>
                            </div>
                            <CardDescription>
                                Berdasarkan gaya belajar {{ getLearningStyleLabel(learningProfile.dominant_style) }}
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div v-if="recommendations && recommendations.length > 0" class="space-y-3">
                                <Link
                                    v-for="rec in recommendations"
                                    :key="rec.id"
                                    :href="materialShow.url(rec.material?.id || 0)"
                                    class="block p-3 rounded-lg border hover:bg-muted/50 transition-colors"
                                >
                                    <p class="font-medium text-sm">{{ rec.material?.title }}</p>
                                    <p class="text-xs text-muted-foreground mt-1 line-clamp-2">
                                        {{ rec.reason }}
                                    </p>
                                </Link>
                            </div>
                            <div v-else class="text-center py-4">
                                <p class="text-sm text-muted-foreground">
                                    Tidak ada rekomendasi khusus untuk topik ini.
                                </p>
                            </div>
                        </CardContent>
                    </Card>

                    <Card v-else>
                        <CardHeader class="pb-3">
                            <div class="flex items-center gap-2">
                                <Sparkles class="h-5 w-5 text-muted-foreground" />
                                <CardTitle class="text-lg">Rekomendasi AI</CardTitle>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <p class="text-sm text-muted-foreground text-center py-4">
                                Lengkapi kuesioner gaya belajar untuk mendapatkan rekomendasi yang dipersonalisasi.
                            </p>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
