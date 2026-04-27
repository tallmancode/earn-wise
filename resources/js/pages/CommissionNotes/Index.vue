<script setup lang="ts">
import type {
    Branch,
    CommissionNote,
    CommissionNoteAudit,
    Company,
    Employee,
    Paginated,
} from '@/types/auth';
import { Head, router, useForm } from '@inertiajs/vue3';
import { useDebounceFn } from '@vueuse/core';
import { computed, h, ref, resolveComponent, watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { useNoteStore } from '@/stores/noteStore';
import {
    destroy as destroyAction,
    index as indexAction,
    store as storeAction,
    update as updateAction,
} from '@/actions/App/Http/Controllers/CommissionNoteController';

defineOptions({ layout: AppLayout });

const props = defineProps<{
    company: Company;
    branch: Branch;
    notes: Paginated<CommissionNote>;
    employees: Employee[];
    canManage: boolean;
    filters: { search: string };
}>();

const noteStore = useNoteStore();

const search = ref(props.filters.search ?? '');

const performSearch = useDebounceFn((value: string) => {
    router.get(
        indexAction.url({ company: props.company.id, branch: props.branch.id }),
        { search: value || undefined },
        { preserveState: true, replace: true },
    );
}, 400);

watch(search, (value) => performSearch(value));

// ── Export URL ────────────────────────────────────────────────────────────────

const exportUrl = computed(
    () =>
        `/companies/${props.company.id}/branches/${props.branch.id}/notes/export`,
);

// ── Audit History modal ───────────────────────────────────────────────────────

const historyNote = ref<CommissionNote | null>(null);

function openHistory(note: CommissionNote) {
    historyNote.value = note;
}

function closeHistory() {
    historyNote.value = null;
}

const eventLabels: Record<CommissionNoteAudit['event'], string> = {
    created: 'Created',
    updated: 'Updated',
    deleted: 'Deleted',
    restored: 'Restored',
};

const eventColors: Record<CommissionNoteAudit['event'], string> = {
    created: 'success',
    updated: 'info',
    deleted: 'error',
    restored: 'warning',
};

function formatDate(iso: string) {
    return new Date(iso).toLocaleString('en-ZA', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

function auditDiff(audit: CommissionNoteAudit): string[] {
    if (audit.event !== 'updated' || !audit.old_values || !audit.new_values) {
        return [];
    }

    const changes: string[] = [];
    const watched = [
        'amount',
        'description',
        'payment_date',
        'employee_id',
    ] as const;

    for (const key of watched) {
        const oldVal = audit.old_values[key];
        const newVal = audit.new_values[key];

        if (String(oldVal) !== String(newVal)) {
            const label =
                key === 'payment_date'
                    ? 'Payment Date'
                    : key === 'employee_id'
                      ? 'Employee'
                      : key.charAt(0).toUpperCase() + key.slice(1);
            const fmtOld =
                key === 'amount'
                    ? `R ${Number(oldVal).toLocaleString('en-ZA', { minimumFractionDigits: 2 })}`
                    : String(oldVal ?? '—');
            const fmtNew =
                key === 'amount'
                    ? `R ${Number(newVal).toLocaleString('en-ZA', { minimumFractionDigits: 2 })}`
                    : String(newVal ?? '—');
            changes.push(`${label}: ${fmtOld} → ${fmtNew}`);
        }
    }

    return changes;
}

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
        header: 'Actions',
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

            <div class="flex items-center gap-2">
                <UButton
                    :href="exportUrl"
                    as="a"
                    target="_blank"
                    label="Export CSV"
                    icon="i-lucide-download"
                    color="neutral"
                    variant="outline"
                    size="sm"
                />
                <UButton
                    v-if="canManage"
                    label="Add Note"
                    icon="i-lucide-plus"
                    @click="noteStore.openCreate()"
                />
            </div>
        </div>

        <!-- Search -->
        <div class="flex items-center gap-3">
            <UInput
                v-model="search"
                placeholder="Search by employee name…"
                icon="i-lucide-search"
                class="w-72"
                :ui="{ trailing: 'pr-1' }"
            >
                <template v-if="search" #trailing>
                    <UButton
                        color="neutral"
                        variant="link"
                        size="sm"
                        icon="i-lucide-x"
                        @click="search = ''"
                    />
                </template>
            </UInput>
            <span class="text-sm text-muted">
                {{ notes.total }} note{{ notes.total !== 1 ? 's' : '' }}
            </span>
        </div>

        <!-- Notes table -->
        <UCard>
            <UTable
                :data="notes.data"
                :columns="columns"
                empty="No commission notes found."
            >
                <template #actions-cell="{ row }">
                    <div class="flex items-center gap-2">
                        <UButton
                            label="History"
                            color="neutral"
                            variant="ghost"
                            size="xs"
                            icon="i-lucide-history"
                            @click="openHistory(row.original)"
                        />
                        <UButton
                            v-if="canManage"
                            label="Edit"
                            color="neutral"
                            variant="ghost"
                            size="xs"
                            icon="i-lucide-pencil"
                            @click="noteStore.selectNote(row.original)"
                        />
                        <UButton
                            v-if="canManage"
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

            <!-- Pagination -->
            <div
                v-if="notes.last_page > 1"
                class="mt-4 flex justify-center border-t pt-4"
            >
                <UPagination
                    :page="notes.current_page"
                    :total="notes.total"
                    :items-per-page="notes.per_page"
                    @update:page="
                        (p: number) =>
                            router.get(
                                indexAction.url({
                                    company: company.id,
                                    branch: branch.id,
                                }),
                                { page: p, search: search || undefined },
                                { preserveState: true },
                            )
                    "
                />
            </div>
        </UCard>
    </div>

    <!-- ── Audit History Modal ─────────────────────────────────────────────── -->
    <UModal
        :open="historyNote !== null"
        title="Note History"
        :description="
            historyNote
                ? `Audit trail for ${historyNote.employee?.name ?? 'this employee'}'s commission note`
                : ''
        "
        @close="closeHistory"
    >
        <template #body>
            <div v-if="historyNote">
                <div
                    v-if="
                        !historyNote.audits || historyNote.audits.length === 0
                    "
                    class="py-8 text-center text-sm text-(--ui-text-muted)"
                >
                    No audit history available.
                </div>
                <ol
                    v-else
                    class="relative ml-3 space-y-4 border-l border-gray-200 dark:border-gray-700"
                >
                    <li
                        v-for="audit in historyNote.audits"
                        :key="audit.id"
                        class="ml-4"
                    >
                        <span
                            class="absolute -left-1.5 flex size-3 items-center justify-center rounded-full border-2 border-white bg-(--ui-primary) dark:border-gray-900"
                        />
                        <div class="flex flex-wrap items-center gap-2">
                            <UBadge
                                :color="eventColors[audit.event] as any"
                                variant="subtle"
                                size="xs"
                            >
                                {{ eventLabels[audit.event] }}
                            </UBadge>
                            <span
                                class="text-sm font-medium text-(--ui-text-highlighted)"
                            >
                                {{ audit.actor?.name ?? 'Unknown' }}
                            </span>
                            <span class="text-xs text-(--ui-text-muted)">
                                {{ formatDate(audit.created_at) }}
                            </span>
                        </div>
                        <ul
                            v-if="auditDiff(audit).length > 0"
                            class="mt-1 space-y-0.5"
                        >
                            <li
                                v-for="change in auditDiff(audit)"
                                :key="change"
                                class="text-xs text-(--ui-text-muted)"
                            >
                                &bull; {{ change }}
                            </li>
                        </ul>
                        <p
                            v-else-if="
                                audit.event === 'created' && audit.new_values
                            "
                            class="mt-1 text-xs text-(--ui-text-muted)"
                        >
                            Amount: R
                            {{
                                Number(
                                    audit.new_values['amount'],
                                ).toLocaleString('en-ZA', {
                                    minimumFractionDigits: 2,
                                })
                            }}
                            &middot; {{ audit.new_values['payment_date'] }}
                        </p>
                    </li>
                </ol>
            </div>
        </template>
        <template #footer>
            <div class="flex justify-end">
                <UButton
                    label="Close"
                    color="neutral"
                    variant="ghost"
                    @click="closeHistory"
                />
            </div>
        </template>
    </UModal>

    <!-- ── Create / Edit Slideover ─────────────────────────────────────────── -->
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
