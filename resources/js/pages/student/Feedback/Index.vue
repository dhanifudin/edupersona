<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { type BreadcrumbItem, type AiFeedback } from '@/types';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { show as feedbackShow, generate as feedbackGenerate } from '@/actions/App/Http/Controllers/Student/FeedbackController';
import { MessageSquare, Sparkles, Clock, CheckCircle, AlertCircle } from 'lucide-vue-next';
import { ref } from 'vue';

interface PaginatedFeedback {
    data: AiFeedback[];
    links: { url: string | null; label: string; active: boolean }[];
    current_page: number;
    last_page: number;
    total: number;
}

interface Props {
    feedback: PaginatedFeedback;
    hasLearningProfile: boolean;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/student/dashboard' },
    { title: 'Umpan Balik AI', href: '/student/feedback' },
];

const isGenerating = ref(false);

const generateFeedback = () => {
    isGenerating.value = true;
    router.post(feedbackGenerate().url, {}, {
        onFinish: () => {
            isGenerating.value = false;
        },
    });
};

const formatDate = (dateString: string): string => {
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const truncateText = (text: string, maxLength: number = 150): string => {
    if (text.length <= maxLength) return text;
    return text.substring(0, maxLength) + '...';
};
</script>

<template>
    <Head title="Umpan Balik AI" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <!-- Header -->
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight flex items-center gap-2">
                        <Sparkles class="h-6 w-6 text-primary" />
                        Umpan Balik AI
                    </h1>
                    <p class="text-muted-foreground">
                        Dapatkan evaluasi dan saran pembelajaran personal dari AI
                    </p>
                </div>
                <div>
                    <Button
                        @click="generateFeedback"
                        :disabled="isGenerating || !hasLearningProfile"
                    >
                        <Sparkles :class="['mr-2 h-4 w-4', isGenerating ? 'animate-spin' : '']" />
                        {{ isGenerating ? 'Membuat...' : 'Buat Umpan Balik Baru' }}
                    </Button>
                </div>
            </div>

            <!-- No Learning Profile Warning -->
            <Card v-if="!hasLearningProfile" class="border-yellow-500/50 bg-yellow-500/10">
                <CardContent class="flex items-center gap-4 py-4">
                    <AlertCircle class="h-8 w-8 text-yellow-500" />
                    <div>
                        <p class="font-medium">Profil Gaya Belajar Belum Tersedia</p>
                        <p class="text-sm text-muted-foreground">
                            Lengkapi kuesioner gaya belajar terlebih dahulu untuk mendapatkan umpan balik personal.
                        </p>
                        <Link href="/student/questionnaire" class="text-sm text-primary hover:underline">
                            Isi Kuesioner Sekarang →
                        </Link>
                    </div>
                </CardContent>
            </Card>

            <!-- Feedback List -->
            <div v-if="feedback.data.length > 0" class="space-y-4">
                <Card
                    v-for="item in feedback.data"
                    :key="item.id"
                    class="transition-all hover:border-primary/50 hover:shadow-md"
                >
                    <Link :href="feedbackShow(item.id).url" class="block">
                        <CardHeader>
                            <div class="flex items-start justify-between">
                                <div class="flex items-center gap-2">
                                    <MessageSquare class="h-5 w-5 text-primary" />
                                    <CardTitle class="text-lg">
                                        Umpan Balik Pembelajaran
                                    </CardTitle>
                                </div>
                                <div class="flex items-center gap-2">
                                    <Badge v-if="!item.is_read" variant="default" class="bg-primary">
                                        Baru
                                    </Badge>
                                    <Badge v-else variant="outline">
                                        <CheckCircle class="mr-1 h-3 w-3" />
                                        Dibaca
                                    </Badge>
                                </div>
                            </div>
                            <CardDescription class="flex items-center gap-1">
                                <Clock class="h-3 w-3" />
                                {{ formatDate(item.generated_at) }}
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <p class="text-sm text-muted-foreground line-clamp-3">
                                {{ truncateText(item.feedback_text.replace(/[#*]/g, ''), 200) }}
                            </p>
                        </CardContent>
                    </Link>
                </Card>

                <!-- Pagination -->
                <div v-if="feedback.last_page > 1" class="flex justify-center gap-2 mt-6">
                    <template v-for="link in feedback.links" :key="link.label">
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
                    <MessageSquare class="h-12 w-12 text-muted-foreground/50" />
                    <h3 class="mt-4 text-lg font-semibold">Belum Ada Umpan Balik</h3>
                    <p class="mt-2 text-center text-sm text-muted-foreground max-w-md">
                        Klik tombol "Buat Umpan Balik Baru" untuk mendapatkan evaluasi dan saran pembelajaran personal dari AI.
                    </p>
                    <Button
                        v-if="hasLearningProfile"
                        class="mt-4"
                        @click="generateFeedback"
                        :disabled="isGenerating"
                    >
                        <Sparkles :class="['mr-2 h-4 w-4', isGenerating ? 'animate-spin' : '']" />
                        {{ isGenerating ? 'Membuat...' : 'Buat Umpan Balik Pertama' }}
                    </Button>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
