<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed, ref, shallowRef } from 'vue';
import AppLogoLight from "@/components/AppLogoLight.vue";
import CompanySwitcher from '@/components/CompanySwitcher.vue';
import NotificationBell from '@/components/NotificationBell.vue';
import type { Auth, Branch, User } from '@/types/auth';

const page = usePage();
const auth = computed(() => page.props.auth as Auth | undefined);
const user = computed(() => auth.value?.user as User | undefined);
const isManager = computed(() => (auth.value?.roles ?? []).includes('manager'));
const selectedCompanyId = computed(() => auth.value?.selectedCompanyId ?? null);
const branches = computed<Branch[]>(() => auth.value?.branches ?? []);

const navLinks = computed(() => [
    {
        label: 'Dashboard',
        icon: 'i-lucide-layout-dashboard',
        href: '/dashboard',
        children: undefined as undefined | { label: string; href: string }[],
    },
    ...(isManager.value
        ? [
              {
                  label: 'Users',
                  icon: 'i-lucide-users',
                  href: '/users',
                  children: undefined as
                      | undefined
                      | { label: string; href: string }[],
              },
          ]
        : []),
    ...(selectedCompanyId.value && branches.value.length
        ? [
              {
                  label: 'Commission Notes',
                  icon: 'i-lucide-file-text',
                  href:
                      branches.value.length === 1
                          ? `/companies/${selectedCompanyId.value}/branches/${branches.value[0]!.id}/notes`
                          : undefined,
                  children:
                      branches.value.length > 1
                          ? branches.value.map((b) => ({
                                label: b.name,
                                href: `/companies/${selectedCompanyId.value}/branches/${b.id}/notes`,
                            }))
                          : undefined,
              },
          ]
        : []),
]);
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
                        <AppLogoLight class="h-auto w-full" />
                    </Link>
                </template>
                <USeparator :ui="{ border: 'border-white-100/80' }" />
                <CompanySwitcher :collapsed="isCollapsed" />
                <USeparator :ui="{ border: 'border-white-100/80' }" />
                <UNavigationMenu
                    :ui="{
                        link: 'data-[active]:text-primary-500 text-white-50',
                        linkLeadingIcon:
                            'group-data-[active]:text-primary-500 text-white-50',
                    }"
                    :items="
                        navLinks.map((link) => ({
                            label: link.label,
                            icon: link.icon,
                            ...(link.children
                                ? {
                                      children: link.children.map((child) => ({
                                          label: child.label,
                                          to: child.href,
                                          as: 'a',
                                      })),
                                  }
                                : { to: link.href, as: 'a' }),
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
                                </div>
                            </template>
                            <template #right>
                                <NotificationBell />
                                <UColorModeButton
                                    :ui="{ leadingIcon: 'text-white-50' }"
                                    variant="link"
                                />
                            </template>
                        </UDashboardNavbar>
                    </div>
                </template>
                <template #body>
                    <div class="flex min-h-0 flex-1 flex-col pr-4 pl-2">
                        <slot />
                    </div>
                </template>
            </UDashboardPanel>
        </UDashboardGroup>
    </div>
</template>
