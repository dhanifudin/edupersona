<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { type BreadcrumbItem, type LearningActivity } from '@/types';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Progress } from '@/components/ui/progress';
import { BookOpen, CheckCircle, Clock, Flame, Target, TrendingUp } from 'lucide-vue-next';

interface Stats {
    totalMinutes: number;
    totalHours: number;
    totalActivities: number;
    completedActivities: number;
    uniqueMaterials: number;
    topicsCompleted: number;
    topicsInProgress: number;
    completionRate: number;
}

interface WeeklyActivity {
    week: string;
    weekStart: string;
    weekEnd: string;
    activities: number;
    completed: number;
    minutes: number;
}

interface SubjectProgress {
    id: number;
    name: string;
    totalActivities: number;
    completed: number;
    minutes: number;
    completionRate: number;
}

interface Streak {
    current: number;
    longest: number;
    lastActivityDate: string | null;
}

interface ActivityByType {
    type: string;
    label: string;
    count: number;
    minutes: number;
}

interface Props {
    stats: Stats;
    weeklyActivity: WeeklyActivity[];
    progressBySubject: SubjectProgress[];
    recentCompleted: LearningActivity[];
    streak: Streak;
    activityByType: ActivityByType[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard Siswa', href: '/dashboard' },
    { title: 'Kemajuan Belajar', href: '/progress' },
];

const formatDuration = (minutes: number): string => {
    if (minutes < 60) {
        return `${minutes} menit`;
    }
    const hours = Math.floor(minutes / 60);
    const remainingMinutes = minutes % 60;
    return remainingMinutes > 0 ? `${hours} jam ${remainingMinutes} menit` : `${hours} jam`;
};

const formatDate = (dateString: string): string => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
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

const getMaxWeeklyActivities = (): number => {
    return Math.max(...props.weeklyActivity.map(w => w.activities), 1);
};

const getTypeColor = (type: string): string => {
    const colors: Record<string, string> = {
        video: 'bg-blue-500',
        document: 'bg-green-500',
        infographic: 'bg-purple-500',
        audio: 'bg-yellow-500',
        simulation: 'bg-orange-500',
    };
    return colors[type] || 'bg-gray-500';
};
</script>

<template>
    <Head title="Kemajuan Belajar" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <!-- Header -->
            <div class="flex flex-col gap-2">
                <h1 class="text-2xl font-bold tracking-tight">Kemajuan Belajar</h1>
                <p class="text-muted-foreground">
                    Pantau perkembangan dan statistik belajarmu
                </p>
            </div>

            <!-- Stats Grid -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <!-- Total Learning Time -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium">Total Waktu Belajar</CardTitle>
                        <Clock class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ formatDuration(stats.totalMinutes) }}</div>
                        <p class="text-xs text-muted-foreground">
                            {{ stats.totalHours }} jam total
                        </p>
                    </CardContent>
                </Card>

                <!-- Materials Studied -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium">Materi Dipelajari</CardTitle>
                        <BookOpen class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.uniqueMaterials }}</div>
                        <p class="text-xs text-muted-foreground">
                            {{ stats.totalActivities }} aktivitas total
                        </p>
                    </CardContent>
                </Card>

                <!-- Completion Rate -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium">Tingkat Penyelesaian</CardTitle>
                        <Target class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.completionRate }}%</div>
                        <Progress :model-value="stats.completionRate" class="mt-2 h-2" />
                        <p class="mt-1 text-xs text-muted-foreground">
                            {{ stats.completedActivities }} dari {{ stats.totalActivities }} selesai
                        </p>
                    </CardContent>
                </Card>

                <!-- Learning Streak -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium">Streak Belajar</CardTitle>
                        <Flame class="h-4 w-4 text-orange-500" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ streak.current }} hari</div>
                        <p class="text-xs text-muted-foreground">
                            Terbaik: {{ streak.longest }} hari
                        </p>
                    </CardContent>
                </Card>
            </div>

            <!-- Charts Row -->
            <div class="grid gap-6 lg:grid-cols-2">
                <!-- Weekly Activity Chart -->
                <Card>
                    <CardHeader>
                        <div class="flex items-center gap-2">
                            <TrendingUp class="h-5 w-5" />
                            <CardTitle>Aktivitas Mingguan</CardTitle>
                        </div>
                        <CardDescription>
                            Aktivitas belajar dalam 4 minggu terakhir
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div
                                v-for="week in weeklyActivity"
                                :key="week.week"
                                class="flex items-center gap-4"
                            >
                                <div class="w-24 flex-shrink-0 text-sm text-muted-foreground">
                                    {{ week.week }}
                                </div>
                                <div class="flex-1">
                                    <div class="flex h-6 rounded-full bg-muted overflow-hidden">
                                        <div
                                            class="bg-primary transition-all duration-500"
                                            :style="{ width: `${(week.activities / getMaxWeeklyActivities()) * 100}%` }"
                                        />
                                    </div>
                                </div>
                                <div class="w-20 flex-shrink-0 text-right text-sm">
                                    {{ week.activities }} aktivitas
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 flex justify-between text-xs text-muted-foreground">
                            <span>Total aktivitas: {{ weeklyActivity.reduce((sum, w) => sum + w.activities, 0) }}</span>
                            <span>Total waktu: {{ formatDuration(weeklyActivity.reduce((sum, w) => sum + w.minutes, 0)) }}</span>
                        </div>
                    </CardContent>
                </Card>

                <!-- Activity by Type -->
                <Card>
                    <CardHeader>
                        <div class="flex items-center gap-2">
                            <BookOpen class="h-5 w-5" />
                            <CardTitle>Berdasarkan Tipe Materi</CardTitle>
                        </div>
                        <CardDescription>
                            Distribusi aktivitas berdasarkan jenis materi
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div v-if="activityByType.length > 0" class="space-y-4">
                            <div
                                v-for="item in activityByType"
                                :key="item.type"
                                class="flex items-center gap-4"
                            >
                                <Badge :class="getTypeColor(item.type)" class="w-24 justify-center">
                                    {{ item.label }}
                                </Badge>
                                <div class="flex-1 text-sm">
                                    {{ item.count }} aktivitas
                                </div>
                                <div class="text-sm text-muted-foreground">
                                    {{ formatDuration(item.minutes) }}
                                </div>
                            </div>
                        </div>
                        <div v-else class="flex flex-col items-center justify-center py-8 text-center">
                            <BookOpen class="h-12 w-12 text-muted-foreground/50" />
                            <p class="mt-2 text-sm text-muted-foreground">
                                Belum ada data aktivitas
                            </p>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Progress by Subject -->
            <Card>
                <CardHeader>
                    <div class="flex items-center gap-2">
                        <Target class="h-5 w-5" />
                        <CardTitle>Kemajuan per Mata Pelajaran</CardTitle>
                    </div>
                    <CardDescription>
                        Progress belajar untuk setiap mata pelajaran
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div v-if="progressBySubject.length > 0" class="space-y-6">
                        <div
                            v-for="subject in progressBySubject"
                            :key="subject.id"
                            class="space-y-2"
                        >
                            <div class="flex items-center justify-between">
                                <span class="font-medium">{{ subject.name }}</span>
                                <span class="text-sm text-muted-foreground">
                                    {{ subject.completed }}/{{ subject.totalActivities }} selesai
                                </span>
                            </div>
                            <Progress :model-value="subject.completionRate" class="h-3" />
                            <div class="flex justify-between text-xs text-muted-foreground">
                                <span>{{ subject.completionRate }}% selesai</span>
                                <span>{{ formatDuration(subject.minutes) }} belajar</span>
                            </div>
                        </div>
                    </div>
                    <div v-else class="flex flex-col items-center justify-center py-8 text-center">
                        <Target class="h-12 w-12 text-muted-foreground/50" />
                        <p class="mt-2 text-sm text-muted-foreground">
                            Belum ada data progress per mata pelajaran
                        </p>
                    </div>
                </CardContent>
            </Card>

            <!-- Recently Completed -->
            <Card>
                <CardHeader>
                    <div class="flex items-center gap-2">
                        <CheckCircle class="h-5 w-5 text-green-500" />
                        <CardTitle>Materi yang Diselesaikan</CardTitle>
                    </div>
                    <CardDescription>
                        Daftar materi yang sudah kamu selesaikan
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div v-if="recentCompleted.length > 0" class="space-y-4">
                        <div
                            v-for="activity in recentCompleted"
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
                                    <span v-if="activity.material?.subject">
                                        {{ activity.material.subject.name }}
                                    </span>
                                </div>
                            </div>
                            <div class="text-right text-sm text-muted-foreground">
                                <div>{{ formatDuration(Math.round(activity.duration_seconds / 60)) }}</div>
                                <div class="text-xs">{{ formatDate(activity.completed_at as string) }}</div>
                            </div>
                        </div>
                    </div>
                    <div v-else class="flex flex-col items-center justify-center py-8 text-center">
                        <CheckCircle class="h-12 w-12 text-muted-foreground/50" />
                        <p class="mt-2 text-sm text-muted-foreground">
                            Belum ada materi yang diselesaikan
                        </p>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
