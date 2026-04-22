<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppLogo from '@/components/AppLogo.vue';
import type { User } from '@/types/auth';

const page = usePage();
const user = computed(() => page.props.auth?.user as User | undefined);

const navLinks = [
    {
        label: 'Dashboard',
        icon: 'i-lucide-layout-dashboard',
        href: '/dashboard',
    },
];
</script>

<template>
    <UDashboardGroup>
        <UDashboardSidebar collapsible>
            <template #header>
                <Link href="/dashboard" class="flex items-center px-1">
                    <AppLogo class="h-7 w-auto" />
                </Link>
            </template>

            <UNavigationMenu
                :items="
                    navLinks.map((link) => ({
                        label: link.label,
                        icon: link.icon,
                        to: link.href,
                        as: 'a',
                    }))
                "
                orientation="vertical"
                class="w-full"
            />

            <template #footer>
                <div class="flex items-center gap-3 px-2 py-1">
                    <UAvatar :alt="user?.name" size="sm" />
                    <div class="flex min-w-0 flex-col">
                        <span
                            class="truncate text-sm font-medium text-(--ui-text-highlighted)"
                        >
                            {{ user?.name }}
                        </span>
                        <span class="truncate text-xs text-(--ui-text-muted)">
                            {{ user?.email }}
                        </span>
                    </div>
                    <UDropdownMenu
                        :items="[
                            [
                                {
                                    label: 'Sign out',
                                    icon: 'i-lucide-log-out',
                                    onSelect: () => $inertia.post('/logout'),
                                },
                            ],
                        ]"
                    >
                        <UButton
                            color="neutral"
                            variant="ghost"
                            icon="i-lucide-ellipsis-vertical"
                            size="sm"
                            class="ml-auto shrink-0"
                            aria-label="User menu"
                        />
                    </UDropdownMenu>
                </div>
            </template>
        </UDashboardSidebar>

        <div class="flex min-h-screen flex-1 flex-col overflow-hidden">
            <UDashboardNavbar>
                <template #right>
                    <UColorModeButton />
                </template>
            </UDashboardNavbar>

            <main class="flex-1 overflow-y-auto p-6">
                <slot />
            </main>
        </div>
    </UDashboardGroup>
</template>
