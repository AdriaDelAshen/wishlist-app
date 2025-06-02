<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {Head, router, usePage} from '@inertiajs/vue3';
import InputLabel from "@/Components/InputLabel.vue";
import TableComponent from "@/Components/Table.vue";
import NavLink from "@/Components/NavLink.vue";
import { trans } from "laravel-vue-i18n";
import Pagination from "@/Components/Pagination.vue";
import usePaginationAndSorting from "@/pagination.js";

const props = defineProps({
    group: {
        type: Object,
        required: true
    }
});

const user = usePage().props.auth.user;
const headers = [
    { label: 'user.id', column: 'id' },
    { label: 'user.name', column: 'name' },
    { label: 'user.email_address', column: 'email' },
    {}, // link/unlink to the group
];

const {
    currentData,
    pagination,
    sortBy,
    sortDirection,
    getCurrentPageData,
    onPageChange,
    onSortChanged
} = usePaginationAndSorting('groups.get_current_users_data_page', {group_id: props.group.id});

const initialUrl = document.URL;
let initialPage = 1;

if(initialUrl.includes('page=')) {
    const regex = /page=(\d+)/;
    const matches = initialUrl.match(regex);
    if(matches && matches[1] != null) {
        initialPage = matches[1];
    }
} else {
    window.history.replaceState(null, document.title, '?page='+initialPage)
}

getCurrentPageData(initialPage);
</script>

<template>
    <Head title="Group" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                {{ $t('group.group') }}: {{ group.name }}
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8">
                    <div class="mt-6 space-y-6">
                        <div>
                            <InputLabel for="name" :value="$t('group.name')" />
                            <p class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 mt-1 block w-full disabled-input">
                                {{ group.name }}
                            </p>
                        </div>

                        <div>
                            <InputLabel for="description" :value="$t('group.description')" />
                            <p class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 mt-1 block w-full disabled-input">
                                {{ group.description?group.description:'('+$t('messages.none')+')' }}
                            </p>
                        </div>

                        <div>
                            <InputLabel for="user_id" :value="$t('group.owner')" />
                            <p class="disabled-input">
                                {{ group.user.name }}
                            </p>
                        </div>

                        <div>
                            <InputLabel for="is_private" :value="$t('group.is_private')" />
                            <p class="disabled-input">
                                {{ group.is_private?$t('messages.yes'):$t('messages.no') }}
                            </p>
                        </div>

                        <div>
                            <InputLabel for="is_active" :value="$t('group.is_active')" />
                            <p class="disabled-input">
                                {{ group.is_active?$t('messages.yes'):$t('messages.no') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div class="py-12" style="padding-top:0;">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <h3 class="text-xl font-semibold leading-tight text-gray-800">
                    Items
                </h3>
                <Pagination
                    :pagination="pagination"
                    @pageChanged="onPageChange"
                />
                <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8">
                    <table-component :headers="headers"
                                     :data="currentData"
                                     :currentSortBy="sortBy"
                                     :currentSortDirection="sortDirection"
                                     @sortChanged="onSortChanged">
                        <template #column0="{ entity }">
                            {{ entity.id }}
                        </template>
                        <template #column1="{ entity }">
                            {{ entity.name }}
                        </template>
                        <template #column2="{ entity }">
                            {{ entity.email }}
                        </template>
                        <template #column3="{ entity }">

                        </template>
                    </table-component>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>

</template>

<style scoped>
</style>
