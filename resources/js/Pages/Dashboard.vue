<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import TableComponent from "@/Components/Table.vue";
import NavLink from "@/Components/NavLink.vue";
import {trans} from "laravel-vue-i18n";
import Pagination from "@/Components/Pagination.vue";
import SelectInput from "@/Components/SelectInput.vue";
import usePaginationAndSorting from "@/pagination.js";

const headers = [
    { label: 'wishlist_item.id', column: 'id' },
    { label: 'wishlist_item.name', column: 'name' },
    { label: 'wishlist_item.price', column: 'price' },
    { label: 'wishlist_item.priority', column: 'priority' },
    { label: 'wishlist_item.wishlist', column: 'wishlist_name' },
    { label: 'wishlist_item.wishlist_owner', column: 'wishlist_user_name' },
    { label: 'wishlist_item.is_bought', column: 'is_bought' },
    {}, // remove from shopping list
];

const {
    currentData,
    pagination,
    sortBy,
    sortDirection,
    getCurrentPageData,
    onPageChange,
    onSortChanged
} = usePaginationAndSorting('dashboard.get_current_data_page');

const removeFromShoppingList = (wishlistItem) => {
    if(confirm(trans('wishlist_item.are_you_sure_you_want_to_remove_this_item'))){
        axios
            .patch(route('wishlist_items.unlink_item_to_user', {wishlist_item: wishlistItem.id, id: wishlistItem.id}))
            .catch(error => console.log(error))
    }
};

//Channel pusher
window.Echo.private("wishlistItem")
    .listen('WishlistItemUserHasChanged', (data) => {
        if(!data.user_id) {
            let i = currentData.value.map(item => item.id).indexOf(data.id)
            currentData.value.splice(i, 1)
        } else {
            currentData.value.push(data);
        }
    });

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

const handleIsBoughtChange = (newValue, entity) => {
    const isBought = newValue === 'true'

    axios
        .patch(route('wishlist_items.state_has_changed', {wishlist_item: entity.id,is_bought: isBought}))
        .then(() => {
            entity.is_bought = isBought
        })
        .catch((error) => {
            console.error('Failed to update:', error)
        })
}
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                {{ $t('messages.dashboard') }}
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        {{ $t('messages.welcome_message') }}
                    </div>
                </div>
            </div>
        </div>
        <div class="py-12" style="padding-top:0;">
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
                            <NavLink :href="route('wishlist_items.show', {wishlist_item: entity})">
                                {{ entity.id }}
                            </NavLink>
                        </template>
                        <template #column1="{ entity }">
                             {{ entity.name }}
                        </template>
                        <template #column2="{ entity }">
                            {{ entity.price }}$
                        </template>
                        <template #column3="{ entity }">
                            {{ entity.priority }}
                        </template>
                        <template #column4="{ entity }">
                            {{ entity.wishlist.name }}
                        </template>
                        <template #column5="{ entity }">
                            {{ entity.wishlist.user.name }}
                        </template>
                        <template #column6="{ entity }">
                            <SelectInput
                                :id="'is_bought_' + entity.id"
                                class="mt-1 block"
                                v-model="entity.is_bought"
                                :options="[{'value':'true', 'label': $t('options.yes')}, {'value':'false','label': $t('options.no')}]"
                                @update:modelValue="(value) => handleIsBoughtChange(value, entity)"
                            />
                        </template>
                        <template #column7="{ entity }">
                            <button
                                :disabled="entity.is_bought"
                                @click="removeFromShoppingList(entity)"
                                class="nav-button">
                                {{ $t('wishlist_item.remove_from_my_shopping_list') }}
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

