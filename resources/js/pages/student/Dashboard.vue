<script setup lang="ts">
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Link, usePage } from '@inertiajs/vue3';
import { Head } from '@inertiajs/vue3';
import { type BreadcrumbItem, type ClassRoom, type LearningStyleProfile, type LearningActivity, type AiRecommendation, type Subject } from '@/types';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { index as questionnaireIndex } from '@/actions/App/Http/Controllers/Student/QuestionnaireController';
import { show as learningProfileShow } from '@/actions/App/Http/Controllers/Student/LearningProfileController';
import { BookOpen, Brain, ClipboardList, Clock, Plus, Sparkles, TrendingUp } from 'lucide-vue-next';
import SubjectCard from '@/components/student/SubjectCard.vue';
import EnrollmentModal from '@/components/student/EnrollmentModal.vue';

interface SubjectProgress {
    completedTopics: number;
    totalTopics: number;
    percentage: number;
}

interface Enrollment {
    id: number;
    subject: Subject;
    enrollment_type: 'assigned' | 'elective';
    enrolled_at: string;
    status: string;
    progress: SubjectProgress;
}

interface AvailableSubject {
    id: number;
    name: string;
    code: string;
    description?: string;
    materials_count: number;
    topic_count: number;
}

interface Props {
    hasCompletedQuestionnaire: boolean;
    learningProfile?: LearningStyleProfile;
    currentClass?: ClassRoom;
    recentActivities?: LearningActivity[];
    recommendations?: AiRecommendation[];
    enrollments?: Enrollment[];
    availableSubjects?: AvailableSubject[];
    stats?: {
        completedTopics: number;
        totalTopics: number;
    };
}

const props = defineProps<Props>();
const page = usePage();
const user = page.props.auth.user;
const showEnrollmentModal = ref(false);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard Siswa',
        href: '/student/dashboard',
    },
];

const getLearningStyleLabel = (style: string): string => {
    const labels: Record<string, string> = {
        visual: 'Visual',
        auditory: 'Auditori',
        kinesthetic: 'Kinestetik',
    };
    return labels[style] || style;
};

const getLearningStyleColor = (style: string): string => {
    const colors: Record<string, string> = {
        visual: 'bg-blue-500',
        auditory: 'bg-green-500',
        kinesthetic: 'bg-orange-500',
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

const formatDuration = (seconds: number): string => {
    const minutes = Math.floor(seconds / 60);
    if (minutes < 60) {
        return `${minutes} menit`;
    }
    const hours = Math.floor(minutes / 60);
    const remainingMinutes = minutes % 60;
    return `${hours} jam ${remainingMinutes} menit`;
};
</script>

<template>
    <Head title="Dashboard Siswa" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <!-- Welcome Section -->
            <div class="flex flex-col gap-2">
                <h1 class="text-2xl font-bold tracking-tight">
                    Selamat datang, {{ user.name }}!
                </h1>
                <p class="text-muted-foreground">
                    <template v-if="currentClass">
                        Kelas {{ currentClass.name }} - {{ currentClass.academic_year }}
                    </template>
                    <template v-else>
                        Belum terdaftar di kelas manapun
                    </template>
                </p>
            </div>

            <!-- Questionnaire CTA -->
            <Card v-if="!hasCompletedQuestionnaire" class="border-amber-200 bg-amber-50 dark:border-amber-800 dark:bg-amber-950">
                <CardHeader>
                    <div class="flex items-center gap-3">
                        <div class="rounded-full bg-amber-100 p-2 dark:bg-amber-900">
                            <ClipboardList class="h-5 w-5 text-amber-600 dark:text-amber-400" />
                        </div>
                        <div>
                            <CardTitle class="text-lg">Lengkapi Kuesioner Gaya Belajar</CardTitle>
                            <CardDescription>
                                Bantu kami memahami cara belajar terbaikmu dengan mengisi kuesioner singkat
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

            <!-- Stats Grid -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <!-- Learning Profile Card -->
                <Card v-if="learningProfile">
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium">Gaya Belajar</CardTitle>
                        <Brain class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="flex items-center gap-2">
                            <Badge :class="getLearningStyleColor(learningProfile.dominant_style)">
                                {{ getLearningStyleLabel(learningProfile.dominant_style) }}
                            </Badge>
                        </div>
                        <p class="mt-2 text-xs text-muted-foreground">
                            V: {{ learningProfile.visual_score }}% |
                            A: {{ learningProfile.auditory_score }}% |
                            K: {{ learningProfile.kinesthetic_score }}%
                        </p>
                        <Link :href="learningProfileShow().url" class="mt-2 inline-block text-xs text-primary hover:underline">
                            Lihat Detail
                        </Link>
                    </CardContent>
                </Card>

                <!-- Recent Activities Summary -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium">Aktivitas Terkini</CardTitle>
                        <Clock class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">
                            {{ recentActivities?.length || 0 }}
                        </div>
                        <p class="text-xs text-muted-foreground">
                            materi dipelajari minggu ini
                        </p>
                    </CardContent>
                </Card>

                <!-- Recommendations Count -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium">Rekomendasi</CardTitle>
                        <Sparkles class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">
                            {{ recommendations?.length || 0 }}
                        </div>
                        <p class="text-xs text-muted-foreground">
                            materi yang disarankan untukmu
                        </p>
                    </CardContent>
                </Card>

                <!-- Progress -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium">Kemajuan</CardTitle>
                        <TrendingUp class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">
                            {{ stats?.completedTopics || 0 }}/{{ stats?.totalTopics || 0 }}
                        </div>
                        <p class="text-xs text-muted-foreground">
                            topik selesai dipelajari
                        </p>
                    </CardContent>
                </Card>
            </div>

            <!-- Subjects Section -->
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-semibold">Mata Pelajaran Saya</h2>
                    <Button
                        v-if="availableSubjects && availableSubjects.length > 0"
                        variant="outline"
                        size="sm"
                        @click="showEnrollmentModal = true"
                    >
                        <Plus class="mr-2 h-4 w-4" />
                        Tambah Pilihan
                    </Button>
                </div>

                <div v-if="enrollments && enrollments.length > 0" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <SubjectCard
                        v-for="enrollment in enrollments"
                        :key="enrollment.id"
                        :subject="enrollment.subject"
                        :enrollment-type="enrollment.enrollment_type"
                        :progress="enrollment.progress"
                    />
                </div>

                <Card v-else class="p-8">
                    <div class="flex flex-col items-center justify-center text-center">
                        <BookOpen class="h-12 w-12 text-muted-foreground/50" />
                        <p class="mt-4 text-muted-foreground">
                            Belum ada mata pelajaran yang terdaftar.
                        </p>
                        <Button
                            v-if="availableSubjects && availableSubjects.length > 0"
                            class="mt-4"
                            @click="showEnrollmentModal = true"
                        >
                            <Plus class="mr-2 h-4 w-4" />
                            Daftar Mata Pelajaran
                        </Button>
                    </div>
                </Card>
            </div>

            <!-- Content Grid -->
            <div class="grid gap-6 lg:grid-cols-2">
                <!-- Recent Activities -->
                <Card>
                    <CardHeader>
                        <div class="flex items-center gap-2">
                            <BookOpen class="h-5 w-5" />
                            <CardTitle>Aktivitas Terakhir</CardTitle>
                        </div>
                        <CardDescription>
                            Materi yang baru saja kamu pelajari
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div v-if="recentActivities && recentActivities.length > 0" class="space-y-4">
                            <div
                                v-for="activity in recentActivities"
                                :key="activity.id"
                                class="flex items-center justify-between rounded-lg border p-3"
                            >
                                <div class="flex flex-col gap-1">
                                    <span class="font-medium">
                                        {{ activity.material?.title || 'Materi' }}
                                    </span>
                                    <div class="flex items-center gap-2 text-sm text-muted-foreground">
                                        <Badge variant="outline" class="text-xs">
                                            {{ getMaterialTypeLabel(activity.material?.type || '') }}
                                        </Badge>
                                        <span>{{ formatDuration(activity.duration_seconds) }}</span>
                                    </div>
                                </div>
                                <Badge v-if="activity.completed_at" variant="secondary">
                                    Selesai
                                </Badge>
                            </div>
                        </div>
                        <div v-else class="flex flex-col items-center justify-center py-8 text-center">
                            <BookOpen class="h-12 w-12 text-muted-foreground/50" />
                            <p class="mt-2 text-sm text-muted-foreground">
                                Belum ada aktivitas belajar
                            </p>
                        </div>
                    </CardContent>
                </Card>

                <!-- AI Recommendations -->
                <Card>
                    <CardHeader>
                        <div class="flex items-center gap-2">
                            <Sparkles class="h-5 w-5" />
                            <CardTitle>Rekomendasi Untukmu</CardTitle>
                        </div>
                        <CardDescription>
                            Materi yang dipersonalisasi berdasarkan gaya belajarmu
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div v-if="recommendations && recommendations.length > 0" class="space-y-4">
                            <div
                                v-for="rec in recommendations"
                                :key="rec.id"
                                class="flex flex-col gap-2 rounded-lg border p-3"
                            >
                                <div class="flex items-start justify-between">
                                    <span class="font-medium">
                                        {{ rec.material?.title || 'Materi' }}
                                    </span>
                                    <Badge variant="outline" class="text-xs">
                                        {{ getMaterialTypeLabel(rec.material?.type || '') }}
                                    </Badge>
                                </div>
                                <p class="text-sm text-muted-foreground line-clamp-2">
                                    {{ rec.reason }}
                                </p>
                            </div>
                        </div>
                        <div v-else class="flex flex-col items-center justify-center py-8 text-center">
                            <Sparkles class="h-12 w-12 text-muted-foreground/50" />
                            <p class="mt-2 text-sm text-muted-foreground">
                                <template v-if="!hasCompletedQuestionnaire">
                                    Lengkapi kuesioner untuk mendapatkan rekomendasi
                                </template>
                                <template v-else>
                                    Belum ada rekomendasi tersedia
                                </template>
                            </p>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>

        <!-- Enrollment Modal -->
        <EnrollmentModal
            v-model:open="showEnrollmentModal"
            :available-subjects="availableSubjects || []"
        />
    </AppLayout>
</template>
