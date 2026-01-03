<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { CheckCircle2, Circle, PlayCircle } from 'lucide-vue-next';
import { topic as topicRoute } from '@/actions/App/Http/Controllers/Student/SubjectLearningController';

interface Topic {
    name: string;
    status: 'not_started' | 'in_progress' | 'completed';
    materialsCount: number;
}

interface Props {
    topics: Topic[];
    subjectId: number;
    currentTopic?: string;
}

const props = defineProps<Props>();

const getStatusIcon = (status: string) => {
    switch (status) {
        case 'completed':
            return CheckCircle2;
        case 'in_progress':
            return PlayCircle;
        default:
            return Circle;
    }
};

const getStatusClass = (status: string) => {
    switch (status) {
        case 'completed':
            return 'text-green-500';
        case 'in_progress':
            return 'text-blue-500';
        default:
            return 'text-muted-foreground';
    }
};
</script>

<template>
    <div class="space-y-2">
        <div
            v-for="(topic, index) in topics"
            :key="topic.name"
            class="relative"
        >
            <Link
                :href="topicRoute.url(subjectId, topic.name)"
                class="flex items-center gap-3 p-3 rounded-lg hover:bg-muted/50 transition-colors"
                :class="{
                    'bg-muted': currentTopic === topic.name,
                    'border border-primary': currentTopic === topic.name,
                }"
            >
                <component
                    :is="getStatusIcon(topic.status)"
                    :class="['h-5 w-5 shrink-0', getStatusClass(topic.status)]"
                />
                <div class="flex-1 min-w-0">
                    <p class="font-medium truncate">
                        {{ index + 1 }}. {{ topic.name }}
                    </p>
                    <p class="text-xs text-muted-foreground">
                        {{ topic.materialsCount }} materi
                    </p>
                </div>
                <span
                    v-if="topic.status === 'in_progress'"
                    class="text-xs text-blue-500 font-medium"
                >
                    Sedang Belajar
                </span>
            </Link>
        </div>
    </div>
</template>
