<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { Head } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { index, enroll } from '@/actions/App/Http/Controllers/Student/SubjectEnrollmentController';
import { ArrowLeft, BookOpen, FileText } from 'lucide-vue-next';

interface AvailableSubject {
    id: number;
    name: string;
    code: string;
    description?: string;
    materials_count: number;
    topic_count: number;
}

interface Props {
    availableSubjects: AvailableSubject[];
}

defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard Siswa', href: '/dashboard' },
    { title: 'Mata Pelajaran', href: index().url },
    { title: 'Pilihan Tersedia', href: '#' },
];

const handleEnroll = (subjectId: number) => {
    router.post(enroll.url(subjectId));
};
</script>

<template>
    <Head title="Mata Pelajaran Tersedia" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <div class="flex items-center gap-4">
                <Link :href="index().url">
                    <Button variant="ghost" size="icon">
                        <ArrowLeft class="h-5 w-5" />
                    </Button>
                </Link>
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Mata Pelajaran Pilihan</h1>
                    <p class="text-muted-foreground">Pilih mata pelajaran tambahan yang ingin kamu pelajari</p>
                </div>
            </div>

            <div v-if="availableSubjects && availableSubjects.length > 0" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <Card
                    v-for="subject in availableSubjects"
                    :key="subject.id"
                    class="transition-all hover:shadow-md"
                >
                    <CardHeader class="pb-2">
                        <div class="flex items-start justify-between">
                            <div>
                                <CardTitle class="text-lg">{{ subject.name }}</CardTitle>
                                <p class="text-sm text-muted-foreground">{{ subject.code }}</p>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent class="pb-2">
                        <p v-if="subject.description" class="text-sm text-muted-foreground line-clamp-2 mb-3">
                            {{ subject.description }}
                        </p>
                        <div class="flex items-center gap-4 text-sm text-muted-foreground">
                            <div class="flex items-center gap-1">
                                <BookOpen class="h-4 w-4" />
                                <span>{{ subject.topic_count }} topik</span>
                            </div>
                            <div class="flex items-center gap-1">
                                <FileText class="h-4 w-4" />
                                <span>{{ subject.materials_count }} materi</span>
                            </div>
                        </div>
                    </CardContent>
                    <CardFooter>
                        <Button class="w-full" @click="handleEnroll(subject.id)">
                            Daftar
                        </Button>
                    </CardFooter>
                </Card>
            </div>

            <Card v-else class="p-8">
                <CardContent class="flex flex-col items-center justify-center text-center">
                    <BookOpen class="h-12 w-12 text-muted-foreground/50" />
                    <CardTitle class="mt-4">Tidak ada mata pelajaran tersedia</CardTitle>
                    <CardDescription class="mt-2">
                        Semua mata pelajaran sudah kamu ikuti atau tidak ada mata pelajaran pilihan lainnya.
                    </CardDescription>
                    <Link :href="index().url">
                        <Button variant="outline" class="mt-4">
                            <ArrowLeft class="mr-2 h-4 w-4" />
                            Kembali
                        </Button>
                    </Link>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
