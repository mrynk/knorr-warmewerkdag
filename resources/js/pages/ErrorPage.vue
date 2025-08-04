<script setup lang="ts">
import CampaignLayout from '@/layouts/CampaignLayout.vue';

</script>

<template>
    <CampaignLayout :no-spacing="true">
        <div class="flex flex-col items-center justify-center h-screen">
            <h1 class="text-4xl font-bold">Error</h1>
            <p class="text-lg">Oeps, er is iets misgegaan.</p>
            <p class="text-lg">
                <a href="/" class="text-blue-500">Terug naar de startpagina</a>
            </p>
        </div>
    </CampaignLayout>
</template>