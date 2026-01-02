<script setup lang="ts">
import { ref, computed } from 'vue';
import { Button } from '@/components/ui/button';
import { Progress } from '@/components/ui/progress';
import { ChevronRight, ChevronLeft } from 'lucide-vue-next';

interface Question {
    id: number;
    text: string;
    type: 'visual' | 'auditory' | 'kinesthetic';
}

const emit = defineEmits<{
    complete: [scores: { visual: number; auditory: number; kinesthetic: number }];
}>();

const questions: Question[] = [
    {
        id: 1,
        text: 'Saya lebih mudah memahami materi jika ada gambar atau diagram',
        type: 'visual',
    },
    {
        id: 2,
        text: 'Saya lebih suka mendengarkan penjelasan daripada membaca sendiri',
        type: 'auditory',
    },
    {
        id: 3,
        text: 'Saya lebih mudah mengingat sesuatu jika langsung mempraktikkannya',
        type: 'kinesthetic',
    },
];

const likertOptions = [
    { value: 1, label: 'Sangat Tidak Setuju' },
    { value: 2, label: 'Tidak Setuju' },
    { value: 3, label: 'Netral' },
    { value: 4, label: 'Setuju' },
    { value: 5, label: 'Sangat Setuju' },
];

const currentIndex = ref(0);
const answers = ref<Record<number, number>>({});

const currentQuestion = computed(() => questions[currentIndex.value]);
const progressValue = computed(() => ((currentIndex.value + 1) / questions.length) * 100);
const canGoNext = computed(() => answers.value[currentQuestion.value.id] !== undefined);
const canGoPrev = computed(() => currentIndex.value > 0);
const isLastQuestion = computed(() => currentIndex.value === questions.length - 1);

const selectAnswer = (value: number) => {
    answers.value[currentQuestion.value.id] = value;
};

const nextQuestion = () => {
    if (isLastQuestion.value) {
        calculateAndEmit();
    } else {
        currentIndex.value++;
    }
};

const prevQuestion = () => {
    if (canGoPrev.value) {
        currentIndex.value--;
    }
};

const calculateAndEmit = () => {
    const scores = {
        visual: 0,
        auditory: 0,
        kinesthetic: 0,
    };

    questions.forEach((q) => {
        const answer = answers.value[q.id] || 3;
        scores[q.type] = answer * 20; // Convert 1-5 to percentage (20-100)
    });

    emit('complete', scores);
};
</script>

<template>
    <div class="space-y-6">
        <!-- Progress -->
        <div class="space-y-2">
            <div class="flex items-center justify-between text-sm">
                <span class="font-medium">Pertanyaan {{ currentIndex + 1 }}/{{ questions.length }}</span>
                <span class="text-muted-foreground">{{ Math.round(progressValue) }}%</span>
            </div>
            <Progress :model-value="progressValue" class="h-2" />
        </div>

        <!-- Question -->
        <div class="min-h-32">
            <p class="text-lg font-medium leading-relaxed">
                {{ currentQuestion.text }}
            </p>
        </div>

        <!-- Options -->
        <div class="space-y-3">
            <button
                v-for="option in likertOptions"
                :key="option.value"
                type="button"
                :class="[
                    'w-full rounded-lg border p-3 text-left transition-all',
                    answers[currentQuestion.id] === option.value
                        ? 'border-primary bg-primary/10 ring-2 ring-primary'
                        : 'border-border hover:border-primary/50 hover:bg-muted/50',
                ]"
                @click="selectAnswer(option.value)"
            >
                <div class="flex items-center gap-3">
                    <div
                        :class="[
                            'flex h-5 w-5 items-center justify-center rounded-full border-2 transition-all',
                            answers[currentQuestion.id] === option.value
                                ? 'border-primary bg-primary'
                                : 'border-muted-foreground',
                        ]"
                    >
                        <div
                            v-if="answers[currentQuestion.id] === option.value"
                            class="h-2 w-2 rounded-full bg-primary-foreground"
                        />
                    </div>
                    <span class="text-sm">{{ option.label }}</span>
                </div>
            </button>
        </div>

        <!-- Navigation -->
        <div class="flex items-center justify-between pt-4">
            <Button variant="outline" size="sm" :disabled="!canGoPrev" @click="prevQuestion">
                <ChevronLeft class="mr-1 h-4 w-4" />
                Sebelumnya
            </Button>
            <Button size="sm" :disabled="!canGoNext" @click="nextQuestion">
                {{ isLastQuestion ? 'Lihat Hasil' : 'Selanjutnya' }}
                <ChevronRight class="ml-1 h-4 w-4" />
            </Button>
        </div>
    </div>
</template>
