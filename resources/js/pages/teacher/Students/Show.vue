<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import {
    type BreadcrumbItem,
    type User,
    type LearningStyleProfile,
    type ClassRoom,
    type LearningActivity,
    type AiRecommendation,
    type AiFeedback,
    type LearningMaterial,
} from '@/types';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { index as studentsIndex } from '@/actions/App/Http/Controllers/Teacher/StudentController';
import {
    ArrowLeft,
    Brain,
    BookOpen,
    Clock,
    CheckCircle,
    Eye,
    Lightbulb,
    MessageSquare,
    TrendingUp,
    Video,
    FileText,
    Image,
    Headphones,
    Gamepad2,
} from 'lucide-vue-next';
import { computed } from 'vue';

interface StudentWithProfile extends User {
    learning_style_profile?: LearningStyleProfile;
    classes?: ClassRoom[];
}

interface ActivityWithMaterial extends LearningActivity {
    material?: {
        id: number;
        title: string;
        type: string;
        topic?: string;
    };
}

interface RecommendationWithMaterial extends AiRecommendation {
    material?: {
        id: number;
        title: string;
        type: string;
    };
}

interface Stats {
    totalActivities: number;
    completedActivities: number;
    totalLearningTime: number;
    averageSessionTime: number;
}

interface Props {
    student: StudentWithProfile;
    recentActivities: ActivityWithMaterial[];
    stats: Stats;
    recommendations: RecommendationWithMaterial[];
    feedback: AiFeedback[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/teacher/dashboard' },
    { title: 'Siswa', href: '/teacher/students' },
    { title: props.student.name, href: `/teacher/students/${props.student.id}` },
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

const formatDate = (dateString: string): string => {
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const formatDuration = (minutes: number): string => {
    if (minutes < 60) {
        return `${minutes} menit`;
    }
    const hours = Math.floor(minutes / 60);
    const remainingMinutes = minutes % 60;
    return remainingMinutes > 0 ? `${hours} jam ${remainingMinutes} menit` : `${hours} jam`;
};

const completionRate = computed(() => {
    if (props.stats.totalActivities === 0) return 0;
    return Math.round((props.stats.completedActivities / props.stats.totalActivities) * 100);
});

const learningStyleScores = computed(() => {
    const profile = props.student.learning_style_profile;
    if (!profile) return null;

    const total = profile.visual_score + profile.auditory_score + profile.kinesthetic_score;
    return {
        visual: Math.round((profile.visual_score / total) * 100),
        auditory: Math.round((profile.auditory_score / total) * 100),
        kinesthetic: Math.round((profile.kinesthetic_score / total) * 100),
    };
});
</script>

<template>
    <Head :title="student.name" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <!-- Back Button -->
            <div>
                <Link :href="studentsIndex().url">
                    <Button variant="ghost" size="sm">
                        <ArrowLeft class="mr-2 h-4 w-4" />
                        Kembali
                    </Button>
                </Link>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Student Info Card -->
                    <Card>
                        <CardHeader>
                            <div class="flex items-start gap-4">
                                <div class="flex h-16 w-16 items-center justify-center rounded-full bg-primary/10 text-primary font-bold text-2xl">
                                    {{ student.name.charAt(0).toUpperCase() }}
                                </div>
                                <div class="flex-1">
                                    <CardTitle class="text-xl">{{ student.name }}</CardTitle>
                                    <CardDescription>
                                        {{ student.email }}
                                        <span v-if="student.student_id_number">
                                            | NIS: {{ student.student_id_number }}
                                        </span>
                                    </CardDescription>
                                    <div class="flex flex-wrap gap-2 mt-2">
                                        <Badge
                                            v-for="cls in student.classes"
                                            :key="cls.id"
                                            variant="outline"
                                        >
                                            {{ cls.name }} ({{ cls.grade_level }})
                                        </Badge>
                                    </div>
                                </div>
                            </div>
                        </CardHeader>
                    </Card>

                    <!-- Learning Style Profile -->
                    <Card v-if="student.learning_style_profile">
                        <CardHeader>
                            <CardTitle class="text-lg flex items-center gap-2">
                                <Brain class="h-5 w-5" />
                                Profil Gaya Belajar
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="flex items-center gap-3 mb-4">
                                <span class="text-sm text-muted-foreground">Gaya Dominan:</span>
                                <Badge
                                    :class="getStyleColor(student.learning_style_profile.dominant_style)"
                                    class="text-white"
                                >
                                    {{ getStyleLabel(student.learning_style_profile.dominant_style) }}
                                </Badge>
                            </div>
                            <div v-if="learningStyleScores" class="space-y-3">
                                <div class="space-y-1">
                                    <div class="flex justify-between text-sm">
                                        <span>Visual</span>
                                        <span class="font-medium">{{ learningStyleScores.visual }}%</span>
                                    </div>
                                    <div class="h-2 rounded-full bg-muted overflow-hidden">
                                        <div
                                            class="h-full bg-blue-500 transition-all"
                                            :style="{ width: `${learningStyleScores.visual}%` }"
                                        />
                                    </div>
                                </div>
                                <div class="space-y-1">
                                    <div class="flex justify-between text-sm">
                                        <span>Auditori</span>
                                        <span class="font-medium">{{ learningStyleScores.auditory }}%</span>
                                    </div>
                                    <div class="h-2 rounded-full bg-muted overflow-hidden">
                                        <div
                                            class="h-full bg-green-500 transition-all"
                                            :style="{ width: `${learningStyleScores.auditory}%` }"
                                        />
                                    </div>
                                </div>
                                <div class="space-y-1">
                                    <div class="flex justify-between text-sm">
                                        <span>Kinestetik</span>
                                        <span class="font-medium">{{ learningStyleScores.kinesthetic }}%</span>
                                    </div>
                                    <div class="h-2 rounded-full bg-muted overflow-hidden">
                                        <div
                                            class="h-full bg-orange-500 transition-all"
                                            :style="{ width: `${learningStyleScores.kinesthetic}%` }"
                                        />
                                    </div>
                                </div>
                            </div>
                            <p class="text-xs text-muted-foreground mt-4">
                                Dianalisis pada {{ formatDate(student.learning_style_profile.analyzed_at) }}
                            </p>
                        </CardContent>
                    </Card>

                    <Card v-else>
                        <CardHeader>
                            <CardTitle class="text-lg flex items-center gap-2">
                                <Brain class="h-5 w-5" />
                                Profil Gaya Belajar
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="text-center py-6 text-muted-foreground">
                                <Brain class="mx-auto h-12 w-12 opacity-50" />
                                <p class="mt-2">Siswa belum mengisi kuesioner gaya belajar</p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Recent Activities -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="text-lg">Aktivitas Terbaru</CardTitle>
                            <CardDescription>Materi yang dipelajari siswa</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div v-if="recentActivities.length > 0" class="space-y-3">
                                <div
                                    v-for="activity in recentActivities"
                                    :key="activity.id"
                                    class="flex items-center justify-between rounded-lg border p-3"
                                >
                                    <div class="flex items-center gap-3">
                                        <div class="rounded-lg bg-primary/10 p-2">
                                            <component
                                                :is="getTypeIcon(activity.material?.type || '')"
                                                class="h-4 w-4 text-primary"
                                            />
                                        </div>
                                        <div>
                                            <p class="font-medium">{{ activity.material?.title }}</p>
                                            <p v-if="activity.material?.topic" class="text-sm text-muted-foreground">
                                                {{ activity.material.topic }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="flex items-center gap-2">
                                            <Badge v-if="activity.completed_at" variant="default" class="bg-green-500">
                                                <CheckCircle class="mr-1 h-3 w-3" />
                                                Selesai
                                            </Badge>
                                            <Badge v-else variant="secondary">
                                                <Clock class="mr-1 h-3 w-3" />
                                                Berlangsung
                                            </Badge>
                                        </div>
                                        <p class="text-xs text-muted-foreground mt-1">
                                            {{ formatDate(activity.started_at) }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="text-center py-8 text-muted-foreground">
                                <BookOpen class="mx-auto h-12 w-12 opacity-50" />
                                <p class="mt-2">Belum ada aktivitas pembelajaran</p>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Stats Cards -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="text-lg flex items-center gap-2">
                                <TrendingUp class="h-5 w-5" />
                                Statistik
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <Eye class="h-4 w-4 text-muted-foreground" />
                                    <span class="text-sm">Total Aktivitas</span>
                                </div>
                                <span class="font-bold">{{ stats.totalActivities }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <CheckCircle class="h-4 w-4 text-muted-foreground" />
                                    <span class="text-sm">Diselesaikan</span>
                                </div>
                                <span class="font-bold">{{ stats.completedActivities }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <Clock class="h-4 w-4 text-muted-foreground" />
                                    <span class="text-sm">Total Waktu</span>
                                </div>
                                <span class="font-bold">{{ formatDuration(stats.totalLearningTime) }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <Clock class="h-4 w-4 text-muted-foreground" />
                                    <span class="text-sm">Rata-rata Sesi</span>
                                </div>
                                <span class="font-bold">{{ stats.averageSessionTime }} menit</span>
                            </div>
                            <div class="pt-2 border-t">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-sm font-medium">Tingkat Penyelesaian</span>
                                    <span class="font-bold">{{ completionRate }}%</span>
                                </div>
                                <div class="h-2 rounded-full bg-muted overflow-hidden">
                                    <div
                                        class="h-full bg-primary transition-all"
                                        :style="{ width: `${completionRate}%` }"
                                    />
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- AI Recommendations -->
                    <Card v-if="recommendations.length > 0">
                        <CardHeader>
                            <CardTitle class="text-lg flex items-center gap-2">
                                <Lightbulb class="h-5 w-5" />
                                Rekomendasi AI
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-3">
                                <div
                                    v-for="rec in recommendations.slice(0, 5)"
                                    :key="rec.id"
                                    class="rounded-lg border p-3"
                                >
                                    <div class="flex items-center gap-2 mb-1">
                                        <component
                                            :is="getTypeIcon(rec.material?.type || '')"
                                            class="h-4 w-4 text-muted-foreground"
                                        />
                                        <p class="font-medium text-sm">{{ rec.material?.title }}</p>
                                    </div>
                                    <p class="text-xs text-muted-foreground line-clamp-2">
                                        {{ rec.reason }}
                                    </p>
                                    <div class="flex items-center gap-2 mt-2">
                                        <Badge variant="outline" class="text-xs">
                                            Relevansi: {{ Math.round(rec.relevance_score * 100) }}%
                                        </Badge>
                                        <Badge
                                            v-if="rec.is_viewed"
                                            variant="secondary"
                                            class="text-xs"
                                        >
                                            Sudah dilihat
                                        </Badge>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- AI Feedback -->
                    <Card v-if="feedback.length > 0">
                        <CardHeader>
                            <CardTitle class="text-lg flex items-center gap-2">
                                <MessageSquare class="h-5 w-5" />
                                Feedback AI
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-3">
                                <div
                                    v-for="fb in feedback"
                                    :key="fb.id"
                                    class="rounded-lg border p-3"
                                >
                                    <p class="text-sm line-clamp-4">{{ fb.feedback_text }}</p>
                                    <p class="text-xs text-muted-foreground mt-2">
                                        {{ formatDate(fb.generated_at) }}
                                    </p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
