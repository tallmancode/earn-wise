<script setup lang="ts">
import type { AppNotification } from '@/types/auth';
import { onMounted, onUnmounted, ref } from 'vue';

const unreadCount = ref(0);
const notifications = ref<AppNotification[]>([]);
const isOpen = ref(false);
let pollInterval: ReturnType<typeof setInterval> | null = null;

async function fetchNotifications() {
    try {
        const res = await fetch('/notifications', {
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        if (!res.ok) {
            return;
        }

        const data = await res.json();
        unreadCount.value = data.unread_count ?? 0;
        notifications.value = data.notifications ?? [];
    } catch {
        // silent — non-critical polling
    }
}

async function markAllRead() {
    await fetch('/notifications/read', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN':
                document
                    .querySelector('meta[name="csrf-token"]')
                    ?.getAttribute('content') ?? '',
            'X-Requested-With': 'XMLHttpRequest',
        },
    });
    unreadCount.value = 0;
    notifications.value = notifications.value.map((n) => ({
        ...n,
        read_at: new Date().toISOString(),
    }));
}

function formatDate(iso: string) {
    return new Date(iso).toLocaleString('en-ZA', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

onMounted(() => {
    fetchNotifications();
    pollInterval = setInterval(fetchNotifications, 60_000);
});

onUnmounted(() => {
    if (pollInterval) {
        clearInterval(pollInterval);
    }
});
</script>

<template>
    <UPopover v-model:open="isOpen">
        <UButton
            color="neutral"
            variant="link"
            :aria-label="`Notifications${unreadCount > 0 ? ` (${unreadCount} unread)` : ''}`"
            class="relative"
            :ui="{ leadingIcon: 'text-white-50' }"
            @click="isOpen = !isOpen"
        >
            <template #leading>
                <UIcon name="i-lucide-bell" class="size-5 text-white-50" />
            </template>
            <span
                v-if="unreadCount > 0"
                class="absolute -top-1 -right-1 flex size-4 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white"
            >
                {{ unreadCount > 9 ? '9+' : unreadCount }}
            </span>
        </UButton>

        <template #content>
            <div class="flex max-h-96 w-80 flex-col overflow-hidden">
                <div
                    class="flex items-center justify-between border-b border-gray-100 px-4 py-3 dark:border-gray-700"
                >
                    <p
                        class="text-sm font-semibold text-(--ui-text-highlighted)"
                    >
                        Notifications
                    </p>
                    <UButton
                        v-if="unreadCount > 0"
                        label="Mark all read"
                        color="neutral"
                        variant="link"
                        size="xs"
                        @click="markAllRead"
                    />
                </div>

                <div class="flex-1 overflow-y-auto">
                    <div
                        v-if="notifications.length === 0"
                        class="flex flex-col items-center justify-center py-8 text-center"
                    >
                        <UIcon
                            name="i-lucide-bell-off"
                            class="size-8 text-(--ui-text-dimmed)"
                        />
                        <p class="mt-2 text-sm text-(--ui-text-muted)">
                            No notifications yet
                        </p>
                    </div>

                    <div
                        v-for="notification in notifications"
                        :key="notification.id"
                        :class="[
                            'border-b border-gray-100 px-4 py-3 last:border-0 dark:border-gray-700',
                            notification.read_at
                                ? 'opacity-60'
                                : 'bg-primary-50/40 dark:bg-primary-900/10',
                        ]"
                    >
                        <div class="flex items-start gap-2">
                            <UIcon
                                name="i-lucide-banknote"
                                class="mt-0.5 size-4 shrink-0 text-(--ui-primary)"
                            />
                            <div class="min-w-0 flex-1">
                                <p
                                    class="text-sm leading-snug text-(--ui-text-highlighted)"
                                >
                                    {{ notification.message }}
                                </p>
                                <p class="mt-1 text-xs text-(--ui-text-muted)">
                                    {{ formatDate(notification.created_at) }}
                                </p>
                            </div>
                            <span
                                v-if="!notification.read_at"
                                class="mt-1 size-2 shrink-0 rounded-full bg-(--ui-primary)"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </UPopover>
</template>
