<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed, ref, shallowRef } from 'vue';
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
const open = ref(false);
const isCollapsed = ref(false);
const panelUi = shallowRef({});
</script>

<template>
    <div class="relative flex min-h-0 flex-1 flex-col">
        <UDashboardGroup>
            <UDashboardSidebar
                id="dashboard-sidebar"
                v-model:open="open"
                v-model:collapsed="isCollapsed"
                :ui="{
                    body: 'gap-2',
                    root: `transition-[width] text-white-200 duration-300 min-h-[calc(100svh_-_4rem)] ease-in-out bg-none border border-horizon-50 shadow-smooth  rounded-xl border-none fancy-bg m-4 ${isCollapsed ? 'p-0' : 'p-2 min-w-60 w-60'}`,
                    footer: 'lg:border-t border-white-100/80',
                    header: `${isCollapsed ? 'p-3 h-[64px]' : ''}`,
                }"
                collapsible
                resizable
            >
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
                            <span
                                class="truncate text-xs text-(--ui-text-muted)"
                            >
                                {{ user?.email }}
                            </span>
                        </div>
                        <UDropdownMenu
                            :items="[
                                [
                                    {
                                        label: 'Sign out',
                                        icon: 'i-lucide-log-out',
                                        onSelect: () =>
                                            $inertia.post('/logout'),
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
            <UDashboardPanel
                :ui="{
                    ...panelUi,
                    ...{
                        body: 'sm:p-0 sm:pb-4 sm:pt-20 scroll-smooth',
                        root: 'pr-4 ',
                    },
                }"
            >
                <template #header>
                    <div
                        :class="[
                            'fixed top-0 right-0 z-100 w-full pt-4 pr-8 transition-[padding] duration-300',
                            isCollapsed ? 'pl-24' : 'pl-68',
                        ]"
                    >
                        <UDashboardNavbar
                            :ui="{
                                right: 'gap-3',
                                root: `sm:px-2 border border-horizon-50 shadow-smooth fancy-bg backdrop-blur rounded-xl w-full  text-white-50`,
                            }"
                        >
                            <template #leading>
                                <div class="flex min-w-0 items-center gap-2">
                                    <UDashboardSidebarCollapse
                                        variant="link"
                                        :ui="{
                                            base: `transition-transform text-light-50 ${isCollapsed ? 'rotate-180' : ''}`,
                                        }"
                                        leading-icon="i-lucide-chevrons-left"
                                    />
                                    <USeparator
                                        :ui="{ border: 'border-white-100/80' }"
                                        class="h-6"
                                        orientation="vertical"
                                    />
                                    <!--                                <span-->
                                    <!--                                    v-if="pageLabel"-->
                                    <!--                                    class="truncate text-sm font-semibold text-light-50"-->
                                    <!--                                >{{ pageLabel }}</span>-->
                                </div>
                            </template>
                            <template #right>
                                <UColorModeButton
                                    :ui="{ leadingIcon: 'text-white-50' }"
                                    variant="link"
                                />
                                <!--                            <UTooltip :shortcuts="['N']" text="Notifications">-->
                                <!--                                <UButton-->
                                <!--                                    :disabled="workspaceLocked"-->
                                <!--                                    color="white"-->
                                <!--                                    square-->
                                <!--                                    variant="link"-->
                                <!--                                >-->
                                <!--                                    <UChip-->
                                <!--                                        color="error"-->
                                <!--                                        inset-->
                                <!--                                        @click="isNotificationsSlideoverOpen = true"-->
                                <!--                                    >-->
                                <!--                                        <UIcon class="size-5 shrink-0" name="i-lucide-bell" />-->
                                <!--                                    </UChip>-->
                                <!--                                </UButton>-->
                                <!--                            </UTooltip>-->
                            </template>
                        </UDashboardNavbar>
                    </div>
                </template>
                <template #body>
                    <div class="flex min-h-0 flex-1 flex-col">
                        <slot />
                    </div>
                </template>
            </UDashboardPanel>
            <!--        <div class="flex min-h-screen flex-1 flex-col overflow-hidden">-->
            <!--            <UDashboardNavbar>-->
            <!--                <template #right>-->
            <!--                    <UColorModeButton />-->
            <!--                </template>-->
            <!--            </UDashboardNavbar>-->

            <!--            <main class="flex-1 overflow-y-auto p-6">-->
            <!--                <slot />-->
            <!--            </main>-->
            <!--        </div>-->
        </UDashboardGroup>
    </div>
</template>
