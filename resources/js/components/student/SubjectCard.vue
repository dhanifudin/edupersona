<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { show } from '@/actions/App/Http/Controllers/Student/SubjectLearningController';

interface Subject {
    id: number;
    name: string;
    code: string;
    description?: string;
}

interface Progress {
    completedTopics: number;
    totalTopics: number;
    percentage: number;
}

interface Props {
    subject: Subject;
    enrollmentType: 'assigned' | 'elective';
    progress: Progress;
}

defineProps<Props>();
</script>

<template>
    <Card class="hover:shadow-md transition-shadow">
        <CardHeader class="pb-2">
            <div class="flex items-start justify-between">
                <CardTitle class="text-lg font-semibold">{{ subject.name }}</CardTitle>
                <Badge :variant="enrollmentType === 'assigned' ? 'default' : 'secondary'">
                    {{ enrollmentType === 'assigned' ? 'Wajib' : 'Pilihan' }}
                </Badge>
            </div>
            <p v-if="subject.description" class="text-sm text-muted-foreground line-clamp-2">
                {{ subject.description }}
            </p>
        </CardHeader>
        <CardContent class="pb-2">
            <div class="space-y-2">
                <div class="flex justify-between text-sm">
                    <span class="text-muted-foreground">Progress</span>
                    <span class="font-medium">{{ progress.percentage }}%</span>
                </div>
                <div class="h-2 w-full bg-secondary rounded-full overflow-hidden">
                    <div
                        class="h-full bg-primary transition-all duration-300"
                        :style="{ width: `${progress.percentage}%` }"
                    />
                </div>
                <p class="text-xs text-muted-foreground">
                    {{ progress.completedTopics }}/{{ progress.totalTopics }} topik selesai
                </p>
            </div>
        </CardContent>
        <CardFooter>
            <Link :href="show.url(subject.id)" class="w-full">
                <Button class="w-full" :variant="progress.percentage > 0 ? 'default' : 'outline'">
                    {{ progress.percentage > 0 ? 'Lanjutkan' : 'Mulai Belajar' }}
                </Button>
            </Link>
        </CardFooter>
    </Card>
</template>
