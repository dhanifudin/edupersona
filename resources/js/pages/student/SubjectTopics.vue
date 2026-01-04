<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';
import { Head } from '@inertiajs/vue3';
import { type BreadcrumbItem, type Subject } from '@/types';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { topic as topicRoute } from '@/actions/App/Http/Controllers/Student/SubjectLearningController';
import { show as subjectLearningShow } from '@/actions/App/Http/Controllers/Student/SubjectLearningController';
import { ArrowLeft, BookOpen, CheckCircle, Circle, Play } from 'lucide-vue-next';

interface Topic {
    name: string;
    status: 'not_started' | 'in_progress' | 'completed';
    materialsCount: number;
}

interface Props {
    subject: Subject;
    topics: Topic[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard Siswa', href: '/dashboard' },
    { title: props.subject.name, href: subjectLearningShow.url(props.subject.id) },
    { title: 'Daftar Topik', href: '#' },
];

const getStatusIcon = (status: string) => {
    switch (status) {
        case 'completed':
            return CheckCircle;
        case 'in_progress':
            return Play;
        default:
            return Circle;
    }
};

const getStatusColor = (status: string) => {
    switch (status) {
        case 'completed':
            return 'text-green-500';
        case 'in_progress':
            return 'text-blue-500';
        default:
            return 'text-muted-foreground';
    }
};

const getStatusLabel = (status: string) => {
    switch (status) {
        case 'completed':
            return 'Selesai';
        case 'in_progress':
            return 'Sedang Dipelajari';
        default:
            return 'Belum Dimulai';
    }
};
</script>

<template>
    <Head :title="`${subject.name} - Daftar Topik`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <div class="flex items-center gap-4">
                <Link :href="subjectLearningShow.url(subject.id)">
                    <Button variant="ghost" size="icon">
                        <ArrowLeft class="h-5 w-5" />
                    </Button>
                </Link>
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">{{ subject.name }}</h1>
                    <p class="text-muted-foreground">Daftar Topik Pembelajaran</p>
                </div>
            </div>

            <div class="grid gap-4">
                <Card
                    v-for="(topicItem, index) in topics"
                    :key="topicItem.name"
                    class="transition-all hover:shadow-md"
                >
                    <CardHeader class="pb-2">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex h-8 w-8 items-center justify-center rounded-full bg-muted text-sm font-medium"
                                >
                                    {{ index + 1 }}
                                </div>
                                <CardTitle class="text-lg">{{ topicItem.name }}</CardTitle>
                            </div>
                            <div class="flex items-center gap-2">
                                <component
                                    :is="getStatusIcon(topicItem.status)"
                                    class="h-5 w-5"
                                    :class="getStatusColor(topicItem.status)"
                                />
                                <Badge
                                    :variant="topicItem.status === 'completed' ? 'default' : topicItem.status === 'in_progress' ? 'secondary' : 'outline'"
                                >
                                    {{ getStatusLabel(topicItem.status) }}
                                </Badge>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent class="flex items-center justify-between">
                        <div class="flex items-center gap-2 text-sm text-muted-foreground">
                            <BookOpen class="h-4 w-4" />
                            <span>{{ topicItem.materialsCount }} materi</span>
                        </div>
                        <Link :href="topicRoute.url({ subject: subject.id, topic: topicItem.name })">
                            <Button
                                :variant="topicItem.status === 'not_started' ? 'outline' : 'default'"
                                size="sm"
                            >
                                {{ topicItem.status === 'not_started' ? 'Mulai' : topicItem.status === 'in_progress' ? 'Lanjutkan' : 'Review' }}
                            </Button>
                        </Link>
                    </CardContent>
                </Card>
            </div>

            <div v-if="topics.length === 0" class="flex flex-col items-center justify-center py-12">
                <BookOpen class="h-16 w-16 text-muted-foreground/50" />
                <p class="mt-4 text-muted-foreground">Belum ada topik untuk mata pelajaran ini.</p>
            </div>
        </div>
    </AppLayout>
</template>
