<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { type BreadcrumbItem, type Subject, type LearningStyleProfile } from '@/types';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { index as dashboardIndex } from '@/actions/App/Http/Controllers/Student/DashboardController';
import { topic as topicRoute, startTopic } from '@/actions/App/Http/Controllers/Student/SubjectLearningController';
import TopicList from '@/components/student/TopicList.vue';
import { ArrowLeft, BookOpen, CheckCircle2, PlayCircle } from 'lucide-vue-next';

interface Topic {
    name: string;
    status: 'not_started' | 'in_progress' | 'completed';
    materialsCount: number;
}

interface Enrollment {
    id: number;
    enrollment_type: 'assigned' | 'elective';
    enrolled_at: string;
    status: string;
}

interface Props {
    subject: Subject;
    enrollment: Enrollment;
    topics: Topic[];
    currentTopic?: Topic;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboardIndex.url(),
    },
    {
        title: props.subject.name,
        href: '#',
    },
];

const completedTopics = props.topics.filter((t) => t.status === 'completed').length;
const totalTopics = props.topics.length;
const progressPercentage = totalTopics > 0 ? Math.round((completedTopics / totalTopics) * 100) : 0;

const handleStartTopic = (topicName: string) => {
    router.post(startTopic.url(props.subject.id, topicName));
};
</script>

<template>
    <Head :title="`Belajar ${subject.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <!-- Header -->
            <div class="flex items-start justify-between">
                <div class="flex items-center gap-4">
                    <Link :href="dashboardIndex.url()">
                        <Button variant="ghost" size="icon">
                            <ArrowLeft class="h-5 w-5" />
                        </Button>
                    </Link>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-2xl font-bold">{{ subject.name }}</h1>
                            <Badge :variant="enrollment.enrollment_type === 'assigned' ? 'default' : 'secondary'">
                                {{ enrollment.enrollment_type === 'assigned' ? 'Wajib' : 'Pilihan' }}
                            </Badge>
                        </div>
                        <p v-if="subject.description" class="mt-1 text-muted-foreground">
                            {{ subject.description }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Progress Overview -->
            <Card>
                <CardContent class="pt-6">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex items-center gap-4">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-primary/10">
                                <BookOpen class="h-6 w-6 text-primary" />
                            </div>
                            <div>
                                <p class="text-sm text-muted-foreground">Kemajuan Belajar</p>
                                <p class="text-2xl font-bold">{{ completedTopics }}/{{ totalTopics }} Topik</p>
                            </div>
                        </div>
                        <div class="flex-1 sm:max-w-xs">
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-muted-foreground">Progress</span>
                                <span class="font-medium">{{ progressPercentage }}%</span>
                            </div>
                            <div class="h-2 w-full bg-secondary rounded-full overflow-hidden">
                                <div
                                    class="h-full bg-primary transition-all duration-300"
                                    :style="{ width: `${progressPercentage}%` }"
                                />
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Main Content Grid -->
            <div class="grid gap-6 lg:grid-cols-3">
                <!-- Topics List -->
                <Card class="lg:col-span-1">
                    <CardHeader>
                        <CardTitle class="text-lg">Daftar Topik</CardTitle>
                        <CardDescription>
                            {{ totalTopics }} topik pembelajaran
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <TopicList
                            :topics="topics"
                            :subject-id="subject.id"
                            :current-topic="currentTopic?.name"
                        />
                    </CardContent>
                </Card>

                <!-- Current Topic / Call to Action -->
                <Card class="lg:col-span-2">
                    <CardHeader>
                        <CardTitle class="text-lg">
                            <template v-if="currentTopic">
                                {{ currentTopic.status === 'in_progress' ? 'Lanjutkan Belajar' : 'Mulai Belajar' }}
                            </template>
                            <template v-else>
                                Selesai!
                            </template>
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <template v-if="currentTopic">
                            <div class="flex flex-col gap-4">
                                <div class="flex items-center gap-3 p-4 rounded-lg bg-muted/50">
                                    <component
                                        :is="currentTopic.status === 'in_progress' ? PlayCircle : BookOpen"
                                        class="h-8 w-8 text-primary shrink-0"
                                    />
                                    <div class="flex-1">
                                        <p class="font-semibold text-lg">{{ currentTopic.name }}</p>
                                        <p class="text-sm text-muted-foreground">
                                            {{ currentTopic.materialsCount }} materi pembelajaran
                                        </p>
                                    </div>
                                    <Badge v-if="currentTopic.status === 'in_progress'" class="bg-blue-500">
                                        Sedang Belajar
                                    </Badge>
                                </div>

                                <div class="flex gap-3">
                                    <Link :href="topicRoute.url(subject.id, currentTopic.name)" class="flex-1">
                                        <Button class="w-full" size="lg">
                                            <PlayCircle class="mr-2 h-5 w-5" />
                                            {{ currentTopic.status === 'in_progress' ? 'Lanjutkan' : 'Mulai Belajar' }}
                                        </Button>
                                    </Link>
                                </div>
                            </div>
                        </template>

                        <template v-else>
                            <div class="flex flex-col items-center justify-center py-8 text-center">
                                <CheckCircle2 class="h-16 w-16 text-green-500" />
                                <p class="mt-4 text-lg font-semibold">Selamat!</p>
                                <p class="text-muted-foreground">
                                    Kamu telah menyelesaikan semua topik di mata pelajaran ini.
                                </p>
                                <Link :href="dashboardIndex.url()" class="mt-4">
                                    <Button variant="outline">
                                        Kembali ke Dashboard
                                    </Button>
                                </Link>
                            </div>
                        </template>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
