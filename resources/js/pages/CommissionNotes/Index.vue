<script setup lang="ts">
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { computed, h, resolveComponent, watch } from 'vue';
import {
    destroy as destroyAction,
    store as storeAction,
    update as updateAction,
} from '@/actions/App/Http/Controllers/CommissionNoteController';
import AppLayout from '@/layouts/AppLayout.vue';
import { useNoteStore } from '@/stores/noteStore';
import type { Branch, CommissionNote, Company, Employee } from '@/types/auth';

defineOptions({ layout: AppLayout });

const props = defineProps<{
    company: Company;
    branch: Branch;
    notes: CommissionNote[];
    employees: Employee[];
    canManage: boolean;
}>();

const page = usePage();
const noteStore = useNoteStore();

const currentUserId = computed(
    () => (page.props.auth as any)?.user?.id as number,
);

const filteredNotes = computed(() =>
    noteStore.filterEmployeeId
        ? props.notes.filter(
              (n) => n.employee_id === noteStore.filterEmployeeId,
          )
        : props.notes,
);

const employeeItems = computed(() => [
    { label: 'All Employees', value: null },
    ...props.employees.map((e) => ({ label: e.name, value: e.id })),
]);

// ── Create form ──────────────────────────────────────────────────────────────

const createForm = useForm({
    company_id: props.company.id,
    branch_id: props.branch.id,
    employee_id: undefined as number | undefined,
    amount: '',
    description: '',
    payment_date: '',
});

function submitCreate() {
    createForm.post(
        storeAction.url({ company: props.company.id, branch: props.branch.id }),
        {
            onSuccess: () => {
                createForm.reset();
                noteStore.closePanel();
            },
        },
    );
}

// ── Edit form ─────────────────────────────────────────────────────────────────

const editForm = useForm({
    amount: '',
    description: '',
    payment_date: '',
});

watch(
    () => noteStore.selectedNote,
    (note) => {
        if (note) {
            editForm.amount = note.amount;
            editForm.description = note.description ?? '';
            editForm.payment_date = note.payment_date;
        }
    },
);

function submitEdit() {
    if (!noteStore.selectedNote) {
        return;
    }

    editForm.patch(updateAction.url(noteStore.selectedNote.id), {
        onSuccess: () => noteStore.closePanel(),
    });
}

// ── Delete ────────────────────────────────────────────────────────────────────

function deleteNote(note: CommissionNote) {
    if (
        !confirm(
            `Delete this commission note for ${note.employee?.name ?? 'this employee'}?`,
        )
    ) {
        return;
    }

    router.delete(destroyAction.url(note.id), { preserveScroll: true });
}

// ── Table columns ─────────────────────────────────────────────────────────────

const UBadge = resolveComponent('UBadge');

const columns = [
    {
        accessorKey: 'employee',
        header: 'Employee',
        cell: ({ row }: any) =>
            h(
                'span',
                { class: 'font-medium' },
                row.original.employee?.name ?? '—',
            ),
    },
    {
        accessorKey: 'amount',
        header: 'Amount',
        cell: ({ row }: any) =>
            h(
                UBadge,
                { color: 'success', variant: 'subtle', size: 'sm' },
                {
                    default: () =>
                        `R ${Number(row.original.amount).toLocaleString('en-ZA', { minimumFractionDigits: 2 })}`,
                },
            ),
    },
    {
        accessorKey: 'payment_date',
        header: 'Payment Date',
        cell: ({ row }: any) => h('span', {}, row.original.payment_date),
    },
    {
        accessorKey: 'description',
        header: 'Description',
        cell: ({ row }: any) =>
            h(
                'span',
                { class: 'text-(--ui-text-muted)' },
                row.original.description ?? '—',
            ),
    },
    {
        accessorKey: 'author',
        header: 'Created By',
        cell: ({ row }: any) =>
            h('span', { class: 'text-sm' }, row.original.author?.name ?? '—'),
    },
    {
        id: 'actions',
        header: '',
    },
];
</script>

<template>
    <Head :title="`${company.name} — Commission Notes`" />

    <div class="flex flex-col gap-6">
        <div class="flex items-start justify-between">
            <div>
                <h1 class="text-2xl font-bold text-highlighted">
                    {{ company.name }} &mdash; {{ branch.name }}
                </h1>
                <p class="mt-1 text-sm text-muted">Commission Notes</p>
            </div>

            <UButton
                v-if="canManage"
                label="Add Note"
                icon="i-lucide-plus"
                @click="noteStore.openCreate()"
            />
        </div>

        <!-- Employee filter -->
        <div class="flex items-center gap-3">
            <USelect
                :items="employeeItems"
                :model-value="noteStore.filterEmployeeId"
                placeholder="Filter by employee"
                class="w-56"
                @update:model-value="noteStore.setFilter($event)"
            />
        </div>

        <!-- Notes table -->
        <UCard>
            <UTable
                :data="filteredNotes"
                :columns="columns"
                empty="No commission notes found."
            >
                <template #actions-data="{ row }">
                    <div class="flex items-center gap-2">
                        <UButton
                            v-if="
                                canManage ||
                                row.original.created_by === currentUserId
                            "
                            label="Edit"
                            color="neutral"
                            variant="ghost"
                            size="xs"
                            icon="i-lucide-pencil"
                            @click="noteStore.selectNote(row.original)"
                        />
                        <UButton
                            v-if="
                                canManage ||
                                row.original.created_by === currentUserId
                            "
                            label="Delete"
                            color="error"
                            variant="ghost"
                            size="xs"
                            icon="i-lucide-trash-2"
                            @click="deleteNote(row.original)"
                        />
                    </div>
                </template>
            </UTable>
        </UCard>
    </div>

    <!-- Create / Edit Slideover -->
    <USlideover
        v-model:open="noteStore.isPanelOpen"
        :title="
            noteStore.selectedNote
                ? 'Edit Commission Note'
                : 'New Commission Note'
        "
        :description="
            noteStore.selectedNote
                ? 'Update the details for this commission note.'
                : 'Fill in the details to create a new commission note.'
        "
        @close="noteStore.closePanel()"
    >
        <template #body>
            <!-- Create form -->
            <form
                v-if="!noteStore.selectedNote"
                class="flex flex-col gap-4"
                @submit.prevent="submitCreate"
            >
                <UFormField
                    label="Employee"
                    required
                    :error="createForm.errors.employee_id"
                >
                    <USelect
                        v-model="createForm.employee_id"
                        :items="
                            employees.map((e) => ({
                                label: e.name,
                                value: e.id,
                            }))
                        "
                        placeholder="Select an employee"
                        class="w-full"
                    />
                </UFormField>

                <UFormField
                    label="Amount (R)"
                    required
                    :error="createForm.errors.amount"
                >
                    <UInput
                        v-model="createForm.amount"
                        type="number"
                        step="0.01"
                        min="0"
                        placeholder="0.00"
                        class="w-full"
                    />
                </UFormField>

                <UFormField
                    label="Payment Date"
                    required
                    :error="createForm.errors.payment_date"
                >
                    <UInput
                        v-model="createForm.payment_date"
                        type="date"
                        class="w-full"
                    />
                </UFormField>

                <UFormField
                    label="Description"
                    :error="createForm.errors.description"
                >
                    <UTextarea
                        v-model="createForm.description"
                        placeholder="Optional description…"
                        :rows="3"
                        class="w-full"
                    />
                </UFormField>
            </form>

            <!-- Edit form -->
            <form
                v-else
                class="flex flex-col gap-4"
                @submit.prevent="submitEdit"
            >
                <UFormField
                    label="Amount (R)"
                    required
                    :error="editForm.errors.amount"
                >
                    <UInput
                        v-model="editForm.amount"
                        type="number"
                        step="0.01"
                        min="0"
                        placeholder="0.00"
                        class="w-full"
                    />
                </UFormField>

                <UFormField
                    label="Payment Date"
                    required
                    :error="editForm.errors.payment_date"
                >
                    <UInput
                        v-model="editForm.payment_date"
                        type="date"
                        class="w-full"
                    />
                </UFormField>

                <UFormField
                    label="Description"
                    :error="editForm.errors.description"
                >
                    <UTextarea
                        v-model="editForm.description"
                        placeholder="Optional description…"
                        :rows="3"
                        class="w-full"
                    />
                </UFormField>
            </form>
        </template>

        <template #footer>
            <div v-if="!noteStore.selectedNote" class="flex justify-end gap-2">
                <UButton
                    label="Cancel"
                    color="neutral"
                    variant="ghost"
                    @click="noteStore.closePanel()"
                />
                <UButton
                    label="Save Note"
                    icon="i-lucide-save"
                    :loading="createForm.processing"
                    @click="submitCreate"
                />
            </div>
            <div v-else class="flex justify-end gap-2">
                <UButton
                    label="Cancel"
                    color="neutral"
                    variant="ghost"
                    @click="noteStore.closePanel()"
                />
                <UButton
                    label="Update Note"
                    icon="i-lucide-save"
                    :loading="editForm.processing"
                    @click="submitEdit"
                />
            </div>
        </template>
    </USlideover>
</template>
