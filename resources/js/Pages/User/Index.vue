<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import TableComponent from '@/Components/Table.vue'
import {Head, useForm, usePage} from '@inertiajs/vue3';
import NavLink from "@/Components/NavLink.vue";
import IconBase from "@/Components/Icons/IconBase.vue";
import IconWrite from "@/Components/Icons/IconWrite.vue";
import IconTrash from "@/Components/Icons/IconTrash.vue";

const user = usePage().props.auth.user;

const props = defineProps({
    users: {
        type: Array,
    },
});

const form = useForm({});
const destroyUser = (id) => {
    if(confirm("Are you sure you want to delete this user?")){
        form.delete(route('users.destroy', {user: id}));
    }
};

</script>

<template>
    <Head title="Users" />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800"
            >
                Users
            </h2>
        </template>
        <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8" style="padding-top: 15px;">
            <NavLink
                class="nav-button"
                :href="route('users.create')"
                :active="route().current('users.index')"
                style="float:right;"
            >
                Create
            </NavLink>
        </div>
        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <div
                    class="bg-white p-4 shadow sm:rounded-lg sm:p-8"
                >
                    <table-component :headers="['Name', 'Email', 'Is admin', null, null]" :data="users">
                        <template #column0="{ entity }">
                            <NavLink
                                :href="route('users.show', {user: entity})"
                                :active="route().current('users.index')"
                            >
                                {{ entity.name }}
                            </NavLink>
                        </template>
                        <template #column1="{ entity }">
                            {{ entity.email }}
                        </template>
                        <template #column2="{ entity }">
                            {{ entity.id }}
                        </template>
                        <template #column3="{ entity }">
                            <NavLink
                                class="nav-button"
                                :href="route('users.edit', {user: entity})"
                                :active="route().current('users.index')"
                            >
                                <icon-base icon-name="write">
                                    <icon-write />
                                </icon-base>
                            </NavLink>
                        </template>
                        <template #column4="{ entity }">
                            <button
                                v-if="user.id !== entity.id"
                                @click="destroyUser(entity.id)"
                                class="nav-button delete">
                                <icon-base icon-name="write">
                                    <icon-trash />
                                </icon-base>
                            </button>
                        </template>
                    </table-component>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>

</template>

<style scoped>
@import '././resources/css/nav_button.css';
</style>
