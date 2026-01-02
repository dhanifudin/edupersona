<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { type BreadcrumbItem, type LearningMaterial, type LearningActivity } from '@/types';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    index as materialsIndex,
    edit as materialsEdit,
    destroy as materialsDestroy,
    toggleActive,
} from '@/actions/App/Http/Controllers/Teacher/MaterialController';
import {
    ArrowLeft,
    Pencil,
    Trash2,
    Eye,
    EyeOff,
    ExternalLink,
    Download,
    Clock,
    CheckCircle,
    Users,
    Video,
    FileText,
    Image,
    Headphones,
    Gamepad2,
    BookOpen,
} from 'lucide-vue-next';
import { ref, computed } from 'vue';

interface MaterialWithRelations extends LearningMaterial {
    subject?: { id: number; name: string; code: string };
    class_room?: { id: number; name: string; grade_level: string };
}

interface ActivityWithUser extends LearningActivity {
    user?: { id: number; name: string };
}

interface Stats {
    totalViews: number;
    completions: number;
    averageDuration: number;
}

interface Props {
    material: MaterialWithRelations;
    activities: ActivityWithUser[];
    stats: Stats;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/teacher/dashboard' },
    { title: 'Materi', href: '/teacher/materials' },
    { title: props.material.title, href: `/teacher/materials/${props.material.id}` },
];

const deleting = ref(false);

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
        all: 'Semua Gaya',
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

const formatDate = (dateString: string): string => {
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const formatDuration = (seconds: number): string => {
    const minutes = Math.floor(seconds / 60);
    if (minutes < 60) {
        return `${minutes} menit`;
    }
    const hours = Math.floor(minutes / 60);
    const remainingMinutes = minutes % 60;
    return `${hours} jam ${remainingMinutes} menit`;
};

const completionRate = computed(() => {
    if (props.stats.totalViews === 0) return 0;
    return Math.round((props.stats.completions / props.stats.totalViews) * 100);
});

const handleToggleActive = () => {
    router.patch(toggleActive(props.material.id).url, {}, {
        preserveScroll: true,
    });
};

const handleDelete = () => {
    if (confirm(`Apakah Anda yakin ingin menghapus materi "${props.material.title}"?`)) {
        deleting.value = true;
        router.delete(materialsDestroy(props.material.id).url);
    }
};
</script>

<template>
    <Head :title="material.title" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <!-- Back Button & Actions -->
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <Link :href="materialsIndex().url">
                    <Button variant="ghost" size="sm">
                        <ArrowLeft class="mr-2 h-4 w-4" />
                        Kembali
                    </Button>
                </Link>
                <div class="flex gap-2">
                    <Button
                        variant="outline"
                        size="sm"
                        @click="handleToggleActive"
                    >
                        <Eye v-if="!material.is_active" class="mr-2 h-4 w-4" />
                        <EyeOff v-else class="mr-2 h-4 w-4" />
                        {{ material.is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                    </Button>
                    <Link :href="materialsEdit(material.id).url">
                        <Button variant="outline" size="sm">
                            <Pencil class="mr-2 h-4 w-4" />
                            Edit
                        </Button>
                    </Link>
                    <Button
                        variant="destructive"
                        size="sm"
                        @click="handleDelete"
                        :disabled="deleting"
                    >
                        <Trash2 class="mr-2 h-4 w-4" />
                        Hapus
                    </Button>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Material Info Card -->
                    <Card>
                        <CardHeader>
                            <div class="flex items-start gap-4">
                                <div class="rounded-lg bg-primary/10 p-3">
                                    <component :is="getTypeIcon(material.type)" class="h-8 w-8 text-primary" />
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <CardTitle class="text-xl">{{ material.title }}</CardTitle>
                                        <Badge :variant="material.is_active ? 'default' : 'secondary'">
                                            {{ material.is_active ? 'Aktif' : 'Nonaktif' }}
                                        </Badge>
                                    </div>
                                    <CardDescription class="mt-1">
                                        {{ material.subject?.name }}
                                        <span v-if="material.class_room">
                                            - {{ material.class_room.name }}
                                        </span>
                                    </CardDescription>
                                </div>
                            </div>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <!-- Badges -->
                            <div class="flex flex-wrap gap-2">
                                <Badge variant="outline">{{ getTypeLabel(material.type) }}</Badge>
                                <Badge variant="secondary">{{ getStyleLabel(material.learning_style) }}</Badge>
                                <Badge :class="getDifficultyColor(material.difficulty_level)" class="text-white">
                                    {{ getDifficultyLabel(material.difficulty_level) }}
                                </Badge>
                            </div>

                            <!-- Topic -->
                            <div v-if="material.topic">
                                <p class="text-sm font-medium text-muted-foreground">Topik</p>
                                <p>{{ material.topic }}</p>
                            </div>

                            <!-- Description -->
                            <div v-if="material.description">
                                <p class="text-sm font-medium text-muted-foreground">Deskripsi</p>
                                <p class="whitespace-pre-wrap">{{ material.description }}</p>
                            </div>

                            <!-- Links & Files -->
                            <div class="flex flex-wrap gap-3 pt-2">
                                <a
                                    v-if="material.content_url"
                                    :href="material.content_url"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                >
                                    <Button variant="outline" size="sm">
                                        <ExternalLink class="mr-2 h-4 w-4" />
                                        Buka URL Konten
                                    </Button>
                                </a>
                                <a
                                    v-if="material.file_path"
                                    :href="`/storage/${material.file_path}`"
                                    target="_blank"
                                >
                                    <Button variant="outline" size="sm">
                                        <Download class="mr-2 h-4 w-4" />
                                        Unduh File
                                    </Button>
                                </a>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Recent Activities -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="text-lg">Aktivitas Terbaru</CardTitle>
                            <CardDescription>Siswa yang mengakses materi ini</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div v-if="activities.length > 0" class="space-y-3">
                                <div
                                    v-for="activity in activities"
                                    :key="activity.id"
                                    class="flex items-center justify-between rounded-lg border p-3"
                                >
                                    <div class="flex-1">
                                        <p class="font-medium">{{ activity.user?.name }}</p>
                                        <p class="text-sm text-muted-foreground">
                                            {{ formatDate(activity.started_at) }}
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <div class="flex items-center gap-2">
                                            <Badge v-if="activity.completed_at" variant="default" class="bg-green-500">
                                                <CheckCircle class="mr-1 h-3 w-3" />
                                                Selesai
                                            </Badge>
                                            <Badge v-else variant="secondary">
                                                <Clock class="mr-1 h-3 w-3" />
                                                Belum Selesai
                                            </Badge>
                                        </div>
                                        <p class="text-sm text-muted-foreground mt-1">
                                            {{ formatDuration(activity.duration_seconds) }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="text-center py-8 text-muted-foreground">
                                <Users class="mx-auto h-12 w-12 opacity-50" />
                                <p class="mt-2">Belum ada siswa yang mengakses materi ini</p>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Stats Sidebar -->
                <div class="space-y-6">
                    <!-- Stats Cards -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="text-lg">Statistik</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <Eye class="h-4 w-4 text-muted-foreground" />
                                    <span class="text-sm">Total Dilihat</span>
                                </div>
                                <span class="font-bold">{{ stats.totalViews }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <CheckCircle class="h-4 w-4 text-muted-foreground" />
                                    <span class="text-sm">Diselesaikan</span>
                                </div>
                                <span class="font-bold">{{ stats.completions }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <Clock class="h-4 w-4 text-muted-foreground" />
                                    <span class="text-sm">Rata-rata Durasi</span>
                                </div>
                                <span class="font-bold">{{ stats.averageDuration }} menit</span>
                            </div>
                            <div class="flex items-center justify-between pt-2 border-t">
                                <span class="text-sm font-medium">Tingkat Penyelesaian</span>
                                <span class="font-bold">{{ completionRate }}%</span>
                            </div>
                            <div class="h-2 rounded-full bg-muted overflow-hidden">
                                <div
                                    class="h-full bg-primary transition-all"
                                    :style="{ width: `${completionRate}%` }"
                                />
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Material Meta -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="text-lg">Informasi</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-3 text-sm">
                            <div class="flex justify-between">
                                <span class="text-muted-foreground">Dibuat</span>
                                <span>{{ formatDate(material.created_at) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-muted-foreground">Diperbarui</span>
                                <span>{{ formatDate(material.updated_at) }}</span>
                            </div>
                            <div v-if="material.file_path" class="flex justify-between">
                                <span class="text-muted-foreground">File</span>
                                <span class="text-green-600">Tersedia</span>
                            </div>
                            <div v-if="material.content_url" class="flex justify-between">
                                <span class="text-muted-foreground">URL Konten</span>
                                <span class="text-green-600">Tersedia</span>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
