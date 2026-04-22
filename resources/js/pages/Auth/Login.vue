<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
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

    <UContainer class="flex flex-1 flex-col items-center justify-center py-16">
        <div class="w-full max-w-sm">
            <div class="mb-8 text-center">
                <h1 class="text-2xl font-bold text-(--ui-text-highlighted)">
                    Sign in to your account
                </h1>
                <p class="mt-2 text-sm text-(--ui-text-muted)">
                    Enter your credentials to access your dashboard
                </p>
            </div>

            <UCard>
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
            </UCard>
        </div>
    </UContainer>
</template>
