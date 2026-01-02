<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { type BreadcrumbItem, type LearningMaterial } from '@/types';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';
import { index as materialsIndex, update } from '@/actions/App/Http/Controllers/Teacher/MaterialController';
import { ArrowLeft, Upload } from 'lucide-vue-next';
import { ref } from 'vue';

interface Subject {
    id: number;
    name: string;
    code: string;
}

interface ClassRoom {
    id: number;
    name: string;
    grade_level: string;
    major: string;
}

interface SelectOption {
    value: string;
    label: string;
}

interface Props {
    material: LearningMaterial;
    subjects: Subject[];
    classes: ClassRoom[];
    materialTypes: SelectOption[];
    learningStyles: SelectOption[];
    difficultyLevels: SelectOption[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/teacher/dashboard' },
    { title: 'Materi', href: '/teacher/materials' },
    { title: 'Edit Materi', href: `/teacher/materials/${props.material.id}/edit` },
];

const form = useForm({
    _method: 'PUT',
    subject_id: props.material.subject_id?.toString() || '',
    class_id: props.material.class_id?.toString() || '',
    title: props.material.title,
    description: props.material.description || '',
    topic: props.material.topic || '',
    type: props.material.type,
    learning_style: props.material.learning_style,
    difficulty_level: props.material.difficulty_level,
    content_url: props.material.content_url || '',
    file: null as File | null,
    is_active: props.material.is_active,
});

const fileInput = ref<HTMLInputElement | null>(null);
const fileName = ref(props.material.file_path ? 'File tersimpan' : '');

const handleFileChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        form.file = target.files[0];
        fileName.value = target.files[0].name;
    }
};

const submit = () => {
    form.post(update(props.material.id).url, {
        forceFormData: true,
    });
};
</script>

<template>
    <Head title="Edit Materi" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <!-- Back Button -->
            <div>
                <Link :href="materialsIndex().url">
                    <Button variant="ghost" size="sm">
                        <ArrowLeft class="mr-2 h-4 w-4" />
                        Kembali
                    </Button>
                </Link>
            </div>

            <Card class="max-w-2xl">
                <CardHeader>
                    <CardTitle>Edit Materi</CardTitle>
                    <CardDescription>
                        Perbarui informasi materi pembelajaran
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Subject -->
                        <div class="space-y-2">
                            <Label for="subject_id">Mata Pelajaran *</Label>
                            <Select v-model="form.subject_id">
                                <SelectTrigger>
                                    <SelectValue placeholder="Pilih mata pelajaran" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem
                                        v-for="subject in subjects"
                                        :key="subject.id"
                                        :value="subject.id.toString()"
                                    >
                                        {{ subject.name }} ({{ subject.code }})
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <p v-if="form.errors.subject_id" class="text-sm text-destructive">
                                {{ form.errors.subject_id }}
                            </p>
                        </div>

                        <!-- Class (Optional) -->
                        <div class="space-y-2">
                            <Label for="class_id">Kelas (Opsional)</Label>
                            <Select v-model="form.class_id">
                                <SelectTrigger>
                                    <SelectValue placeholder="Semua kelas" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="">Semua Kelas</SelectItem>
                                    <SelectItem
                                        v-for="classRoom in classes"
                                        :key="classRoom.id"
                                        :value="classRoom.id.toString()"
                                    >
                                        {{ classRoom.name }} ({{ classRoom.grade_level }})
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <!-- Title -->
                        <div class="space-y-2">
                            <Label for="title">Judul Materi *</Label>
                            <Input
                                id="title"
                                v-model="form.title"
                                placeholder="Masukkan judul materi"
                            />
                            <p v-if="form.errors.title" class="text-sm text-destructive">
                                {{ form.errors.title }}
                            </p>
                        </div>

                        <!-- Topic -->
                        <div class="space-y-2">
                            <Label for="topic">Topik</Label>
                            <Input
                                id="topic"
                                v-model="form.topic"
                                placeholder="Contoh: Persamaan Linear"
                            />
                        </div>

                        <!-- Description -->
                        <div class="space-y-2">
                            <Label for="description">Deskripsi</Label>
                            <Textarea
                                id="description"
                                v-model="form.description"
                                placeholder="Jelaskan isi materi ini..."
                                rows="4"
                            />
                        </div>

                        <!-- Type, Style, Difficulty -->
                        <div class="grid gap-4 md:grid-cols-3">
                            <div class="space-y-2">
                                <Label>Tipe Materi *</Label>
                                <Select v-model="form.type">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Pilih tipe" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem
                                            v-for="type in materialTypes"
                                            :key="type.value"
                                            :value="type.value"
                                        >
                                            {{ type.label }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <p v-if="form.errors.type" class="text-sm text-destructive">
                                    {{ form.errors.type }}
                                </p>
                            </div>

                            <div class="space-y-2">
                                <Label>Gaya Belajar *</Label>
                                <Select v-model="form.learning_style">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Pilih gaya" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem
                                            v-for="style in learningStyles"
                                            :key="style.value"
                                            :value="style.value"
                                        >
                                            {{ style.label }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <p v-if="form.errors.learning_style" class="text-sm text-destructive">
                                    {{ form.errors.learning_style }}
                                </p>
                            </div>

                            <div class="space-y-2">
                                <Label>Tingkat Kesulitan *</Label>
                                <Select v-model="form.difficulty_level">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Pilih tingkat" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem
                                            v-for="level in difficultyLevels"
                                            :key="level.value"
                                            :value="level.value"
                                        >
                                            {{ level.label }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <p v-if="form.errors.difficulty_level" class="text-sm text-destructive">
                                    {{ form.errors.difficulty_level }}
                                </p>
                            </div>
                        </div>

                        <!-- Content URL -->
                        <div class="space-y-2">
                            <Label for="content_url">URL Konten</Label>
                            <Input
                                id="content_url"
                                v-model="form.content_url"
                                type="url"
                                placeholder="https://..."
                            />
                            <p class="text-xs text-muted-foreground">
                                Link ke video YouTube, dokumen online, atau konten eksternal lainnya
                            </p>
                            <p v-if="form.errors.content_url" class="text-sm text-destructive">
                                {{ form.errors.content_url }}
                            </p>
                        </div>

                        <!-- File Upload -->
                        <div class="space-y-2">
                            <Label>Unggah File</Label>
                            <div class="flex items-center gap-4">
                                <Button
                                    type="button"
                                    variant="outline"
                                    @click="fileInput?.click()"
                                >
                                    <Upload class="mr-2 h-4 w-4" />
                                    Pilih File
                                </Button>
                                <span v-if="fileName" class="text-sm text-muted-foreground">
                                    {{ fileName }}
                                </span>
                                <input
                                    ref="fileInput"
                                    type="file"
                                    class="hidden"
                                    @change="handleFileChange"
                                />
                            </div>
                            <p class="text-xs text-muted-foreground">
                                Kosongkan jika tidak ingin mengubah file. Maksimal 50MB.
                            </p>
                            <p v-if="form.errors.file" class="text-sm text-destructive">
                                {{ form.errors.file }}
                            </p>
                        </div>

                        <!-- Active Status -->
                        <div class="flex items-center gap-3">
                            <Switch
                                id="is_active"
                                :checked="form.is_active"
                                @update:checked="form.is_active = $event"
                            />
                            <Label for="is_active">Aktifkan materi (dapat dilihat siswa)</Label>
                        </div>

                        <!-- Submit -->
                        <div class="flex gap-4">
                            <Button type="submit" :disabled="form.processing">
                                {{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                            </Button>
                            <Link :href="materialsIndex().url">
                                <Button type="button" variant="outline">Batal</Button>
                            </Link>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
