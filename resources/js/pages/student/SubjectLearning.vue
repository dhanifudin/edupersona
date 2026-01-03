<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { type BreadcrumbItem, type Subject, type LearningStyleProfile } from '@/types';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { index as dashboardIndex } from '@/actions/App/Http/Controllers/Student/DashboardController';
import { topic as topicRoute, startTopic } from '@/actions/App/Http/Controllers/Student/SubjectLearningController';
import TopicList from '@/components/student/TopicList.vue';
import {
    ArrowLeft,
    BookOpen,
    CheckCircle2,
    PlayCircle,
    Eye,
    Headphones,
    Hand,
    Sparkles,
    Brain,
    Lightbulb,
    Star,
    ArrowRight,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface Topic {
    name: string;
    status: 'not_started' | 'in_progress' | 'completed';
    materialsCount: number;
}

interface Enrollment {
    id: number;
    enrollment_type: 'assigned' | 'elective';
    enrolled_at: string;
    status: string;
}

interface Material {
    id: number;
    title: string;
    type: string;
    learning_style: string;
    description: string;
    topic: string;
}

interface Recommendation {
    id: number;
    material_id: number;
    reason: string;
    relevance_score: number;
    material: Material;
}

interface AiFeedback {
    id: number;
    feedback_type: string;
    feedback_text: string;
    generated_at: string;
}

interface Props {
    subject: Subject;
    enrollment: Enrollment;
    topics: Topic[];
    currentTopic?: Topic;
    learningProfile?: LearningStyleProfile;
    recommendations: Recommendation[];
    aiFeedback?: AiFeedback;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboardIndex.url(),
    },
    {
        title: props.subject.name,
        href: '#',
    },
];

const completedTopics = props.topics.filter((t) => t.status === 'completed').length;
const totalTopics = props.topics.length;
const progressPercentage = totalTopics > 0 ? Math.round((completedTopics / totalTopics) * 100) : 0;

const isFirstTime = computed(() => {
    return props.topics.every(t => t.status === 'not_started');
});

const learningStyleInfo = computed(() => {
    const style = props.learningProfile?.dominant_style ?? 'mixed';
    const styleMap: Record<string, { label: string; icon: typeof Eye; color: string; description: string }> = {
        visual: {
            label: 'Visual',
            icon: Eye,
            color: 'text-blue-500',
            description: 'Kamu belajar paling baik melalui gambar, video, diagram, dan visualisasi.',
        },
        auditory: {
            label: 'Auditori',
            icon: Headphones,
            color: 'text-purple-500',
            description: 'Kamu belajar paling baik melalui audio, podcast, diskusi, dan penjelasan verbal.',
        },
        kinesthetic: {
            label: 'Kinestetik',
            icon: Hand,
            color: 'text-green-500',
            description: 'Kamu belajar paling baik melalui praktik langsung, simulasi, dan aktivitas hands-on.',
        },
        mixed: {
            label: 'Campuran',
            icon: Brain,
            color: 'text-orange-500',
            description: 'Kamu dapat belajar efektif dengan berbagai metode pembelajaran.',
        },
    };
    return styleMap[style] || styleMap.mixed;
});

const getMaterialTypeIcon = (type: string) => {
    const icons: Record<string, string> = {
        video: '🎬',
        audio: '🎧',
        document: '📄',
        simulation: '🎮',
        infographic: '📊',
    };
    return icons[type] || '📚';
};

const getMaterialStyleBadge = (style: string) => {
    const styles: Record<string, { label: string; variant: 'default' | 'secondary' | 'outline' }> = {
        visual: { label: 'Visual', variant: 'default' },
        auditory: { label: 'Auditori', variant: 'secondary' },
        kinesthetic: { label: 'Kinestetik', variant: 'outline' },
        all: { label: 'Universal', variant: 'secondary' },
    };
    return styles[style] || styles.all;
};

const showFeedback = ref(true);

const dismissFeedback = () => {
    showFeedback.value = false;
};
</script>

<template>
    <Head :title="`Belajar ${subject.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <!-- Header -->
            <div class="flex items-start justify-between">
                <div class="flex items-center gap-4">
                    <Link :href="dashboardIndex.url()">
                        <Button variant="ghost" size="icon">
                            <ArrowLeft class="h-5 w-5" />
                        </Button>
                    </Link>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-2xl font-bold">{{ subject.name }}</h1>
                            <Badge :variant="enrollment.enrollment_type === 'assigned' ? 'default' : 'secondary'">
                                {{ enrollment.enrollment_type === 'assigned' ? 'Wajib' : 'Pilihan' }}
                            </Badge>
                        </div>
                        <p v-if="subject.description" class="mt-1 text-muted-foreground">
                            {{ subject.description }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- First Time Welcome Banner -->
            <Alert v-if="isFirstTime && learningProfile" class="border-primary/50 bg-primary/5">
                <Sparkles class="h-5 w-5 text-primary" />
                <AlertTitle class="text-lg">Selamat Datang di Pembelajaran Personalisasi!</AlertTitle>
                <AlertDescription class="mt-2">
                    <p class="mb-3">
                        Berdasarkan profil gaya belajarmu, kami telah menyusun materi yang paling cocok untukmu.
                        Materi dengan gaya belajar <strong>{{ learningStyleInfo.label }}</strong> akan ditampilkan terlebih dahulu.
                    </p>
                    <div class="flex flex-wrap gap-2">
                        <Badge variant="outline" class="gap-1">
                            <component :is="learningStyleInfo.icon" class="h-3 w-3" />
                            Gaya Belajar: {{ learningStyleInfo.label }}
                        </Badge>
                        <Badge variant="outline">{{ totalTopics }} Topik Tersedia</Badge>
                    </div>
                </AlertDescription>
            </Alert>

            <!-- AI Feedback Card (Motivational Message) -->
            <Card v-if="aiFeedback && showFeedback" class="border-l-4 border-l-yellow-500 bg-gradient-to-r from-yellow-50 to-transparent dark:from-yellow-950/20">
                <CardHeader class="pb-2">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <Lightbulb class="h-5 w-5 text-yellow-500" />
                            <CardTitle class="text-base">Pesan dari AI Tutor</CardTitle>
                        </div>
                        <Button variant="ghost" size="sm" @click="dismissFeedback">
                            Tutup
                        </Button>
                    </div>
                </CardHeader>
                <CardContent>
                    <div class="prose prose-sm dark:prose-invert max-w-none" v-html="aiFeedback.feedback_text.replace(/\n/g, '<br>')" />
                </CardContent>
            </Card>

            <!-- Learning Profile + Progress Row -->
            <div class="grid gap-6 lg:grid-cols-2">
                <!-- Learning Style Profile Card -->
                <Card v-if="learningProfile">
                    <CardHeader class="pb-3">
                        <div class="flex items-center gap-2">
                            <Brain class="h-5 w-5 text-primary" />
                            <CardTitle class="text-lg">Profil Gaya Belajarmu</CardTitle>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="flex items-center gap-4 mb-4">
                            <div class="flex h-14 w-14 items-center justify-center rounded-full bg-primary/10">
                                <component :is="learningStyleInfo.icon" :class="['h-7 w-7', learningStyleInfo.color]" />
                            </div>
                            <div>
                                <p class="font-semibold text-lg">{{ learningStyleInfo.label }}</p>
                                <p class="text-sm text-muted-foreground">{{ learningStyleInfo.description }}</p>
                            </div>
                        </div>
                        <!-- Score Bars -->
                        <div class="space-y-2 text-sm">
                            <div class="flex items-center gap-2">
                                <Eye class="h-4 w-4 text-blue-500" />
                                <span class="w-16">Visual</span>
                                <div class="flex-1 h-2 bg-secondary rounded-full overflow-hidden">
                                    <div class="h-full bg-blue-500" :style="{ width: `${learningProfile.visual_score}%` }" />
                                </div>
                                <span class="w-10 text-right text-muted-foreground">{{ learningProfile.visual_score }}%</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <Headphones class="h-4 w-4 text-purple-500" />
                                <span class="w-16">Auditori</span>
                                <div class="flex-1 h-2 bg-secondary rounded-full overflow-hidden">
                                    <div class="h-full bg-purple-500" :style="{ width: `${learningProfile.auditory_score}%` }" />
                                </div>
                                <span class="w-10 text-right text-muted-foreground">{{ learningProfile.auditory_score }}%</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <Hand class="h-4 w-4 text-green-500" />
                                <span class="w-16">Kinestetik</span>
                                <div class="flex-1 h-2 bg-secondary rounded-full overflow-hidden">
                                    <div class="h-full bg-green-500" :style="{ width: `${learningProfile.kinesthetic_score}%` }" />
                                </div>
                                <span class="w-10 text-right text-muted-foreground">{{ learningProfile.kinesthetic_score }}%</span>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Progress Overview -->
                <Card>
                    <CardHeader class="pb-3">
                        <div class="flex items-center gap-2">
                            <BookOpen class="h-5 w-5 text-primary" />
                            <CardTitle class="text-lg">Kemajuan Belajar</CardTitle>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="flex items-center gap-4 mb-4">
                            <div class="flex h-14 w-14 items-center justify-center rounded-full bg-primary/10">
                                <span class="text-xl font-bold text-primary">{{ progressPercentage }}%</span>
                            </div>
                            <div>
                                <p class="font-semibold text-lg">{{ completedTopics }}/{{ totalTopics }} Topik</p>
                                <p class="text-sm text-muted-foreground">
                                    <template v-if="completedTopics === 0">Mulai perjalanan belajarmu!</template>
                                    <template v-else-if="completedTopics === totalTopics">Semua topik selesai!</template>
                                    <template v-else>Terus semangat belajar!</template>
                                </p>
                            </div>
                        </div>
                        <div class="h-3 w-full bg-secondary rounded-full overflow-hidden">
                            <div
                                class="h-full bg-primary transition-all duration-300"
                                :style="{ width: `${progressPercentage}%` }"
                            />
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- AI Recommendations Section -->
            <Card v-if="recommendations.length > 0">
                <CardHeader>
                    <div class="flex items-center gap-2">
                        <Star class="h-5 w-5 text-yellow-500" />
                        <CardTitle class="text-lg">Rekomendasi Untukmu</CardTitle>
                    </div>
                    <CardDescription>
                        Materi yang dipilih khusus berdasarkan gaya belajar {{ learningStyleInfo.label }} milikmu
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                        <Link
                            v-for="rec in recommendations.slice(0, 3)"
                            :key="rec.id"
                            :href="topicRoute.url({ subject: subject.id, topic: rec.material.topic })"
                            class="group block"
                        >
                            <div class="p-4 rounded-lg border bg-card hover:bg-muted/50 transition-colors h-full">
                                <div class="flex items-start gap-3">
                                    <span class="text-2xl">{{ getMaterialTypeIcon(rec.material.type) }}</span>
                                    <div class="flex-1 min-w-0">
                                        <p class="font-medium group-hover:text-primary transition-colors line-clamp-2">
                                            {{ rec.material.title }}
                                        </p>
                                        <div class="flex flex-wrap gap-1 mt-2">
                                            <Badge :variant="getMaterialStyleBadge(rec.material.learning_style).variant" class="text-xs">
                                                {{ getMaterialStyleBadge(rec.material.learning_style).label }}
                                            </Badge>
                                            <Badge variant="outline" class="text-xs">
                                                {{ rec.material.topic }}
                                            </Badge>
                                        </div>
                                        <p class="text-xs text-muted-foreground mt-2 line-clamp-2">
                                            {{ rec.reason }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </Link>
                    </div>
                </CardContent>
            </Card>

            <!-- Main Content Grid -->
            <div class="grid gap-6 lg:grid-cols-3">
                <!-- Topics List -->
                <Card class="lg:col-span-1">
                    <CardHeader>
                        <CardTitle class="text-lg">Daftar Topik</CardTitle>
                        <CardDescription>
                            Pilih topik untuk mulai belajar
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <TopicList
                            :topics="topics"
                            :subject-id="subject.id"
                            :current-topic="currentTopic?.name"
                        />
                    </CardContent>
                </Card>

                <!-- Current Topic / Call to Action -->
                <Card class="lg:col-span-2">
                    <CardHeader>
                        <CardTitle class="text-lg">
                            <template v-if="currentTopic">
                                {{ currentTopic.status === 'in_progress' ? 'Lanjutkan Belajar' : 'Mulai dari Sini' }}
                            </template>
                            <template v-else>
                                Selamat!
                            </template>
                        </CardTitle>
                        <CardDescription v-if="currentTopic">
                            <template v-if="currentTopic.status === 'in_progress'">
                                Kamu sedang mempelajari topik ini
                            </template>
                            <template v-else>
                                Topik yang direkomendasikan untuk dipelajari selanjutnya
                            </template>
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <template v-if="currentTopic">
                            <div class="flex flex-col gap-4">
                                <div class="flex items-center gap-3 p-4 rounded-lg bg-muted/50">
                                    <component
                                        :is="currentTopic.status === 'in_progress' ? PlayCircle : BookOpen"
                                        class="h-8 w-8 text-primary shrink-0"
                                    />
                                    <div class="flex-1">
                                        <p class="font-semibold text-lg">{{ currentTopic.name }}</p>
                                        <p class="text-sm text-muted-foreground">
                                            {{ currentTopic.materialsCount }} materi pembelajaran tersedia
                                        </p>
                                    </div>
                                    <Badge v-if="currentTopic.status === 'in_progress'" class="bg-blue-500">
                                        Sedang Belajar
                                    </Badge>
                                </div>

                                <div class="p-4 rounded-lg bg-primary/5 border border-primary/20">
                                    <p class="text-sm text-muted-foreground mb-2">
                                        <Sparkles class="inline h-4 w-4 text-primary mr-1" />
                                        Materi akan disusun berdasarkan gaya belajar <strong>{{ learningStyleInfo.label }}</strong> milikmu
                                    </p>
                                </div>

                                <div class="flex gap-3">
                                    <Link :href="topicRoute.url({ subject: subject.id, topic: currentTopic.name })" class="flex-1">
                                        <Button class="w-full" size="lg">
                                            <PlayCircle class="mr-2 h-5 w-5" />
                                            {{ currentTopic.status === 'in_progress' ? 'Lanjutkan Belajar' : 'Mulai Belajar' }}
                                            <ArrowRight class="ml-2 h-5 w-5" />
                                        </Button>
                                    </Link>
                                </div>
                            </div>
                        </template>

                        <template v-else>
                            <div class="flex flex-col items-center justify-center py-8 text-center">
                                <CheckCircle2 class="h-16 w-16 text-green-500" />
                                <p class="mt-4 text-lg font-semibold">Semua Topik Selesai!</p>
                                <p class="text-muted-foreground">
                                    Kamu telah menyelesaikan semua topik di mata pelajaran ini.
                                </p>
                                <Link :href="dashboardIndex.url()" class="mt-4">
                                    <Button variant="outline">
                                        Kembali ke Dashboard
                                    </Button>
                                </Link>
                            </div>
                        </template>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
