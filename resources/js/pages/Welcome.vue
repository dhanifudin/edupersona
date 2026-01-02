<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import LandingHeader from '@/components/landing/LandingHeader.vue';
import LandingHero from '@/components/landing/LandingHero.vue';
import LandingFeatures from '@/components/landing/LandingFeatures.vue';
import LandingDemo from '@/components/landing/LandingDemo.vue';
import LandingCTA from '@/components/landing/LandingCTA.vue';
import LandingFooter from '@/components/landing/LandingFooter.vue';

interface Props {
    canRegister: boolean;
}

withDefaults(defineProps<Props>(), {
    canRegister: true,
});

const page = usePage();
const isAuthenticated = computed(() => !!page.props.auth?.user);
</script>

<template>
    <Head title="EduPersona.ai - Platform Pembelajaran Personalisasi">
        <meta name="description" content="Platform pembelajaran yang dipersonalisasi berdasarkan gaya belajar Visual, Auditori, dan Kinestetik menggunakan AI." />
    </Head>

    <div class="min-h-screen bg-background text-foreground">
        <!-- Header -->
        <LandingHeader :can-register="canRegister" :is-authenticated="isAuthenticated" />

        <!-- Main Content -->
        <main>
            <!-- Hero Section -->
            <LandingHero :can-register="canRegister" />

            <!-- Features Section -->
            <LandingFeatures />

            <!-- Interactive Demo Section -->
            <LandingDemo :can-register="canRegister" />

            <!-- Call to Action Section -->
            <LandingCTA :can-register="canRegister" />
        </main>

        <!-- Footer -->
        <LandingFooter />
    </div>
</template>
