<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import RewardsHeader from '@/components/RewardsHeader.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Link } from '@inertiajs/vue3';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div class="flex min-h-svh flex-col items-center bg-black">
        <RewardsHeader />
        <div class="relative z-10 w-full">
            <div class="w-full h-[100px] bg-gradient-to-b from-black/0 to-black/80 absolute -top-[100px] left-0"></div>
            <div class="bg-black/80 flex w-full flex-col gap-6 items-center">
                <div class="flex flex-col w-full">
                    <slot />
                </div>
            </div>
        </div>
    </div>
</template>
