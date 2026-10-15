<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { landing } from '@/routes';

defineProps<{
    status?: number;
}>();
</script>

<template>
    <div
        class="flex min-h-svh flex-col bg-gradient-to-b from-black via-black via-60% to-[#4E8B45] text-white selection:bg-[#F8BA00] selection:text-black"
    >
        <Head title="(Nog) niets te vinden hier helaas." />

        <main
            class="mx-auto flex w-full max-w-lg flex-1 flex-col items-center justify-center gap-8 px-6 py-10 text-center"
        >
            <h1
                class="font-title text-4xl leading-tight font-bold text-balance text-white sm:text-5xl"
            >
                (Nog) niets te vinden hier helaas.
            </h1>
            <!-- Button
                as="a"
                :href="landing.url()"
                size="lg"
                class="h-11 cursor-pointer bg-[#F8BA00] px-6 text-white hover:bg-[#F8BA00]/80"
            >
                Terug naar de startpagina
            </Button -->
        </main>

        <footer class="bg-white text-black">
            <div class="flex items-center justify-center px-6 py-8">
                <img src="/static/brandbar.svg" alt="UFS Logo" class="h-12" />
            </div>
        </footer>
    </div>
</template>
