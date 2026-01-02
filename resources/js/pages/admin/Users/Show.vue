<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { type BreadcrumbItem, type User, type LearningStyleProfile, type ClassRoom, type LearningMaterial, type LearningActivity } from '@/types';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { index as usersIndex, edit as usersEdit } from '@/actions/App/Http/Controllers/Admin/UserController';
import {
    ArrowLeft,
    Pencil,
    Mail,
    Phone,
    GraduationCap,
    BookOpen,
    CheckCircle,
    Clock,
    Brain,
    Users,
} from 'lucide-vue-next';

interface ClassSubject {
    class_room: ClassRoom;
    subject: { id: number; name: string; code: string };
}

interface StudentAdditionalData {
    activitiesCount: number;
    completedCount: number;
    totalLearningMinutes: number;
    recentActivities: (LearningActivity & { material: LearningMaterial })[];
}

interface TeacherAdditionalData {
    materialsCount: number;
    activeMaterialsCount: number;
    teachingClasses: ClassSubject[];
    recentMaterials: LearningMaterial[];
}

interface UserWithRelations extends User {
    learning_style_profile?: LearningStyleProfile;
    classes?: ClassRoom[];
    homeroom_classes?: ClassRoom[];
}

interface Props {
    user: UserWithRelations;
    additionalData: StudentAdditionalData | TeacherAdditionalData | Record<string, unknown>;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin Dashboard', href: '/admin/dashboard' },
    { title: 'Pengguna', href: '/admin/users' },
    { title: props.user.name, href: `/admin/users/${props.user.id}` },
];

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
        month: 'long',
        year: 'numeric',
    });
};

const isStudent = props.user.role === 'student';
const isTeacher = props.user.role === 'teacher';

const studentData = isStudent ? props.additionalData as StudentAdditionalData : null;
const teacherData = isTeacher ? props.additionalData as TeacherAdditionalData : null;
</script>

<template>
    <Head :title="`Detail Pengguna - ${user.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <!-- Back Button & Actions -->
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <Link :href="usersIndex().url">
                    <Button variant="ghost" size="sm">
                        <ArrowLeft class="mr-2 h-4 w-4" />
                        Kembali
                    </Button>
                </Link>
                <Link :href="usersEdit(user.id).url">
                    <Button>
                        <Pencil class="mr-2 h-4 w-4" />
                        Edit Pengguna
                    </Button>
                </Link>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <!-- User Info -->
                <div class="lg:col-span-1 space-y-6">
                    <Card>
                        <CardContent class="pt-6">
                            <div class="flex flex-col items-center text-center">
                                <div class="flex h-20 w-20 items-center justify-center rounded-full bg-primary/10 text-primary text-2xl font-bold">
                                    {{ user.name.charAt(0).toUpperCase() }}
                                </div>
                                <h2 class="mt-4 text-xl font-semibold">{{ user.name }}</h2>
                                <Badge :variant="getRoleBadgeVariant(user.role)" class="mt-2">
                                    {{ getRoleLabel(user.role) }}
                                </Badge>
                            </div>

                            <div class="mt-6 space-y-4">
                                <div class="flex items-center gap-3">
                                    <Mail class="h-4 w-4 text-muted-foreground" />
                                    <span class="text-sm">{{ user.email }}</span>
                                </div>
                                <div v-if="user.phone" class="flex items-center gap-3">
                                    <Phone class="h-4 w-4 text-muted-foreground" />
                                    <span class="text-sm">{{ user.phone }}</span>
                                </div>
                                <div v-if="user.role === 'student' && user.student_id_number" class="flex items-center gap-3">
                                    <GraduationCap class="h-4 w-4 text-muted-foreground" />
                                    <span class="text-sm">NIS: {{ user.student_id_number }}</span>
                                </div>
                                <div v-if="user.role === 'teacher' && user.teacher_id_number" class="flex items-center gap-3">
                                    <Users class="h-4 w-4 text-muted-foreground" />
                                    <span class="text-sm">NIP: {{ user.teacher_id_number }}</span>
                                </div>
                            </div>

                            <div class="mt-6 pt-6 border-t">
                                <p class="text-sm text-muted-foreground">
                                    Terdaftar pada {{ formatDate(user.created_at) }}
                                </p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Learning Style (for students) -->
                    <Card v-if="isStudent && user.learning_style_profile">
                        <CardHeader>
                            <CardTitle class="text-lg flex items-center gap-2">
                                <Brain class="h-5 w-5" />
                                Profil Gaya Belajar
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="text-center mb-4">
                                <Badge :class="getStyleColor(user.learning_style_profile.dominant_style)" class="text-white">
                                    {{ getStyleLabel(user.learning_style_profile.dominant_style) }}
                                </Badge>
                            </div>
                            <div class="space-y-3">
                                <div>
                                    <div class="flex justify-between text-sm mb-1">
                                        <span>Visual</span>
                                        <span>{{ user.learning_style_profile.visual_score }}%</span>
                                    </div>
                                    <div class="h-2 rounded-full bg-muted overflow-hidden">
                                        <div class="h-full bg-blue-500" :style="{ width: `${user.learning_style_profile.visual_score}%` }" />
                                    </div>
                                </div>
                                <div>
                                    <div class="flex justify-between text-sm mb-1">
                                        <span>Auditori</span>
                                        <span>{{ user.learning_style_profile.auditory_score }}%</span>
                                    </div>
                                    <div class="h-2 rounded-full bg-muted overflow-hidden">
                                        <div class="h-full bg-green-500" :style="{ width: `${user.learning_style_profile.auditory_score}%` }" />
                                    </div>
                                </div>
                                <div>
                                    <div class="flex justify-between text-sm mb-1">
                                        <span>Kinestetik</span>
                                        <span>{{ user.learning_style_profile.kinesthetic_score }}%</span>
                                    </div>
                                    <div class="h-2 rounded-full bg-muted overflow-hidden">
                                        <div class="h-full bg-orange-500" :style="{ width: `${user.learning_style_profile.kinesthetic_score}%` }" />
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Classes (for students) -->
                    <Card v-if="isStudent && user.classes && user.classes.length > 0">
                        <CardHeader>
                            <CardTitle class="text-lg">Kelas</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-2">
                                <div
                                    v-for="cls in user.classes"
                                    :key="cls.id"
                                    class="rounded-lg border p-3"
                                >
                                    <p class="font-medium">{{ cls.name }}</p>
                                    <p class="text-sm text-muted-foreground">
                                        {{ cls.grade_level }} - {{ cls.academic_year }}
                                    </p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Student Stats -->
                    <div v-if="isStudent && studentData" class="grid gap-4 md:grid-cols-3">
                        <Card>
                            <CardHeader class="flex flex-row items-center justify-between pb-2">
                                <CardTitle class="text-sm font-medium">Total Aktivitas</CardTitle>
                                <BookOpen class="h-4 w-4 text-muted-foreground" />
                            </CardHeader>
                            <CardContent>
                                <div class="text-2xl font-bold">{{ studentData.activitiesCount }}</div>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader class="flex flex-row items-center justify-between pb-2">
                                <CardTitle class="text-sm font-medium">Materi Selesai</CardTitle>
                                <CheckCircle class="h-4 w-4 text-muted-foreground" />
                            </CardHeader>
                            <CardContent>
                                <div class="text-2xl font-bold">{{ studentData.completedCount }}</div>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader class="flex flex-row items-center justify-between pb-2">
                                <CardTitle class="text-sm font-medium">Waktu Belajar</CardTitle>
                                <Clock class="h-4 w-4 text-muted-foreground" />
                            </CardHeader>
                            <CardContent>
                                <div class="text-2xl font-bold">{{ studentData.totalLearningMinutes }}</div>
                                <p class="text-xs text-muted-foreground">menit</p>
                            </CardContent>
                        </Card>
                    </div>

                    <!-- Teacher Stats -->
                    <div v-if="isTeacher && teacherData" class="grid gap-4 md:grid-cols-2">
                        <Card>
                            <CardHeader class="flex flex-row items-center justify-between pb-2">
                                <CardTitle class="text-sm font-medium">Total Materi</CardTitle>
                                <BookOpen class="h-4 w-4 text-muted-foreground" />
                            </CardHeader>
                            <CardContent>
                                <div class="text-2xl font-bold">{{ teacherData.materialsCount }}</div>
                                <p class="text-xs text-muted-foreground">
                                    {{ teacherData.activeMaterialsCount }} aktif
                                </p>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader class="flex flex-row items-center justify-between pb-2">
                                <CardTitle class="text-sm font-medium">Kelas Diajar</CardTitle>
                                <Users class="h-4 w-4 text-muted-foreground" />
                            </CardHeader>
                            <CardContent>
                                <div class="text-2xl font-bold">{{ teacherData.teachingClasses?.length || 0 }}</div>
                            </CardContent>
                        </Card>
                    </div>

                    <!-- Recent Activities (for students) -->
                    <Card v-if="isStudent && studentData && studentData.recentActivities.length > 0">
                        <CardHeader>
                            <CardTitle class="text-lg">Aktivitas Terbaru</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-3">
                                <div
                                    v-for="activity in studentData.recentActivities"
                                    :key="activity.id"
                                    class="flex items-center justify-between rounded-lg border p-3"
                                >
                                    <div>
                                        <p class="font-medium">{{ activity.material?.title }}</p>
                                        <Badge variant="outline" class="text-xs mt-1">
                                            {{ getTypeLabel(activity.material?.type || '') }}
                                        </Badge>
                                    </div>
                                    <div class="text-right">
                                        <div class="flex items-center gap-1 text-sm">
                                            <Clock class="h-3 w-3" />
                                            {{ Math.round(activity.duration_seconds / 60) }} menit
                                        </div>
                                        <Badge v-if="activity.completed_at" variant="default" class="text-xs mt-1">
                                            Selesai
                                        </Badge>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Teaching Classes (for teachers) -->
                    <Card v-if="isTeacher && teacherData && teacherData.teachingClasses && teacherData.teachingClasses.length > 0">
                        <CardHeader>
                            <CardTitle class="text-lg">Kelas yang Diajar</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-3">
                                <div
                                    v-for="item in teacherData.teachingClasses"
                                    :key="`${item.class_room?.id}-${item.subject?.id}`"
                                    class="flex items-center justify-between rounded-lg border p-3"
                                >
                                    <div>
                                        <p class="font-medium">{{ item.class_room?.name }}</p>
                                        <p class="text-sm text-muted-foreground">
                                            {{ item.class_room?.grade_level }}
                                        </p>
                                    </div>
                                    <Badge variant="secondary">
                                        {{ item.subject?.name }}
                                    </Badge>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Recent Materials (for teachers) -->
                    <Card v-if="isTeacher && teacherData && teacherData.recentMaterials && teacherData.recentMaterials.length > 0">
                        <CardHeader>
                            <CardTitle class="text-lg">Materi Terbaru</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-3">
                                <div
                                    v-for="material in teacherData.recentMaterials"
                                    :key="material.id"
                                    class="flex items-center justify-between rounded-lg border p-3"
                                >
                                    <div>
                                        <p class="font-medium">{{ material.title }}</p>
                                        <Badge variant="outline" class="text-xs mt-1">
                                            {{ getTypeLabel(material.type) }}
                                        </Badge>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Homeroom Classes (for teachers) -->
                    <Card v-if="isTeacher && user.homeroom_classes && user.homeroom_classes.length > 0">
                        <CardHeader>
                            <CardTitle class="text-lg">Wali Kelas</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-3">
                                <div
                                    v-for="cls in user.homeroom_classes"
                                    :key="cls.id"
                                    class="rounded-lg border p-3"
                                >
                                    <p class="font-medium">{{ cls.name }}</p>
                                    <p class="text-sm text-muted-foreground">
                                        {{ cls.grade_level }} - {{ cls.academic_year }}
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
