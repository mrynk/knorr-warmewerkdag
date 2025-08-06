<script setup lang="ts">
import { computed, onMounted, onUpdated, ref, Transition } from 'vue';
import CampaignLayout from '@/layouts/CampaignLayout.vue';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import {
    PinInput,
    PinInputGroup,
    PinInputSlot,
} from '@/components/ui/pin-input'
import { ArrowBigDown, ChevronRight, Loader2 } from 'lucide-vue-next';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { useForm } from '@inertiajs/vue3';
import { useWindowScroll } from '@vueuse/core';
import type Entry from '@/types/Entry';
import { gsap } from 'gsap';

const props = defineProps<{
    entry?: Entry;
}>();

const form = useForm({
    code: '',
    name: '',
    email: '',
    soup: '',
}).transform(data => {
    return {
        ...data,
        code: data.code.join('').toUpperCase(),
    };
});

const { y } = useWindowScroll();

onMounted(() => {
    gsap.from('[data-reveal-me]', {
        translateY: -50,
        autoAlpha: 0,
        duration: 0.5,
        ease: 'sine.out',
        stagger: 0.15
    });
    console.log('mounted');
});

const revealDelay = ref(false);
onUpdated(() => {
    console.log('updated');
    if (!props.entry) return;
    revealDelay.value = true;
    setTimeout(() => {
        revealDelay.value = false;
    }, 20000);
});

const dots = ref('');
setInterval(() => {
    dots.value = dots.value + '.';
    if (dots.value.length > 3) dots.value = '';
}, 333);

</script>

<template>
    <CampaignLayout :no-spacing="entry">
        <div class="bg-gradient-to-b from-black/0 via-black/0 to-[#4E8B45]">
            <div class="relative flex flex-col gap-8 items-center px-6 py-10 max-w-xl mx-auto">
                <Transition name="fade">
                    <ArrowBigDown
                        class="absolute z-20 -top-10 left-1/2 -translate-x-1/2 w-6 h-6 xl:h-10 xl:w-10 animate-bounce"
                        v-show="y === 0" />
                </Transition>
                <img src="/static/title.png" alt="Soep op? Tijd voor je prijs@" class="w-full h-auto" data-reveal-me />

                <div class="relative flex flex-col gap-4 w-full items-center" data-reveal-me>
                    <div class="w-full bg-white rounded-xl text-black text-center py-16" v-if="revealDelay">
                        <span class="text-xl font-bold py-16">
                            We controleren je code<span class="w-5 inline-block text-left">{{ dots }}</span>
                        </span>
                    </div>
                    <Transition name="fade">
                        <template v-if="!revealDelay">
                            <div :class="(entry.reward ? 'bg-[#4E8B45]' : 'bg-white') + ' w-full rounded-xl'"
                                v-if="entry">
                                <div class="flex flex-col gap-4 w-full items-center" v-if="entry.reward">
                                    <div class="p-8 flex flex-col gap-4 items-center">
                                        <img src="/static/reward-title.png" alt="No reward" class="w-full h-auto" />
                                        <p>Geniet van je soep,<br />je prijs komt eraan!</p>
                                        <img :src="`/static/rewards/${entry.reward.name}.png`" :alt="entry.reward.name"
                                            class="w-1/2 h-auto" />
                                    </div>
                                    <div class="bg-white text-black w-full rounded-b-xl p-8 text-center">
                                        {{ entry.reward.description }}
                                    </div>

                                </div>
                                <div class="flex flex-col gap-4 w-full items-center p-8" v-else>
                                    <img src="/static/no-reward-title.png" alt="No reward" class="w-full h-auto" />
                                    <img src="/static/giphy1.gif" alt="No reward" class="w-full h-auto" />
                                    <Button as="a" type="button"
                                        class="w-full uppercase bg-[#4E8B45] hover:bg-[#4E8B45]/80 h-auto"
                                        :href="route('landing')">
                                        <img src="/static/cta.png" alt="Opnieuw" class="w-1/2 h-auto" />
                                    </Button>
                                    <p class="text-center text-black font-bold text-md">Doe zo vaak mee als je wilt</p>
                                </div>
                            </div>
                        </template>
                    </Transition>
                    <form @submit.prevent="form.post('/redeem', { preserveScroll: true })" data-reveal-me v-if="!entry"
                        class="flex flex-col gap-4 w-full items-center">
                        <p class="text-center text-white text-2xl font-bold p-2" data-reveal-me>Vul je code in en ontdek
                            direct of je prijs hebt!
                        </p>
                        <div v-if="form.errors.message" class="text-red-300 text-sm text-left w-full">&#x2757; {{
                            form.errors.message }}
                        </div>
                        <PinInput id="pin-input" v-model="form.code" placeholder="-" class="h-16">
                            <PinInputGroup class="w-full h-16">
                                <PinInputSlot v-for="(id, index) in 8" :key="id" :index="index"
                                    class="w-full h-16 dark:bg-white dark:text-black dark:placeholder:text-black/80 text-2xl m-1 uppercase" />
                            </PinInputGroup>
                        </PinInput>
                        <div v-if="form.errors.code" class="text-red-300 text-sm text-left w-full">&#x2757; {{
                            form.errors.code }}
                        </div>
                        <Input type="text" placeholder="Naam" v-model="form.name" required class="w-full text-xl 
                            dark:bg-white dark:text-black dark:placeholder:text-black/80
                            placeholder:text-xl
                            md:text-xl" />
                        <div v-if="form.errors.name" class="text-red-300 text-sm text-left w-full">&#x2757; {{
                            form.errors.name }}
                        </div>
                        <Input type="email" placeholder="E-mail" v-model="form.email" required
                            class="w-full text-xl dark:bg-white dark:text-black dark:placeholder:text-black/80 placeholder:text-xl md:text-xl" />
                        <div v-if="form.errors.email" class="text-red-300 text-sm text-left w-full">&#x2757; {{
                            form.errors.email }}
                        </div>
                        <div class="w-full relative flex flex-col items-center">
                            <Select v-model="form.soup" name="soup" required>
                                <SelectTrigger
                                    class="w-full text-xl dark:bg-white dark:hover:bg-white/70 dark:text-black dark:placeholder:text-black/80">
                                    <SelectValue placeholder="Welke soep heb je gegeten?" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="tomatensoep">Tomatensoep</SelectItem>
                                    <SelectItem value="erwtensoep">Erwtensoep</SelectItem>
                                    <SelectItem value="kippensoep">Kippensoep</SelectItem>
                                    <SelectItem value="groentesoep">Groentesoep</SelectItem>
                                </SelectContent>
                            </Select>
                            <div v-if="form.errors.soup" class="text-red-300 text-sm text-left w-full">&#x2757; {{
                                form.errors.soup }}
                            </div>
                        </div>
                        <p class="text-sm text-white p-2 text-center">
                            Door deel te nemen met deze actie ga je akkoord met onze <a href="#"
                                class="text-white underline">actievoorwaarden</a>.
                        </p>
                        <Button type="submit" class="w-2/3 bg-[#F8BA00] text-white hover:bg-[#F8BA00]/80"
                            :disabled="form.processing"><span>Check mijn code</span>
                            <ChevronRight class="w-4 h-4" v-if="!form.processing" />
                            <Loader2 class="w-4 h-4 animate-spin" v-if="form.processing" />
                        </Button>
                    </form>
                </div>

            </div>
        </div>
        <div class="w-full bg-[#4E8B45]">
            <div class="flex flex-col gap-8 items-center px-6 py-10 max-w-4xl mx-auto">
                <img src="/static/soup-title.png" alt="Soep op? Tijd voor je prijs@"
                    class="w-3/4 h-auto md:my-8 max-w-xl" />
                <video src="/static/video.mp4" controls class="aspect-1/1 object-cover w-full xl:aspect-16/9" />
            </div>
        </div>
    </CampaignLayout>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    opacity: 1;
    transition: opacity 0.5s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>