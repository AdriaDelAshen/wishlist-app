<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import TableComponent from '@/Components/Table.vue'
import {Head, useForm, usePage} from '@inertiajs/vue3';
import NavLink from "@/Components/NavLink.vue";
import IconBase from "@/Components/Icons/IconBase.vue";
import IconWrite from "@/Components/Icons/IconWrite.vue";
import IconTrash from "@/Components/Icons/IconTrash.vue";
import {trans} from "laravel-vue-i18n";

const user = usePage().props.auth.user;

const props = defineProps({
    wishlists: {
        type: Array,
    },
});

const form = useForm({});
const destroyWishlist = (id) => {
    if(confirm(trans('wishlist.are_you_sure_you_want_to_delete_this_wishlist'))){
        form.delete(route('wishlists.destroy', {wishlist: id}));
    }
};

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
                <div
                    class="bg-white p-4 shadow sm:rounded-lg sm:p-8"
                >
                    <table-component :headers="[$t('wishlist.name'), $t('wishlist.owner'), $t('wishlist.is_shared'), $t('wishlist.expiration_date'), null, null]" :data="wishlists">
                        <template #column0="{ entity }">
                            <NavLink
                                :href="route('wishlists.show', {wishlist: entity})"
                                :active="route().current('wishlists.index')"
                            >
                                {{ entity.name }}
                            </NavLink>
                        </template>
                        <template #column1="{ entity }">
                            {{ entity.user.name }}
                        </template>
                        <template #column2="{ entity }">
                            {{ entity.is_shared?$t('messages.yes'):$t('messages.no') }}
                        </template>
                        <template #column3="{ entity }">
                            {{ entity.expiration_date }}
                        </template>
                        <template #column4="{ entity }">
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
                        <template #column5="{ entity }">
                            <button
                                v-if="user.id == entity.user_id && !entity.is_shared"
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
@import '././resources/css/nav_button.css';
</style>
