<script setup lang="ts">
import { landing } from '@/routes';

defineProps<{
    status?: number;
}>();
</script>

<template>
    <div class="bg-gradient-to-b from-black via-black to-[#4E8B45]">
        <div class="flex h-screen flex-col items-center justify-center">
            <h1 class="text-4xl font-bold">Oeps</h1>
            <p class="text-lg">Er is iets misgegaan.</p>
            <p class="text-lg">
                <a :href="landing.url()" class="text-blue-500"
                    >Terug naar de startpagina</a
                >
            </p>
        </div>
    </div>
</template>
