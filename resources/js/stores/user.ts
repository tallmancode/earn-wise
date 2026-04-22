import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useUserStore = defineStore('user', () => {
    const name = ref<string | null>(null);
    const email = ref<string | null>(null);

    function setUser(userData: { name: string; email: string }) {
        name.value = userData.name;
        email.value = userData.email;
    }

    function clear() {
        name.value = null;
        email.value = null;
    }

    return { name, email, setUser, clear };
});
