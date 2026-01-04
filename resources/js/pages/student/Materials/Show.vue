<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { type BreadcrumbItem, type LearningMaterial, type LearningActivity } from '@/types';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { index as materialsIndex, show as materialShow } from '@/actions/App/Http/Controllers/Student/MaterialController';
import { topic as topicRoute } from '@/actions/App/Http/Controllers/Student/SubjectLearningController';
import { BookOpen, Video, FileText, Image, Headphones, Gamepad2, ExternalLink, Clock, User, ArrowLeft, Check, ArrowRight, CheckCircle2 } from 'lucide-vue-next';
import { ref, onMounted, onUnmounted } from 'vue';

interface MaterialWithRelations extends LearningMaterial {
    subject?: { id: number; name: string; code: string };
    teacher?: { id: number; name: string };
}

interface OtherMaterial {
    id: number;
    title: string;
    type: string;
    learning_style: string;
    difficulty_level: string;
}

interface TopicNavigation {
    subjectId: number;
    subjectName?: string;
    currentTopic: string;
    nextTopic: string | null;
    otherStyleMaterials: OtherMaterial[];
}

interface Props {
    material: MaterialWithRelations;
    activity: LearningActivity;
    relatedMaterials: LearningMaterial[];
    topicNavigation?: TopicNavigation;
}

const props = defineProps<Props>();

const showCompletionDialog = ref(false);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Materi', href: '/materials' },
    { title: props.material.title, href: `/materials/${props.material.id}` },
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
        const response = await fetch(`/activities/${props.activity.id}`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content || '',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({
                duration_seconds: elapsedSeconds.value,
                completed: isCompleted.value,
            }),
        });

        if (!response.ok) {
            console.error('Failed to update activity:', response.status, await response.text());
        }
    } catch (error) {
        console.error('Failed to update activity', error);
    }
};

const markAsCompleted = async () => {
    // Stop the timer
    if (intervalId) {
        clearInterval(intervalId);
        intervalId = null;
    }

    isCompleted.value = true;
    await updateActivity();

    // Show completion dialog
    showCompletionDialog.value = true;
};

const goToNextTopic = () => {
    if (props.topicNavigation?.nextTopic) {
        router.visit(topicRoute.url({
            subject: props.topicNavigation.subjectId,
            topic: props.topicNavigation.nextTopic
        }));
    }
};

const goToCurrentTopic = () => {
    if (props.topicNavigation) {
        router.visit(topicRoute.url({
            subject: props.topicNavigation.subjectId,
            topic: props.topicNavigation.currentTopic
        }));
    }
};

const goToOtherMaterial = (materialId: number) => {
    router.visit(materialShow(materialId).url);
};

const getStyleColor = (style: string): string => {
    const colors: Record<string, string> = {
        visual: 'bg-blue-500',
        auditory: 'bg-purple-500',
        kinesthetic: 'bg-orange-500',
        all: 'bg-gray-500',
    };
    return colors[style] || 'bg-gray-500';
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
                    <!-- Current Topic Info -->
                    <Card v-if="topicNavigation">
                        <CardHeader class="pb-3">
                            <CardTitle class="text-lg">Topik Saat Ini</CardTitle>
                            <CardDescription>{{ topicNavigation.currentTopic }}</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <p class="text-sm text-muted-foreground">
                                Selesaikan materi ini untuk menandai topik sebagai selesai.
                            </p>
                            <div v-if="topicNavigation.nextTopic" class="mt-3 p-2 bg-muted/50 rounded-md">
                                <p class="text-xs text-muted-foreground">Topik selanjutnya:</p>
                                <p class="text-sm font-medium">{{ topicNavigation.nextTopic }}</p>
                            </div>
                        </CardContent>
                    </Card>

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
                                    <CheckCircle2 class="h-5 w-5" />
                                    <span class="font-medium">Topik Selesai!</span>
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

        <!-- Completion Dialog -->
        <Dialog v-model:open="showCompletionDialog">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle class="flex items-center gap-2">
                        <CheckCircle2 class="h-5 w-5 text-green-500" />
                        Selamat! Topik Selesai
                    </DialogTitle>
                    <DialogDescription>
                        Kamu telah menyelesaikan topik <strong>{{ topicNavigation?.currentTopic }}</strong>.
                        Apa yang ingin kamu lakukan selanjutnya?
                    </DialogDescription>
                </DialogHeader>

                <div class="space-y-3 py-4">
                    <!-- Option 1: Go to next topic -->
                    <Button
                        v-if="topicNavigation?.nextTopic"
                        class="w-full justify-start h-auto py-3"
                        @click="goToNextTopic"
                    >
                        <ArrowRight class="mr-3 h-5 w-5" />
                        <div class="text-left">
                            <p class="font-medium">Lanjut ke Topik Berikutnya</p>
                            <p class="text-xs text-primary-foreground/70">{{ topicNavigation.nextTopic }}</p>
                        </div>
                    </Button>

                    <!-- Option 2: Learn with different style (if other materials exist) -->
                    <div v-if="topicNavigation?.otherStyleMaterials?.length" class="space-y-2">
                        <p class="text-sm text-muted-foreground">
                            Atau pelajari topik ini dengan gaya belajar lain:
                        </p>
                        <div class="grid gap-2">
                            <Button
                                v-for="mat in topicNavigation.otherStyleMaterials"
                                :key="mat.id"
                                variant="outline"
                                class="w-full justify-start h-auto py-3"
                                @click="goToOtherMaterial(mat.id)"
                            >
                                <component :is="getTypeIcon(mat.type)" class="mr-3 h-5 w-5" />
                                <div class="text-left flex-1 min-w-0">
                                    <p class="font-medium truncate">{{ mat.title }}</p>
                                    <div class="flex items-center gap-2 mt-1">
                                        <Badge :class="getStyleColor(mat.learning_style)" class="text-xs text-white">
                                            {{ getStyleLabel(mat.learning_style) }}
                                        </Badge>
                                        <span class="text-xs text-muted-foreground">{{ getTypeLabel(mat.type) }}</span>
                                    </div>
                                </div>
                            </Button>
                        </div>
                    </div>

                    <!-- Option 3: Go back to current topic page -->
                    <Button
                        variant="ghost"
                        class="w-full"
                        @click="goToCurrentTopic"
                    >
                        <ArrowLeft class="mr-2 h-4 w-4" />
                        Kembali ke Halaman Topik
                    </Button>
                </div>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
