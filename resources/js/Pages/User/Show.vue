<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {Head} from '@inertiajs/vue3';
import InputLabel from "@/Components/InputLabel.vue";

const props = defineProps({
    user: {
        type: Object,
        required: true
    },
});

</script>

<template>
    <Head title="User" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                {{ $t('user.user') }}: {{ user.name }}
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8">
                    <div class="mt-6 space-y-6">
                        <div>
                            <InputLabel for="name" :value="$t('user.name')" />
                            <p
                                class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 mt-1 block w-full disabled-input"
                            >{{ user.name }}</p>
                        </div>
                        <div>
                            <InputLabel for="email" :value="$t('user.email_address')" />
                            <p
                                class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 mt-1 block w-full disabled-input"
                            >{{ user.email }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>

</template>

<style scoped>
</style>
