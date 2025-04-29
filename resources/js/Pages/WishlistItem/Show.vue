<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {Head, usePage} from '@inertiajs/vue3';
import InputLabel from "@/Components/InputLabel.vue";
import {ref} from "vue";
import {trans} from "laravel-vue-i18n";

const props = defineProps({
    wishlistItem: {
        type: Object,
        required: false
    },
});

const user = usePage().props.auth.user;
const item = ref(props.wishlistItem);

const addToShoppingList = (wishlistItem) => {
    if(confirm(trans('messages.are_you_sure_you_want_to_add_this_item'))){
        axios
            .patch(route('wishlist_items.link_item_to_user', {wishlist_item: wishlistItem.id, id: wishlistItem.id}))
            .catch(error => console.log(error))
    }
};

const removeFromShoppingList = (wishlistItem) => {
    if(confirm(trans('messages.are_you_sure_you_want_to_remove_this_item'))){
        axios
            .patch(route('wishlist_items.unlink_item_to_user', {wishlist_item: wishlistItem.id, id: wishlistItem.id}))
            .catch(error => console.log(error))
    }
};

//Channel pusher
window.Echo.private("wishlistItem")
    .listen('WishlistItemUserHasChanged', (data) => {
        item.value.is_bought = data.is_bought;
        item.value.user_id = data.user_id;
    });
</script>

<template>
    <Head title="Wishlist Item" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                {{ $t('messages.wishlist_item') }}: {{ item.name }}
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8">
                    <div class="mt-6 space-y-6">
                        <div>
                            <InputLabel for="name" :value="$t('messages.name')" />
                            <p class="disabled-input">{{ item.name }}</p>
                        </div>
                        <div>
                            <InputLabel for="wishlist_name" :value="$t('messages.from_wishlist_owner')" />
                            <p class="disabled-input">{{ item.wishlist.name }} ({{ item.wishlist.user.name }})</p>
                        </div>
                        <div>
                            <InputLabel for="description" value="Description" />
                            <p class="disabled-input">{{ item.description?item.description:'-' }}</p>
                        </div>
                        <div>
                            <InputLabel for="url_link" :value="$t('messages.url_link')" />
                            <p class="disabled-input">{{ item.url_link?item.url_link:'-' }}</p>
                        </div>
                        <div>
                            <InputLabel for="price" :value="$t('messages.price')" />
                            <p class="disabled-input">{{ item.price }}</p>
                        </div>
                        <div>
                            <InputLabel for="priority" :value="$t('messages.priority')" />
                            <p class="disabled-input">{{ item.priority }}</p>
                        </div>
                        <div>
                            <InputLabel for="is_bought" :value="$t('messages.is_in_someone_else_shopping_list')" />
                            <p class="disabled-input">{{ user.id == item.wishlist.user_id?$t('messages.hidden'):item.is_bought?$t('message.yes'):$t('messages.no') }}</p>
                        </div>
                        <button
                            v-if="user.id != item.wishlist.user_id && item.wishlist.is_shared && !item.user_id"
                            @click="addToShoppingList(wishlistItem)"
                            class="nav-button">
                            {{ $t('messages.going_to_buy') }}
                        </button>
                        <button
                            v-if="user.id != item.wishlist.user_id && item.wishlist.is_shared && user.id == item.user_id"
                            @click="removeFromShoppingList(wishlistItem)"
                            class="nav-button">
                            {{ $t('messages.not_buying_anymore') }}
                        </button>
                    </div>
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
