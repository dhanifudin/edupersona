<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';
import { enroll } from '@/actions/App/Http/Controllers/Student/SubjectEnrollmentController';

interface AvailableSubject {
    id: number;
    name: string;
    code: string;
    description?: string;
    materials_count: number;
    topic_count: number;
}

interface Props {
    open: boolean;
    availableSubjects: AvailableSubject[];
}

const props = defineProps<Props>();
const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
}>();

const selectedSubjects = ref<number[]>([]);
const isSubmitting = ref(false);

const toggleSubject = (id: number) => {
    const index = selectedSubjects.value.indexOf(id);
    if (index === -1) {
        selectedSubjects.value.push(id);
    } else {
        selectedSubjects.value.splice(index, 1);
    }
};

const handleEnroll = async () => {
    if (selectedSubjects.value.length === 0) return;

    isSubmitting.value = true;

    for (const subjectId of selectedSubjects.value) {
        await router.post(enroll.url(subjectId), {}, {
            preserveState: true,
            preserveScroll: true,
        });
    }

    selectedSubjects.value = [];
    isSubmitting.value = false;
    emit('update:open', false);
};

const handleClose = () => {
    selectedSubjects.value = [];
    emit('update:open', false);
};
</script>

<template>
    <Dialog :open="open" @update:open="handleClose">
        <DialogContent class="sm:max-w-lg">
            <DialogHeader>
                <DialogTitle>Tambah Mata Pelajaran Pilihan</DialogTitle>
                <DialogDescription>
                    Pilih mata pelajaran yang ingin kamu pelajari.
                </DialogDescription>
            </DialogHeader>

            <div class="max-h-[300px] overflow-y-auto space-y-3 py-4">
                <div
                    v-for="subject in availableSubjects"
                    :key="subject.id"
                    class="flex items-start gap-3 p-3 rounded-lg border hover:bg-muted/50 cursor-pointer transition-colors"
                    :class="{ 'border-primary bg-primary/5': selectedSubjects.includes(subject.id) }"
                    @click="toggleSubject(subject.id)"
                >
                    <Checkbox
                        :id="`subject-${subject.id}`"
                        :checked="selectedSubjects.includes(subject.id)"
                        @click.stop
                        @update:checked="toggleSubject(subject.id)"
                    />
                    <div class="flex-1">
                        <Label :for="`subject-${subject.id}`" class="font-medium cursor-pointer">
                            {{ subject.name }}
                        </Label>
                        <p v-if="subject.description" class="text-sm text-muted-foreground line-clamp-1">
                            {{ subject.description }}
                        </p>
                        <p class="text-xs text-muted-foreground mt-1">
                            {{ subject.topic_count }} topik &bull; {{ subject.materials_count }} materi
                        </p>
                    </div>
                </div>

                <div v-if="availableSubjects.length === 0" class="text-center py-8 text-muted-foreground">
                    <p>Tidak ada mata pelajaran pilihan yang tersedia.</p>
                </div>
            </div>

            <DialogFooter>
                <Button variant="outline" @click="handleClose">
                    Batal
                </Button>
                <Button
                    :disabled="selectedSubjects.length === 0 || isSubmitting"
                    @click="handleEnroll"
                >
                    {{ isSubmitting ? 'Mendaftar...' : 'Daftar Sekarang' }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
