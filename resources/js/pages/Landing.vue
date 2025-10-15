<script setup lang="ts">
import { computed, onMounted, ref, Transition } from 'vue';
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
    soups: Record<string, { id: string; name: string; advise: string; image: string }>;
    soup_of_the_day: { id: string; name: string; advise: string; image: string };
}>();

console.log(props.soups, props.entry);

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
});


const dots = ref('');
setInterval(() => {
    dots.value = dots.value + '.';
    if (dots.value.length > 3) dots.value = '';
}, 333);

const revealDelay = ref(false);
const submit = () => {
    form.post('/redeem', {
        preserveScroll: true,
        onSuccess: () => {
            window.scrollTo(0, 0);
            revealDelay.value = true;
            setTimeout(() => {
                revealDelay.value = false;
            }, 4000);
        }
    });
};

const focusName = () => {
    document.getElementById('name')?.focus();
};

const footerSoup = computed(() => {
    if (props.entry && props.entry.soup !== 'andere soep') {
        return props.soups[props.entry.soup];
    }
    return props.soup_of_the_day;
});

console.log('footerSoup', footerSoup.value);

</script>

<template>
    <div class="bg-gradient-to-b from-black via-black via-60% to-[#4E8B45]">
        <div class="relative flex flex-col items-center w-full max-w-screen overflow-hidden" v-if="!entry">
            <div class="max-w-lg w-full h-auto p-8">
                <img src="/static/title.png" alt="Soep op? Tijd voor je prijs@" class="w-full h-auto" data-reveal-me />
            </div>
            <img src="/static/rewards-visual.jpg" alt="Rewards"
                class="inline-block w-[150%] max-w-none md:w-full lg:max-w-2xl h-auto" data-reveal-me />
            <Transition name="fade">
                <ArrowBigDown
                    class="absolute z-20 bottom-0 left-1/2 -translate-x-1/2 w-6 h-6 xl:h-10 xl:w-10 animate-bounce"
                    v-show="y === 0" />
            </Transition>
        </div>
        <div class="relative flex flex-col gap-8 items-center p-6 max-w-xl mx-auto">
            <div class="relative flex flex-col gap-4 w-full items-center" data-reveal-me>
                <div class="w-full bg-white rounded-xl text-black text-center py-16 shadow-xl" v-if="revealDelay">
                    <span class="text-xl font-bold py-16">
                        We controleren je code<span class="w-5 inline-block text-left">{{ dots }}</span>
                    </span>
                </div>
                <Transition name="fade">
                    <template v-if="!revealDelay">
                        <div :class="(entry.reward ? 'bg-[#4E8B45]' : 'bg-white') + ' w-full rounded-xl shadow-xl'"
                            v-if="entry">
                            <div class="flex flex-col gap-4 w-full items-center" v-if="entry.reward">
                                <div class="p-8 flex flex-col gap-4 items-center">
                                    <img src="/static/reward-title.png" alt="No reward" class="w-full h-auto" />
                                    <p>Geniet van je soep, je prijs komt eraan*</p>
                                    <img :src="`/static/rewards/${entry.reward.name}.png`" :alt="entry.reward.name"
                                        class="w-1/2 h-auto" />
                                </div>
                                <div class="bg-white text-black w-full p-4 text-center"
                                    v-html="entry.reward.description">
                                </div>
                                <div class=" w-full rounded-b-xl text-center p-4 pb-8">
                                    <p class="text-sm">*Je ontvangt een mail op
                                        <strong>{{
                                            entry.masked_email
                                        }}</strong> van ons om je gegevens door te geven. Check mogelijk je spambox.
                                    </p>
                                </div>

                            </div>
                            <div class="flex flex-col gap-4 w-full items-center p-8" v-else>
                                <img src="/static/no-reward-title.png" alt="No reward" class="w-full h-auto" />
                                <img src="/static/giphy1.gif" alt="No reward" class="w-full h-auto" />
                            </div>
                        </div>
                    </template>
                </Transition>
                <form @submit.prevent="submit()" data-reveal-me v-if="!entry"
                    class="flex flex-col gap-4 w-full items-center">
                    <p class="text-center text-white text-2xl font-bold p-2" data-reveal-me>Vul je code in en ontdek
                        direct of je prijs hebt!
                    </p>
                    <div v-if="form.errors.message" class="text-red-300 text-sm text-left w-full">&#x2757; {{
                        form.errors.message }}
                    </div>
                    <PinInput id="code-input" name="code-input" v-model="form.code" placeholder="-" class="h-16"
                        @complete="focusName()">
                        <PinInputGroup class="w-full h-16">
                            <PinInputSlot v-for="(id, index) in 8" :key="id" :index="index"
                                class="w-full h-16 dark:bg-white dark:text-black dark:placeholder:text-black/80 text-2xl uppercase" />
                        </PinInputGroup>
                    </PinInput>
                    <div v-if="form.errors.code" class="text-red-300 text-sm text-left w-full">&#x2757; {{
                        form.errors.code }}
                    </div>
                    <Input type="text" placeholder="Naam" name="name" id="name" autocomplete="name" v-model="form.name"
                        required class="w-full text-xl 
                        dark:bg-white dark:text-black dark:placeholder:text-black/80
                        placeholder:text-xl
                        md:text-xl" />
                    <div v-if="form.errors.name" class="text-red-300 text-sm text-left w-full">&#x2757; {{
                        form.errors.name }}
                    </div>
                    <Input type="email" name="email" id="email" autocomplete="email" placeholder="E-mail"
                        v-model="form.email" required
                        class="w-full text-xl dark:bg-white dark:text-black dark:placeholder:text-black/80 placeholder:text-xl md:text-xl" />
                    <div v-if="form.errors.email" class="text-red-300 text-sm text-left w-full">&#x2757; {{
                        form.errors.email }}
                    </div>
                    <div class="w-full relative flex flex-col items-center">
                        <Select v-model="form.soup" name="soup" required id="soup" autocomplete="soup">
                            <SelectTrigger
                                class="w-full text-xl dark:bg-white dark:hover:bg-white/70 dark:text-black dark:placeholder:text-black/80">
                                <SelectValue placeholder="Welke soep heb je gegeten?" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="soup in soups" :key="soup.id" :value="soup.id">
                                    {{ soup.name }}
                                </SelectItem>
                                <SelectItem value="andere ">andere soep</SelectItem>
                            </SelectContent>
                        </Select>
                        <div v-if="form.errors.soup" class="text-red-300 text-sm text-left w-full">&#x2757; {{
                            form.errors.soup }}
                        </div>
                    </div>
                    <p class="text-xs text-white text-center">
                        Door deel te nemen met deze actie ga je akkoord met onze <a href="/static/actievoorwaarden.pdf"
                            class="text-white underline">actievoorwaarden</a>.
                    </p>
                    <Button type="submit" class="w-2/3 bg-[#F8BA00] text-white hover:bg-[#F8BA00]/80 cursor-pointer"
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
            <img src="/static/soup-title.png" alt="Duik in de soep met onze chefs"
                class="w-3/4 h-auto md:my-8 max-w-xl" />
            <video :src="soup_of_the_day.video" controls inline
                class="aspect-4/5 object-cover w-full xl:aspect-4/5 xl:w-2/3" />
        </div>
    </div>
    <div class="w-full bg-black">
        <div class="flex flex-col gap-8 items-center px-6 py-10 max-w-4xl mx-auto">
            <h2 class="text-white text-6xl font-bold text-center font-title">Je {{ footerSoup.name }} op?</h2>
            <p class="text-white text-lg text-center">
                Probeer onze {{ soups[footerSoup.advise].name }} en doe opnieuw mee!
            </p>
            <img :src="`/static/${soups[footerSoup.advise].id}.jpg`" alt="Rewards"
                class="inline-block md:w-full lg:max-w-2xl h-auto" data-reveal-me />
            <Button class="bg-[#F8BA00] text-white hover:bg-[#F8BA00]/80 cursor-pointer" href="/" as="a" v-if="entry">
                Probeer het opnieuw!
            </Button>
        </div>
    </div>
    <div class="w-full bg-white">
        <div class="flex flex-col md:flex-row items-center justify-center gap-4 px-6 py-8">
            <img src="/static/brandbar.svg" alt="UFS Logo" class="h-12" />
            <a href="/static/actievoorwaarden.pdf" class="text-black text-sm underline">Actievoorwaarden</a>
            <p class="text-black text-sm text-center">Voor vragen omtrent deze actie kun je contact opnemen met
                de <a href="mailto:de.klantenservice@ufs.nl?subject=warmewerkdag.nl"
                    class="text-black underline">klantenservice</a>
            </p>
        </div>
    </div>
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