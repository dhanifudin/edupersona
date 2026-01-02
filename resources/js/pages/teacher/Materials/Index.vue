<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { type BreadcrumbItem, type LearningMaterial } from '@/types';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    create as materialsCreate,
    show as materialsShow,
    edit as materialsEdit,
    destroy as materialsDestroy,
    toggleActive,
} from '@/actions/App/Http/Controllers/Teacher/MaterialController';
import { Plus, Pencil, Trash2, Eye, EyeOff, BookOpen, Video, FileText, Image, Headphones, Gamepad2 } from 'lucide-vue-next';
import { ref } from 'vue';

interface MaterialWithStats extends LearningMaterial {
    activities_count: number;
    subject?: { id: number; name: string; code: string };
    class_room?: { id: number; name: string; grade_level: string };
}

interface PaginatedMaterials {
    data: MaterialWithStats[];
    links: { url: string | null; label: string; active: boolean }[];
    current_page: number;
    last_page: number;
    total: number;
}

interface Subject {
    id: number;
    name: string;
    code: string;
}

interface Props {
    materials: PaginatedMaterials;
    subjects: Subject[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/teacher/dashboard' },
    { title: 'Materi', href: '/teacher/materials' },
];

const deletingId = ref<number | null>(null);

const getTypeIcon = (type: string) => {
    const icons: Record<string, typeof Video> = {
        video: Video,
        document: FileText,
        infographic: Image,
        audio: Headphones,
        simulation: Gamepad2,
    };
    return icons[type] || BookOpen;
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

const getStyleLabel = (style: string): string => {
    const labels: Record<string, string> = {
        visual: 'Visual',
        auditory: 'Auditori',
        kinesthetic: 'Kinestetik',
        all: 'Semua',
    };
    return labels[style] || style;
};

const getDifficultyLabel = (level: string): string => {
    const labels: Record<string, string> = {
        beginner: 'Pemula',
        intermediate: 'Menengah',
        advanced: 'Lanjutan',
    };
    return labels[level] || level;
};

const getDifficultyColor = (level: string): string => {
    const colors: Record<string, string> = {
        beginner: 'bg-green-500',
        intermediate: 'bg-yellow-500',
        advanced: 'bg-red-500',
    };
    return colors[level] || 'bg-gray-500';
};

const handleToggleActive = (material: MaterialWithStats) => {
    router.patch(toggleActive(material.id).url, {}, {
        preserveScroll: true,
    });
};

const handleDelete = (material: MaterialWithStats) => {
    if (confirm(`Apakah Anda yakin ingin menghapus materi "${material.title}"?`)) {
        deletingId.value = material.id;
        router.delete(materialsDestroy(material.id).url, {
            onFinish: () => {
                deletingId.value = null;
            },
        });
    }
};
</script>

<template>
    <Head title="Kelola Materi" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <!-- Header -->
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Kelola Materi</h1>
                    <p class="text-muted-foreground">
                        Kelola materi pembelajaran yang Anda buat
                    </p>
                </div>
                <div>
                    <Link :href="materialsCreate().url">
                        <Button>
                            <Plus class="mr-2 h-4 w-4" />
                            Tambah Materi
                        </Button>
                    </Link>
                </div>
            </div>

            <!-- Materials List -->
            <div v-if="materials.data.length > 0">
                <div class="grid gap-4">
                    <Card
                        v-for="material in materials.data"
                        :key="material.id"
                        :class="{ 'opacity-60': !material.is_active }"
                    >
                        <CardContent class="p-4">
                            <div class="flex items-start gap-4">
                                <div class="rounded-lg bg-primary/10 p-3">
                                    <component :is="getTypeIcon(material.type)" class="h-6 w-6 text-primary" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start justify-between gap-2">
                                        <div>
                                            <Link
                                                :href="materialsShow(material.id).url"
                                                class="font-semibold hover:text-primary transition-colors"
                                            >
                                                {{ material.title }}
                                            </Link>
                                            <p class="text-sm text-muted-foreground mt-1">
                                                {{ material.subject?.name }}
                                                <span v-if="material.class_room">
                                                    - {{ material.class_room.name }}
                                                </span>
                                            </p>
                                        </div>
                                        <Badge :variant="material.is_active ? 'default' : 'secondary'">
                                            {{ material.is_active ? 'Aktif' : 'Nonaktif' }}
                                        </Badge>
                                    </div>
                                    <p v-if="material.description" class="text-sm text-muted-foreground mt-2 line-clamp-2">
                                        {{ material.description }}
                                    </p>
                                    <div class="flex flex-wrap items-center gap-2 mt-3">
                                        <Badge variant="outline">{{ getTypeLabel(material.type) }}</Badge>
                                        <Badge variant="secondary">{{ getStyleLabel(material.learning_style) }}</Badge>
                                        <Badge :class="getDifficultyColor(material.difficulty_level)" class="text-white">
                                            {{ getDifficultyLabel(material.difficulty_level) }}
                                        </Badge>
                                        <span class="text-sm text-muted-foreground">
                                            {{ material.activities_count }} kali diakses
                                        </span>
                                    </div>
                                </div>
                                <div class="flex gap-1">
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        @click="handleToggleActive(material)"
                                        :title="material.is_active ? 'Nonaktifkan' : 'Aktifkan'"
                                    >
                                        <Eye v-if="material.is_active" class="h-4 w-4" />
                                        <EyeOff v-else class="h-4 w-4" />
                                    </Button>
                                    <Link :href="materialsEdit(material.id).url">
                                        <Button variant="ghost" size="icon" title="Edit">
                                            <Pencil class="h-4 w-4" />
                                        </Button>
                                    </Link>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        @click="handleDelete(material)"
                                        :disabled="deletingId === material.id"
                                        class="text-destructive hover:text-destructive"
                                        title="Hapus"
                                    >
                                        <Trash2 class="h-4 w-4" />
                                    </Button>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Pagination -->
                <div v-if="materials.last_page > 1" class="flex justify-center gap-2 mt-6">
                    <template v-for="link in materials.links" :key="link.label">
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
                    <BookOpen class="h-12 w-12 text-muted-foreground/50" />
                    <h3 class="mt-4 text-lg font-semibold">Belum Ada Materi</h3>
                    <p class="mt-2 text-center text-sm text-muted-foreground max-w-md">
                        Mulai buat materi pembelajaran untuk siswa Anda.
                    </p>
                    <Link :href="materialsCreate().url" class="mt-4">
                        <Button>
                            <Plus class="mr-2 h-4 w-4" />
                            Tambah Materi Pertama
                        </Button>
                    </Link>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
