<script setup lang="ts">
import RewardsHeader from '@/components/RewardsHeader.vue';
import { onMounted, onUpdated, ref, unref } from 'vue';
import { gsap } from "gsap";

defineProps<{
    title?: string;
    description?: string;
    noSpacing?: boolean;
}>();


</script>

<template>
    <div class="relative flex min-h-svh flex-col items-center bg-black">
        <RewardsHeader :class="noSpacing ? 'fixed top-0 left-0 w-full' : ''" />
        <div class="relative z-10 w-full" data-reveal-me>
            <div class="w-full h-[100px] bg-gradient-to-b from-black/0 to-black/80 absolute -top-[100px] left-0"></div>
            <div class="bg-black/80 flex w-full flex-col gap-6 items-center">
                <div class="flex flex-col w-full">
                    <slot />
                </div>
            </div>
        </div>
    </div>
</template>
