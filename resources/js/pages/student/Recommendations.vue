<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { type BreadcrumbItem, type AiRecommendation, type LearningStyleProfile, type Subject, type PaginatedData } from '@/types';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { index as questionnaireIndex } from '@/actions/App/Http/Controllers/Student/QuestionnaireController';
import { show as materialShow } from '@/actions/App/Http/Controllers/Student/MaterialController';
import { BookOpen, Brain, ClipboardList, ExternalLink, Filter, History, RefreshCw, Sparkles, Star } from 'lucide-vue-next';
import { ref, watch } from 'vue';

interface Props {
    recommendations: PaginatedData<AiRecommendation>;
    viewedRecommendations: AiRecommendation[];
    hasLearningProfile: boolean;
    learningProfile?: LearningStyleProfile;
    subjects: Subject[];
    filters: {
        subject?: string;
        type?: string;
        style?: string;
    };
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard Siswa', href: '/student/dashboard' },
    { title: 'Rekomendasi', href: '/student/recommendations' },
];

const selectedSubject = ref(props.filters.subject || '');
const selectedType = ref(props.filters.type || '');
const selectedStyle = ref(props.filters.style || '');
const isRefreshing = ref(false);

const materialTypes = [
    { value: 'video', label: 'Video' },
    { value: 'document', label: 'Dokumen' },
    { value: 'infographic', label: 'Infografis' },
    { value: 'audio', label: 'Audio' },
    { value: 'simulation', label: 'Simulasi' },
];

const learningStyles = [
    { value: 'visual', label: 'Visual' },
    { value: 'auditory', label: 'Auditori' },
    { value: 'kinesthetic', label: 'Kinestetik' },
    { value: 'all', label: 'Semua Gaya' },
];

const applyFilters = () => {
    const params: Record<string, string> = {};
    if (selectedSubject.value) params.subject = selectedSubject.value;
    if (selectedType.value) params.type = selectedType.value;
    if (selectedStyle.value) params.style = selectedStyle.value;

    router.get('/student/recommendations', params, { preserveState: true });
};

const clearFilters = () => {
    selectedSubject.value = '';
    selectedType.value = '';
    selectedStyle.value = '';
    router.get('/student/recommendations', {}, { preserveState: true });
};

const refreshRecommendations = () => {
    isRefreshing.value = true;
    router.post('/student/recommendations/refresh', {}, {
        onFinish: () => {
            isRefreshing.value = false;
        },
    });
};

const generateRecommendations = () => {
    isRefreshing.value = true;
    router.post('/student/recommendations/generate', {}, {
        onFinish: () => {
            isRefreshing.value = false;
        },
    });
};

watch([selectedSubject, selectedType, selectedStyle], () => {
    applyFilters();
});

const getLearningStyleLabel = (style: string): string => {
    const labels: Record<string, string> = {
        visual: 'Visual',
        auditory: 'Auditori',
        kinesthetic: 'Kinestetik',
        all: 'Semua',
    };
    return labels[style] || style;
};

const getLearningStyleColor = (style: string): string => {
    const colors: Record<string, string> = {
        visual: 'bg-blue-500',
        auditory: 'bg-green-500',
        kinesthetic: 'bg-orange-500',
        all: 'bg-gray-500',
    };
    return colors[style] || 'bg-gray-500';
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

const getDifficultyLabel = (level: string): string => {
    const labels: Record<string, string> = {
        beginner: 'Pemula',
        intermediate: 'Menengah',
        advanced: 'Lanjutan',
    };
    return labels[level] || level;
};

const formatRelevanceScore = (score: number): string => {
    return `${Math.round(score * 100)}%`;
};
</script>

<template>
    <Head title="Rekomendasi" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <!-- Header -->
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Rekomendasi Untukmu</h1>
                    <p class="text-muted-foreground">
                        Materi yang dipersonalisasi berdasarkan gaya belajarmu
                    </p>
                </div>
                <div class="flex gap-2">
                    <Button
                        variant="outline"
                        @click="generateRecommendations"
                        :disabled="isRefreshing || !hasLearningProfile"
                    >
                        <Sparkles class="mr-2 h-4 w-4" />
                        Buat Rekomendasi
                    </Button>
                    <Button
                        variant="outline"
                        @click="refreshRecommendations"
                        :disabled="isRefreshing || !hasLearningProfile"
                    >
                        <RefreshCw class="mr-2 h-4 w-4" :class="{ 'animate-spin': isRefreshing }" />
                        Perbarui
                    </Button>
                </div>
            </div>

            <!-- No Learning Profile CTA -->
            <Card v-if="!hasLearningProfile" class="border-amber-200 bg-amber-50 dark:border-amber-800 dark:bg-amber-950">
                <CardHeader>
                    <div class="flex items-center gap-3">
                        <div class="rounded-full bg-amber-100 p-2 dark:bg-amber-900">
                            <ClipboardList class="h-5 w-5 text-amber-600 dark:text-amber-400" />
                        </div>
                        <div>
                            <CardTitle class="text-lg">Lengkapi Kuesioner Gaya Belajar</CardTitle>
                            <CardDescription>
                                Untuk mendapatkan rekomendasi yang dipersonalisasi, silakan isi kuesioner gaya belajar terlebih dahulu
                            </CardDescription>
                        </div>
                    </div>
                </CardHeader>
                <CardContent>
                    <Link :href="questionnaireIndex().url">
                        <Button>
                            <ClipboardList class="mr-2 h-4 w-4" />
                            Mulai Kuesioner
                        </Button>
                    </Link>
                </CardContent>
            </Card>

            <!-- Learning Profile Summary -->
            <Card v-if="learningProfile" class="border-primary/20 bg-primary/5">
                <CardContent class="flex items-center justify-between p-4">
                    <div class="flex items-center gap-3">
                        <Brain class="h-6 w-6 text-primary" />
                        <div>
                            <p class="font-medium">Gaya Belajar Dominan: {{ getLearningStyleLabel(learningProfile.dominant_style) }}</p>
                            <p class="text-sm text-muted-foreground">
                                V: {{ learningProfile.visual_score }}% |
                                A: {{ learningProfile.auditory_score }}% |
                                K: {{ learningProfile.kinesthetic_score }}%
                            </p>
                        </div>
                    </div>
                    <Badge :class="getLearningStyleColor(learningProfile.dominant_style)">
                        {{ getLearningStyleLabel(learningProfile.dominant_style) }}
                    </Badge>
                </CardContent>
            </Card>

            <!-- Filters -->
            <Card>
                <CardHeader class="pb-3">
                    <div class="flex items-center gap-2">
                        <Filter class="h-4 w-4" />
                        <CardTitle class="text-base">Filter Rekomendasi</CardTitle>
                    </div>
                </CardHeader>
                <CardContent>
                    <div class="flex flex-wrap gap-4">
                        <div class="w-48">
                            <Select v-model="selectedSubject">
                                <SelectTrigger>
                                    <SelectValue placeholder="Mata Pelajaran" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="">Semua Mata Pelajaran</SelectItem>
                                    <SelectItem v-for="subject in subjects" :key="subject.id" :value="String(subject.id)">
                                        {{ subject.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="w-40">
                            <Select v-model="selectedType">
                                <SelectTrigger>
                                    <SelectValue placeholder="Tipe Materi" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="">Semua Tipe</SelectItem>
                                    <SelectItem v-for="type in materialTypes" :key="type.value" :value="type.value">
                                        {{ type.label }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="w-40">
                            <Select v-model="selectedStyle">
                                <SelectTrigger>
                                    <SelectValue placeholder="Gaya Belajar" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="">Semua Gaya</SelectItem>
                                    <SelectItem v-for="style in learningStyles" :key="style.value" :value="style.value">
                                        {{ style.label }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <Button variant="ghost" size="sm" @click="clearFilters">
                            Reset Filter
                        </Button>
                    </div>
                </CardContent>
            </Card>

            <!-- Recommendations Grid -->
            <div v-if="recommendations.data.length > 0" class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                <Card v-for="rec in recommendations.data" :key="rec.id" class="flex flex-col">
                    <CardHeader class="pb-2">
                        <div class="flex items-start justify-between gap-2">
                            <CardTitle class="text-base line-clamp-2">
                                {{ rec.material?.title || 'Materi' }}
                            </CardTitle>
                            <div class="flex items-center gap-1 text-amber-500">
                                <Star class="h-4 w-4 fill-current" />
                                <span class="text-xs font-medium">{{ formatRelevanceScore(rec.relevance_score) }}</span>
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-1.5">
                            <Badge variant="outline" class="text-xs">
                                {{ getMaterialTypeLabel(rec.material?.type || '') }}
                            </Badge>
                            <Badge :class="getLearningStyleColor(rec.material?.learning_style || '')" class="text-xs">
                                {{ getLearningStyleLabel(rec.material?.learning_style || '') }}
                            </Badge>
                            <Badge variant="secondary" class="text-xs">
                                {{ getDifficultyLabel(rec.material?.difficulty_level || '') }}
                            </Badge>
                        </div>
                    </CardHeader>
                    <CardContent class="flex flex-1 flex-col">
                        <p v-if="rec.material?.subject" class="mb-2 text-sm text-muted-foreground">
                            {{ rec.material.subject.name }}
                        </p>
                        <p class="flex-1 text-sm text-muted-foreground line-clamp-3">
                            {{ rec.reason }}
                        </p>
                        <div class="mt-4 flex gap-2">
                            <Link :href="materialShow.url(rec.material?.id || 0)" class="flex-1">
                                <Button class="w-full" size="sm">
                                    <BookOpen class="mr-2 h-4 w-4" />
                                    Mulai Belajar
                                </Button>
                            </Link>
                            <Button
                                v-if="rec.material?.content_url"
                                variant="outline"
                                size="sm"
                                as="a"
                                :href="rec.material.content_url"
                                target="_blank"
                            >
                                <ExternalLink class="h-4 w-4" />
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Empty State -->
            <Card v-else-if="hasLearningProfile">
                <CardContent class="flex flex-col items-center justify-center py-12 text-center">
                    <Sparkles class="h-16 w-16 text-muted-foreground/50" />
                    <h3 class="mt-4 text-lg font-medium">Belum Ada Rekomendasi</h3>
                    <p class="mt-2 max-w-md text-sm text-muted-foreground">
                        Klik tombol "Buat Rekomendasi" untuk mendapatkan rekomendasi materi yang dipersonalisasi berdasarkan gaya belajarmu.
                    </p>
                    <Button class="mt-4" @click="generateRecommendations" :disabled="isRefreshing">
                        <Sparkles class="mr-2 h-4 w-4" />
                        Buat Rekomendasi
                    </Button>
                </CardContent>
            </Card>

            <!-- Pagination -->
            <div v-if="recommendations.data.length > 0 && recommendations.last_page > 1" class="flex justify-center gap-2">
                <Button
                    v-for="link in recommendations.links"
                    :key="link.label"
                    :variant="link.active ? 'default' : 'outline'"
                    :disabled="!link.url"
                    size="sm"
                    @click="link.url && router.get(link.url)"
                    v-html="link.label"
                />
            </div>

            <!-- Viewed Recommendations History -->
            <Card v-if="viewedRecommendations.length > 0">
                <CardHeader>
                    <div class="flex items-center gap-2">
                        <History class="h-5 w-5" />
                        <CardTitle>Riwayat Rekomendasi</CardTitle>
                    </div>
                    <CardDescription>
                        Rekomendasi yang sudah kamu lihat
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="space-y-2">
                        <div
                            v-for="rec in viewedRecommendations"
                            :key="rec.id"
                            class="flex items-center justify-between rounded-lg border p-3"
                        >
                            <div class="flex items-center gap-3">
                                <Badge variant="outline" class="text-xs">
                                    {{ getMaterialTypeLabel(rec.material?.type || '') }}
                                </Badge>
                                <span class="text-sm">{{ rec.material?.title || 'Materi' }}</span>
                            </div>
                            <Link :href="materialShow.url(rec.material?.id || 0)">
                                <Button variant="ghost" size="sm">
                                    Lihat Lagi
                                </Button>
                            </Link>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
