<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { index as usersIndex, store } from '@/actions/App/Http/Controllers/Admin/UserController';
import { ArrowLeft } from 'lucide-vue-next';
import { watch } from 'vue';

interface ClassRoom {
    id: number;
    name: string;
    grade_level: string;
}

interface Props {
    classes: ClassRoom[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin Dashboard', href: '/admin/dashboard' },
    { title: 'Pengguna', href: '/admin/users' },
    { title: 'Tambah Pengguna', href: '/admin/users/create' },
];

const form = useForm({
    name: '',
    email: '',
    password: '',
    role: '',
    student_id_number: '',
    teacher_id_number: '',
    phone: '',
    class_id: '',
});

watch(() => form.role, () => {
    if (form.role !== 'student') {
        form.student_id_number = '';
        form.class_id = '';
    }
    if (form.role !== 'teacher') {
        form.teacher_id_number = '';
    }
});

const submit = () => {
    form.post(store().url);
};
</script>

<template>
    <Head title="Tambah Pengguna" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <!-- Back Button -->
            <div>
                <Link :href="usersIndex().url">
                    <Button variant="ghost" size="sm">
                        <ArrowLeft class="mr-2 h-4 w-4" />
                        Kembali
                    </Button>
                </Link>
            </div>

            <Card class="max-w-2xl">
                <CardHeader>
                    <CardTitle>Tambah Pengguna Baru</CardTitle>
                    <CardDescription>
                        Daftarkan pengguna baru ke sistem
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Name -->
                        <div class="space-y-2">
                            <Label for="name">Nama Lengkap *</Label>
                            <Input
                                id="name"
                                v-model="form.name"
                                placeholder="Masukkan nama lengkap"
                            />
                            <p v-if="form.errors.name" class="text-sm text-destructive">
                                {{ form.errors.name }}
                            </p>
                        </div>

                        <!-- Email -->
                        <div class="space-y-2">
                            <Label for="email">Email *</Label>
                            <Input
                                id="email"
                                v-model="form.email"
                                type="email"
                                placeholder="contoh@email.com"
                            />
                            <p v-if="form.errors.email" class="text-sm text-destructive">
                                {{ form.errors.email }}
                            </p>
                        </div>

                        <!-- Password -->
                        <div class="space-y-2">
                            <Label for="password">Password *</Label>
                            <Input
                                id="password"
                                v-model="form.password"
                                type="password"
                                placeholder="Minimal 8 karakter"
                            />
                            <p v-if="form.errors.password" class="text-sm text-destructive">
                                {{ form.errors.password }}
                            </p>
                        </div>

                        <!-- Role -->
                        <div class="space-y-2">
                            <Label>Peran *</Label>
                            <Select v-model="form.role">
                                <SelectTrigger>
                                    <SelectValue placeholder="Pilih peran" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="student">Siswa</SelectItem>
                                    <SelectItem value="teacher">Guru</SelectItem>
                                    <SelectItem value="admin">Admin</SelectItem>
                                </SelectContent>
                            </Select>
                            <p v-if="form.errors.role" class="text-sm text-destructive">
                                {{ form.errors.role }}
                            </p>
                        </div>

                        <!-- Student ID Number -->
                        <div v-if="form.role === 'student'" class="space-y-2">
                            <Label for="student_id_number">NIS (Nomor Induk Siswa)</Label>
                            <Input
                                id="student_id_number"
                                v-model="form.student_id_number"
                                placeholder="Masukkan NIS"
                            />
                            <p v-if="form.errors.student_id_number" class="text-sm text-destructive">
                                {{ form.errors.student_id_number }}
                            </p>
                        </div>

                        <!-- Class (for students) -->
                        <div v-if="form.role === 'student'" class="space-y-2">
                            <Label>Kelas</Label>
                            <Select v-model="form.class_id">
                                <SelectTrigger>
                                    <SelectValue placeholder="Pilih kelas" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="">Belum ada kelas</SelectItem>
                                    <SelectItem
                                        v-for="classRoom in classes"
                                        :key="classRoom.id"
                                        :value="classRoom.id.toString()"
                                    >
                                        {{ classRoom.name }} ({{ classRoom.grade_level }})
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <p v-if="form.errors.class_id" class="text-sm text-destructive">
                                {{ form.errors.class_id }}
                            </p>
                        </div>

                        <!-- Teacher ID Number -->
                        <div v-if="form.role === 'teacher'" class="space-y-2">
                            <Label for="teacher_id_number">NIP (Nomor Induk Pegawai)</Label>
                            <Input
                                id="teacher_id_number"
                                v-model="form.teacher_id_number"
                                placeholder="Masukkan NIP"
                            />
                            <p v-if="form.errors.teacher_id_number" class="text-sm text-destructive">
                                {{ form.errors.teacher_id_number }}
                            </p>
                        </div>

                        <!-- Phone -->
                        <div class="space-y-2">
                            <Label for="phone">Nomor Telepon</Label>
                            <Input
                                id="phone"
                                v-model="form.phone"
                                placeholder="08xxxxxxxxxx"
                            />
                            <p v-if="form.errors.phone" class="text-sm text-destructive">
                                {{ form.errors.phone }}
                            </p>
                        </div>

                        <!-- Submit -->
                        <div class="flex gap-4">
                            <Button type="submit" :disabled="form.processing">
                                {{ form.processing ? 'Menyimpan...' : 'Simpan Pengguna' }}
                            </Button>
                            <Link :href="usersIndex().url">
                                <Button type="button" variant="outline">Batal</Button>
                            </Link>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
