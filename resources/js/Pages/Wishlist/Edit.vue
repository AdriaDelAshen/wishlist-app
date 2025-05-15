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
import {trans} from "laravel-vue-i18n";

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
    id: null,
    name: '',
    description: '',
    url_link: '',
    price: 0,
    priority: 0,
    wishlist_id: props.wishlist.id,
});

const showModalForWishlistItem = (entity) => {
    if(entity) {
        wishlistItemForm.id = entity.id;
        wishlistItemForm.name = entity.name;
        wishlistItemForm.description = entity.description?entity.description:'';
        wishlistItemForm.url_link = entity.url_link?entity.url_link:'';
        wishlistItemForm.price = entity.price;
        wishlistItemForm.priority = entity.priority;
    } else {
        wishlistItemForm.id = null;
        wishlistItemForm.name = '';
        wishlistItemForm.description = '';
        wishlistItemForm.url_link = '';
        wishlistItemForm.price = 0;
        wishlistItemForm.priority = 0;
    }
};
const destroyWishlistItem = (id) => {
    if(confirm(trans('wishlist_item.are_you_sure_you_want_to_delete_this_item'))){
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
                {{ $t('wishlist.editing_wishlist') }}: {{ wishlist.name }}
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
                        {{ $t('wishlist_item.add_item') }}
                    </PrimaryButton>
                </div>

                <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8">
                    <table-component :headers="[$t('wishlist_item.id'), $t('wishlist_item.name'), $t('wishlist_item.price'), $t('wishlist_item.priority'), $t('wishlist_item.is_in_someone_else_shopping_list'), null, null]" :data="wishlistItems">
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
                            {{ user.id == wishlist.user_id?$t('messages.hidden'):entity.in_shopping_list?$t('messages.yes'):$t('messages.no') }}
                        </template>
                        <template #column5="{ entity }">
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
                        <template #column6="{ entity }">
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
                            <h5 class="modal-title" id="modalLabel">{{ $t('wishlist_item.add_a_wishlist_item') }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                                <div>
                                    <InputLabel for="item_name" :value="$t('wishlist_item.name')" />
                                    <TextInput
                                        id="item_name"
                                        type="text"
                                        class="mt-1 block w-full"
                                        v-model="wishlistItemForm.name"
                                        required
                                        autocomplete="name"
                                    />
                                    <InputError class="mt-2" :message="wishlistItemForm.errors.name" />
                                </div>

                                <div>
                                    <InputLabel for="description" value="Description" />
                                    <TextInput
                                        id="description"
                                        type="text"
                                        class="mt-1 block w-full"
                                        v-model="wishlistItemForm.description"
                                        autocomplete="description"
                                    />
                                    <InputError class="mt-2" :message="wishlistItemForm.errors.description" />
                                </div>

                                <div>
                                    <InputLabel for="url_link" :value="$t('wishlist_item.url_link')" />
                                    <TextInput
                                        id="url_link"
                                        type="text"
                                        class="mt-1 block w-full"
                                        v-model="wishlistItemForm.url_link"
                                        autocomplete="url_link"
                                    />
                                    <InputError class="mt-2" :message="wishlistItemForm.errors.url_link" />
                                </div>

                                <div>
                                    <InputLabel for="price" :value="$t('wishlist_item.price')" />
                                    <PriceInput
                                        id="price"
                                        class="mt-1 block w-full"
                                        v-model="wishlistItemForm.price"
                                    />
                                    <InputError class="mt-2" :message="wishlistItemForm.errors.price" />
                                </div>

                                <div>
                                    <InputLabel for="priority" :value="$t('wishlist_item.priority')" />
                                    <NumberInput
                                        id="priority"
                                        class="mt-1 block w-full"
                                        v-model="wishlistItemForm.priority"
                                    />
                                    <InputError class="mt-2" :message="wishlistItemForm.errors.priority" />
                                </div>


                        </div>
                        <div class="modal-footer">
                            <PrimaryButton data-bs-dismiss="modal">{{ $t('messages.close') }}</PrimaryButton>
                            <div class="flex items-center gap-4">
                                <PrimaryButton :disabled="wishlistItemForm.processing">
                                    {{ wishlistItemForm.id?$t('messages.save'):$t('messages.add') }}
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
</style>
