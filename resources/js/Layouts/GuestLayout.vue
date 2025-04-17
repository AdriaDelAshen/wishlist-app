<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
import DropdownButton from "@/Components/DropdownButton.vue";
import Dropdown from "@/Components/Dropdown.vue";
import { useLocalesStore } from '@/Stores/localesStore.js';
import { storeToRefs } from 'pinia';

const localesStore = useLocalesStore();
const { locale, appLocales } = storeToRefs(localesStore);
const { changeLocale } = localesStore;

</script>

<template>
    <div
        class="flex min-h-screen flex-col items-center bg-gray-100 pt-6 sm:justify-center sm:pt-0"
    >
        <div>
            <Link href="/">
                <ApplicationLogo class="h-20 w-20 fill-current text-gray-500" />
            </Link>
        </div>

        <div
            class="mt-6 w-full overflow-hidden bg-white px-6 py-4 shadow-md sm:max-w-md sm:rounded-lg"
        >
            <div class="sm:flex sm:items-center" style="justify-content: flex-end; margin-bottom: 10px;">
                <div class="relative">
                    <Dropdown align="right" width="48">
                        <template #trigger>
                                        <span class="inline-flex rounded-md">
                                            <button
                                                type="button"
                                                class="inline-flex items-center rounded-md border border-transparent bg-white px-3 py-2 text-sm font-medium leading-4 text-gray-500 transition duration-150 ease-in-out hover:text-gray-700 focus:outline-none"
                                            >
                                                {{ locale === 'fr' ? $t('options.french') : $t('options.english') }}

                                                <svg
                                                    class="-me-0.5 ms-2 h-4 w-4"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 20 20"
                                                    fill="currentColor"
                                                >
                                                    <path
                                                        fill-rule="evenodd"
                                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                        clip-rule="evenodd"
                                                    />
                                                </svg>
                                            </button>
                                        </span>
                        </template>

                        <template #content>
                            <DropdownButton
                                v-for="(option, index) in appLocales"
                                @click="changeLocale(index)"
                                method="post"
                                as="button"
                            >
                                {{ $t('options.'+option) }}
                            </DropdownButton>
                        </template>
                    </Dropdown>
                </div>
            </div>
            <slot />
        </div>
    </div>
</template>
