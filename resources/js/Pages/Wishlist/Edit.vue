<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {Head, useForm, usePage} from '@inertiajs/vue3';
import Form from "@/Pages/Wishlist/Partials/Form.vue";
import TextInput from "@/Components/TextInput.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import IconTrash from "@/Components/Icons/IconTrash.vue";
import TableComponent from "@/Components/Table.vue";
import IconWrite from "@/Components/Icons/IconWrite.vue";
import IconBase from "@/Components/Icons/IconBase.vue";
import PriceInput from "@/Components/PriceInput.vue";
import NumberInput from "@/Components/NumberInput.vue";
import * as bootstrap from "bootstrap";
import NavLink from "@/Components/NavLink.vue";

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

const wishlistItemForm = useForm({
    item_id: null,
    item_name: '',
    item_description: '',
    item_url_link: '',
    item_price: 0,
    item_priority: 0,
    item_wishlist_id: props.wishlist.id,
});

const showModalForWishlistItem = (entity) => {
    if(entity) {
        wishlistItemForm.item_id = entity.id;
        wishlistItemForm.item_name = entity.name;
        wishlistItemForm.item_description = entity.description?entity.description:'';
        wishlistItemForm.item_url_link = entity.url_link?entity.url_link:'';
        wishlistItemForm.item_price = entity.price;
        wishlistItemForm.item_priority = entity.priority;
    } else {
        wishlistItemForm.item_id = null;
        wishlistItemForm.item_name = '';
        wishlistItemForm.item_description = '';
        wishlistItemForm.item_url_link = '';
        wishlistItemForm.item_price = 0;
        wishlistItemForm.item_priority = 0;
    }
};
const destroyWishlistItem = (id) => {
    if(confirm("Are you sure you want to delete this wishlist item?")){
        wishlistItemForm.delete(route('wishlist_items.destroy', {wishlist_item: id}),
            {
                preserveScroll: true,
            });
    }
};

const closeModal = () => {
    const myModalEl = document.getElementById('wishlist_item_form_modal');
    const modal = bootstrap.Modal.getInstance(myModalEl)
    modal.hide();
};
</script>

<template>
    <Head title="Wishlist" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Editing wishlist: {{ wishlist.name }}
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8">
                    <Form class="max-w-xl" :wishlist="wishlist" :wishlist-items="wishlistItems"/>
                </div>
            </div>
        </div>

        <div class="py-12" style="padding-top:0;">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <div style="height: 30px;">
                    <h3 class="text-xl font-semibold leading-tight text-gray-800" style="float: left;">
                        Items
                    </h3>
                    <PrimaryButton  v-if="user.id == wishlist.user_id && !wishlist.is_shared"
                             type="button"
                             data-bs-toggle="modal"
                             data-bs-target="#wishlist_item_form_modal"
                             @click="showModalForWishlistItem(null)"
                             style="float: right;">
                        Add item
                    </PrimaryButton>
                </div>

                <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8">
                    <table-component :headers="['Name', 'Price', 'Priority', 'Is in someone\'s shopping list', null, null]" :data="wishlistItems">
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
                            <button v-if="user.id == wishlist.user_id && !wishlist.is_shared"
                                    type="button"
                                    class="nav-button"
                                    data-bs-toggle="modal"
                                    data-bs-target="#wishlist_item_form_modal"
                                    @click="showModalForWishlistItem(entity)">
                                <icon-base icon-name="write">
                                    <icon-write />
                                </icon-base>
                            </button>
                        </template>
                        <template #column5="{ entity }">
                            <button
                                v-if="user.id == wishlist.user_id && !wishlist.is_shared"
                                @click="destroyWishlistItem(entity.id)"
                                class="nav-button delete">
                                <icon-base icon-name="trash">
                                    <icon-trash />
                                </icon-base>
                            </button>
                        </template>
                    </table-component>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div class="modal fade" id="wishlist_item_form_modal" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form @submit.prevent="wishlistItemForm.item_id?wishlistItemForm.put(route('wishlist_items.update', {wishlist_item: wishlistItemForm.item_id}),{onSuccess: () => closeModal(),preserveScroll: true}):wishlistItemForm.post(route('wishlist_items.store'),{onSuccess: () => closeModal()})" class="mt-6 space-y-6">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalLabel">Add a wishlist item</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">

                                <div>
                                    <InputLabel for="item_name" value="Name" />

                                    <TextInput
                                        id="item_name"
                                        type="text"
                                        class="mt-1 block w-full"
                                        v-model="wishlistItemForm.item_name"
                                        required
                                        autocomplete="item_name"
                                    />

                                    <InputError class="mt-2" :message="wishlistItemForm.errors.item_name" />
                                </div>

                                <div>
                                    <InputLabel for="item_description" value="Description" />

                                    <TextInput
                                        id="item_description"
                                        type="text"
                                        class="mt-1 block w-full"
                                        v-model="wishlistItemForm.item_description"
                                        autocomplete="item_description"
                                    />

                                    <InputError class="mt-2" :message="wishlistItemForm.errors.item_description" />
                                </div>

                                <div>
                                    <InputLabel for="item_url_link" value="Url Link" />

                                    <TextInput
                                        id="item_url_link"
                                        type="text"
                                        class="mt-1 block w-full"
                                        v-model="wishlistItemForm.item_url_link"
                                        autocomplete="item_url_link"
                                    />

                                    <InputError class="mt-2" :message="wishlistItemForm.errors.item_url_link" />
                                </div>

                                <div>
                                    <InputLabel for="item_price" value="Price" />

                                    <PriceInput
                                        id="item_price"
                                        class="mt-1 block w-full"
                                        v-model="wishlistItemForm.item_price"
                                    />

                                    <InputError class="mt-2" :message="wishlistItemForm.errors.item_price" />
                                </div>

                                <div>
                                    <InputLabel for="item_priority" value="Priority" />

                                    <NumberInput
                                        id="item_priority"
                                        class="mt-1 block w-full"
                                        v-model="wishlistItemForm.item_priority"
                                    />

                                    <InputError class="mt-2" :message="wishlistItemForm.errors.item_priority" />
                                </div>


                        </div>
                        <div class="modal-footer">
                            <PrimaryButton data-bs-dismiss="modal">Close</PrimaryButton>
                            <div class="flex items-center gap-4">
                                <PrimaryButton :disabled="wishlistItemForm.processing">
                                    {{ wishlistItemForm.item_id?'Save':'Add' }}
                                </PrimaryButton>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>

</template>

<style scoped>
@import '././resources/css/nav_button.css';
</style>
