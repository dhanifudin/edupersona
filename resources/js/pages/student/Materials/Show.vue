<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { type BreadcrumbItem, type LearningMaterial, type LearningActivity } from '@/types';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { index as materialsIndex, show as materialShow } from '@/actions/App/Http/Controllers/Student/MaterialController';
import { BookOpen, Video, FileText, Image, Headphones, Gamepad2, ExternalLink, Clock, User, ArrowLeft, Check } from 'lucide-vue-next';
import { ref, onMounted, onUnmounted } from 'vue';

interface MaterialWithRelations extends LearningMaterial {
    subject?: { id: number; name: string; code: string };
    teacher?: { id: number; name: string };
}

interface Props {
    material: MaterialWithRelations;
    activity: LearningActivity;
    relatedMaterials: LearningMaterial[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/student/dashboard' },
    { title: 'Materi', href: '/student/materials' },
    { title: props.material.title, href: `/student/materials/${props.material.id}` },
];

// Activity tracking
const elapsedSeconds = ref(0);
const isCompleted = ref(false);
let intervalId: ReturnType<typeof setInterval> | null = null;

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

const formatDuration = (seconds: number): string => {
    const mins = Math.floor(seconds / 60);
    const secs = seconds % 60;
    return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
};

const updateActivity = async () => {
    try {
        await fetch(`/student/activities/${props.activity.id}`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content || '',
            },
            body: JSON.stringify({
                duration_seconds: elapsedSeconds.value,
                completed: isCompleted.value,
            }),
        });
    } catch (error) {
        console.error('Failed to update activity', error);
    }
};

const markAsCompleted = async () => {
    isCompleted.value = true;
    await updateActivity();
};

onMounted(() => {
    // Start tracking time
    intervalId = setInterval(() => {
        elapsedSeconds.value++;
        // Update activity every 30 seconds
        if (elapsedSeconds.value % 30 === 0) {
            updateActivity();
        }
    }, 1000);
});

onUnmounted(() => {
    if (intervalId) {
        clearInterval(intervalId);
    }
    // Final update when leaving
    updateActivity();
});
</script>

<template>
    <Head :title="material.title" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <!-- Back Button -->
            <div>
                <Link :href="materialsIndex().url">
                    <Button variant="ghost" size="sm">
                        <ArrowLeft class="mr-2 h-4 w-4" />
                        Kembali ke Daftar Materi
                    </Button>
                </Link>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Material Header -->
                    <Card>
                        <CardHeader>
                            <div class="flex items-start gap-4">
                                <div class="rounded-lg bg-primary/10 p-3">
                                    <component :is="getTypeIcon(material.type)" class="h-8 w-8 text-primary" />
                                </div>
                                <div class="flex-1">
                                    <CardTitle class="text-xl">{{ material.title }}</CardTitle>
                                    <CardDescription class="mt-1">
                                        {{ material.subject?.name }} - {{ material.topic || 'Umum' }}
                                    </CardDescription>
                                </div>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <div class="flex flex-wrap gap-2">
                                <Badge variant="outline">
                                    {{ getTypeLabel(material.type) }}
                                </Badge>
                                <Badge variant="secondary">
                                    {{ getStyleLabel(material.learning_style) }}
                                </Badge>
                                <Badge :class="{
                                    'bg-green-500 text-white': material.difficulty_level === 'beginner',
                                    'bg-yellow-500 text-white': material.difficulty_level === 'intermediate',
                                    'bg-red-500 text-white': material.difficulty_level === 'advanced',
                                }">
                                    {{ getDifficultyLabel(material.difficulty_level) }}
                                </Badge>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Description -->
                    <Card v-if="material.description">
                        <CardHeader>
                            <CardTitle class="text-lg">Deskripsi</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <p class="text-muted-foreground whitespace-pre-wrap">{{ material.description }}</p>
                        </CardContent>
                    </Card>

                    <!-- Content -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="text-lg">Konten Materi</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <!-- External URL -->
                            <div v-if="material.content_url" class="space-y-4">
                                <p class="text-sm text-muted-foreground">
                                    Materi ini tersedia di link eksternal. Klik tombol di bawah untuk membuka.
                                </p>
                                <a
                                    :href="material.content_url"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="inline-flex"
                                >
                                    <Button>
                                        <ExternalLink class="mr-2 h-4 w-4" />
                                        Buka Materi
                                    </Button>
                                </a>

                                <!-- Video Embed Preview -->
                                <div v-if="material.type === 'video' && material.content_url.includes('youtube')" class="mt-4">
                                    <div class="aspect-video w-full overflow-hidden rounded-lg border">
                                        <iframe
                                            :src="material.content_url.replace('watch?v=', 'embed/').replace('youtu.be/', 'youtube.com/embed/')"
                                            class="h-full w-full"
                                            frameborder="0"
                                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                            allowfullscreen
                                        ></iframe>
                                    </div>
                                </div>
                            </div>

                            <!-- File Download -->
                            <div v-else-if="material.file_path" class="space-y-4">
                                <p class="text-sm text-muted-foreground">
                                    Materi ini tersedia dalam bentuk file. Klik tombol di bawah untuk mengunduh.
                                </p>
                                <a :href="`/storage/${material.file_path}`" download class="inline-flex">
                                    <Button>
                                        <FileText class="mr-2 h-4 w-4" />
                                        Unduh File
                                    </Button>
                                </a>
                            </div>

                            <!-- No Content -->
                            <div v-else class="text-center py-8">
                                <BookOpen class="mx-auto h-12 w-12 text-muted-foreground/50" />
                                <p class="mt-4 text-muted-foreground">
                                    Konten materi belum tersedia.
                                </p>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Activity Tracking -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="text-lg flex items-center gap-2">
                                <Clock class="h-5 w-5" />
                                Waktu Belajar
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="text-center">
                                <div class="text-4xl font-bold font-mono">
                                    {{ formatDuration(elapsedSeconds) }}
                                </div>
                                <p class="text-sm text-muted-foreground mt-1">
                                    Waktu yang dihabiskan
                                </p>
                            </div>

                            <div class="mt-4">
                                <Button
                                    v-if="!isCompleted"
                                    class="w-full"
                                    @click="markAsCompleted"
                                >
                                    <Check class="mr-2 h-4 w-4" />
                                    Tandai Selesai
                                </Button>
                                <div v-else class="flex items-center justify-center gap-2 text-green-600">
                                    <Check class="h-5 w-5" />
                                    <span class="font-medium">Selesai</span>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Teacher Info -->
                    <Card v-if="material.teacher">
                        <CardHeader>
                            <CardTitle class="text-lg flex items-center gap-2">
                                <User class="h-5 w-5" />
                                Pengajar
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <p class="font-medium">{{ material.teacher.name }}</p>
                        </CardContent>
                    </Card>

                    <!-- Related Materials -->
                    <Card v-if="relatedMaterials.length > 0">
                        <CardHeader>
                            <CardTitle class="text-lg">Materi Terkait</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-3">
                                <Link
                                    v-for="related in relatedMaterials"
                                    :key="related.id"
                                    :href="materialShow(related.id).url"
                                    class="block rounded-lg border p-3 transition-colors hover:bg-accent"
                                >
                                    <div class="flex items-start gap-2">
                                        <component :is="getTypeIcon(related.type)" class="h-4 w-4 mt-0.5 text-muted-foreground" />
                                        <div>
                                            <p class="font-medium text-sm line-clamp-2">{{ related.title }}</p>
                                            <Badge variant="outline" class="mt-1 text-xs">
                                                {{ getTypeLabel(related.type) }}
                                            </Badge>
                                        </div>
                                    </div>
                                </Link>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
