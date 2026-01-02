<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { type BreadcrumbItem, type LearningMaterial, type LearningActivity, type ClassRoom } from '@/types';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { index as materialsIndex, create as materialsCreate } from '@/actions/App/Http/Controllers/Teacher/MaterialController';
import { index as studentsIndex } from '@/actions/App/Http/Controllers/Teacher/StudentController';
import { Users, BookOpen, Eye, CheckCircle, Clock, Plus, TrendingUp, BarChart3 } from 'lucide-vue-next';

interface TeachingClass {
    class: {
        id: number;
        name: string;
        grade_level: string;
        major: string;
    };
    subjects: { id: number; name: string; code: string }[];
}

interface Stats {
    totalStudents: number;
    totalMaterials: number;
    activeMaterials: number;
    totalViews: number;
    totalCompletions: number;
    completionRate: number;
}

interface Props {
    stats: Stats;
    teachingClasses: TeachingClass[];
    homeroomClasses: ClassRoom[];
    recentMaterials: LearningMaterial[];
    recentActivities: LearningActivity[];
    learningStyleDistribution: Record<string, number>;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/teacher/dashboard' },
];

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

const formatDate = (dateString: string): string => {
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const totalStyleCount = Object.values(props.learningStyleDistribution).reduce((a, b) => a + b, 0);
</script>

<template>
    <Head title="Dashboard Guru" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <!-- Header -->
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Dashboard Guru</h1>
                    <p class="text-muted-foreground">
                        Kelola materi dan pantau perkembangan siswa
                    </p>
                </div>
                <div class="flex gap-2">
                    <Link :href="materialsCreate().url">
                        <Button>
                            <Plus class="mr-2 h-4 w-4" />
                            Tambah Materi
                        </Button>
                    </Link>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium">Total Siswa</CardTitle>
                        <Users class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.totalStudents }}</div>
                        <p class="text-xs text-muted-foreground">di kelas yang diajar</p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium">Total Materi</CardTitle>
                        <BookOpen class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.totalMaterials }}</div>
                        <p class="text-xs text-muted-foreground">
                            {{ stats.activeMaterials }} aktif
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium">Total Dilihat</CardTitle>
                        <Eye class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.totalViews }}</div>
                        <p class="text-xs text-muted-foreground">kali materi diakses</p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium">Tingkat Selesai</CardTitle>
                        <TrendingUp class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.completionRate }}%</div>
                        <p class="text-xs text-muted-foreground">
                            {{ stats.totalCompletions }} materi diselesaikan
                        </p>
                    </CardContent>
                </Card>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Recent Materials -->
                    <Card>
                        <CardHeader>
                            <div class="flex items-center justify-between">
                                <CardTitle class="text-lg">Materi Terbaru</CardTitle>
                                <Link :href="materialsIndex().url">
                                    <Button variant="ghost" size="sm">Lihat Semua</Button>
                                </Link>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <div v-if="recentMaterials.length > 0" class="space-y-3">
                                <div
                                    v-for="material in recentMaterials"
                                    :key="material.id"
                                    class="flex items-center justify-between rounded-lg border p-3"
                                >
                                    <div class="flex-1 min-w-0">
                                        <p class="font-medium truncate">{{ material.title }}</p>
                                        <div class="flex items-center gap-2 mt-1">
                                            <Badge variant="outline" class="text-xs">
                                                {{ getTypeLabel(material.type) }}
                                            </Badge>
                                            <span class="text-xs text-muted-foreground">
                                                {{ material.subject?.name }}
                                            </span>
                                        </div>
                                    </div>
                                    <Badge :variant="material.is_active ? 'default' : 'secondary'">
                                        {{ material.is_active ? 'Aktif' : 'Nonaktif' }}
                                    </Badge>
                                </div>
                            </div>
                            <div v-else class="text-center py-8 text-muted-foreground">
                                <BookOpen class="mx-auto h-12 w-12 opacity-50" />
                                <p class="mt-2">Belum ada materi</p>
                                <Link :href="materialsCreate().url" class="mt-4 inline-block">
                                    <Button size="sm">Tambah Materi Pertama</Button>
                                </Link>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Recent Activities -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="text-lg">Aktivitas Siswa Terbaru</CardTitle>
                            <CardDescription>Siswa yang mengakses materi Anda</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div v-if="recentActivities.length > 0" class="space-y-3">
                                <div
                                    v-for="activity in recentActivities"
                                    :key="activity.id"
                                    class="flex items-center justify-between rounded-lg border p-3"
                                >
                                    <div class="flex-1 min-w-0">
                                        <p class="font-medium">{{ activity.user?.name }}</p>
                                        <p class="text-sm text-muted-foreground truncate">
                                            {{ activity.material?.title }}
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <div class="flex items-center gap-1 text-sm">
                                            <Clock class="h-3 w-3" />
                                            {{ Math.round(activity.duration_seconds / 60) }} menit
                                        </div>
                                        <p class="text-xs text-muted-foreground">
                                            {{ formatDate(activity.started_at) }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="text-center py-8 text-muted-foreground">
                                <Eye class="mx-auto h-12 w-12 opacity-50" />
                                <p class="mt-2">Belum ada aktivitas</p>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Learning Style Distribution -->
                    <Card v-if="totalStyleCount > 0">
                        <CardHeader>
                            <CardTitle class="text-lg flex items-center gap-2">
                                <BarChart3 class="h-5 w-5" />
                                Distribusi Gaya Belajar
                            </CardTitle>
                            <CardDescription>Siswa di kelas wali Anda</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-3">
                                <div
                                    v-for="(count, style) in learningStyleDistribution"
                                    :key="style"
                                    class="space-y-1"
                                >
                                    <div class="flex justify-between text-sm">
                                        <span>{{ getStyleLabel(style) }}</span>
                                        <span class="font-medium">{{ count }} siswa</span>
                                    </div>
                                    <div class="h-2 rounded-full bg-muted overflow-hidden">
                                        <div
                                            :class="getStyleColor(style)"
                                            class="h-full transition-all"
                                            :style="{ width: `${(count / totalStyleCount) * 100}%` }"
                                        />
                                    </div>
                                </div>
                            </div>
                            <Link :href="studentsIndex().url" class="mt-4 inline-block">
                                <Button variant="outline" size="sm" class="w-full">
                                    Lihat Semua Siswa
                                </Button>
                            </Link>
                        </CardContent>
                    </Card>

                    <!-- Teaching Classes -->
                    <Card v-if="teachingClasses.length > 0">
                        <CardHeader>
                            <CardTitle class="text-lg">Kelas yang Diajar</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-3">
                                <div
                                    v-for="item in teachingClasses"
                                    :key="item.class.id"
                                    class="rounded-lg border p-3"
                                >
                                    <p class="font-medium">{{ item.class.name }}</p>
                                    <p class="text-sm text-muted-foreground">
                                        {{ item.class.grade_level }} - {{ item.class.major }}
                                    </p>
                                    <div class="flex flex-wrap gap-1 mt-2">
                                        <Badge
                                            v-for="subject in item.subjects"
                                            :key="subject.id"
                                            variant="secondary"
                                            class="text-xs"
                                        >
                                            {{ subject.name }}
                                        </Badge>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Homeroom Classes -->
                    <Card v-if="homeroomClasses.length > 0">
                        <CardHeader>
                            <CardTitle class="text-lg">Wali Kelas</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-3">
                                <Link
                                    v-for="classRoom in homeroomClasses"
                                    :key="classRoom.id"
                                    :href="studentsIndex().url + '?class=' + classRoom.id"
                                    class="block rounded-lg border p-3 hover:bg-accent transition-colors"
                                >
                                    <p class="font-medium">{{ classRoom.name }}</p>
                                    <p class="text-sm text-muted-foreground">
                                        {{ classRoom.activeStudents?.length || 0 }} siswa aktif
                                    </p>
                                </Link>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
