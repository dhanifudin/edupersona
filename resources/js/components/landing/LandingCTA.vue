<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { register, login } from '@/routes';
import { ArrowRight } from 'lucide-vue-next';

interface Props {
    canRegister: boolean;
}

defineProps<Props>();
</script>

<template>
    <section class="py-20 px-4">
        <div class="mx-auto max-w-4xl">
            <div class="rounded-2xl bg-gradient-to-r from-primary/10 via-primary/5 to-secondary/10 p-8 text-center sm:p-12">
                <h2 class="mb-4 text-3xl font-bold tracking-tight sm:text-4xl">
                    Siap Menemukan Gaya Belajarmu?
                </h2>
                <p class="mx-auto mb-8 max-w-2xl text-muted-foreground">
                    Bergabung dengan siswa yang sudah meningkatkan cara belajar mereka dengan EduPersona.ai
                </p>

                <div class="flex flex-col items-center gap-4 sm:flex-row sm:justify-center">
                    <Link v-if="canRegister" :href="register().url">
                        <Button size="lg" class="min-w-48">
                            Daftar Sekarang - Gratis
                            <ArrowRight class="ml-2 h-4 w-4" />
                        </Button>
                    </Link>
                </div>

                <p class="mt-6 text-sm text-muted-foreground">
                    Sudah punya akun?
                    <Link :href="login().url" class="font-medium text-primary hover:underline">
                        Masuk di sini
                    </Link>
                </p>
            </div>
        </div>
    </section>
</template>
