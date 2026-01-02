<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { index as subjectsIndex, store } from '@/actions/App/Http/Controllers/Admin/SubjectController';
import { ArrowLeft } from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin Dashboard', href: '/admin/dashboard' },
    { title: 'Mata Pelajaran', href: '/admin/subjects' },
    { title: 'Tambah Mata Pelajaran', href: '/admin/subjects/create' },
];

const form = useForm({
    name: '',
    code: '',
    description: '',
});

const submit = () => {
    form.post(store().url);
};
</script>

<template>
    <Head title="Tambah Mata Pelajaran" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <!-- Back Button -->
            <div>
                <Link :href="subjectsIndex().url">
                    <Button variant="ghost" size="sm">
                        <ArrowLeft class="mr-2 h-4 w-4" />
                        Kembali
                    </Button>
                </Link>
            </div>

            <Card class="max-w-2xl">
                <CardHeader>
                    <CardTitle>Tambah Mata Pelajaran Baru</CardTitle>
                    <CardDescription>
                        Daftarkan mata pelajaran baru ke sistem
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Name -->
                        <div class="space-y-2">
                            <Label for="name">Nama Mata Pelajaran *</Label>
                            <Input
                                id="name"
                                v-model="form.name"
                                placeholder="Contoh: Matematika"
                            />
                            <p v-if="form.errors.name" class="text-sm text-destructive">
                                {{ form.errors.name }}
                            </p>
                        </div>

                        <!-- Code -->
                        <div class="space-y-2">
                            <Label for="code">Kode Mata Pelajaran *</Label>
                            <Input
                                id="code"
                                v-model="form.code"
                                placeholder="Contoh: MTK"
                                class="uppercase"
                            />
                            <p class="text-xs text-muted-foreground">
                                Kode unik untuk identifikasi mata pelajaran (maks. 20 karakter)
                            </p>
                            <p v-if="form.errors.code" class="text-sm text-destructive">
                                {{ form.errors.code }}
                            </p>
                        </div>

                        <!-- Description -->
                        <div class="space-y-2">
                            <Label for="description">Deskripsi</Label>
                            <Textarea
                                id="description"
                                v-model="form.description"
                                placeholder="Deskripsi singkat tentang mata pelajaran..."
                                rows="4"
                            />
                            <p v-if="form.errors.description" class="text-sm text-destructive">
                                {{ form.errors.description }}
                            </p>
                        </div>

                        <!-- Submit -->
                        <div class="flex gap-4">
                            <Button type="submit" :disabled="form.processing">
                                {{ form.processing ? 'Menyimpan...' : 'Simpan Mata Pelajaran' }}
                            </Button>
                            <Link :href="subjectsIndex().url">
                                <Button type="button" variant="outline">Batal</Button>
                            </Link>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
