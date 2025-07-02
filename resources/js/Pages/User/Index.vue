<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import TableComponent from '@/Components/Table.vue';
import AccordionPanel from '@/Components/AccordionPanel.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import SelectInput from '@/Components/SelectInput.vue';
import { Head, usePage } from '@inertiajs/vue3';
import NavLink from "@/Components/NavLink.vue";
import IconBase from "@/Components/Icons/IconBase.vue";
import IconWrite from "@/Components/Icons/IconWrite.vue";
import Pagination from "@/Components/Pagination.vue";
import usePaginationAndSorting from "@/pagination.js";
import { trans } from "laravel-vue-i18n";
import {ref, watch} from "vue";

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
    onSortChanged,
    updateFilters
} = usePaginationAndSorting('users.get_current_data_page', {}, 5, 'id', { name: '', email: '', is_admin: '', is_active: '' });

//Fetching initial users data
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

//Filters
let nameFilter = ref('');
let emailFilter = ref('');
let isAdminScopeFilter = ref('');
let isActiveScopeFilter = ref('');

watch([nameFilter, emailFilter, isAdminScopeFilter, isActiveScopeFilter], ([newName, newEmail, newIsAdminScope, newIsActiveScope]) => {
    updateFilters({
        name: newName,
        email: newEmail,
        is_admin: newIsAdminScope,
        is_active: newIsActiveScope
    });
});
const filterOptions = {
    is_admin: [
        { value: '', label: 'all' },
        { value: '1', label: 'yes' },
        { value: '0', label: 'no' },
    ],
    is_active: [
        { value: '', label: 'all' },
        { value: '1', label: 'yes' },
        { value: '0', label: 'no' },
    ],
};

const clearFilters = () => {
    nameFilter.value = '';
    emailFilter.value = '';
    isAdminScopeFilter.value = '';
    isActiveScopeFilter.value = '';
};
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
                <AccordionPanel :title="$t('user.filters')">
                    <template #toggle="{ isOpen }">
                        {{ isOpen ? $t('messages.hide_filters') : $t('messages.show_filters') }}
                    </template>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div>
                            <InputLabel for="filter_name" :value="$t('user.name')" />
                            <TextInput
                                id="filter_name"
                                type="text"
                                class="mt-1 block w-full"
                                v-model="nameFilter"
                            />
                        </div>
                        <div>
                            <InputLabel for="filter_email" :value="$t('user.email_address')" />
                            <TextInput
                                id="filter_email"
                                type="text"
                                class="mt-1 block w-full"
                                v-model="emailFilter"
                            />
                        </div>
                        <div>
                            <InputLabel for="filter_is_admin" :value="$t('user.is_admin')" />
                            <SelectInput
                                id="filter_is_admin"
                                class="mt-1 block w-full"
                                v-model="isAdminScopeFilter"
                                :must-translate-option="true"
                                :options="filterOptions.is_admin"
                            />
                        </div>
                        <div>
                            <InputLabel for="filter_is_active" :value="$t('user.is_active')" />
                            <SelectInput
                                id="filter_is_active"
                                class="mt-1 block w-full"
                                v-model="isActiveScopeFilter"
                                :must-translate-option="true"
                                :options="filterOptions.is_active"
                            />
                        </div>
                    </div>
                    <div class="mt-4 flex justify-end space-x-2">
                        <button type="button" @click="clearFilters" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">
                            {{ $t('messages.clear') }}
                        </button>
                    </div>
                </AccordionPanel>
            </div>
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8" style="margin-top: 20px;">
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
