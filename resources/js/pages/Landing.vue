<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { useWindowScroll } from '@vueuse/core';
import { gsap } from 'gsap';
import { ArrowBigDown, ChevronRight, Loader2 } from 'lucide-vue-next';
import { computed, onMounted, ref, Transition } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    PinInput,
    PinInputGroup,
    PinInputSlot,
} from '@/components/ui/pin-input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { landing, redeem } from '@/routes';
import type Entry from '@/types/Entry';

const props = defineProps<{
    entry?: Entry;
    soups: Record<
        string,
        { id: string; name: string; advise: string; video: string }
    >;
    soup_of_the_day: {
        id: string;
        name: string;
        advise: string;
        video: string;
    };
}>();

const form = useForm({
    code: [] as string[],
    name: '',
    email: '',
    soup: '',
}).transform((data) => {
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
        stagger: 0.15,
    });
});

const dots = ref('');
setInterval(() => {
    dots.value = dots.value + '.';
    if (dots.value.length > 3) dots.value = '';
}, 333);

const revealDelay = ref(false);
const submit = () => {
    form.post(redeem.url(), {
        preserveScroll: true,
        onSuccess: () => {
            window.scrollTo(0, 0);
            revealDelay.value = true;
            setTimeout(() => {
                revealDelay.value = false;
            }, 4000);
        },
    });
};

const focusName = () => {
    document.getElementById('name')?.focus();
};

const footerSoup = computed(() => {
    if (props.entry && props.entry.soup !== 'andere') {
        return props.soups[props.entry.soup];
    }
    return props.soup_of_the_day;
});
</script>

<template>
    <div class="bg-gradient-to-b from-black via-black via-60% to-[#4E8B45]">
        <div
            class="relative flex w-full max-w-screen flex-col items-center overflow-hidden"
            v-if="!entry"
        >
            <div class="h-auto w-full max-w-lg p-8">
                <img
                    src="/static/title.png"
                    alt="Soep op? Tijd voor je prijs@"
                    class="h-auto w-full"
                    data-reveal-me
                />
            </div>
            <img
                src="/static/rewards-visual.jpg"
                alt="Rewards"
                class="inline-block h-auto w-[150%] max-w-none md:w-full lg:max-w-2xl"
                data-reveal-me
            />
            <Transition name="fade">
                <ArrowBigDown
                    class="absolute bottom-0 left-1/2 z-20 h-6 w-6 -translate-x-1/2 animate-bounce xl:h-10 xl:w-10"
                    v-show="y === 0"
                />
            </Transition>
        </div>
        <div
            class="relative mx-auto flex max-w-xl flex-col items-center gap-8 p-6"
        >
            <div
                class="relative flex w-full flex-col items-center gap-4"
                data-reveal-me
            >
                <div
                    class="w-full rounded-xl bg-white py-16 text-center text-black shadow-xl"
                    v-if="revealDelay"
                >
                    <span class="py-16 text-xl font-bold">
                        We controleren je code<span
                            class="inline-block w-5 text-left"
                            >{{ dots }}</span
                        >
                    </span>
                </div>
                <Transition name="fade">
                    <template v-if="!revealDelay">
                        <div
                            :class="
                                (entry.reward ? 'bg-[#4E8B45]' : 'bg-white') +
                                ' w-full rounded-xl shadow-xl'
                            "
                            v-if="entry"
                        >
                            <div
                                class="flex w-full flex-col items-center gap-4"
                                v-if="entry.reward"
                            >
                                <div
                                    class="flex flex-col items-center gap-4 p-8"
                                >
                                    <img
                                        src="/static/reward-title.png"
                                        alt="No reward"
                                        class="h-auto w-full"
                                    />
                                    <p>
                                        Geniet van je soep, je prijs komt eraan*
                                    </p>
                                    <img
                                        :src="`/static/rewards/${entry.reward.name}.png`"
                                        :alt="entry.reward.name"
                                        class="h-auto w-1/2"
                                    />
                                </div>
                                <div
                                    class="w-full bg-white p-4 text-center text-black"
                                    v-html="entry.reward.description"
                                ></div>
                                <div
                                    class="w-full rounded-b-xl p-4 pb-8 text-center"
                                >
                                    <p class="text-sm">
                                        *Je ontvangt een mail op
                                        <strong>{{
                                            entry.masked_email
                                        }}</strong>
                                        van ons om je gegevens door te geven.
                                        Check mogelijk je spambox.
                                    </p>
                                </div>
                            </div>
                            <div
                                class="flex w-full flex-col items-center gap-4 p-8"
                                v-else
                            >
                                <img
                                    src="/static/no-reward-title.png"
                                    alt="No reward"
                                    class="h-auto w-full"
                                />
                                <img
                                    src="/static/giphy1.gif"
                                    alt="No reward"
                                    class="h-auto w-full"
                                />
                            </div>
                        </div>
                    </template>
                </Transition>
                <form
                    @submit.prevent="submit()"
                    data-reveal-me
                    v-if="!entry"
                    class="flex w-full flex-col items-center gap-4"
                >
                    <p
                        class="p-2 text-center text-2xl font-bold text-white"
                        data-reveal-me
                    >
                        Vul je code in en ontdek direct of je prijs hebt!
                    </p>
                    <div
                        v-if="form.errors.message"
                        class="w-full text-left text-sm text-red-300"
                    >
                        &#x2757; {{ form.errors.message }}
                    </div>
                    <PinInput
                        id="code-input"
                        name="code-input"
                        v-model="form.code"
                        placeholder="-"
                        class="h-16"
                        @complete="focusName()"
                    >
                        <PinInputGroup class="h-16 w-full">
                            <PinInputSlot
                                v-for="(id, index) in 8"
                                :key="id"
                                :index="index"
                                class="h-16 w-full text-2xl uppercase dark:bg-white dark:text-black dark:placeholder:text-black/80"
                            />
                        </PinInputGroup>
                    </PinInput>
                    <div
                        v-if="form.errors.code"
                        class="w-full text-left text-sm text-red-300"
                    >
                        &#x2757; {{ form.errors.code }}
                    </div>
                    <Input
                        type="text"
                        placeholder="Naam"
                        name="name"
                        id="name"
                        autocomplete="name"
                        v-model="form.name"
                        required
                        class="w-full text-xl placeholder:text-xl md:text-xl dark:bg-white dark:text-black dark:placeholder:text-black/80"
                    />
                    <div
                        v-if="form.errors.name"
                        class="w-full text-left text-sm text-red-300"
                    >
                        &#x2757; {{ form.errors.name }}
                    </div>
                    <Input
                        type="email"
                        name="email"
                        id="email"
                        autocomplete="email"
                        placeholder="E-mail"
                        v-model="form.email"
                        required
                        class="w-full text-xl placeholder:text-xl md:text-xl dark:bg-white dark:text-black dark:placeholder:text-black/80"
                    />
                    <div
                        v-if="form.errors.email"
                        class="w-full text-left text-sm text-red-300"
                    >
                        &#x2757; {{ form.errors.email }}
                    </div>
                    <div class="relative flex w-full flex-col items-center">
                        <Select
                            v-model="form.soup"
                            name="soup"
                            required
                            id="soup"
                            autocomplete="soup"
                        >
                            <SelectTrigger
                                class="w-full text-xl dark:bg-white dark:text-black dark:placeholder:text-black/80 dark:hover:bg-white/70"
                            >
                                <SelectValue
                                    placeholder="Welke soep heb je gegeten?"
                                />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="soup in soups"
                                    :key="soup.id"
                                    :value="soup.id"
                                >
                                    {{ soup.name }}
                                </SelectItem>
                                <SelectItem value="andere "
                                    >andere soep</SelectItem
                                >
                            </SelectContent>
                        </Select>
                        <div
                            v-if="form.errors.soup"
                            class="w-full text-left text-sm text-red-300"
                        >
                            &#x2757; {{ form.errors.soup }}
                        </div>
                    </div>
                    <p class="text-center text-xs text-white">
                        Door deel te nemen met deze actie ga je akkoord met onze
                        <a
                            href="/static/actievoorwaarden.pdf"
                            class="text-white underline"
                            >actievoorwaarden</a
                        >.
                    </p>
                    <Button
                        type="submit"
                        class="w-2/3 cursor-pointer bg-[#F8BA00] text-white hover:bg-[#F8BA00]/80"
                        :disabled="form.processing"
                        ><span>Check mijn code</span>
                        <ChevronRight class="h-4 w-4" v-if="!form.processing" />
                        <Loader2
                            class="h-4 w-4 animate-spin"
                            v-if="form.processing"
                        />
                    </Button>
                </form>
            </div>
        </div>
    </div>
    <div class="w-full bg-[#4E8B45]">
        <div
            class="mx-auto flex max-w-4xl flex-col items-center gap-8 px-6 py-10"
        >
            <img
                src="/static/soup-title.png"
                alt="Duik in de soep met onze chefs"
                class="h-auto w-3/4 max-w-xl md:my-8"
            />
            <video
                :src="soup_of_the_day.video"
                controls
                inline
                class="aspect-4/5 w-full object-cover xl:aspect-4/5 xl:w-2/3"
            />
        </div>
    </div>
    <div class="w-full bg-black">
        <div
            class="mx-auto flex max-w-4xl flex-col items-center gap-8 px-6 py-10"
        >
            <h2 class="text-center font-title text-6xl font-bold text-white">
                Je {{ footerSoup.name }} op?
            </h2>
            <p class="text-center text-lg text-white">
                Probeer onze {{ soups[footerSoup.advise].name }} en doe opnieuw
                mee!
            </p>
            <img
                :src="`/static/${soups[footerSoup.advise].id}.jpg`"
                alt="Rewards"
                class="inline-block h-auto md:w-full lg:max-w-2xl"
                data-reveal-me
            />
            <Button
                class="cursor-pointer bg-[#F8BA00] text-white hover:bg-[#F8BA00]/80"
                :href="landing.url()"
                as="a"
                v-if="entry"
            >
                Probeer het opnieuw!
            </Button>
        </div>
    </div>
    <div class="w-full bg-white">
        <div
            class="flex flex-col items-center justify-center gap-4 px-6 py-8 md:flex-row"
        >
            <img src="/static/brandbar.svg" alt="UFS Logo" class="h-12" />
            <a
                href="/static/actievoorwaarden.pdf"
                class="text-sm text-black underline"
                >Actievoorwaarden</a
            >
            <p class="text-center text-sm text-black">
                Voor vragen omtrent deze actie kun je contact opnemen met de
                <a
                    href="mailto:de.klantenservice@ufs.nl?subject=warmewerkdag.nl"
                    class="text-black underline"
                    >klantenservice</a
                >
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
