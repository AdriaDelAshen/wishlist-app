<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import Form from "@/Pages/Wishlist/Partials/Form.vue";
</script>

<template>
    <Head title="Wishlist" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                {{ $t('wishlist.new_wishlist') }}
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8">
                    <Form class="max-w-xl"/>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>

</template>

<style scoped>

</style>
