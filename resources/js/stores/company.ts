import type { Company } from '@/types/auth';
import { usePage, router } from '@inertiajs/vue3';
import { defineStore } from 'pinia';
import { computed } from 'vue';

export const useCompanyStore = defineStore('company', () => {
    const page = usePage();

    const companies = computed<Company[]>(
        () => page.props.auth?.companies ?? [],
    );

    const selectedCompanyId = computed<number | null>(
        () => page.props.auth?.selectedCompanyId ?? null,
    );

    const selectedCompany = computed<Company | undefined>(() =>
        companies.value.find((c) => c.id === selectedCompanyId.value),
    );

    function switchCompany(id: number): void {
        router.post(
            '/company/switch',
            { company_id: id },
            { preserveScroll: true },
        );
    }

    return { companies, selectedCompanyId, selectedCompany, switchCompany };
});
