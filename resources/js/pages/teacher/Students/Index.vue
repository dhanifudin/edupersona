<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { type BreadcrumbItem, type User, type LearningStyleProfile, type ClassRoom } from '@/types';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { show as studentsShow } from '@/actions/App/Http/Controllers/Teacher/StudentController';
import { Users, Search, Eye, Brain, BookOpen, CheckCircle } from 'lucide-vue-next';
import { ref, watch } from 'vue';
import { useDebounceFn } from '@vueuse/core';

interface StudentWithProfile extends User {
    learning_style_profile?: LearningStyleProfile;
    classes?: ClassRoom[];
    learning_activities_count: number;
    completed_activities_count: number;
}

interface PaginatedStudents {
    data: StudentWithProfile[];
    links: { url: string | null; label: string; active: boolean }[];
    current_page: number;
    last_page: number;
    total: number;
}

interface ClassOption {
    id: number;
    name: string;
    grade_level: string;
}

interface Filters {
    class: string | null;
    style: string | null;
    search: string | null;
}

interface Props {
    students: PaginatedStudents;
    classes: ClassOption[];
    filters: Filters;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/teacher/dashboard' },
    { title: 'Siswa', href: '/teacher/students' },
];

const searchQuery = ref(props.filters.search || '');
const selectedClass = ref(props.filters.class || '');
const selectedStyle = ref(props.filters.style || '');

const learningStyles = [
    { value: '', label: 'Semua Gaya Belajar' },
    { value: 'visual', label: 'Visual' },
    { value: 'auditory', label: 'Auditori' },
    { value: 'kinesthetic', label: 'Kinestetik' },
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

const applyFilters = () => {
    router.get('/teacher/students', {
        class: selectedClass.value || undefined,
        style: selectedStyle.value || undefined,
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

watch([selectedClass, selectedStyle], () => {
    applyFilters();
});

const clearFilters = () => {
    searchQuery.value = '';
    selectedClass.value = '';
    selectedStyle.value = '';
    router.get('/teacher/students');
};
</script>

<template>
    <Head title="Daftar Siswa" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <!-- Header -->
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Daftar Siswa</h1>
                    <p class="text-muted-foreground">
                        {{ students.total }} siswa di kelas yang Anda ajar
                    </p>
                </div>
            </div>

            <!-- Filters -->
            <Card>
                <CardContent class="p-4">
                    <div class="flex flex-col gap-4 md:flex-row md:items-center">
                        <div class="relative flex-1">
                            <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                            <Input
                                v-model="searchQuery"
                                placeholder="Cari nama, email, atau NIS..."
                                class="pl-9"
                            />
                        </div>
                        <Select v-model="selectedClass">
                            <SelectTrigger class="w-full md:w-48">
                                <SelectValue placeholder="Semua Kelas" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="">Semua Kelas</SelectItem>
                                <SelectItem
                                    v-for="cls in classes"
                                    :key="cls.id"
                                    :value="cls.id.toString()"
                                >
                                    {{ cls.name }} ({{ cls.grade_level }})
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <Select v-model="selectedStyle">
                            <SelectTrigger class="w-full md:w-48">
                                <SelectValue placeholder="Gaya Belajar" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="style in learningStyles"
                                    :key="style.value"
                                    :value="style.value"
                                >
                                    {{ style.label }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <Button
                            v-if="searchQuery || selectedClass || selectedStyle"
                            variant="ghost"
                            size="sm"
                            @click="clearFilters"
                        >
                            Reset
                        </Button>
                    </div>
                </CardContent>
            </Card>

            <!-- Students List -->
            <div v-if="students.data.length > 0">
                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                    <Link
                        v-for="student in students.data"
                        :key="student.id"
                        :href="studentsShow(student.id).url"
                        class="block"
                    >
                        <Card class="hover:bg-accent/50 transition-colors cursor-pointer h-full">
                            <CardContent class="p-4">
                                <div class="flex items-start gap-4">
                                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-primary/10 text-primary font-semibold text-lg">
                                        {{ student.name.charAt(0).toUpperCase() }}
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h3 class="font-semibold truncate">{{ student.name }}</h3>
                                        <p class="text-sm text-muted-foreground">
                                            {{ student.student_id_number || student.email }}
                                        </p>
                                        <div class="flex flex-wrap gap-1 mt-2">
                                            <Badge
                                                v-for="cls in student.classes"
                                                :key="cls.id"
                                                variant="outline"
                                                class="text-xs"
                                            >
                                                {{ cls.name }}
                                            </Badge>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-4 pt-4 border-t">
                                    <!-- Learning Style -->
                                    <div v-if="student.learning_style_profile" class="flex items-center gap-2 mb-3">
                                        <Brain class="h-4 w-4 text-muted-foreground" />
                                        <span class="text-sm">Gaya Belajar:</span>
                                        <Badge
                                            :class="getStyleColor(student.learning_style_profile.dominant_style)"
                                            class="text-white text-xs"
                                        >
                                            {{ getStyleLabel(student.learning_style_profile.dominant_style) }}
                                        </Badge>
                                    </div>
                                    <div v-else class="flex items-center gap-2 mb-3 text-muted-foreground">
                                        <Brain class="h-4 w-4" />
                                        <span class="text-sm">Belum mengisi kuesioner</span>
                                    </div>

                                    <!-- Activity Stats -->
                                    <div class="grid grid-cols-2 gap-3 text-sm">
                                        <div class="flex items-center gap-2">
                                            <BookOpen class="h-4 w-4 text-muted-foreground" />
                                            <span class="text-muted-foreground">Aktivitas:</span>
                                            <span class="font-medium">{{ student.learning_activities_count }}</span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <CheckCircle class="h-4 w-4 text-muted-foreground" />
                                            <span class="text-muted-foreground">Selesai:</span>
                                            <span class="font-medium">{{ student.completed_activities_count }}</span>
                                        </div>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </Link>
                </div>

                <!-- Pagination -->
                <div v-if="students.last_page > 1" class="flex justify-center gap-2 mt-6">
                    <template v-for="link in students.links" :key="link.label">
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

            <!-- Empty State -->
            <Card v-else>
                <CardContent class="flex flex-col items-center justify-center py-12">
                    <Users class="h-12 w-12 text-muted-foreground/50" />
                    <h3 class="mt-4 text-lg font-semibold">Tidak Ada Siswa</h3>
                    <p class="mt-2 text-center text-sm text-muted-foreground max-w-md">
                        <template v-if="searchQuery || selectedClass || selectedStyle">
                            Tidak ada siswa yang cocok dengan filter yang dipilih.
                        </template>
                        <template v-else>
                            Belum ada siswa di kelas yang Anda ajar.
                        </template>
                    </p>
                    <Button
                        v-if="searchQuery || selectedClass || selectedStyle"
                        variant="outline"
                        class="mt-4"
                        @click="clearFilters"
                    >
                        Reset Filter
                    </Button>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
