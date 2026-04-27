import type { CommissionNote } from '@/types/auth';
import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useNoteStore = defineStore('notes', () => {
    const selectedNote = ref<CommissionNote | null>(null);
    const isPanelOpen = ref(false);
    const filterEmployeeId = ref<number | null>(null);

    function selectNote(note: CommissionNote) {
        selectedNote.value = note;
        isPanelOpen.value = true;
    }

    function openCreate() {
        selectedNote.value = null;
        isPanelOpen.value = true;
    }

    function closePanel() {
        selectedNote.value = null;
        isPanelOpen.value = false;
    }

    function setFilter(employeeId: number | null) {
        filterEmployeeId.value = employeeId;
    }

    return {
        selectedNote,
        isPanelOpen,
        filterEmployeeId,
        selectNote,
        openCreate,
        closePanel,
        setFilter,
    };
});
