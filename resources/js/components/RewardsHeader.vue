<script setup lang="ts">
import { cn } from '@/lib/utils';

const props = defineProps<{
    class?: string;
}>();
</script>

<template>
    <div :class="cn(' bg-black w-full h-[40lvh] overflow-hidden', props.class)">
        <div class="relative w-full h-[40lvh]" data-reveal-me>
            <img src="/static/wood.jpg" alt="Rewards Header"
                class="w-[200%] max-w-none h-1/2 object-fill absolute bottom-0 -left-1/2 xl:w-full xl:-left-0" />
            <img src="/static/rewards.png" alt="Rewards Header"
                class="w-3/4 h-auto absolute bottom-1/6 right-1/2 translate-x-1/2 xs:w-1/2 md:w-1/3 lg:w-1/4 xl:w-1/5" />
        </div>
    </div>
</template>