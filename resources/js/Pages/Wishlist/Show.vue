<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {Head, usePage} from '@inertiajs/vue3';
import InputLabel from "@/Components/InputLabel.vue";
import TableComponent from "@/Components/Table.vue";
import NavLink from "@/Components/NavLink.vue";
import {ref} from "vue";

const props = defineProps({
    wishlist: {
        type: Object,
        required: false
    },
    wishlistItems: {
        type: Array,
        required: false
    }
});

const user = usePage().props.auth.user;
let wishlistItems = ref(props.wishlistItems);

const addToShoppingList = (wishlistItem) => {
    if(confirm("Are you sure you want to add this item to your shopping list?")){
        axios
            .patch(route('wishlist_items.linkItemToUser', {wishlist_item: wishlistItem.id, id: wishlistItem.id}))
            .catch(error => console.log(error))
    }
};

const removeFromShoppingList = (wishlistItem) => {
    if(confirm("Are you sure you want to remove this item from your shopping list?")){
        axios
            .patch(route('wishlist_items.unlinkItemToUser', {wishlist_item: wishlistItem.id, id: wishlistItem.id}))
            .catch(error => console.log(error))
    }
};

//Channel pusher
window.Echo.private("wishlistItem")
    .listen('WishlistItemUserHasChanged', (data) => {
        let item = wishlistItems.value.find(item => item.id === data.id);
        item.is_bought = data.is_bought;
        item.user_id = data.user_id;
    });
</script>

<template>
    <Head title="Wishlist" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Showing wishlist: {{ wishlist.name }}
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8">
                    <div class="mt-6 space-y-6">
                        <div>
                            <InputLabel for="name" value="Name" />

                            <p
                                class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 mt-1 block w-full disabled-input"
                            >{{ wishlist.name }}</p>
                        </div>

                        <div>
                            <InputLabel for="expiration_date" value="Expiration date" />

                            <p
                                class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 mt-1 block w-full disabled-input"
                            >{{ wishlist.expiration_date }}</p>
                        </div>

                        <div v-if="user.id == wishlist.user_id">
                            <InputLabel for="is_shared" value="Is shared" />

                            <p
                                class="disabled-input"
                            >{{ wishlist.is_shared?'Yes':'No' }}</p>
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
                <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8">
                    <table-component :headers="['Name', 'Price', 'Priority', 'Is in someone\'s shopping list', null]" :data="wishlistItems">
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
                            {{ user.id == wishlist.user_id?'Hidden':entity.is_bought?'Yes':'No' }}
                        </template>
                        <template #column4="{ entity }">
                            <button
                                v-if="user.id != wishlist.user_id && wishlist.is_shared && !entity.user_id"
                                @click="addToShoppingList(entity)"
                                class="nav-button">
                                {{ 'Going to buy' }}
                            </button>
                            <button
                                v-if="user.id != wishlist.user_id && wishlist.is_shared && user.id == entity.user_id"
                                @click="removeFromShoppingList(entity)"
                                class="nav-button">
                                {{ 'Not buying anymore' }}
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
