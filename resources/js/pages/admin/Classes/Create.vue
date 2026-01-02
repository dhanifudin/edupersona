<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { type BreadcrumbItem, type User } from '@/types';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';
import { index as classesIndex, store } from '@/actions/App/Http/Controllers/Admin/ClassController';
import { ArrowLeft } from 'lucide-vue-next';

interface Teacher extends User {
    teacher_id_number?: string;
}

interface Props {
    teachers: Teacher[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin Dashboard', href: '/admin/dashboard' },
    { title: 'Kelas', href: '/admin/classes' },
    { title: 'Tambah Kelas', href: '/admin/classes/create' },
];

const currentYear = new Date().getFullYear();
const defaultAcademicYear = `${currentYear}/${currentYear + 1}`;

const form = useForm({
    name: '',
    grade_level: '',
    major: '',
    academic_year: defaultAcademicYear,
    homeroom_teacher_id: '',
    is_active: true,
});

const submit = () => {
    form.post(store().url);
};
</script>

<template>
    <Head title="Tambah Kelas" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <!-- Back Button -->
            <div>
                <Link :href="classesIndex().url">
                    <Button variant="ghost" size="sm">
                        <ArrowLeft class="mr-2 h-4 w-4" />
                        Kembali
                    </Button>
                </Link>
            </div>

            <Card class="max-w-2xl">
                <CardHeader>
                    <CardTitle>Tambah Kelas Baru</CardTitle>
                    <CardDescription>
                        Buat kelas baru untuk tahun ajaran
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Name -->
                        <div class="space-y-2">
                            <Label for="name">Nama Kelas *</Label>
                            <Input
                                id="name"
                                v-model="form.name"
                                placeholder="Contoh: X IPA 1"
                            />
                            <p v-if="form.errors.name" class="text-sm text-destructive">
                                {{ form.errors.name }}
                            </p>
                        </div>

                        <!-- Grade Level -->
                        <div class="space-y-2">
                            <Label>Tingkat *</Label>
                            <Select v-model="form.grade_level">
                                <SelectTrigger>
                                    <SelectValue placeholder="Pilih tingkat" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="X">Kelas X</SelectItem>
                                    <SelectItem value="XI">Kelas XI</SelectItem>
                                    <SelectItem value="XII">Kelas XII</SelectItem>
                                </SelectContent>
                            </Select>
                            <p v-if="form.errors.grade_level" class="text-sm text-destructive">
                                {{ form.errors.grade_level }}
                            </p>
                        </div>

                        <!-- Major -->
                        <div class="space-y-2">
                            <Label for="major">Jurusan</Label>
                            <Input
                                id="major"
                                v-model="form.major"
                                placeholder="Contoh: IPA, IPS, Bahasa"
                            />
                            <p v-if="form.errors.major" class="text-sm text-destructive">
                                {{ form.errors.major }}
                            </p>
                        </div>

                        <!-- Academic Year -->
                        <div class="space-y-2">
                            <Label for="academic_year">Tahun Ajaran *</Label>
                            <Input
                                id="academic_year"
                                v-model="form.academic_year"
                                placeholder="2024/2025"
                            />
                            <p class="text-xs text-muted-foreground">
                                Format: YYYY/YYYY (contoh: 2024/2025)
                            </p>
                            <p v-if="form.errors.academic_year" class="text-sm text-destructive">
                                {{ form.errors.academic_year }}
                            </p>
                        </div>

                        <!-- Homeroom Teacher -->
                        <div class="space-y-2">
                            <Label>Wali Kelas</Label>
                            <Select v-model="form.homeroom_teacher_id">
                                <SelectTrigger>
                                    <SelectValue placeholder="Pilih wali kelas" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="">Tidak ada</SelectItem>
                                    <SelectItem
                                        v-for="teacher in teachers"
                                        :key="teacher.id"
                                        :value="teacher.id.toString()"
                                    >
                                        {{ teacher.name }}
                                        <span v-if="teacher.teacher_id_number" class="text-muted-foreground">
                                            ({{ teacher.teacher_id_number }})
                                        </span>
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <p v-if="form.errors.homeroom_teacher_id" class="text-sm text-destructive">
                                {{ form.errors.homeroom_teacher_id }}
                            </p>
                        </div>

                        <!-- Active Status -->
                        <div class="flex items-center gap-3">
                            <Switch
                                id="is_active"
                                :checked="form.is_active"
                                @update:checked="form.is_active = $event"
                            />
                            <Label for="is_active">Kelas aktif</Label>
                        </div>

                        <!-- Submit -->
                        <div class="flex gap-4">
                            <Button type="submit" :disabled="form.processing">
                                {{ form.processing ? 'Menyimpan...' : 'Simpan Kelas' }}
                            </Button>
                            <Link :href="classesIndex().url">
                                <Button type="button" variant="outline">Batal</Button>
                            </Link>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
