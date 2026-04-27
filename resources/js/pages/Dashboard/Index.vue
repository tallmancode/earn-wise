<script setup lang="ts">
import type { BranchStat, EmployeeStat, RecentNote, User } from '@/types/auth';
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';

defineOptions({ layout: AppLayout });

const props = defineProps<{
    totalThisMonth: number;
    totalAllTime: number;
    noteCountThisMonth: number;
    branchBreakdown: BranchStat[];
    topEmployees: EmployeeStat[];
    recentNotes: RecentNote[];
}>();

const page = usePage();
const user = computed(() => page.props.auth?.user as User | undefined);

function formatZAR(value: number) {
    return new Intl.NumberFormat('en-ZA', {
        style: 'currency',
        currency: 'ZAR',
        minimumFractionDigits: 2,
    }).format(value);
}

const stats = computed(() => [
    {
        label: 'Commissions This Month',
        value: formatZAR(props.totalThisMonth),
        icon: 'i-lucide-trending-up',
        description: `${props.noteCountThisMonth} payment${props.noteCountThisMonth !== 1 ? 's' : ''}`,
    },
    {
        label: 'All-Time Total',
        value: formatZAR(props.totalAllTime),
        icon: 'i-lucide-wallet',
        description: 'Across all periods',
    },
    {
        label: 'Branches Active',
        value: props.branchBreakdown
            .filter((b) => b.total_this_month > 0)
            .length.toString(),
        icon: 'i-lucide-building-2',
        description: 'With payments this month',
    },
    {
        label: 'Top Earner',
        value: props.topEmployees[0]?.name ?? '—',
        icon: 'i-lucide-trophy',
        description: props.topEmployees[0]
            ? formatZAR(Number(props.topEmployees[0].total_commission))
            : 'No payments yet',
    },
]);
</script>

<template>
    <Head title="Dashboard" />

    <div class="flex flex-col gap-6">
        <div>
            <h1 class="text-2xl font-bold text-highlighted">
                Welcome back{{ user?.name ? `, ${user.name}` : '' }}
            </h1>
            <p class="mt-1 text-sm text-muted">
                Here's an overview of commission activity for the selected
                company.
            </p>
        </div>

        <!-- KPI cards -->
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <UCard v-for="stat in stats" :key="stat.label">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm text-muted">{{ stat.label }}</p>
                        <p
                            class="mt-1 truncate text-2xl font-semibold text-(--ui-text-highlighted)"
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

        <div class="grid gap-4 lg:grid-cols-2">
            <!-- Branch breakdown -->
            <UCard>
                <template #header>
                    <p class="font-medium text-(--ui-text-highlighted)">
                        Branch Breakdown — This Month
                    </p>
                </template>
                <div
                    v-if="branchBreakdown.length === 0"
                    class="py-8 text-center text-sm text-(--ui-text-muted)"
                >
                    No branches found for this company.
                </div>
                <table v-else class="w-full text-sm">
                    <thead>
                        <tr
                            class="border-b border-gray-100 dark:border-gray-700"
                        >
                            <th
                                class="py-2 text-left font-medium text-(--ui-text-muted)"
                            >
                                Branch
                            </th>
                            <th
                                class="py-2 text-right font-medium text-(--ui-text-muted)"
                            >
                                This Month
                            </th>
                            <th
                                class="py-2 text-right font-medium text-(--ui-text-muted)"
                            >
                                All Time
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="branch in branchBreakdown"
                            :key="branch.id"
                            class="border-b border-gray-50 last:border-0 dark:border-gray-800"
                        >
                            <td class="py-2.5 text-(--ui-text-highlighted)">
                                {{ branch.name }}
                            </td>
                            <td
                                class="py-2.5 text-right font-medium text-(--ui-primary)"
                            >
                                {{ formatZAR(branch.total_this_month) }}
                            </td>
                            <td
                                class="py-2.5 text-right text-(--ui-text-muted)"
                            >
                                {{ formatZAR(branch.total_all_time) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </UCard>

            <!-- Top employees -->
            <UCard>
                <template #header>
                    <p class="font-medium text-(--ui-text-highlighted)">
                        Top Earners — All Time
                    </p>
                </template>
                <div
                    v-if="topEmployees.length === 0"
                    class="py-8 text-center text-sm text-(--ui-text-muted)"
                >
                    No commission data yet.
                </div>
                <ol v-else class="space-y-2">
                    <li
                        v-for="(emp, idx) in topEmployees"
                        :key="emp.id"
                        class="flex items-center justify-between gap-2"
                    >
                        <div class="flex items-center gap-3">
                            <span
                                class="flex size-6 shrink-0 items-center justify-center rounded-full text-xs font-bold"
                                :class="
                                    idx === 0
                                        ? 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400'
                                        : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400'
                                "
                            >
                                {{ idx + 1 }}
                            </span>
                            <span
                                class="text-sm text-(--ui-text-highlighted)"
                                >{{ emp.name }}</span
                            >
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-medium text-(--ui-primary)">
                                {{ formatZAR(Number(emp.total_commission)) }}
                            </p>
                            <p class="text-xs text-(--ui-text-muted)">
                                {{ emp.note_count }} payment{{
                                    emp.note_count !== 1 ? 's' : ''
                                }}
                            </p>
                        </div>
                    </li>
                </ol>
            </UCard>
        </div>

        <!-- Recent activity -->
        <UCard>
            <template #header>
                <p class="font-medium text-(--ui-text-highlighted)">
                    Recent Activity
                </p>
            </template>
            <div
                v-if="recentNotes.length === 0"
                class="flex flex-col items-center justify-center py-12 text-center"
            >
                <UIcon
                    name="i-lucide-inbox"
                    class="size-10 text-(--ui-text-dimmed)"
                />
                <p class="mt-3 text-sm text-(--ui-text-muted)">
                    No commission notes yet.
                </p>
            </div>
            <ul v-else class="divide-y divide-gray-100 dark:divide-gray-700">
                <li
                    v-for="note in recentNotes"
                    :key="note.id"
                    class="flex items-center justify-between gap-4 py-3"
                >
                    <div class="min-w-0">
                        <p
                            class="truncate text-sm font-medium text-(--ui-text-highlighted)"
                        >
                            {{ note.employee_name ?? '—' }}
                            <span class="font-normal text-(--ui-text-muted)"
                                >@ {{ note.branch_name }}</span
                            >
                        </p>
                        <p class="text-xs text-(--ui-text-muted)">
                            {{ note.payment_date }} · by {{ note.author_name }}
                        </p>
                    </div>
                    <UBadge
                        color="success"
                        variant="subtle"
                        size="sm"
                        class="shrink-0"
                    >
                        {{ formatZAR(Number(note.amount)) }}
                    </UBadge>
                </li>
            </ul>
        </UCard>
    </div>
</template>
