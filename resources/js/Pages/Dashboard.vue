<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import TableComponent from "@/Components/Table.vue";
import NavLink from "@/Components/NavLink.vue";
import {ref} from "vue";
import {trans} from "laravel-vue-i18n";
import Pagination from "@/Components/Pagination.vue";
import SelectInput from "@/Components/SelectInput.vue";

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

//Pagination
let perPage = ref(5);
let currentData = ref([]);
let pagination = ref([]);

const getCurrentPageData = (page) => {
    axios
        .get(route('dashboard.get_current_data_page',{ perPage: perPage.value, page: page }))
        .then((response) => {
            pagination.value = response.data.pagination;
            currentData.value = response.data.pagination.data;
        })
        .catch((error) => console.log(error))
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

const onPageChange = (url) => {
    const regex = /page=(\d+)/;
    const matches = url.match(regex);
    if(matches && matches[1] != null) {
        getCurrentPageData(matches[1]);
        window.history.replaceState(null, document.title, '?page='+matches[1])
    }
};

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
            <h2
                class="text-xl font-semibold leading-tight text-gray-800"
            >
                {{ $t('messages.dashboard') }}
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div
                    class="overflow-hidden bg-white shadow-sm sm:rounded-lg"
                >
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
                    <table-component :headers="[$t('wishlist_item.id'), $t('wishlist_item.name'), $t('wishlist_item.price'), $t('wishlist_item.priority'), $t('wishlist_item.wishlist'), $t('wishlist_item.wishlist_owner'), $t('wishlist_item.is_bought'), null]" :data="currentData">
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
                                id="is_bought"
                                class="mt-1 block"
                                v-model="entity.is_bought"
                                :options="{'true':$t('options.yes'), 'false':$t('options.no')}"
                                :must-translate-option="false"
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

