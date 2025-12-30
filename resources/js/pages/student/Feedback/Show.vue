<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { type BreadcrumbItem, type AiFeedback } from '@/types';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { index as feedbackIndex } from '@/actions/App/Http/Controllers/Student/FeedbackController';
import { ArrowLeft, Clock, Sparkles } from 'lucide-vue-next';
import { computed } from 'vue';

interface Props {
    feedback: AiFeedback;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/student/dashboard' },
    { title: 'Umpan Balik AI', href: '/student/feedback' },
    { title: 'Detail', href: `/student/feedback/${props.feedback.id}` },
];

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

// Parse markdown-like content
const formattedContent = computed(() => {
    let content = props.feedback.feedback_text;

    // Convert headers
    content = content.replace(/^### (.+)$/gm, '<h3 class="text-lg font-semibold mt-6 mb-2">$1</h3>');
    content = content.replace(/^## (.+)$/gm, '<h2 class="text-xl font-bold mt-6 mb-3">$1</h2>');
    content = content.replace(/^# (.+)$/gm, '<h1 class="text-2xl font-bold mt-6 mb-4">$1</h1>');

    // Convert bold
    content = content.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');

    // Convert italic
    content = content.replace(/\*(.+?)\*/g, '<em>$1</em>');

    // Convert bullet points
    content = content.replace(/^- (.+)$/gm, '<li class="ml-4">$1</li>');

    // Wrap consecutive li elements in ul
    content = content.replace(/(<li[^>]*>.*<\/li>\n?)+/g, '<ul class="list-disc space-y-1 my-3">$&</ul>');

    // Convert line breaks
    content = content.replace(/\n\n/g, '</p><p class="my-3">');
    content = content.replace(/\n/g, '<br>');

    // Wrap in paragraph if not already wrapped
    if (!content.startsWith('<')) {
        content = '<p class="my-3">' + content + '</p>';
    }

    return content;
});
</script>

<template>
    <Head title="Detail Umpan Balik" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <!-- Back Button -->
            <div>
                <Link :href="feedbackIndex().url">
                    <Button variant="ghost" size="sm">
                        <ArrowLeft class="mr-2 h-4 w-4" />
                        Kembali ke Daftar
                    </Button>
                </Link>
            </div>

            <!-- Feedback Content -->
            <Card>
                <CardHeader>
                    <div class="flex items-center gap-3">
                        <div class="rounded-lg bg-primary/10 p-3">
                            <Sparkles class="h-6 w-6 text-primary" />
                        </div>
                        <div>
                            <CardTitle class="text-xl">Umpan Balik Pembelajaran</CardTitle>
                            <p class="text-sm text-muted-foreground flex items-center gap-1 mt-1">
                                <Clock class="h-3 w-3" />
                                {{ formatDate(feedback.generated_at) }}
                            </p>
                        </div>
                    </div>
                </CardHeader>
                <CardContent>
                    <div
                        class="prose prose-sm dark:prose-invert max-w-none"
                        v-html="formattedContent"
                    />
                </CardContent>
            </Card>

            <!-- Action Buttons -->
            <div class="flex gap-4">
                <Link :href="feedbackIndex().url">
                    <Button variant="outline">
                        Lihat Semua Umpan Balik
                    </Button>
                </Link>
                <Link href="/student/materials">
                    <Button>
                        Jelajahi Materi
                    </Button>
                </Link>
            </div>
        </div>
    </AppLayout>
</template>
