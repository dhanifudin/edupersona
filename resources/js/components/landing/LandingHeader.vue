<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { login, register } from '@/routes';
import { dashboard } from '@/routes';

interface Props {
    canRegister: boolean;
    isAuthenticated: boolean;
}

defineProps<Props>();
</script>

<template>
    <header class="fixed top-0 left-0 right-0 z-50 border-b border-border/40 bg-background/80 backdrop-blur-sm">
        <div class="mx-auto flex h-16 max-w-6xl items-center justify-between px-4 sm:px-6 lg:px-8">
            <!-- Logo -->
            <Link href="/" class="flex items-center gap-2">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-primary text-primary-foreground">
                    <AppLogoIcon class="h-5 w-5" />
                </div>
                <span class="text-lg font-semibold">EduPersona.ai</span>
            </Link>

            <!-- Navigation -->
            <nav class="flex items-center gap-3">
                <template v-if="isAuthenticated">
                    <Link :href="dashboard().url">
                        <Button>Dashboard</Button>
                    </Link>
                </template>
                <template v-else>
                    <Link :href="login().url">
                        <Button variant="ghost">Masuk</Button>
                    </Link>
                    <Link v-if="canRegister" :href="register().url">
                        <Button>Daftar</Button>
                    </Link>
                </template>
            </nav>
        </div>
    </header>
</template>
