<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { type BreadcrumbItem, type LearningMaterial, type LearningStyleProfile, type AiRecommendation } from '@/types';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { show as materialShow } from '@/actions/App/Http/Controllers/Student/MaterialController';
import { BookOpen, Video, FileText, Image, Headphones, Gamepad2, Sparkles, Filter, RefreshCw } from 'lucide-vue-next';
import { ref, computed } from 'vue';

interface Subject {
    id: number;
    name: string;
    code: string;
}

interface PaginatedMaterials {
    data: LearningMaterial[];
    links: { url: string | null; label: string; active: boolean }[];
    current_page: number;
    last_page: number;
    total: number;
}

interface Filters {
    subject: string | null;
    type: string | null;
    style: string | null;
}

interface Props {
    materials: PaginatedMaterials;
    subjects: Subject[];
    recommendations: AiRecommendation[];
    learningProfile?: LearningStyleProfile;
    filters: Filters;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Materi', href: '/materials' },
];

const isRefreshing = ref(false);

const materialTypes = [
    { value: 'video', label: 'Video', icon: Video },
    { value: 'document', label: 'Dokumen', icon: FileText },
    { value: 'infographic', label: 'Infografis', icon: Image },
    { value: 'audio', label: 'Audio', icon: Headphones },
    { value: 'simulation', label: 'Simulasi', icon: Gamepad2 },
];

const learningStyles = [
    { value: 'visual', label: 'Visual' },
    { value: 'auditory', label: 'Auditori' },
    { value: 'kinesthetic', label: 'Kinestetik' },
    { value: 'all', label: 'Semua' },
];

const getTypeIcon = (type: string) => {
    const icons: Record<string, typeof Video> = {
        video: Video,
        document: FileText,
        infographic: Image,
        audio: Headphones,
        simulation: Gamepad2,
    };
    return icons[type] || BookOpen;
};

const getTypeLabel = (type: string): string => {
    const labels: Record<string, string> = {
        video: 'Video',
        document: 'Dokumen',
        infographic: 'Infografis',
        audio: 'Audio',
        simulation: 'Simulasi',
    };
    return labels[type] || type;
};

const getStyleLabel = (style: string): string => {
    const labels: Record<string, string> = {
        visual: 'Visual',
        auditory: 'Auditori',
        kinesthetic: 'Kinestetik',
        all: 'Semua',
    };
    return labels[style] || style;
};

const getDifficultyLabel = (level: string): string => {
    const labels: Record<string, string> = {
        beginner: 'Pemula',
        intermediate: 'Menengah',
        advanced: 'Lanjutan',
    };
    return labels[level] || level;
};

const getDifficultyColor = (level: string): string => {
    const colors: Record<string, string> = {
        beginner: 'bg-green-500',
        intermediate: 'bg-yellow-500',
        advanced: 'bg-red-500',
    };
    return colors[level] || 'bg-gray-500';
};

const applyFilter = (key: string, value: string | null) => {
    const params: Record<string, string | null> = { ...props.filters };
    params[key] = value;

    // Remove null values
    Object.keys(params).forEach((k) => {
        if (params[k] === null || params[k] === '') {
            delete params[k];
        }
    });

    router.get('/student/materials', params, { preserveState: true });
};

const clearFilters = () => {
    router.get('/student/materials', {}, { preserveState: true });
};

const refreshRecommendations = () => {
    isRefreshing.value = true;
    router.post('/student/recommendations/refresh', {}, {
        onFinish: () => {
            isRefreshing.value = false;
        },
    });
};

const hasActiveFilters = computed(() => {
    return props.filters.subject || props.filters.type || props.filters.style;
});
</script>

<template>
    <Head title="Materi Pembelajaran" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <!-- Header -->
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Materi Pembelajaran</h1>
                    <p class="text-muted-foreground">
                        Jelajahi materi yang sesuai dengan gaya belajarmu
                    </p>
                </div>
                <div class="flex gap-2">
                    <Button variant="outline" size="sm" @click="clearFilters" v-if="hasActiveFilters">
                        Hapus Filter
                    </Button>
                </div>
            </div>

            <!-- AI Recommendations -->
            <Card v-if="recommendations.length > 0" class="border-primary/20 bg-primary/5">
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <Sparkles class="h-5 w-5 text-primary" />
                            <CardTitle class="text-lg">Rekomendasi Untukmu</CardTitle>
                        </div>
                        <Button variant="ghost" size="sm" @click="refreshRecommendations" :disabled="isRefreshing">
                            <RefreshCw :class="['h-4 w-4', isRefreshing ? 'animate-spin' : '']" />
                        </Button>
                    </div>
                    <CardDescription>
                        Materi yang dipersonalisasi berdasarkan gaya belajar {{ getStyleLabel(learningProfile?.dominant_style || '') }}
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="grid gap-3 md:grid-cols-2 lg:grid-cols-3">
                        <Link
                            v-for="rec in recommendations"
                            :key="rec.id"
                            :href="materialShow(rec.material?.id || 0).url"
                            class="flex flex-col gap-2 rounded-lg border bg-background p-3 transition-colors hover:bg-accent"
                        >
                            <div class="flex items-start justify-between">
                                <span class="font-medium line-clamp-1">{{ rec.material?.title }}</span>
                                <Badge variant="outline" class="shrink-0 text-xs">
                                    {{ getTypeLabel(rec.material?.type || '') }}
                                </Badge>
                            </div>
                            <p class="text-xs text-muted-foreground line-clamp-2">{{ rec.reason }}</p>
                        </Link>
                    </div>
                </CardContent>
            </Card>

            <!-- Filters -->
            <Card>
                <CardHeader class="pb-3">
                    <div class="flex items-center gap-2">
                        <Filter class="h-4 w-4" />
                        <CardTitle class="text-sm font-medium">Filter</CardTitle>
                    </div>
                </CardHeader>
                <CardContent>
                    <div class="flex flex-wrap gap-4">
                        <!-- Subject Filter -->
                        <div class="flex flex-col gap-2">
                            <span class="text-xs font-medium text-muted-foreground">Mata Pelajaran</span>
                            <div class="flex flex-wrap gap-1">
                                <Button
                                    v-for="subject in subjects"
                                    :key="subject.id"
                                    variant="outline"
                                    size="sm"
                                    :class="filters.subject == subject.id.toString() ? 'border-primary bg-primary/10' : ''"
                                    @click="applyFilter('subject', filters.subject == subject.id.toString() ? null : subject.id.toString())"
                                >
                                    {{ subject.name }}
                                </Button>
                            </div>
                        </div>

                        <!-- Type Filter -->
                        <div class="flex flex-col gap-2">
                            <span class="text-xs font-medium text-muted-foreground">Tipe Materi</span>
                            <div class="flex flex-wrap gap-1">
                                <Button
                                    v-for="type in materialTypes"
                                    :key="type.value"
                                    variant="outline"
                                    size="sm"
                                    :class="filters.type === type.value ? 'border-primary bg-primary/10' : ''"
                                    @click="applyFilter('type', filters.type === type.value ? null : type.value)"
                                >
                                    <component :is="type.icon" class="mr-1 h-3 w-3" />
                                    {{ type.label }}
                                </Button>
                            </div>
                        </div>

                        <!-- Style Filter -->
                        <div class="flex flex-col gap-2">
                            <span class="text-xs font-medium text-muted-foreground">Gaya Belajar</span>
                            <div class="flex flex-wrap gap-1">
                                <Button
                                    v-for="style in learningStyles"
                                    :key="style.value"
                                    variant="outline"
                                    size="sm"
                                    :class="filters.style === style.value ? 'border-primary bg-primary/10' : ''"
                                    @click="applyFilter('style', filters.style === style.value ? null : style.value)"
                                >
                                    {{ style.label }}
                                </Button>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Materials Grid -->
            <div v-if="materials.data.length > 0">
                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    <Link
                        v-for="material in materials.data"
                        :key="material.id"
                        :href="materialShow(material.id).url"
                        class="group"
                    >
                        <Card class="h-full transition-all hover:border-primary/50 hover:shadow-md">
                            <CardHeader class="pb-3">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="rounded-lg bg-primary/10 p-2">
                                        <component :is="getTypeIcon(material.type)" class="h-5 w-5 text-primary" />
                                    </div>
                                    <Badge :class="getDifficultyColor(material.difficulty_level)" class="text-xs text-white">
                                        {{ getDifficultyLabel(material.difficulty_level) }}
                                    </Badge>
                                </div>
                                <CardTitle class="line-clamp-2 text-base group-hover:text-primary">
                                    {{ material.title }}
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <p v-if="material.description" class="mb-3 text-sm text-muted-foreground line-clamp-2">
                                    {{ material.description }}
                                </p>
                                <div class="flex flex-wrap gap-1">
                                    <Badge variant="outline" class="text-xs">
                                        {{ getTypeLabel(material.type) }}
                                    </Badge>
                                    <Badge variant="secondary" class="text-xs">
                                        {{ getStyleLabel(material.learning_style) }}
                                    </Badge>
                                </div>
                            </CardContent>
                        </Card>
                    </Link>
                </div>

                <!-- Pagination -->
                <div v-if="materials.last_page > 1" class="mt-6 flex justify-center gap-2">
                    <template v-for="link in materials.links" :key="link.label">
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            :class="[
                                'px-3 py-2 text-sm rounded-md transition-colors',
                                link.active
                                    ? 'bg-primary text-primary-foreground'
                                    : 'bg-muted hover:bg-accent',
                            ]"
                            v-html="link.label"
                        />
                        <span
                            v-else
                            class="px-3 py-2 text-sm text-muted-foreground"
                            v-html="link.label"
                        />
                    </template>
                </div>
            </div>

            <!-- Empty State -->
            <Card v-else>
                <CardContent class="flex flex-col items-center justify-center py-12">
                    <BookOpen class="h-12 w-12 text-muted-foreground/50" />
                    <h3 class="mt-4 text-lg font-semibold">Tidak Ada Materi</h3>
                    <p class="mt-2 text-center text-sm text-muted-foreground">
                        <template v-if="hasActiveFilters">
                            Tidak ada materi yang sesuai dengan filter. Coba ubah filter atau hapus filter.
                        </template>
                        <template v-else>
                            Belum ada materi pembelajaran tersedia saat ini.
                        </template>
                    </p>
                    <Button v-if="hasActiveFilters" variant="outline" class="mt-4" @click="clearFilters">
                        Hapus Filter
                    </Button>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
