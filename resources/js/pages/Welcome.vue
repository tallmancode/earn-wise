<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLogo from '@/components/AppLogo.vue';
import GuestLayout from '@/layouts/GuestLayout.vue';

defineOptions({ layout: GuestLayout });
</script>

<template>
    <Head title="Welcome" />

    <UContainer
        class="flex flex-1 flex-col items-center justify-center py-24 text-center"
    >
        <AppLogo class="mb-8 h-12 w-auto" />
        <h1
            class="text-5xl font-bold tracking-tight text-highlighted sm:text-6xl"
        >
            Take control of your finances
        </h1>
        <p class="mt-6 max-w-xl text-lg text-muted">
            Track earnings, manage expenses, and grow your wealth — all in one
            place.
        </p>
        <div class="mt-10 flex items-center gap-4">
            <UButton as-child size="xl" color="primary">
                <Link href="/login">Get Started</Link>
            </UButton>
        </div>
    </UContainer>
</template>
