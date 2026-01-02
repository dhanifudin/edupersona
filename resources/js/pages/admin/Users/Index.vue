<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { type BreadcrumbItem, type User } from '@/types';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import {
    show as usersShow,
    create as usersCreate,
    destroy as usersDestroy,
} from '@/actions/App/Http/Controllers/Admin/UserController';
import { Users, Search, Plus, Eye, Pencil, Trash2 } from 'lucide-vue-next';
import { ref, watch } from 'vue';
import { useDebounceFn } from '@vueuse/core';

interface UserWithCounts extends User {
    learning_activities_count: number;
    uploaded_materials_count: number;
}

interface PaginatedUsers {
    data: UserWithCounts[];
    links: { url: string | null; label: string; active: boolean }[];
    current_page: number;
    last_page: number;
    total: number;
}

interface Filters {
    role: string | null;
    search: string | null;
}

interface Props {
    users: PaginatedUsers;
    filters: Filters;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin Dashboard', href: '/admin/dashboard' },
    { title: 'Pengguna', href: '/admin/users' },
];

const searchQuery = ref(props.filters.search || '');
const selectedRole = ref(props.filters.role || '');

const roles = [
    { value: '', label: 'Semua Peran' },
    { value: 'student', label: 'Siswa' },
    { value: 'teacher', label: 'Guru' },
    { value: 'admin', label: 'Admin' },
];

const getRoleLabel = (role: string): string => {
    const labels: Record<string, string> = {
        student: 'Siswa',
        teacher: 'Guru',
        admin: 'Admin',
    };
    return labels[role] || role;
};

const getRoleBadgeVariant = (role: string): 'default' | 'secondary' | 'outline' => {
    if (role === 'admin') return 'default';
    if (role === 'teacher') return 'secondary';
    return 'outline';
};

const applyFilters = () => {
    router.get('/admin/users', {
        role: selectedRole.value || undefined,
        search: searchQuery.value || undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const debouncedSearch = useDebounceFn(() => {
    applyFilters();
}, 300);

watch(searchQuery, () => {
    debouncedSearch();
});

watch(selectedRole, () => {
    applyFilters();
});

const clearFilters = () => {
    searchQuery.value = '';
    selectedRole.value = '';
    router.get('/admin/users');
};

const deleteUser = (user: UserWithCounts) => {
    if (confirm(`Apakah Anda yakin ingin menghapus pengguna "${user.name}"?`)) {
        router.delete(usersDestroy(user.id).url);
    }
};
</script>

<template>
    <Head title="Kelola Pengguna" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <!-- Header -->
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Kelola Pengguna</h1>
                    <p class="text-muted-foreground">
                        {{ users.total }} pengguna terdaftar
                    </p>
                </div>
                <Link :href="usersCreate().url">
                    <Button>
                        <Plus class="mr-2 h-4 w-4" />
                        Tambah Pengguna
                    </Button>
                </Link>
            </div>

            <!-- Filters -->
            <Card>
                <CardContent class="p-4">
                    <div class="flex flex-col gap-4 md:flex-row md:items-center">
                        <div class="relative flex-1">
                            <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                            <Input
                                v-model="searchQuery"
                                placeholder="Cari nama, email, NIS, atau NIP..."
                                class="pl-9"
                            />
                        </div>
                        <Select v-model="selectedRole">
                            <SelectTrigger class="w-full md:w-48">
                                <SelectValue placeholder="Semua Peran" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="role in roles"
                                    :key="role.value"
                                    :value="role.value"
                                >
                                    {{ role.label }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <Button
                            v-if="searchQuery || selectedRole"
                            variant="ghost"
                            size="sm"
                            @click="clearFilters"
                        >
                            Reset
                        </Button>
                    </div>
                </CardContent>
            </Card>

            <!-- Users Table -->
            <Card>
                <CardContent class="p-0">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b bg-muted/50">
                                    <th class="px-4 py-3 text-left text-sm font-medium">Nama</th>
                                    <th class="px-4 py-3 text-left text-sm font-medium">Email</th>
                                    <th class="px-4 py-3 text-left text-sm font-medium">Peran</th>
                                    <th class="px-4 py-3 text-left text-sm font-medium">ID</th>
                                    <th class="px-4 py-3 text-right text-sm font-medium">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="user in users.data"
                                    :key="user.id"
                                    class="border-b hover:bg-muted/50 transition-colors"
                                >
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-primary/10 text-primary font-semibold">
                                                {{ user.name.charAt(0).toUpperCase() }}
                                            </div>
                                            <div>
                                                <p class="font-medium">{{ user.name }}</p>
                                                <p v-if="user.phone" class="text-sm text-muted-foreground">
                                                    {{ user.phone }}
                                                </p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-sm">{{ user.email }}</td>
                                    <td class="px-4 py-3">
                                        <Badge :variant="getRoleBadgeVariant(user.role)">
                                            {{ getRoleLabel(user.role) }}
                                        </Badge>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-muted-foreground">
                                        <template v-if="user.role === 'student'">
                                            {{ user.student_id_number || '-' }}
                                        </template>
                                        <template v-else-if="user.role === 'teacher'">
                                            {{ user.teacher_id_number || '-' }}
                                        </template>
                                        <template v-else>-</template>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex justify-end gap-2">
                                            <Link :href="usersShow(user.id).url">
                                                <Button variant="ghost" size="sm">
                                                    <Eye class="h-4 w-4" />
                                                </Button>
                                            </Link>
                                            <Link :href="`/admin/users/${user.id}/edit`">
                                                <Button variant="ghost" size="sm">
                                                    <Pencil class="h-4 w-4" />
                                                </Button>
                                            </Link>
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                class="text-destructive hover:text-destructive"
                                                @click="deleteUser(user)"
                                            >
                                                <Trash2 class="h-4 w-4" />
                                            </Button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Empty State -->
                    <div v-if="users.data.length === 0" class="flex flex-col items-center justify-center py-12">
                        <Users class="h-12 w-12 text-muted-foreground/50" />
                        <h3 class="mt-4 text-lg font-semibold">Tidak Ada Pengguna</h3>
                        <p class="mt-2 text-center text-sm text-muted-foreground max-w-md">
                            <template v-if="searchQuery || selectedRole">
                                Tidak ada pengguna yang cocok dengan filter yang dipilih.
                            </template>
                            <template v-else>
                                Belum ada pengguna terdaftar.
                            </template>
                        </p>
                        <div class="mt-4 flex gap-2">
                            <Button
                                v-if="searchQuery || selectedRole"
                                variant="outline"
                                @click="clearFilters"
                            >
                                Reset Filter
                            </Button>
                            <Link :href="usersCreate().url">
                                <Button>Tambah Pengguna</Button>
                            </Link>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Pagination -->
            <div v-if="users.last_page > 1" class="flex justify-center gap-2">
                <template v-for="link in users.links" :key="link.label">
                    <Link
                        v-if="link.url"
                        :href="link.url"
                        :class="[
                            'px-3 py-2 text-sm rounded-md transition-colors',
                            link.active
                                ? 'bg-primary text-primary-foreground'
                                : 'bg-muted hover:bg-accent',
                        ]"
                        v-html="link.label"
                    />
                    <span
                        v-else
                        class="px-3 py-2 text-sm text-muted-foreground"
                        v-html="link.label"
                    />
                </template>
            </div>
        </div>
    </AppLayout>
</template>
