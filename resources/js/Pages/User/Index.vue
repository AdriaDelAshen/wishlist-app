<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import TableComponent from '@/Components/Table.vue'
import { Head, usePage } from '@inertiajs/vue3';
import NavLink from "@/Components/NavLink.vue";
import IconBase from "@/Components/Icons/IconBase.vue";
import IconWrite from "@/Components/Icons/IconWrite.vue";
import Pagination from "@/Components/Pagination.vue";
import usePaginationAndSorting from "@/pagination.js";

const user = usePage().props.auth.user;
const headers = [
    { label: 'user.id', column: 'id' },
    { label: 'user.name', column: 'name' },
    { label: 'user.email_address', column: 'email' },
    { label: 'user.is_active', column: 'is_active' },
    { label: 'user.is_admin', column: 'is_admin' },
    {}, // Edit
];

const {
    currentData,
    pagination,
    sortBy,
    sortDirection,
    getCurrentPageData,
    onPageChange,
    onSortChanged
} = usePaginationAndSorting('users.get_current_data_page');

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
    <Head title="Users" />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800"
            >
                {{ $t('user.users') }}
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8" style="padding-top: 15px;">
                <NavLink
                    class="nav-button"
                    :href="route('users.create')"
                    :active="route().current('users.index')"
                    style="float:right;"
                >
                    {{ $t('messages.create') }}
                </NavLink>
            </div>
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
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
                            <NavLink
                                :href="route('users.show', {user: entity})"
                                :active="route().current('users.index')"
                            >
                                {{ entity.id }}
                            </NavLink>
                        </template>
                        <template #column1="{ entity }">
                            {{ entity.name }}
                        </template>
                        <template #column2="{ entity }">
                            {{ entity.email }}
                        </template>
                        <template #column3="{ entity }">
                            {{ entity.is_active?$t('messages.yes'):$t('messages.no') }}
                        </template>
                        <template #column4="{ entity }">
                            {{ entity.is_admin?$t('messages.yes'):$t('messages.no') }}
                        </template>
                        <template #column5="{ entity }">
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
                    </table-component>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>

</template>

<style scoped>
</style>
