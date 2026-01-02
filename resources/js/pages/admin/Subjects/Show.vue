<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { type BreadcrumbItem, type User, type LearningMaterial, type ClassRoom } from '@/types';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { index as subjectsIndex, edit as subjectsEdit } from '@/actions/App/Http/Controllers/Admin/SubjectController';
import {
    ArrowLeft,
    Pencil,
    BookOpen,
    School,
    FileText,
    Users,
} from 'lucide-vue-next';

interface MaterialWithTeacher extends LearningMaterial {
    teacher?: User;
    activities_count: number;
    is_active: boolean;
    created_at: string;
}

interface ClassSubject {
    class_room: ClassRoom;
    teacher?: User;
}

interface Subject {
    id: number;
    name: string;
    code: string;
    description?: string;
    learning_materials_count: number;
    class_subjects_count: number;
}

interface Stats {
    totalMaterials: number;
    activeMaterials: number;
    totalClasses: number;
    totalTeachers: number;
}

interface Props {
    subject: Subject;
    materials: MaterialWithTeacher[];
    classes: ClassSubject[];
    stats: Stats;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin Dashboard', href: '/admin/dashboard' },
    { title: 'Mata Pelajaran', href: '/admin/subjects' },
    { title: props.subject.name, href: `/admin/subjects/${props.subject.id}` },
];

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
        year: 'numeric',
    });
};
</script>

<template>
    <Head :title="`Detail Mata Pelajaran - ${subject.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <!-- Back Button & Actions -->
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <Link :href="subjectsIndex().url">
                    <Button variant="ghost" size="sm">
                        <ArrowLeft class="mr-2 h-4 w-4" />
                        Kembali
                    </Button>
                </Link>
                <Link :href="subjectsEdit(subject.id).url">
                    <Button>
                        <Pencil class="mr-2 h-4 w-4" />
                        Edit Mata Pelajaran
                    </Button>
                </Link>
            </div>

            <!-- Subject Header -->
            <Card>
                <CardContent class="pt-6">
                    <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                        <div>
                            <div class="flex items-center gap-3">
                                <h1 class="text-2xl font-bold">{{ subject.name }}</h1>
                                <Badge variant="outline">{{ subject.code }}</Badge>
                            </div>
                            <p v-if="subject.description" class="mt-2 text-muted-foreground max-w-2xl">
                                {{ subject.description }}
                            </p>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Stats -->
            <div class="grid gap-4 md:grid-cols-4">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium">Total Materi</CardTitle>
                        <FileText class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.totalMaterials }}</div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium">Materi Aktif</CardTitle>
                        <BookOpen class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.activeMaterials }}</div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium">Kelas</CardTitle>
                        <School class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.totalClasses }}</div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium">Guru Pengajar</CardTitle>
                        <Users class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.totalTeachers }}</div>
                    </CardContent>
                </Card>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <!-- Materials -->
                <Card>
                    <CardHeader>
                        <CardTitle class="text-lg flex items-center gap-2">
                            <BookOpen class="h-5 w-5" />
                            Materi Pembelajaran
                        </CardTitle>
                        <CardDescription>
                            {{ materials.length }} materi terbaru
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div v-if="materials.length > 0" class="space-y-3">
                            <div
                                v-for="material in materials"
                                :key="material.id"
                                class="flex items-center justify-between rounded-lg border p-3"
                            >
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium truncate">{{ material.title }}</p>
                                    <div class="flex items-center gap-2 mt-1">
                                        <Badge variant="outline" class="text-xs">
                                            {{ getTypeLabel(material.type) }}
                                        </Badge>
                                        <span v-if="material.teacher" class="text-xs text-muted-foreground">
                                            oleh {{ material.teacher.name }}
                                        </span>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <Badge :variant="material.is_active ? 'default' : 'secondary'">
                                        {{ material.is_active ? 'Aktif' : 'Nonaktif' }}
                                    </Badge>
                                    <p class="text-xs text-muted-foreground mt-1">
                                        {{ material.activities_count }} akses
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center py-8 text-muted-foreground">
                            <BookOpen class="mx-auto h-12 w-12 opacity-50" />
                            <p class="mt-2">Belum ada materi</p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Classes -->
                <Card>
                    <CardHeader>
                        <CardTitle class="text-lg flex items-center gap-2">
                            <School class="h-5 w-5" />
                            Kelas yang Menggunakan
                        </CardTitle>
                        <CardDescription>
                            {{ classes.length }} kelas terdaftar
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div v-if="classes.length > 0" class="space-y-3">
                            <div
                                v-for="classSubject in classes"
                                :key="classSubject.class_room?.id"
                                class="flex items-center justify-between rounded-lg border p-3"
                            >
                                <div>
                                    <p class="font-medium">{{ classSubject.class_room?.name }}</p>
                                    <p class="text-sm text-muted-foreground">
                                        {{ classSubject.class_room?.grade_level }} - {{ classSubject.class_room?.academic_year }}
                                    </p>
                                </div>
                                <div v-if="classSubject.teacher" class="text-right text-sm">
                                    <p class="text-muted-foreground">Pengajar:</p>
                                    <p class="font-medium">{{ classSubject.teacher.name }}</p>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center py-8 text-muted-foreground">
                            <School class="mx-auto h-12 w-12 opacity-50" />
                            <p class="mt-2">Belum ada kelas</p>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
