<script setup>
import { ref, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import TableComponent from '@/Components/Table.vue'
import {Head, useForm, usePage} from '@inertiajs/vue3';
import NavLink from "@/Components/NavLink.vue";
import IconBase from "@/Components/Icons/IconBase.vue";
import IconWrite from "@/Components/Icons/IconWrite.vue";
import IconTrash from "@/Components/Icons/IconTrash.vue";
import {trans} from "laravel-vue-i18n";
import Pagination from "@/Components/Pagination.vue";
import usePaginationAndSorting from '@/pagination.js';

const user = usePage().props.auth.user;
const headers = [
    { label: 'wishlist.id', column: 'id' },
    { label: 'wishlist.name', column: 'name' },
    { label: 'wishlist.owner', column: 'user_id' },
    { label: 'wishlist.is_shared', column: 'is_shared' },
    { label: 'wishlist.expiration_date', column: 'expiration_date' },
    {}, // for edit icon
    {}, // for delete icon
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
} = usePaginationAndSorting('wishlists.get_current_data_page', {}, 5, 'id', {
    expiration_date: '',
    wishlist_scope: 'all'
});

const expirationDateFilter = ref('');
const wishlistScopeFilter = ref('all');

watch([expirationDateFilter, wishlistScopeFilter], ([newExpirationDate, newWishlistScope]) => {
    updateFilters({
        expiration_date: newExpirationDate,
        wishlist_scope: newWishlistScope
    });
});

const form = useForm({});
const destroyWishlist = (id) => {
    if(confirm(trans('wishlist.are_you_sure_you_want_to_delete_this_wishlist'))){
        form.delete(route('wishlists.destroy', {wishlist: id}));
    }
};

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
    <Head title="Wishlists" />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800"
            >
                {{ $t('wishlist.wishlists') }}
            </h2>
        </template>
        <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8" style="padding-top: 15px;">
            <NavLink
                class="nav-button"
                :href="route('wishlists.create')"
                :active="route().current('wishlists.index')"
                style="float:right;"
            >
                {{ $t('messages.create') }}
            </NavLink>
        </div>
        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <div class="flex space-x-4 mb-4">
                    <div>
                        <label for="expiration_date_filter" class="block text-sm font-medium text-gray-700">{{ $t('wishlist.filter_by_expiration_date') }}:</label>
                        <input type="date" id="expiration_date_filter" v-model="expirationDateFilter" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                    </div>
                    <div>
                        <label for="wishlist_scope_filter" class="block text-sm font-medium text-gray-700">{{ $t('wishlist.show_wishlists') }}:</label>
                        <select id="wishlist_scope_filter" v-model="wishlistScopeFilter" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                            <option value="all">{{ $t('wishlist.all_wishlists') }}</option>
                            <option value="mine">{{ $t('wishlist.my_wishlists') }}</option>
                        </select>
                    </div>
                </div>
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
                                :href="route('wishlists.show', {wishlist: entity})"
                                :active="route().current('wishlists.index')"
                            >
                                {{ entity.id }}
                            </NavLink>
                        </template>
                        <template #column1="{ entity }">
                            {{ entity.name }}
                        </template>
                        <template #column2="{ entity }">
                            {{ entity.user.name }}
                        </template>
                        <template #column3="{ entity }">
                            {{ entity.is_shared?$t('messages.yes'):$t('messages.no') }}
                        </template>
                        <template #column4="{ entity }">
                            {{ entity.expiration_date }}
                        </template>
                        <template #column5="{ entity }">
                            <NavLink
                                class="nav-button"
                                v-if="user.id == entity.user_id"
                                :href="route('wishlists.edit', {wishlist: entity})"
                                :active="route().current('wishlists.index')"
                            >
                                <icon-base icon-name="write">
                                    <icon-write />
                                </icon-base>
                            </NavLink>
                        </template>
                        <template #column6="{ entity }">
                            <button
                                v-if="user.id === entity.user_id && !entity.is_shared"
                                @click="destroyWishlist(entity.id)"
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
