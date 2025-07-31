<script setup lang="ts">
import { ref } from 'vue';
import CampaignLayout from '@/layouts/CampaignLayout.vue';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import {
    PinInput,
    PinInputGroup,
    PinInputSlot,
} from '@/components/ui/pin-input'
import { ChevronRight } from 'lucide-vue-next';
import { Separator } from '@/components/ui/separator';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';

const value = ref<string[]>([''])
const handleComplete = (e: string[]) => alert(e.join(''))
</script>

<template>
    <CampaignLayout>
        <div class="bg-gradient-to-b from-black/0 via-black/0 to-[#4E8B45]">
            <div class="flex flex-col gap-8 items-center px-6 py-10 max-w-xl mx-auto">
                <img src="/static/title.png" alt="Soep op? Tijd voor je prijs@" class="w-full h-auto" />
                <p class="text-center text-white text-2xl font-bold p-2">Vul je code in en ontdek direct of je prijs
                    hebt!
                </p>
                <form action="" class="flex flex-col gap-4 w-full items-center">
                    <PinInput id="pin-input" v-model="value" placeholder="-" class="h-16">
                        <PinInputGroup class="w-full h-16">
                            <PinInputSlot v-for="(id, index) in 8" :key="id" :index="index"
                                class="w-full h-16 dark:bg-white dark:text-black dark:placeholder:text-black/80 text-2xl m-1" />
                        </PinInputGroup>
                    </PinInput>
                    <Input type="text" placeholder="Naam"
                        class="w-full text-2xl md:text-xl dark:bg-white dark:text-black dark:placeholder:text-black/80" />
                    <Input type="text" placeholder="E-mail"
                        class="w-full text-2xl md:text-xl dark:bg-white dark:text-black dark:placeholder:text-black/80" />
                    <Select>
                        <SelectTrigger
                            class="w-full text-2xl md:text-xl dark:bg-white dark:hover:bg-white/70 dark:text-black dark:placeholder:text-black/80">
                            <SelectValue placeholder="Welke soep heb je gegeten?" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="tomatensoep">Tomatensoep</SelectItem>
                            <SelectItem value="erwtensoep">Erwtensoep</SelectItem>
                            <SelectItem value="kippensoep">Kippensoep</SelectItem>
                            <SelectItem value="groentesoep">Groentesoep</SelectItem>
                        </SelectContent>
                    </Select>
                    <p class="text-sm text-white p-2 text-center">
                        Door deel te nemen met deze actie ga je akkoord met onze <a href="#"
                            class="text-white underline">actievoorwaarden</a>.
                    </p>
                    <Button type="submit" class="w-2/3 bg-[#F8BA00] text-white">Check mijn code
                        <ChevronRight class="w-4 h-4" />
                    </Button>
                </form>
            </div>
        </div>
        <div class="w-full bg-[#4E8B45]">
            <div class="flex flex-col gap-8 items-center px-6 py-10">
                <img src="/static/soup-title.png" alt="Soep op? Tijd voor je prijs@"
                    class="w-3/4 h-auto my-8 max-w-xl" />
                <video src="/static/video.mp4" paused controls class="aspect-1/1 object-cover w-full xl:aspect-16/9" />
            </div>
        </div>
    </CampaignLayout>
</template>