<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';
import { Head } from '@inertiajs/vue3';
import { type BreadcrumbItem, type ClassRoom, type Subject } from '@/types';
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { available } from '@/actions/App/Http/Controllers/Student/SubjectEnrollmentController';
import SubjectCard from '@/components/student/SubjectCard.vue';
import { BookOpen, Plus } from 'lucide-vue-next';

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

interface Props {
    enrollments: Enrollment[];
    currentClass?: ClassRoom;
}

defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard Siswa', href: '/dashboard' },
    { title: 'Mata Pelajaran', href: '#' },
];
</script>

<template>
    <Head title="Mata Pelajaran Saya" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Mata Pelajaran Saya</h1>
                    <p class="text-muted-foreground">
                        <template v-if="currentClass">
                            Kelas {{ currentClass.name }} - {{ currentClass.academic_year }}
                        </template>
                        <template v-else>
                            Daftar mata pelajaran yang kamu ikuti
                        </template>
                    </p>
                </div>
                <Link :href="available().url">
                    <Button>
                        <Plus class="mr-2 h-4 w-4" />
                        Tambah Pilihan
                    </Button>
                </Link>
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
                <CardContent class="flex flex-col items-center justify-center text-center">
                    <BookOpen class="h-12 w-12 text-muted-foreground/50" />
                    <p class="mt-4 text-muted-foreground">
                        Belum ada mata pelajaran yang terdaftar.
                    </p>
                    <Link :href="available().url">
                        <Button class="mt-4">
                            <Plus class="mr-2 h-4 w-4" />
                            Daftar Mata Pelajaran
                        </Button>
                    </Link>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
