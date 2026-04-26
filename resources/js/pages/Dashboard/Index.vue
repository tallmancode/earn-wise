<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { User } from '@/types/auth';

defineOptions({ layout: AppLayout });

const page = usePage();
const user = computed(() => page.props.auth?.user as User | undefined);

const stats = [
    {
        label: 'Total Earnings',
        value: '$0.00',
        icon: 'i-lucide-trending-up',
        description: 'This month',
    },
    {
        label: 'Expenses',
        value: '$0.00',
        icon: 'i-lucide-trending-down',
        description: 'This month',
    },
    {
        label: 'Net Balance',
        value: '$0.00',
        icon: 'i-lucide-wallet',
        description: 'Current balance',
    },
    {
        label: 'Transactions',
        value: '0',
        icon: 'i-lucide-activity',
        description: 'This month',
    },
];
</script>

<template>
    <Head title="Dashboard" />

    <div class="flex flex-col gap-6">
        <div>
            <h1 class="text-2xl font-bold text-highlighted">
                Welcome back{{ user?.name ? `, ${user.name}` : '' }}
            </h1>
            <p class="mt-1 text-sm text-muted">
                Here's an overview of your finances.
            </p>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <UCard v-for="stat in stats" :key="stat.label">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm text-muted">
                            {{ stat.label }}
                        </p>
                        <p
                            class="mt-1 text-2xl font-semibold text-(--ui-text-highlighted)"
                        >
                            {{ stat.value }}
                        </p>
                        <p class="mt-1 text-xs text-(--ui-text-muted)">
                            {{ stat.description }}
                        </p>
                    </div>
                    <UIcon
                        :name="stat.icon"
                        class="size-5 text-(--ui-primary)"
                    />
                </div>
            </UCard>
        </div>

        <UCard>
            <template #header>
                <p class="font-medium text-(--ui-text-highlighted)">
                    Recent Activity
                </p>
            </template>
            <div
                class="flex flex-col items-center justify-center py-12 text-center"
            >
                <UIcon
                    name="i-lucide-inbox"
                    class="size-10 text-(--ui-text-dimmed)"
                />
                <p class="mt-3 text-sm text-(--ui-text-muted)">
                    No transactions yet
                </p>
                <p class="mt-1 text-xs text-(--ui-text-dimmed)">
                    Your recent activity will appear here.
                </p>
            </div>
        </UCard>
    </div>
</template>
