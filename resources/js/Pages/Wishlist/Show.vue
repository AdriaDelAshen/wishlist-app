<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import InputLabel from "@/Components/InputLabel.vue";
import TableComponent from "@/Components/Table.vue";
import NavLink from "@/Components/NavLink.vue";
import { ref } from "vue";
import { trans } from "laravel-vue-i18n";
import Pagination from "@/Components/Pagination.vue";

const props = defineProps({
    wishlist: {
        type: Object,
        required: true
    }
});

const user = usePage().props.auth.user;
// let wishlistItems = ref(props.wishlistItems);

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

//Channel pusher
window.Echo.private("wishlistItem")
    .listen('WishlistItemUserHasChanged', (data) => {
        let item = currentData.value.find(item => item.id === data.id);
        item.is_bought = data.is_bought;
        item.user_id = data.user_id;
    });

//Pagination
let perPage = ref(5);
let currentData = ref([]);
let pagination = ref([]);

const getCurrentPageData = (page) => {
    axios
        .get(route('wishlist_items.get_current_data_page',{ perPage: perPage.value, page: page }))
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
                <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8">
                    <div class="mt-6 space-y-6">
                        <div>
                            <InputLabel for="name" :value="$t('wishlist.name')" />
                            <p
                                class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 mt-1 block w-full disabled-input"
                            >{{ wishlist.name }}</p>
                        </div>

                        <div>
                            <InputLabel for="expiration_date" :value="$t('wishlist.expiration_date')" />

                            <p
                                class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 mt-1 block w-full disabled-input"
                            >{{ wishlist.expiration_date }}</p>
                        </div>

                        <div v-if="user.id == wishlist.user_id">
                            <InputLabel for="is_shared" :value="$t('wishlist.is_shared')" />

                            <p
                                class="disabled-input"
                            >{{ wishlist.is_shared?$t('messages.yes'):$t('messages.no') }}</p>
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
                    <table-component :headers="[$t('wishlist_item.id'), $t('wishlist_item.name'), $t('wishlist_item.price'), $t('wishlist_item.priority'), $t('wishlist_item.is_in_someone_else_shopping_list'), null]" :data="currentData">
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
                            {{ user.id == wishlist.user_id?$t('messages.hidden'):entity.is_bought?$t('messages.yes'):$t('messages.no') }}
                        </template>
                        <template #column5="{ entity }">
                            <button
                                v-if="user.id != wishlist.user_id && wishlist.is_shared && !entity.user_id"
                                @click="addToShoppingList(entity)"
                                class="nav-button">
                                {{ $t('wishlist_item.going_to_buy') }}
                            </button>
                            <button
                                v-if="user.id != wishlist.user_id && wishlist.is_shared && user.id == entity.user_id"
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

.disabled-input {
    --tw-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);
    --tw-shadow-colored: 0 1px 2px 0 var(--tw-shadow-color);
    --tw-border-opacity: 1;
    border-color: rgb(209 213 219 / var(--tw-border-opacity, 1));
    box-shadow: var(--tw-ring-offset-shadow, 0 0 #0000), var(--tw-ring-shadow, 0 0 #0000), var(--tw-shadow);
    border-radius: 0.375rem;
    width: 100%;
    display: block;
    margin-top: 0.25rem;
    background-color: lightgrey;
    padding: 9px;
}
</style>
