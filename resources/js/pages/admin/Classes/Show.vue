<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { type BreadcrumbItem, type ClassRoom, type User, type LearningStyleProfile } from '@/types';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { index as classesIndex, edit as classesEdit } from '@/actions/App/Http/Controllers/Admin/ClassController';
import {
    ArrowLeft,
    Pencil,
    Users,
    BookOpen,
    Brain,
    GraduationCap,
    Calendar,
    BarChart3,
} from 'lucide-vue-next';

interface StudentWithProfile extends User {
    learning_style_profile?: LearningStyleProfile;
}

interface ClassSubject {
    subject: { id: number; name: string; code: string };
    teacher?: User;
}

interface ClassWithRelations extends ClassRoom {
    homeroom_teacher?: User;
    active_students: StudentWithProfile[];
    class_subjects: ClassSubject[];
    is_active: boolean;
}

interface Stats {
    totalStudents: number;
    totalSubjects: number;
}

interface Props {
    class: ClassWithRelations;
    stats: Stats;
    learningStyleDistribution: Record<string, number>;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin Dashboard', href: '/admin/dashboard' },
    { title: 'Kelas', href: '/admin/classes' },
    { title: props.class.name, href: `/admin/classes/${props.class.id}` },
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

const totalStyleCount = Object.values(props.learningStyleDistribution).reduce((a, b) => a + b, 0);
</script>

<template>
    <Head :title="`Detail Kelas - ${props.class.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <!-- Back Button & Actions -->
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <Link :href="classesIndex().url">
                    <Button variant="ghost" size="sm">
                        <ArrowLeft class="mr-2 h-4 w-4" />
                        Kembali
                    </Button>
                </Link>
                <Link :href="classesEdit(props.class.id).url">
                    <Button>
                        <Pencil class="mr-2 h-4 w-4" />
                        Edit Kelas
                    </Button>
                </Link>
            </div>

            <!-- Class Header -->
            <Card>
                <CardContent class="pt-6">
                    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                        <div>
                            <div class="flex items-center gap-3">
                                <h1 class="text-2xl font-bold">{{ props.class.name }}</h1>
                                <Badge :variant="props.class.is_active ? 'default' : 'secondary'">
                                    {{ props.class.is_active ? 'Aktif' : 'Nonaktif' }}
                                </Badge>
                            </div>
                            <div class="flex flex-wrap gap-4 mt-2 text-sm text-muted-foreground">
                                <div class="flex items-center gap-1">
                                    <GraduationCap class="h-4 w-4" />
                                    {{ props.class.grade_level }}
                                </div>
                                <div v-if="props.class.major" class="flex items-center gap-1">
                                    <BookOpen class="h-4 w-4" />
                                    {{ props.class.major }}
                                </div>
                                <div class="flex items-center gap-1">
                                    <Calendar class="h-4 w-4" />
                                    {{ props.class.academic_year }}
                                </div>
                            </div>
                        </div>
                        <div v-if="props.class.homeroom_teacher" class="text-sm">
                            <p class="text-muted-foreground">Wali Kelas:</p>
                            <p class="font-medium">{{ props.class.homeroom_teacher.name }}</p>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Stats -->
            <div class="grid gap-4 md:grid-cols-2">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium">Total Siswa</CardTitle>
                        <Users class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.totalStudents }}</div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium">Mata Pelajaran</CardTitle>
                        <BookOpen class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.totalSubjects }}</div>
                    </CardContent>
                </Card>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <!-- Students List -->
                <div class="lg:col-span-2 space-y-6">
                    <Card>
                        <CardHeader>
                            <CardTitle class="text-lg flex items-center gap-2">
                                <Users class="h-5 w-5" />
                                Daftar Siswa
                            </CardTitle>
                            <CardDescription>{{ props.class.active_students.length }} siswa aktif</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div v-if="props.class.active_students.length > 0" class="space-y-3">
                                <div
                                    v-for="student in props.class.active_students"
                                    :key="student.id"
                                    class="flex items-center justify-between rounded-lg border p-3"
                                >
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-primary/10 text-primary font-semibold">
                                            {{ student.name.charAt(0).toUpperCase() }}
                                        </div>
                                        <div>
                                            <p class="font-medium">{{ student.name }}</p>
                                            <p class="text-sm text-muted-foreground">
                                                {{ student.student_id_number || student.email }}
                                            </p>
                                        </div>
                                    </div>
                                    <div v-if="student.learning_style_profile">
                                        <Badge
                                            :class="getStyleColor(student.learning_style_profile.dominant_style)"
                                            class="text-white"
                                        >
                                            {{ getStyleLabel(student.learning_style_profile.dominant_style) }}
                                        </Badge>
                                    </div>
                                    <Badge v-else variant="outline">Belum mengisi</Badge>
                                </div>
                            </div>
                            <div v-else class="text-center py-8 text-muted-foreground">
                                <Users class="mx-auto h-12 w-12 opacity-50" />
                                <p class="mt-2">Belum ada siswa di kelas ini</p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Subjects -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="text-lg flex items-center gap-2">
                                <BookOpen class="h-5 w-5" />
                                Mata Pelajaran
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div v-if="props.class.class_subjects.length > 0" class="space-y-3">
                                <div
                                    v-for="classSubject in props.class.class_subjects"
                                    :key="classSubject.subject.id"
                                    class="flex items-center justify-between rounded-lg border p-3"
                                >
                                    <div>
                                        <p class="font-medium">{{ classSubject.subject.name }}</p>
                                        <Badge variant="outline" class="text-xs mt-1">
                                            {{ classSubject.subject.code }}
                                        </Badge>
                                    </div>
                                    <div v-if="classSubject.teacher" class="text-right text-sm">
                                        <p class="text-muted-foreground">Pengajar:</p>
                                        <p class="font-medium">{{ classSubject.teacher.name }}</p>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="text-center py-8 text-muted-foreground">
                                <BookOpen class="mx-auto h-12 w-12 opacity-50" />
                                <p class="mt-2">Belum ada mata pelajaran</p>
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

                    <Card v-else>
                        <CardHeader>
                            <CardTitle class="text-lg flex items-center gap-2">
                                <Brain class="h-5 w-5" />
                                Gaya Belajar
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="text-center py-4 text-muted-foreground">
                                <Brain class="mx-auto h-8 w-8 opacity-50" />
                                <p class="mt-2 text-sm">Belum ada data gaya belajar</p>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
