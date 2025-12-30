<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { type BreadcrumbItem, type LearningStyleQuestion } from '@/types';
import { Card, CardContent, CardDescription, CardHeader, CardTitle, CardFooter } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Progress } from '@/components/ui/progress';
import { store } from '@/actions/App/Http/Controllers/Student/QuestionnaireController';
import { ref, computed } from 'vue';
import { ClipboardList, ChevronLeft, ChevronRight, Check } from 'lucide-vue-next';

interface Props {
    questions: LearningStyleQuestion[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/student/dashboard',
    },
    {
        title: 'Kuesioner',
        href: '/student/questionnaire',
    },
];

// State
const currentQuestionIndex = ref(0);
const responses = ref<Record<number, number>>({});
const isSubmitting = ref(false);

// Computed
const currentQuestion = computed(() => props.questions[currentQuestionIndex.value]);
const totalQuestions = computed(() => props.questions.length);
const progress = computed(() => ((currentQuestionIndex.value + 1) / totalQuestions.value) * 100);
const answeredCount = computed(() => Object.keys(responses.value).length);
const isLastQuestion = computed(() => currentQuestionIndex.value === totalQuestions.value - 1);
const isFirstQuestion = computed(() => currentQuestionIndex.value === 0);
const allAnswered = computed(() => answeredCount.value === totalQuestions.value);
const currentAnswer = computed(() => responses.value[currentQuestion.value?.id]);

// Likert scale options
const likertOptions = [
    { value: 1, label: 'Sangat Tidak Setuju' },
    { value: 2, label: 'Tidak Setuju' },
    { value: 3, label: 'Netral' },
    { value: 4, label: 'Setuju' },
    { value: 5, label: 'Sangat Setuju' },
];

// Methods
const selectAnswer = (score: number) => {
    if (currentQuestion.value) {
        responses.value[currentQuestion.value.id] = score;
    }
};

const nextQuestion = () => {
    if (!isLastQuestion.value) {
        currentQuestionIndex.value++;
    }
};

const prevQuestion = () => {
    if (!isFirstQuestion.value) {
        currentQuestionIndex.value--;
    }
};

const goToQuestion = (index: number) => {
    currentQuestionIndex.value = index;
};

const submitQuestionnaire = () => {
    if (!allAnswered.value || isSubmitting.value) return;

    isSubmitting.value = true;

    const formattedResponses = Object.entries(responses.value).map(([questionId, score]) => ({
        question_id: parseInt(questionId),
        score: score,
    }));

    router.post(store().url, { responses: formattedResponses }, {
        onFinish: () => {
            isSubmitting.value = false;
        },
    });
};
</script>

<template>
    <Head title="Kuesioner Gaya Belajar" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <!-- Header -->
            <div class="flex items-center gap-3">
                <div class="rounded-full bg-primary/10 p-3">
                    <ClipboardList class="h-6 w-6 text-primary" />
                </div>
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Kuesioner Gaya Belajar</h1>
                    <p class="text-muted-foreground">
                        Jawab setiap pertanyaan sesuai dengan preferensi belajarmu
                    </p>
                </div>
            </div>

            <!-- Progress Bar -->
            <Card>
                <CardContent class="pt-6">
                    <div class="flex items-center justify-between text-sm mb-2">
                        <span>Pertanyaan {{ currentQuestionIndex + 1 }} dari {{ totalQuestions }}</span>
                        <span>{{ answeredCount }} terjawab</span>
                    </div>
                    <Progress :model-value="progress" class="h-2" />
                </CardContent>
            </Card>

            <!-- Question Card -->
            <Card class="min-h-[400px]">
                <CardHeader>
                    <CardTitle class="text-lg">
                        Pertanyaan {{ currentQuestionIndex + 1 }}
                    </CardTitle>
                    <CardDescription class="text-base text-foreground mt-4">
                        {{ currentQuestion?.question_text }}
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="space-y-3">
                        <Label class="text-sm text-muted-foreground mb-4 block">
                            Pilih jawaban yang paling sesuai dengan dirimu:
                        </Label>
                        <div class="grid gap-3">
                            <button
                                v-for="option in likertOptions"
                                :key="option.value"
                                type="button"
                                @click="selectAnswer(option.value)"
                                :class="[
                                    'flex items-center gap-3 rounded-lg border p-4 text-left transition-all hover:bg-accent',
                                    currentAnswer === option.value
                                        ? 'border-primary bg-primary/5 ring-2 ring-primary'
                                        : 'border-input',
                                ]"
                            >
                                <div
                                    :class="[
                                        'flex h-6 w-6 shrink-0 items-center justify-center rounded-full border-2 transition-colors',
                                        currentAnswer === option.value
                                            ? 'border-primary bg-primary text-primary-foreground'
                                            : 'border-muted-foreground/30',
                                    ]"
                                >
                                    <Check v-if="currentAnswer === option.value" class="h-4 w-4" />
                                    <span v-else class="text-xs text-muted-foreground">{{ option.value }}</span>
                                </div>
                                <span :class="currentAnswer === option.value ? 'font-medium' : ''">
                                    {{ option.label }}
                                </span>
                            </button>
                        </div>
                    </div>
                </CardContent>
                <CardFooter class="flex justify-between">
                    <Button
                        variant="outline"
                        @click="prevQuestion"
                        :disabled="isFirstQuestion"
                    >
                        <ChevronLeft class="mr-2 h-4 w-4" />
                        Sebelumnya
                    </Button>

                    <Button
                        v-if="!isLastQuestion"
                        @click="nextQuestion"
                        :disabled="!currentAnswer"
                    >
                        Selanjutnya
                        <ChevronRight class="ml-2 h-4 w-4" />
                    </Button>

                    <Button
                        v-else
                        @click="submitQuestionnaire"
                        :disabled="!allAnswered || isSubmitting"
                    >
                        <template v-if="isSubmitting">
                            Menyimpan...
                        </template>
                        <template v-else>
                            <Check class="mr-2 h-4 w-4" />
                            Selesai
                        </template>
                    </Button>
                </CardFooter>
            </Card>

            <!-- Question Navigator -->
            <Card>
                <CardHeader>
                    <CardTitle class="text-sm font-medium">Navigasi Pertanyaan</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="flex flex-wrap gap-2">
                        <button
                            v-for="(question, index) in questions"
                            :key="question.id"
                            type="button"
                            @click="goToQuestion(index)"
                            :class="[
                                'flex h-10 w-10 items-center justify-center rounded-lg text-sm font-medium transition-all',
                                currentQuestionIndex === index
                                    ? 'bg-primary text-primary-foreground'
                                    : responses[question.id]
                                        ? 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300'
                                        : 'bg-muted text-muted-foreground hover:bg-accent',
                            ]"
                        >
                            {{ index + 1 }}
                        </button>
                    </div>
                    <div class="mt-4 flex gap-4 text-xs text-muted-foreground">
                        <div class="flex items-center gap-2">
                            <div class="h-3 w-3 rounded bg-primary"></div>
                            <span>Saat ini</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="h-3 w-3 rounded bg-green-100 dark:bg-green-900"></div>
                            <span>Terjawab</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="h-3 w-3 rounded bg-muted"></div>
                            <span>Belum dijawab</span>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
