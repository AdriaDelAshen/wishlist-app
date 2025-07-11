<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {Head, router, usePage} from '@inertiajs/vue3';
import InputLabel from "@/Components/InputLabel.vue";
import TableComponent from "@/Components/Table.vue";
import NavLink from "@/Components/NavLink.vue";
import { trans } from "laravel-vue-i18n";
import Pagination from "@/Components/Pagination.vue";
import usePaginationAndSorting from "@/pagination.js";
import TextInput from "@/Components/TextInput.vue";
import AccordionPanel from "@/Components/AccordionPanel.vue";
import {ref, watch} from "vue";
import NumberInput from "@/Components/NumberInput.vue";

const props = defineProps({
    wishlist: {
        type: Object,
        required: true
    }
});

const user = usePage().props.auth.user;
const headers = [
    { label: 'wishlist_item.id', column: 'id' },
    { label: 'wishlist_item.name', column: 'name' },
    { label: 'wishlist_item.price', column: 'price' },
    { label: 'wishlist_item.priority', column: 'priority' },
    { label: 'wishlist_item.is_in_someone_else_shopping_list', column: null },
    {}, // add/remove from shopping list
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
} = usePaginationAndSorting(
    'wishlist_items.get_current_data_page',
    {wishlist_id: props.wishlist.id}, 5, 'id',
    { name: '', price_greater_than: null, price_lesser_than: null }
);

//Fetching initial wishlistItems data
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
let priceGreaterThanFilter = ref(null);
let priceLesserThanFilter = ref(null);

watch([nameFilter, priceGreaterThanFilter, priceLesserThanFilter], ([newName, newPriceGreaterThanFilter, newPriceLesserThanFilter]) => {
    updateFilters({
        name: newName,
        price_greater_than: newPriceGreaterThanFilter,
        price_lesser_than: newPriceLesserThanFilter,
    });
});
const clearFilters = () => {
    nameFilter.value = '';
    priceGreaterThanFilter.value = null;
    priceLesserThanFilter.value = null;
};

//Functions
const addToShoppingList = (wishlistItem) => {
    if(confirm(trans('wishlist_item.are_you_sure_you_want_to_add_this_item'))){
        axios
            .patch(route('wishlist_items.link_item_to_user', {wishlist_item: wishlistItem.id, id: wishlistItem.id}))
            .catch(error => console.log(error))
    }
};

const removeFromShoppingList = (wishlistItem) => {
    if(confirm(trans('wishlist_item.are_you_sure_you_want_to_remove_this_item'))){
        axios
            .patch(route('wishlist_items.unlink_item_to_user', {wishlist_item: wishlistItem.id, id: wishlistItem.id}))
            .catch(error => console.log(error))
    }
};

const duplicateWishlist = (id) => {
    if (confirm(trans('wishlist.are_you_sure_you_want_to_duplicate_this_wishlist'))) {
        router.post(route('wishlists.duplicate', { wishlist: id }), {}, {
            onSuccess: () => {
                // notifySuccess('Wishlist duplicated!');
            }
        });
    }
}

//Channel pusher
window.Echo.private("wishlistItem")
    .listen('WishlistItemUserHasChanged', (data) => {
        let item = currentData.value.find(item => item.id === data.id);
        item.in_shopping_list = data.in_shopping_list;
        item.user_id = data.user_id;
    });
</script>

<template>
    <Head title="Wishlist" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                {{ $t('wishlist.wishlist') }}: {{ wishlist.name }}
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <button
                    v-if="wishlist.can_be_duplicated || wishlist.user_id === user.id"
                    @click="duplicateWishlist(wishlist.id)"
                    class="nav-button">
                    {{ $t('wishlist.duplicate') }}
                </button>
                <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8">
                    <div class="mt-6 space-y-6">
                        <div>
                            <InputLabel for="name" :value="$t('wishlist.name')" />
                            <p class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 mt-1 block w-full disabled-input">
                                {{ wishlist.name }}
                            </p>
                        </div>

                        <div>
                            <InputLabel for="expiration_date" :value="$t('wishlist.expiration_date')" />
                            <p class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 mt-1 block w-full disabled-input">
                                {{ wishlist.expiration_date }}
                            </p>
                        </div>

                        <div>
                            <InputLabel for="can_be_duplicated" :value="$t('wishlist.can_be_duplicated')" />
                            <p class="disabled-input">
                                {{ wishlist.can_be_duplicated?$t('messages.yes'):$t('messages.no') }}
                            </p>
                        </div>

                        <div v-if="user.id == wishlist.user_id">
                            <InputLabel for="is_shared" :value="$t('wishlist.is_shared')" />
                            <p class="disabled-input">
                                {{ wishlist.is_shared?$t('messages.yes'):$t('messages.no') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="py-12" style="padding-top:0;">
            <!-- Accordion Panel for Filters -->
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <AccordionPanel :title="$t('wishlist_item.filters')">
                    <template #toggle="{ isOpen }">
                        {{ isOpen ? $t('messages.hide_filters') : $t('messages.show_filters') }}
                    </template>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <InputLabel for="filter_name" :value="$t('wishlist_item.name')" />
                            <TextInput
                                id="filter_name"
                                type="text"
                                class="mt-1 block w-full"
                                v-model="nameFilter"
                            />
                        </div>
                        <div>
                            <InputLabel for="filter_price_gt" :value="$t('wishlist_item.price_greater_than')" />
                            <NumberInput
                                id="filter_price_gt"
                                step="0.01"
                                class="mt-1 block w-full"
                                v-model="priceGreaterThanFilter"
                            />
                        </div>
                        <div>
                            <InputLabel for="filter_price_lt" :value="$t('wishlist_item.price_lesser_than')" />
                            <NumberInput
                                id="filter_price_lt"
                                step="0.01"
                                class="mt-1 block w-full"
                                v-model="priceLesserThanFilter"
                            />
                        </div>
                    </div>
                    <div class="mt-4 flex justify-end space-x-2">
                        <button type="button" @click="clearFilters" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">
                            {{ $t('messages.clear') }}
                        </button>
                    </div>
                </AccordionPanel>
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
                            {{ user.id === wishlist.user_id?$t('messages.hidden'):entity.in_shopping_list?$t('messages.yes'):$t('messages.no') }}
                        </template>
                        <template #column5="{ entity }">
                            <button
                                v-if="user.id !== wishlist.user_id && wishlist.is_shared && !entity.user_id"
                                @click="addToShoppingList(entity)"
                                class="nav-button">
                                {{ $t('wishlist_item.add_to_my_shopping_list') }}
                            </button>
                            <button
                                v-if="user.id !== wishlist.user_id && wishlist.is_shared && user.id === entity.user_id"
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
