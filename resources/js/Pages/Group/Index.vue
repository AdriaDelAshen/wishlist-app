<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import TableComponent from '@/Components/Table.vue'
import AccordionPanel from '@/Components/AccordionPanel.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import SelectInput from '@/Components/SelectInput.vue';
import {Head, useForm, usePage} from '@inertiajs/vue3';
import NavLink from "@/Components/NavLink.vue";
import IconBase from "@/Components/Icons/IconBase.vue";
import IconWrite from "@/Components/Icons/IconWrite.vue";
import IconTrash from "@/Components/Icons/IconTrash.vue";
import {trans} from "laravel-vue-i18n";
import Pagination from "@/Components/Pagination.vue";
import usePaginationAndSorting from '@/pagination.js';
import {ref, watch} from "vue";

const user = usePage().props.auth.user;
const headers = [
    { label: 'group.id', column: 'id' },
    { label: 'group.name', column: 'name' },
    { label: 'group.owner', column: 'user_id' },
    { label: 'group.is_private', column: 'is_private' },
    { label: 'group.is_active', column: 'is_active' },
    {}, // for edit icon
    {}, // for delete icon
];

const {
    currentData,
    pagination,
    sortBy,
    sortDirection,
    filters,
    getCurrentPageData,
    onPageChange,
    onSortChanged,
    updateFilters
} = usePaginationAndSorting('groups.get_current_data_page', {}, 5, 'id', { name: '', is_private: '', is_active: '' });

//Fetching initial groups data
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

//Form
const form = useForm({});
const destroyGroup = (id) => {
    if(confirm(trans('group.are_you_sure_you_want_to_delete_this_group'))){
        form.delete(route('groups.destroy', {group: id}));
    }
};

//Filters
let nameFilter = ref('');
let isPrivateScopeFilter = ref('');
let isActiveScopeFilter = ref('');

watch([nameFilter, isPrivateScopeFilter, isActiveScopeFilter], ([newName, newIsPrivateScope, newIsActiveScope]) => {
    updateFilters({
        name: newName,
        is_private: newIsPrivateScope,
        is_active: newIsActiveScope
    });
});
const filterOptions = {
    is_private: [
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
    isPrivateScopeFilter.value = '';
    isActiveScopeFilter.value = '';
};
</script>

<template>
    <Head title="Groups" />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800"
            >
                {{ $t('group.groups') }}
            </h2>
        </template>
        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8" style="padding-top: 15px;">
                <NavLink
                    class="nav-button"
                    :href="route('groups.create')"
                    :active="route().current('groups.index')"
                    style="float:right;"
                >
                    {{ $t('messages.create') }}
                </NavLink>
            </div>
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <AccordionPanel :title="$t('group.filters')">
                    <template #toggle="{ isOpen }">
                        {{ isOpen ? $t('messages.hide_filters') : $t('messages.show_filters') }}
                    </template>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <InputLabel for="filter_name" :value="$t('group.name')" />
                            <TextInput
                                id="filter_name"
                                type="text"
                                class="mt-1 block w-full"
                                v-model="nameFilter"
                            />
                        </div>
                        <div>
                            <InputLabel for="filter_is_private" :value="$t('group.is_private')" />
                            <SelectInput
                                id="filter_is_private"
                                class="mt-1 block w-full"
                                v-model="isPrivateScopeFilter"
                                :must-translate-option="true"
                                :options="filterOptions.is_private"
                            />
                        </div>
                        <div>
                            <InputLabel for="filter_is_active" :value="$t('group.is_active')" />
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
                <div
                    class="bg-white p-4 shadow sm:rounded-lg sm:p-8"
                >
                    <table-component :headers="headers"
                                     :data="currentData"
                                     :currentSortBy="sortBy"
                                     :currentSortDirection="sortDirection"
                                     @sortChanged="onSortChanged">
                        <template #column0="{ entity }">
                            <NavLink
                                :href="route('groups.show', {group: entity})"
                                :active="route().current('groups.index')"
                            >
                                {{ entity.id }}
                            </NavLink>
                        </template>
                        <template #column1="{ entity }">
                            {{ entity.name }}
                        </template>
                        <template #column2="{ entity }">
                            {{ entity.owner?entity.owner.name:'-' }}
                        </template>
                        <template #column3="{ entity }">
                            {{ entity.is_private?$t('messages.yes'):$t('messages.no') }}
                        </template>
                        <template #column4="{ entity }">
                            {{ entity.is_active?$t('messages.yes'):$t('messages.no') }}
                        </template>
                        <template #column5="{ entity }">
                            <NavLink
                                class="nav-button"
                                v-if="user.id === entity.user_id"
                                :href="route('groups.edit', {group: entity})"
                                :active="route().current('groups.index')"
                            >
                                <icon-base icon-name="write">
                                    <icon-write />
                                </icon-base>
                            </NavLink>
                        </template>
                        <template #column6="{ entity }">
                            <button
                                v-if="user.id === entity.user_id"
                                @click="destroyGroup(entity.id)"
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
</style>
