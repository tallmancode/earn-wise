<script setup lang="ts"></script>

<template>
    <div class="flex min-h-screen flex-col bg-(--ui-bg)">
        <main class="flex flex-1 flex-col">
            <slot />
        </main>

        <footer class="border-t border-(--ui-border) py-6">
            <UContainer>
                <p class="text-center text-sm text-(--ui-text-muted)">
                    &copy; {{ new Date().getFullYear() }} EarnWise. All rights
                    reserved.
                </p>
            </UContainer>
        </footer>
    </div>
</template>
