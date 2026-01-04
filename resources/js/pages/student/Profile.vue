<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Form, Head, usePage } from '@inertiajs/vue3';
import { type BreadcrumbItem, type ClassRoom, type LearningStyleProfile } from '@/types';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import ProfileController from '@/actions/App/Http/Controllers/Student/ProfileController';
import { User, GraduationCap, Brain, Phone, Hash } from 'lucide-vue-next';

interface Props {
    currentClass?: ClassRoom;
    learningProfile?: LearningStyleProfile;
    status?: string;
}

defineProps<Props>();

const page = usePage();
const user = page.props.auth.user;

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Profil',
        href: '/profile',
    },
];

const getLearningStyleLabel = (style: string): string => {
    const labels: Record<string, string> = {
        visual: 'Visual',
        auditory: 'Auditori',
        kinesthetic: 'Kinestetik',
    };
    return labels[style] || style;
};

const getLearningStyleColor = (style: string): string => {
    const colors: Record<string, string> = {
        visual: 'bg-blue-500',
        auditory: 'bg-green-500',
        kinesthetic: 'bg-orange-500',
    };
    return colors[style] || 'bg-gray-500';
};
</script>

<template>
    <Head title="Profil Siswa" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <!-- Header -->
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Profil Siswa</h1>
                <p class="text-muted-foreground">
                    Kelola informasi profil dan preferensi belajarmu
                </p>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <!-- Profile Summary Card -->
                <Card class="lg:col-span-1">
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <User class="h-5 w-5" />
                            Ringkasan Profil
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="flex flex-col items-center gap-4">
                            <div class="flex h-20 w-20 items-center justify-center rounded-full bg-primary/10 text-primary">
                                <User class="h-10 w-10" />
                            </div>
                            <div class="text-center">
                                <h3 class="font-semibold">{{ user.name }}</h3>
                                <p class="text-sm text-muted-foreground">{{ user.email }}</p>
                            </div>
                        </div>

                        <div class="space-y-3 pt-4 border-t">
                            <div class="flex items-center gap-2 text-sm">
                                <GraduationCap class="h-4 w-4 text-muted-foreground" />
                                <span v-if="currentClass">
                                    {{ currentClass.name }} - {{ currentClass.academic_year }}
                                </span>
                                <span v-else class="text-muted-foreground">
                                    Belum terdaftar di kelas
                                </span>
                            </div>

                            <div v-if="user.student_id_number" class="flex items-center gap-2 text-sm">
                                <Hash class="h-4 w-4 text-muted-foreground" />
                                <span>NIS: {{ user.student_id_number }}</span>
                            </div>

                            <div v-if="user.phone" class="flex items-center gap-2 text-sm">
                                <Phone class="h-4 w-4 text-muted-foreground" />
                                <span>{{ user.phone }}</span>
                            </div>

                            <div v-if="learningProfile" class="flex items-center gap-2 text-sm">
                                <Brain class="h-4 w-4 text-muted-foreground" />
                                <span>Gaya Belajar:</span>
                                <Badge :class="getLearningStyleColor(learningProfile.dominant_style)">
                                    {{ getLearningStyleLabel(learningProfile.dominant_style) }}
                                </Badge>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Edit Profile Form -->
                <Card class="lg:col-span-2">
                    <CardHeader>
                        <CardTitle>Edit Profil</CardTitle>
                        <CardDescription>
                            Perbarui informasi dasar dan kontak
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <Form
                            v-bind="ProfileController.update.form()"
                            class="space-y-6"
                            v-slot="{ errors, processing, recentlySuccessful }"
                        >
                            <div class="grid gap-4 md:grid-cols-2">
                                <div class="grid gap-2">
                                    <Label for="name">Nama Lengkap</Label>
                                    <Input
                                        id="name"
                                        name="name"
                                        :default-value="user.name"
                                        required
                                        autocomplete="name"
                                        placeholder="Nama lengkap"
                                    />
                                    <InputError :message="errors.name" />
                                </div>

                                <div class="grid gap-2">
                                    <Label for="email">Email</Label>
                                    <Input
                                        id="email"
                                        type="email"
                                        name="email"
                                        :default-value="user.email"
                                        required
                                        autocomplete="email"
                                        placeholder="email@example.com"
                                    />
                                    <InputError :message="errors.email" />
                                </div>

                                <div class="grid gap-2">
                                    <Label for="student_id_number">NIS (Nomor Induk Siswa)</Label>
                                    <Input
                                        id="student_id_number"
                                        name="student_id_number"
                                        :default-value="user.student_id_number || ''"
                                        autocomplete="off"
                                        placeholder="Contoh: 12345678"
                                    />
                                    <InputError :message="errors.student_id_number" />
                                </div>

                                <div class="grid gap-2">
                                    <Label for="phone">Nomor Telepon</Label>
                                    <Input
                                        id="phone"
                                        type="tel"
                                        name="phone"
                                        :default-value="user.phone || ''"
                                        autocomplete="tel"
                                        placeholder="Contoh: 08123456789"
                                    />
                                    <InputError :message="errors.phone" />
                                </div>
                            </div>

                            <div class="flex items-center gap-4">
                                <Button :disabled="processing">
                                    {{ processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                                </Button>

                                <Transition
                                    enter-active-class="transition ease-in-out"
                                    enter-from-class="opacity-0"
                                    leave-active-class="transition ease-in-out"
                                    leave-to-class="opacity-0"
                                >
                                    <p
                                        v-show="recentlySuccessful"
                                        class="text-sm text-green-600"
                                    >
                                        Tersimpan.
                                    </p>
                                </Transition>
                            </div>
                        </Form>
                    </CardContent>
                </Card>
            </div>

            <!-- Learning Interests Section -->
            <Card>
                <CardHeader>
                    <CardTitle>Minat Belajar</CardTitle>
                    <CardDescription>
                        Topik atau bidang yang ingin kamu pelajari lebih dalam
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div v-if="user.learning_interests && user.learning_interests.length > 0" class="flex flex-wrap gap-2">
                        <Badge v-for="interest in user.learning_interests" :key="interest" variant="secondary">
                            {{ interest }}
                        </Badge>
                    </div>
                    <div v-else class="text-sm text-muted-foreground">
                        Belum ada minat belajar yang ditambahkan. Fitur ini akan tersedia setelah mengisi kuesioner.
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
