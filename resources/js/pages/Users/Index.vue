<script setup lang="ts">
import type { Branch } from '@/types/auth';
import { Head, useForm } from '@inertiajs/vue3';
import { computed, h, ref, resolveComponent } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';

defineOptions({ layout: AppLayout });

type UserRow = {
    id: number;
    name: string;
    email: string;
    roles: string[];
    branches: { id: number | null; name: string | null }[];
};

const props = defineProps<{
    users: UserRow[];
    branches: Branch[];
    availableRoles: string[];
}>();

const isOpen = ref(false);

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    branch_id: undefined as number | undefined,
    role: '',
});

function submit() {
    form.post('/users', {
        onSuccess: () => {
            isOpen.value = false;
            form.reset();
        },
    });
}

const UBadge = resolveComponent('UBadge');

const columns = [
    {
        accessorKey: 'name',
        header: 'Name',
    },
    {
        accessorKey: 'email',
        header: 'Email',
    },
    {
        accessorKey: 'roles',
        header: 'Role',
        cell: ({ row }: any) =>
            h(
                'div',
                { class: 'flex gap-1 flex-wrap' },
                row.original.roles.length
                    ? row.original.roles.map((role: string) =>
                          h(UBadge, {
                              label:
                                  role.charAt(0).toUpperCase() + role.slice(1),
                              color: role === 'manager' ? 'primary' : 'neutral',
                              variant: 'subtle',
                              size: 'sm',
                          }),
                      )
                    : [
                          h(
                              'span',
                              { class: 'text-sm text-(--ui-text-dimmed)' },
                              '—',
                          ),
                      ],
            ),
    },
    {
        accessorKey: 'branches',
        header: 'Branch',
        cell: ({ row }: any) =>
            h(
                'div',
                { class: 'flex flex-wrap gap-1' },
                row.original.branches.length
                    ? row.original.branches.map((b: { name: string | null }) =>
                          h('span', { class: 'text-sm' }, b.name ?? '—'),
                      )
                    : [
                          h(
                              'span',
                              { class: 'text-sm text-(--ui-text-dimmed)' },
                              '—',
                          ),
                      ],
            ),
    },
];

const branchItems = computed(() =>
    props.branches.map((b) => ({ label: b.name, value: b.id })),
);

const roleItems = computed(() =>
    props.availableRoles.map((r) => ({
        label: r.charAt(0).toUpperCase() + r.slice(1),
        value: r,
    })),
);
</script>

<template>
    <Head title="Users" />

    <div class="flex flex-col gap-6">
        <div class="flex items-start justify-between">
            <div>
                <h1 class="text-2xl font-bold text-highlighted">Users</h1>
                <p class="mt-1 text-sm text-muted">
                    Manage users for this company.
                </p>
            </div>

            <UModal
                v-model:open="isOpen"
                title="Create User"
                description="Add a new user and assign them to a branch."
            >
                <UButton label="Create User" icon="i-lucide-user-plus" />

                <template #body>
                    <form class="flex flex-col gap-4" @submit.prevent="submit">
                        <UFormField
                            label="Name"
                            required
                            :error="form.errors.name"
                        >
                            <UInput
                                v-model="form.name"
                                placeholder="Full name"
                                class="w-full"
                                autofocus
                            />
                        </UFormField>

                        <UFormField
                            label="Email"
                            required
                            :error="form.errors.email"
                        >
                            <UInput
                                v-model="form.email"
                                type="email"
                                placeholder="email@example.com"
                                class="w-full"
                            />
                        </UFormField>

                        <UFormField
                            label="Password"
                            required
                            :error="form.errors.password"
                        >
                            <UInput
                                v-model="form.password"
                                type="password"
                                placeholder="••••••••"
                                class="w-full"
                            />
                        </UFormField>

                        <UFormField
                            label="Confirm Password"
                            required
                            :error="form.errors.password_confirmation"
                        >
                            <UInput
                                v-model="form.password_confirmation"
                                type="password"
                                placeholder="••••••••"
                                class="w-full"
                            />
                        </UFormField>

                        <UFormField
                            label="Branch"
                            required
                            :error="form.errors.branch_id"
                        >
                            <USelect
                                v-model="form.branch_id"
                                :items="branchItems"
                                placeholder="Select a branch"
                                class="w-full"
                            />
                        </UFormField>

                        <UFormField
                            label="Role"
                            required
                            :error="form.errors.role"
                        >
                            <USelect
                                v-model="form.role"
                                :items="roleItems"
                                placeholder="Select a role"
                                class="w-full"
                            />
                        </UFormField>
                    </form>
                </template>

                <template #footer>
                    <div class="flex justify-end gap-2">
                        <UButton
                            label="Cancel"
                            color="neutral"
                            variant="ghost"
                            @click="isOpen = false"
                        />
                        <UButton
                            label="Create User"
                            icon="i-lucide-user-plus"
                            :loading="form.processing"
                            @click="submit"
                        />
                    </div>
                </template>
            </UModal>
        </div>

        <UCard>
            <UTable
                :data="users"
                :columns="columns"
                empty="No users found for this company."
            />
        </UCard>
    </div>
</template>
