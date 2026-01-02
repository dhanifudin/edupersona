<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';
import { Card, CardContent } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    show as subjectsShow,
    create as subjectsCreate,
    destroy as subjectsDestroy,
} from '@/actions/App/Http/Controllers/Admin/SubjectController';
import { BookOpen, Search, Plus, Eye, Pencil, Trash2, FileText, School } from 'lucide-vue-next';
import { ref, watch } from 'vue';
import { useDebounceFn } from '@vueuse/core';

interface Subject {
    id: number;
    name: string;
    code: string;
    description?: string;
    learning_materials_count: number;
    class_subjects_count: number;
}

interface PaginatedSubjects {
    data: Subject[];
    links: { url: string | null; label: string; active: boolean }[];
    current_page: number;
    last_page: number;
    total: number;
}

interface Filters {
    search: string | null;
}

interface Props {
    subjects: PaginatedSubjects;
    filters: Filters;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin Dashboard', href: '/admin/dashboard' },
    { title: 'Mata Pelajaran', href: '/admin/subjects' },
];

const searchQuery = ref(props.filters.search || '');

const applyFilters = () => {
    router.get('/admin/subjects', {
        search: searchQuery.value || undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const debouncedSearch = useDebounceFn(() => {
    applyFilters();
}, 300);

watch(searchQuery, () => {
    debouncedSearch();
});

const clearFilters = () => {
    searchQuery.value = '';
    router.get('/admin/subjects');
};

const deleteSubject = (subject: Subject) => {
    if (confirm(`Apakah Anda yakin ingin menghapus mata pelajaran "${subject.name}"?`)) {
        router.delete(subjectsDestroy(subject.id).url);
    }
};
</script>

<template>
    <Head title="Kelola Mata Pelajaran" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <!-- Header -->
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Kelola Mata Pelajaran</h1>
                    <p class="text-muted-foreground">
                        {{ subjects.total }} mata pelajaran terdaftar
                    </p>
                </div>
                <Link :href="subjectsCreate().url">
                    <Button>
                        <Plus class="mr-2 h-4 w-4" />
                        Tambah Mata Pelajaran
                    </Button>
                </Link>
            </div>

            <!-- Filters -->
            <Card>
                <CardContent class="p-4">
                    <div class="flex flex-col gap-4 md:flex-row md:items-center">
                        <div class="relative flex-1">
                            <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                            <Input
                                v-model="searchQuery"
                                placeholder="Cari nama, kode, atau deskripsi..."
                                class="pl-9"
                            />
                        </div>
                        <Button
                            v-if="searchQuery"
                            variant="ghost"
                            size="sm"
                            @click="clearFilters"
                        >
                            Reset
                        </Button>
                    </div>
                </CardContent>
            </Card>

            <!-- Subjects Table -->
            <Card>
                <CardContent class="p-0">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b bg-muted/50">
                                    <th class="px-4 py-3 text-left text-sm font-medium">Nama</th>
                                    <th class="px-4 py-3 text-left text-sm font-medium">Kode</th>
                                    <th class="px-4 py-3 text-left text-sm font-medium">Deskripsi</th>
                                    <th class="px-4 py-3 text-center text-sm font-medium">Materi</th>
                                    <th class="px-4 py-3 text-center text-sm font-medium">Kelas</th>
                                    <th class="px-4 py-3 text-right text-sm font-medium">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="subject in subjects.data"
                                    :key="subject.id"
                                    class="border-b hover:bg-muted/50 transition-colors"
                                >
                                    <td class="px-4 py-3">
                                        <p class="font-medium">{{ subject.name }}</p>
                                    </td>
                                    <td class="px-4 py-3">
                                        <Badge variant="outline">{{ subject.code }}</Badge>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-muted-foreground max-w-xs truncate">
                                        {{ subject.description || '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <div class="flex items-center justify-center gap-1">
                                            <FileText class="h-4 w-4 text-muted-foreground" />
                                            {{ subject.learning_materials_count }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <div class="flex items-center justify-center gap-1">
                                            <School class="h-4 w-4 text-muted-foreground" />
                                            {{ subject.class_subjects_count }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex justify-end gap-2">
                                            <Link :href="subjectsShow(subject.id).url">
                                                <Button variant="ghost" size="sm">
                                                    <Eye class="h-4 w-4" />
                                                </Button>
                                            </Link>
                                            <Link :href="`/admin/subjects/${subject.id}/edit`">
                                                <Button variant="ghost" size="sm">
                                                    <Pencil class="h-4 w-4" />
                                                </Button>
                                            </Link>
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                class="text-destructive hover:text-destructive"
                                                @click="deleteSubject(subject)"
                                            >
                                                <Trash2 class="h-4 w-4" />
                                            </Button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Empty State -->
                    <div v-if="subjects.data.length === 0" class="flex flex-col items-center justify-center py-12">
                        <BookOpen class="h-12 w-12 text-muted-foreground/50" />
                        <h3 class="mt-4 text-lg font-semibold">Tidak Ada Mata Pelajaran</h3>
                        <p class="mt-2 text-center text-sm text-muted-foreground max-w-md">
                            <template v-if="searchQuery">
                                Tidak ada mata pelajaran yang cocok dengan pencarian.
                            </template>
                            <template v-else>
                                Belum ada mata pelajaran terdaftar.
                            </template>
                        </p>
                        <div class="mt-4 flex gap-2">
                            <Button
                                v-if="searchQuery"
                                variant="outline"
                                @click="clearFilters"
                            >
                                Reset Filter
                            </Button>
                            <Link :href="subjectsCreate().url">
                                <Button>Tambah Mata Pelajaran</Button>
                            </Link>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Pagination -->
            <div v-if="subjects.last_page > 1" class="flex justify-center gap-2">
                <template v-for="link in subjects.links" :key="link.label">
                    <Link
                        v-if="link.url"
                        :href="link.url"
                        :class="[
                            'px-3 py-2 text-sm rounded-md transition-colors',
                            link.active
                                ? 'bg-primary text-primary-foreground'
                                : 'bg-muted hover:bg-accent',
                        ]"
                        v-html="link.label"
                    />
                    <span
                        v-else
                        class="px-3 py-2 text-sm text-muted-foreground"
                        v-html="link.label"
                    />
                </template>
            </div>
        </div>
    </AppLayout>
</template>
