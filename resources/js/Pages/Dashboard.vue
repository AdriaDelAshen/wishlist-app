<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import TableComponent from "@/Components/Table.vue";
import NavLink from "@/Components/NavLink.vue";
import {ref} from "vue";
import {trans} from "laravel-vue-i18n";

const props = defineProps({
    wishlistItems: {
        type: Array,
    },
});

let wishlistItems = ref(props.wishlistItems);

const removeFromShoppingList = (wishlistItem) => {
    if(confirm(trans('wishlist_item.are_you_sure_you_want_to_remove_this_item'))){
        axios
            .patch(route('wishlist_items.unlinkItemToUser', {wishlist_item: wishlistItem.id, id: wishlistItem.id}))
            .catch(error => console.log(error))
    }
};

//Channel pusher
window.Echo.private("wishlistItem")
    .listen('WishlistItemUserHasChanged', (data) => {
        if(!data.user_id) {
            let i = wishlistItems.value.map(item => item.id).indexOf(data.id)
            wishlistItems.value.splice(i, 1)
        } else {
            wishlistItems.value.push(data);
        }
    });
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
                <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8">
                    <table-component :headers="[$t('wishlist_item.name'), $t('wishlist_item.price'), $t('wishlist_item.priority'), $t('wishlist_item.wishlist'), $t('wishlist_item.wishlist_owner'), null]" :data="wishlistItems">
                        <template #column0="{ entity }">
                            <NavLink :href="route('wishlist_items.show', {wishlist_item: entity})">
                                {{ entity.name }}
                            </NavLink>
                        </template>
                        <template #column1="{ entity }">
                            {{ entity.price }}$
                        </template>
                        <template #column2="{ entity }">
                            {{ entity.priority }}
                        </template>
                        <template #column3="{ entity }">
                            {{ entity.wishlist.name }}
                        </template>
                        <template #column4="{ entity }">
                            {{ entity.wishlist.user.name }}
                        </template>
                        <template #column5="{ entity }">
                            <button
                                @click="removeFromShoppingList(entity)"
                                class="nav-button">
                                {{ $t('wishlist_item.not_buying_anymore') }}
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

