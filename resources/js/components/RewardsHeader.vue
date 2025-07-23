<script setup lang="ts">

</script>

<template>
    <div class="relative flex w-full h-[50lvh] overflow-hidden flex-col gap-6 md:h-[60lvh] xl:h-[75lvh]">
        <div class="fixed z-0 w-full  h-[50lvh] md:h-[60lvh] xl:h-[75lvh]">
            <img src="/static/wood.jpg" alt="Rewards Header"
                class="w-[200%] max-w-none h-1/2 object-fill absolute bottom-0 -left-1/2 xl:w-full xl:-left-0" />
            <div class="flex w-full h-1/2 bg-gradient-to-b from-[#4E8B45] to-black/0 absolute top-0 left-0"></div>
            <img src="/static/rewards.png" alt="Rewards Header"
                class="w-1/2 h-auto absolute bottom-1/6 right-1/2 translate-x-1/2 md:w-1/3 xl:w-1/4" />
        </div>
    </div>
</template>