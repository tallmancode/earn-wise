<script setup lang="ts"></script>

<template>
    <div class="flex min-h-screen flex-col bg-(--ui-bg)">
        <!--        <header class="border-b border-(&#45;&#45;ui-border) bg-(&#45;&#45;ui-bg)">-->
        <!--            <UContainer>-->
        <!--                <div class="flex h-16 items-center justify-between">-->
        <!--                    <Link href="/" class="flex items-center">-->
        <!--                        <AppLogo class="h-8 w-auto" />-->
        <!--                    </Link>-->
        <!--                    <nav class="flex items-center gap-2">-->
        <!--                        <UButton as-child color="neutral" variant="ghost">-->
        <!--                            <Link href="/">Home</Link>-->
        <!--                        </UButton>-->
        <!--                        <UButton as-child color="primary" variant="solid">-->
        <!--                            <Link href="/login">Login</Link>-->
        <!--                        </UButton>-->
        <!--                    </nav>-->
        <!--                </div>-->
        <!--            </UContainer>-->
        <!--        </header>-->

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
