<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { type BreadcrumbItem, type User, type LearningMaterial } from '@/types';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { index as usersIndex } from '@/actions/App/Http/Controllers/Admin/UserController';
import { index as classesIndex } from '@/actions/App/Http/Controllers/Admin/ClassController';
import { index as subjectsIndex } from '@/actions/App/Http/Controllers/Admin/SubjectController';
import {
    Users,
    GraduationCap,
    BookOpen,
    School,
    Brain,
    Activity,
    TrendingUp,
    Clock,
    MessageSquare,
    BarChart3,
    UserPlus,
} from 'lucide-vue-next';

interface UserStats {
    totalUsers: number;
    totalStudents: number;
    totalTeachers: number;
    totalAdmins: number;
    newUsersThisMonth: number;
}

interface ClassStats {
    totalClasses: number;
    activeClasses: number;
    totalSubjects: number;
}

interface LearningStats {
    totalMaterials: number;
    activeMaterials: number;
    totalActivities: number;
    completedActivities: number;
    totalLearningHours: number;
}

interface QuestionnaireStats {
    completed: number;
    pending: number;
}

interface FeedbackStats {
    totalFeedback: number;
    thisMonth: number;
}

interface TopMaterial extends LearningMaterial {
    activities_count: number;
}

interface RecentUser extends User {
    created_at: string;
}

interface Props {
    userStats: UserStats;
    classStats: ClassStats;
    learningStats: LearningStats;
    learningStyleDistribution: Record<string, number>;
    questionnaireStats: QuestionnaireStats;
    activityTrend: Record<string, number>;
    topMaterials: TopMaterial[];
    recentUsers: RecentUser[];
    feedbackStats: FeedbackStats;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin Dashboard', href: '/admin/dashboard' },
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

const getRoleLabel = (role: string): string => {
    const labels: Record<string, string> = {
        student: 'Siswa',
        teacher: 'Guru',
        admin: 'Admin',
    };
    return labels[role] || role;
};

const getRoleBadgeVariant = (role: string): 'default' | 'secondary' | 'outline' => {
    if (role === 'admin') return 'default';
    if (role === 'teacher') return 'secondary';
    return 'outline';
};

const formatDate = (dateString: string): string => {
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
};

const totalStyleCount = Object.values(props.learningStyleDistribution).reduce((a, b) => a + b, 0);

const completionRate = props.learningStats.totalActivities > 0
    ? Math.round((props.learningStats.completedActivities / props.learningStats.totalActivities) * 100)
    : 0;
</script>

<template>
    <Head title="Admin Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <!-- Header -->
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Admin Dashboard</h1>
                    <p class="text-muted-foreground">
                        Pantau dan kelola seluruh aktivitas sekolah
                    </p>
                </div>
                <div class="flex gap-2">
                    <Link :href="usersIndex().url + '?role=student'">
                        <Button variant="outline">
                            <Users class="mr-2 h-4 w-4" />
                            Kelola Pengguna
                        </Button>
                    </Link>
                </div>
            </div>

            <!-- User Stats Cards -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-5">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium">Total Pengguna</CardTitle>
                        <Users class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ userStats.totalUsers }}</div>
                        <p class="text-xs text-muted-foreground">
                            +{{ userStats.newUsersThisMonth }} bulan ini
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium">Siswa</CardTitle>
                        <GraduationCap class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ userStats.totalStudents }}</div>
                        <p class="text-xs text-muted-foreground">siswa terdaftar</p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium">Guru</CardTitle>
                        <Users class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ userStats.totalTeachers }}</div>
                        <p class="text-xs text-muted-foreground">guru terdaftar</p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium">Kelas Aktif</CardTitle>
                        <School class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ classStats.activeClasses }}</div>
                        <p class="text-xs text-muted-foreground">
                            dari {{ classStats.totalClasses }} kelas
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium">Mata Pelajaran</CardTitle>
                        <BookOpen class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ classStats.totalSubjects }}</div>
                        <p class="text-xs text-muted-foreground">mata pelajaran</p>
                    </CardContent>
                </Card>
            </div>

            <!-- Learning Stats Cards -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium">Total Materi</CardTitle>
                        <BookOpen class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ learningStats.totalMaterials }}</div>
                        <p class="text-xs text-muted-foreground">
                            {{ learningStats.activeMaterials }} aktif
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium">Total Aktivitas</CardTitle>
                        <Activity class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ learningStats.totalActivities }}</div>
                        <p class="text-xs text-muted-foreground">
                            {{ learningStats.completedActivities }} selesai
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium">Tingkat Selesai</CardTitle>
                        <TrendingUp class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ completionRate }}%</div>
                        <p class="text-xs text-muted-foreground">materi diselesaikan</p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium">Jam Belajar</CardTitle>
                        <Clock class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ learningStats.totalLearningHours }}</div>
                        <p class="text-xs text-muted-foreground">total jam belajar</p>
                    </CardContent>
                </Card>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Questionnaire Stats -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="text-lg flex items-center gap-2">
                                <Brain class="h-5 w-5" />
                                Status Kuesioner Gaya Belajar
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="grid gap-4 md:grid-cols-2">
                                <div class="rounded-lg border p-4 text-center">
                                    <div class="text-3xl font-bold text-primary">
                                        {{ questionnaireStats.completed }}
                                    </div>
                                    <p class="text-sm text-muted-foreground mt-1">
                                        Siswa telah mengisi
                                    </p>
                                </div>
                                <div class="rounded-lg border p-4 text-center">
                                    <div class="text-3xl font-bold text-orange-500">
                                        {{ questionnaireStats.pending }}
                                    </div>
                                    <p class="text-sm text-muted-foreground mt-1">
                                        Siswa belum mengisi
                                    </p>
                                </div>
                            </div>
                            <div class="mt-4">
                                <div class="flex justify-between text-sm mb-2">
                                    <span>Progress Pengisian</span>
                                    <span class="font-medium">
                                        {{ Math.round((questionnaireStats.completed / (questionnaireStats.completed + questionnaireStats.pending)) * 100) || 0 }}%
                                    </span>
                                </div>
                                <div class="h-3 rounded-full bg-muted overflow-hidden">
                                    <div
                                        class="h-full bg-primary transition-all"
                                        :style="{ width: `${(questionnaireStats.completed / (questionnaireStats.completed + questionnaireStats.pending)) * 100 || 0}%` }"
                                    />
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Top Materials -->
                    <Card>
                        <CardHeader>
                            <div class="flex items-center justify-between">
                                <CardTitle class="text-lg">Materi Terpopuler</CardTitle>
                            </div>
                            <CardDescription>Berdasarkan jumlah akses siswa</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div v-if="topMaterials.length > 0" class="space-y-3">
                                <div
                                    v-for="(material, index) in topMaterials"
                                    :key="material.id"
                                    class="flex items-center justify-between rounded-lg border p-3"
                                >
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-primary/10 text-primary font-semibold">
                                            {{ index + 1 }}
                                        </div>
                                        <div>
                                            <p class="font-medium">{{ material.title }}</p>
                                            <Badge variant="outline" class="text-xs mt-1">
                                                {{ getTypeLabel(material.type) }}
                                            </Badge>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="font-semibold">{{ material.activities_count }}</div>
                                        <p class="text-xs text-muted-foreground">akses</p>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="text-center py-8 text-muted-foreground">
                                <BookOpen class="mx-auto h-12 w-12 opacity-50" />
                                <p class="mt-2">Belum ada data materi</p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Recent Users -->
                    <Card>
                        <CardHeader>
                            <div class="flex items-center justify-between">
                                <CardTitle class="text-lg flex items-center gap-2">
                                    <UserPlus class="h-5 w-5" />
                                    Pengguna Terbaru
                                </CardTitle>
                                <Link :href="usersIndex().url">
                                    <Button variant="ghost" size="sm">Lihat Semua</Button>
                                </Link>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <div v-if="recentUsers.length > 0" class="space-y-3">
                                <div
                                    v-for="user in recentUsers"
                                    :key="user.id"
                                    class="flex items-center justify-between rounded-lg border p-3"
                                >
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-primary/10 text-primary font-semibold">
                                            {{ user.name.charAt(0).toUpperCase() }}
                                        </div>
                                        <div>
                                            <p class="font-medium">{{ user.name }}</p>
                                            <p class="text-sm text-muted-foreground">{{ user.email }}</p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <Badge :variant="getRoleBadgeVariant(user.role)">
                                            {{ getRoleLabel(user.role) }}
                                        </Badge>
                                        <p class="text-xs text-muted-foreground mt-1">
                                            {{ formatDate(user.created_at) }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="text-center py-8 text-muted-foreground">
                                <Users class="mx-auto h-12 w-12 opacity-50" />
                                <p class="mt-2">Belum ada pengguna</p>
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
                            <CardDescription>Seluruh siswa yang telah mengisi</CardDescription>
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
                        </CardContent>
                    </Card>

                    <!-- AI Feedback Stats -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="text-lg flex items-center gap-2">
                                <MessageSquare class="h-5 w-5" />
                                Feedback AI
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-4">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-muted-foreground">Total Feedback</span>
                                    <span class="text-2xl font-bold">{{ feedbackStats.totalFeedback }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-muted-foreground">Bulan Ini</span>
                                    <span class="text-lg font-semibold text-primary">
                                        +{{ feedbackStats.thisMonth }}
                                    </span>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Quick Links -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="text-lg">Menu Cepat</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-2">
                            <Link :href="usersIndex().url" class="block">
                                <Button variant="outline" class="w-full justify-start">
                                    <Users class="mr-2 h-4 w-4" />
                                    Kelola Pengguna
                                </Button>
                            </Link>
                            <Link :href="classesIndex().url" class="block">
                                <Button variant="outline" class="w-full justify-start">
                                    <School class="mr-2 h-4 w-4" />
                                    Kelola Kelas
                                </Button>
                            </Link>
                            <Link :href="subjectsIndex().url" class="block">
                                <Button variant="outline" class="w-full justify-start">
                                    <BookOpen class="mr-2 h-4 w-4" />
                                    Kelola Mata Pelajaran
                                </Button>
                            </Link>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
