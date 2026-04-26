<script setup lang="ts">
import type { DropdownMenuItem } from '@nuxt/ui';
import { computed } from 'vue';
import { useCompanyStore } from '@/stores/company';

defineProps<{
    collapsed?: boolean;
}>();

const companyStore = useCompanyStore();

const buttonLabel = computed(() => {
    const company = companyStore.selectedCompany;

    if (company) {
        return company.name;
    }

    if (companyStore.companies.length === 0) {
        return 'No companies';
    }

    return 'Select company';
});

const items = computed<DropdownMenuItem[][]>(() => {
    const groups: DropdownMenuItem[][] = [];

    if (companyStore.companies.length > 0) {
        groups.push(
            companyStore.companies.map((c) => ({
                label: c.name,
                type: 'checkbox' as const,
                checked: companyStore.selectedCompanyId === c.id,
                onSelect: () => companyStore.switchCompany(c.id),
            })),
        );
    }

    return groups;
});
</script>

<template>
    <UDropdownMenu
        :items="items"
        :disabled="companyStore.companies.length === 0"
        :content="{ align: 'center', collisionPadding: 12 }"
        :ui="{
            content: collapsed
                ? 'w-48'
                : 'w-(--reka-dropdown-menu-trigger-width)',
        }"
    >
        <UButton
            v-bind="{
                label: collapsed ? undefined : buttonLabel,
                icon: 'i-lucide-building-2',
                trailingIcon: collapsed
                    ? undefined
                    : 'i-lucide-chevrons-up-down',
            }"
            color="neutral"
            variant="ghost"
            block
            :square="collapsed"
            :disabled="companyStore.companies.length === 0"
            :class="[!collapsed && 'py-2']"
            :ui="{
                base: 'hover:bg-white/15 data-[state=open]:bg-white/15 text-white',
            }"
        />
    </UDropdownMenu>
</template>
