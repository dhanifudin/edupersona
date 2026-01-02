<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
</script>

<template>
    <footer class="border-t border-border py-8 px-4">
        <div class="mx-auto max-w-6xl">
            <div class="flex flex-col items-center justify-between gap-4 sm:flex-row">
                <!-- Logo & Name -->
                <div class="flex items-center gap-2">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary text-primary-foreground">
                        <AppLogoIcon class="h-4 w-4" />
                    </div>
                    <span class="font-semibold">EduPersona.ai</span>
                </div>

                <!-- Description -->
                <p class="text-center text-sm text-muted-foreground sm:text-left">
                    Platform Pembelajaran Personalisasi berbasis AI
                </p>

                <!-- Copyright -->
                <p class="text-sm text-muted-foreground">
                    &copy; {{ new Date().getFullYear() }} EduPersona.ai
                </p>
            </div>
        </div>
    </footer>
</template>
