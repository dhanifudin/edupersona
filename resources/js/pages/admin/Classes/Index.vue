<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { type BreadcrumbItem, type ClassRoom, type User } from '@/types';
import { Card, CardContent } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import {
    show as classesShow,
    create as classesCreate,
    destroy as classesDestroy,
    toggleActive,
} from '@/actions/App/Http/Controllers/Admin/ClassController';
import { School, Search, Plus, Eye, Pencil, Trash2, Users, BookOpen, ToggleLeft, ToggleRight } from 'lucide-vue-next';
import { ref, watch } from 'vue';
import { useDebounceFn } from '@vueuse/core';

interface ClassWithCounts extends ClassRoom {
    homeroom_teacher?: User;
    active_students_count: number;
    class_subjects_count: number;
    is_active: boolean;
}

interface PaginatedClasses {
    data: ClassWithCounts[];
    links: { url: string | null; label: string; active: boolean }[];
    current_page: number;
    last_page: number;
    total: number;
}

interface Filters {
    grade: string | null;
    year: string | null;
    active: string | null;
    search: string | null;
}

interface Props {
    classes: PaginatedClasses;
    academicYears: string[];
    filters: Filters;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin Dashboard', href: '/admin/dashboard' },
    { title: 'Kelas', href: '/admin/classes' },
];

const searchQuery = ref(props.filters.search || '');
const selectedGrade = ref(props.filters.grade || '');
const selectedYear = ref(props.filters.year || '');
const selectedActive = ref(props.filters.active || '');

const grades = [
    { value: '', label: 'Semua Tingkat' },
    { value: 'X', label: 'Kelas X' },
    { value: 'XI', label: 'Kelas XI' },
    { value: 'XII', label: 'Kelas XII' },
];

const activeOptions = [
    { value: '', label: 'Semua Status' },
    { value: '1', label: 'Aktif' },
    { value: '0', label: 'Nonaktif' },
];

const applyFilters = () => {
    router.get('/admin/classes', {
        grade: selectedGrade.value || undefined,
        year: selectedYear.value || undefined,
        active: selectedActive.value || undefined,
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

watch([selectedGrade, selectedYear, selectedActive], () => {
    applyFilters();
});

const clearFilters = () => {
    searchQuery.value = '';
    selectedGrade.value = '';
    selectedYear.value = '';
    selectedActive.value = '';
    router.get('/admin/classes');
};

const deleteClass = (classRoom: ClassWithCounts) => {
    if (confirm(`Apakah Anda yakin ingin menghapus kelas "${classRoom.name}"?`)) {
        router.delete(classesDestroy(classRoom.id).url);
    }
};

const toggleClassActive = (classRoom: ClassWithCounts) => {
    router.patch(toggleActive(classRoom.id).url);
};
</script>

<template>
    <Head title="Kelola Kelas" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <!-- Header -->
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Kelola Kelas</h1>
                    <p class="text-muted-foreground">
                        {{ classes.total }} kelas terdaftar
                    </p>
                </div>
                <Link :href="classesCreate().url">
                    <Button>
                        <Plus class="mr-2 h-4 w-4" />
                        Tambah Kelas
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
                                placeholder="Cari nama kelas atau jurusan..."
                                class="pl-9"
                            />
                        </div>
                        <Select v-model="selectedGrade">
                            <SelectTrigger class="w-full md:w-40">
                                <SelectValue placeholder="Tingkat" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="grade in grades"
                                    :key="grade.value"
                                    :value="grade.value"
                                >
                                    {{ grade.label }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <Select v-model="selectedYear">
                            <SelectTrigger class="w-full md:w-40">
                                <SelectValue placeholder="Tahun Ajaran" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="">Semua Tahun</SelectItem>
                                <SelectItem
                                    v-for="year in academicYears"
                                    :key="year"
                                    :value="year"
                                >
                                    {{ year }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <Select v-model="selectedActive">
                            <SelectTrigger class="w-full md:w-32">
                                <SelectValue placeholder="Status" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="option in activeOptions"
                                    :key="option.value"
                                    :value="option.value"
                                >
                                    {{ option.label }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <Button
                            v-if="searchQuery || selectedGrade || selectedYear || selectedActive"
                            variant="ghost"
                            size="sm"
                            @click="clearFilters"
                        >
                            Reset
                        </Button>
                    </div>
                </CardContent>
            </Card>

            <!-- Classes Table -->
            <Card>
                <CardContent class="p-0">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b bg-muted/50">
                                    <th class="px-4 py-3 text-left text-sm font-medium">Nama Kelas</th>
                                    <th class="px-4 py-3 text-left text-sm font-medium">Tingkat</th>
                                    <th class="px-4 py-3 text-left text-sm font-medium">Tahun Ajaran</th>
                                    <th class="px-4 py-3 text-left text-sm font-medium">Wali Kelas</th>
                                    <th class="px-4 py-3 text-center text-sm font-medium">Siswa</th>
                                    <th class="px-4 py-3 text-center text-sm font-medium">Mapel</th>
                                    <th class="px-4 py-3 text-center text-sm font-medium">Status</th>
                                    <th class="px-4 py-3 text-right text-sm font-medium">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="classRoom in classes.data"
                                    :key="classRoom.id"
                                    class="border-b hover:bg-muted/50 transition-colors"
                                >
                                    <td class="px-4 py-3">
                                        <div>
                                            <p class="font-medium">{{ classRoom.name }}</p>
                                            <p v-if="classRoom.major" class="text-sm text-muted-foreground">
                                                {{ classRoom.major }}
                                            </p>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <Badge variant="outline">{{ classRoom.grade_level }}</Badge>
                                    </td>
                                    <td class="px-4 py-3 text-sm">{{ classRoom.academic_year }}</td>
                                    <td class="px-4 py-3 text-sm">
                                        {{ classRoom.homeroom_teacher?.name || '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <div class="flex items-center justify-center gap-1">
                                            <Users class="h-4 w-4 text-muted-foreground" />
                                            {{ classRoom.active_students_count }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <div class="flex items-center justify-center gap-1">
                                            <BookOpen class="h-4 w-4 text-muted-foreground" />
                                            {{ classRoom.class_subjects_count }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <Badge :variant="classRoom.is_active ? 'default' : 'secondary'">
                                            {{ classRoom.is_active ? 'Aktif' : 'Nonaktif' }}
                                        </Badge>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex justify-end gap-2">
                                            <Link :href="classesShow(classRoom.id).url">
                                                <Button variant="ghost" size="sm">
                                                    <Eye class="h-4 w-4" />
                                                </Button>
                                            </Link>
                                            <Link :href="`/admin/classes/${classRoom.id}/edit`">
                                                <Button variant="ghost" size="sm">
                                                    <Pencil class="h-4 w-4" />
                                                </Button>
                                            </Link>
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                @click="toggleClassActive(classRoom)"
                                            >
                                                <ToggleRight v-if="classRoom.is_active" class="h-4 w-4" />
                                                <ToggleLeft v-else class="h-4 w-4" />
                                            </Button>
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                class="text-destructive hover:text-destructive"
                                                @click="deleteClass(classRoom)"
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
                    <div v-if="classes.data.length === 0" class="flex flex-col items-center justify-center py-12">
                        <School class="h-12 w-12 text-muted-foreground/50" />
                        <h3 class="mt-4 text-lg font-semibold">Tidak Ada Kelas</h3>
                        <p class="mt-2 text-center text-sm text-muted-foreground max-w-md">
                            <template v-if="searchQuery || selectedGrade || selectedYear || selectedActive">
                                Tidak ada kelas yang cocok dengan filter yang dipilih.
                            </template>
                            <template v-else>
                                Belum ada kelas terdaftar.
                            </template>
                        </p>
                        <div class="mt-4 flex gap-2">
                            <Button
                                v-if="searchQuery || selectedGrade || selectedYear || selectedActive"
                                variant="outline"
                                @click="clearFilters"
                            >
                                Reset Filter
                            </Button>
                            <Link :href="classesCreate().url">
                                <Button>Tambah Kelas</Button>
                            </Link>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Pagination -->
            <div v-if="classes.last_page > 1" class="flex justify-center gap-2">
                <template v-for="link in classes.links" :key="link.label">
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
