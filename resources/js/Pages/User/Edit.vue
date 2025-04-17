<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {Head, usePage} from '@inertiajs/vue3';
import UpdatePasswordForm from "@/Pages/User/Partials/UpdatePasswordForm.vue";
import UpdateUserInformationForm from "@/Pages/User/Partials/UpdateUserInformationForm.vue";
import DeleteUserForm from "@/Pages/User/Partials/DeleteUserForm.vue";

const props = defineProps({
    user: {
        type: Object,
        required: false
    },
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
    options: {
        type: Object,
        required: true,
    },
});

const connectedUser = usePage().props.auth.user;

</script>

<template>
    <Head title="User" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                {{ $t('user.editing_user') }}: {{ user.name }}
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8">
                    <UpdateUserInformationForm
                        :user="user"
                        :must-verify-email="mustVerifyEmail"
                        :status="status"
                        :options="options"
                        class="max-w-xl"
                    />
                </div>

                <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8">
                    <UpdatePasswordForm :user="user" class="max-w-xl" />
                </div>

                <div v-if="connectedUser.id !== user.id" class="bg-white p-4 shadow sm:rounded-lg sm:p-8">
                    <DeleteUserForm :user="user" class="max-w-xl" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>

</template>

<style scoped>
@import '././resources/css/nav_button.css';
</style>
