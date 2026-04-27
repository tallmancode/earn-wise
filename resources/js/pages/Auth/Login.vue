<script setup lang="ts">
import { Head, useForm, Link  } from '@inertiajs/vue3';
import AppLogo from '@/components/AppLogo.vue';
import GuestLayout from '@/layouts/GuestLayout.vue';

defineOptions({ layout: GuestLayout });

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

function submit() {
    form.post('/login', {
        onFinish: () => form.reset('password'),
    });
}
</script>

<template>
    <Head title="Login" />
    <div class="grid h-screen grid-cols-1 gap-x-8 p-8 md:grid-cols-2">
        <div class="flex items-center justify-center">
            <div class="flex max-w-100 flex-1 flex-col justify-center">
                <div class="mb-4 w-full">
                    <Link
                        href="/"
                        class="flex w-full items-center justify-center"
                    >
                        <AppLogo class="h-16 w-auto" />
                    </Link>
                </div>
                <div class="mb-8 text-center">
                    <h1 class="text-3xl">Sign into your account</h1>
                    <p>Enter your credentials to access your dashboard</p>
                </div>

                <form @submit.prevent="submit" class="flex flex-col gap-5">
                    <UFormField
                        label="Email address"
                        name="email"
                        :error="form.errors.email"
                    >
                        <UInput
                            v-model="form.email"
                            type="email"
                            placeholder="you@example.com"
                            autocomplete="email"
                            :disabled="form.processing"
                            class="w-full"
                        />
                    </UFormField>

                    <UFormField
                        label="Password"
                        name="password"
                        :error="form.errors.password"
                    >
                        <UInput
                            v-model="form.password"
                            type="password"
                            placeholder="••••••••"
                            autocomplete="current-password"
                            :disabled="form.processing"
                            class="w-full"
                        />
                    </UFormField>

                    <div class="flex items-center justify-between">
                        <UCheckbox
                            v-model="form.remember"
                            label="Remember me"
                            name="remember"
                        />
                    </div>

                    <UButton
                        type="submit"
                        color="primary"
                        block
                        :loading="form.processing"
                    >
                        Sign in
                    </UButton>
                </form>
            </div>
        </div>
        <div class="cutout overflow-hidden rounded-4xl">
            <img
                src="/images/login-hero.webp"
                alt="Login Background"
                class="h-full w-full object-cover"
            />
            <UButton
                icon="i-lucide-x"
                to="/"
                size="xl"
                color="neutral"
                variant="subtle"
                class="absolute top-8 right-8 z-10 rounded-full"
            />
        </div>
    </div>
</template>

<style scoped>
.cutout {
    corner-top-right-shape: scoop;
    border-top-right-radius: 60px;
}
</style>
