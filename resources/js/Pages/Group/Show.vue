<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {Head, router, useForm, usePage} from '@inertiajs/vue3';
import InputLabel from "@/Components/InputLabel.vue";
import TableComponent from "@/Components/Table.vue";
import NavLink from "@/Components/NavLink.vue";
import { trans } from "laravel-vue-i18n";
import Pagination from "@/Components/Pagination.vue";
import usePaginationAndSorting from "@/pagination.js";
import {computed, ref} from "vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import InputError from "@/Components/InputError.vue";
import Form from "@/Pages/Group/Partials/Form.vue";
import NumberInput from "@/Components/NumberInput.vue";
import PriceInput from "@/Components/PriceInput.vue";

const props = defineProps({
    group: {
        type: Object,
        required: true
    },
    currentUserPersonalContribution: {
        type: Number,
        required: true
    }
});

const user = usePage().props.auth.user;
const headers = computed(() => {
    const baseHeaders = [
        { label: 'user.id', column: 'id' },
        { label: 'user.name', column: 'name' },
        { label: 'user.email_address', column: 'email' },
        {}, // link/unlink to the group
    ];

    // Insert contribution_amount only if group has a wishlist_item
    if (props.group?.wishlist_item) {
        // Insert it before the last empty object
        baseHeaders.splice(3, 0, {
            label: 'user.contribution_amount',
            column: 'contribution_amount'
        });
    }

    return baseHeaders;
});
const contributedAmount = computed(() => {
    if (props.group?.wishlist_item) {
        return props.group.wishlist_item.contributed_amount;
    }
});

const {
    currentData,
    pagination,
    sortBy,
    sortDirection,
    getCurrentPageData,
    onPageChange,
    onSortChanged
} = usePaginationAndSorting('groups.get_current_group_users_data_page', {group_id: props.group.id});

const contributionAmount = ref(props.currentUserPersonalContribution)
const errors = ref({})
const loading = ref(false)
const saved = ref(false)

const updateContribution = async () => {
    loading.value = true
    errors.value = {}
    saved.value = false

    try {
        const { data } = await axios.patch(
            route('wishlist_items.update_contribution', { wishlist_item: props.group.wishlist_item }),
            { contribution_amount: contributionAmount.value }
        )

        // Update local data
        props.group.wishlist_item.contributed_amount = data.contributed_amount
        saved.value = true
    } catch (error) {
        if (error.response?.status === 422) {
            // Laravel validation errors
            errors.value = error.response.data.errors || {}
        } else {
            console.error(error)
        }
    } finally {
        loading.value = false;
    }
}

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
</script>

<template>
    <Head title="Group" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                {{ $t('group.group') }}: {{ group.name }}
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8">
                    <div class="mt-6 space-y-6">
                        <div>
                            <InputLabel for="name" :value="$t('group.name')" />
                            <p class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 mt-1 block w-full disabled-input">
                                {{ group.name }}
                            </p>
                        </div>

                        <div>
                            <InputLabel for="description" :value="$t('group.description')" />
                            <p class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 mt-1 block w-full disabled-input">
                                {{ group.description?group.description:'('+$t('messages.none')+')' }}
                            </p>
                        </div>

                        <div>
                            <InputLabel for="user_id" :value="$t('group.owner')" />
                            <p class="disabled-input">
                                {{ group.owner?group.owner.name:'-' }}
                            </p>
                        </div>

                        <div>
                            <InputLabel for="is_private" :value="$t('group.is_private')" />
                            <p class="disabled-input">
                                {{ group.is_private?$t('messages.yes'):$t('messages.no') }}
                            </p>
                        </div>

                        <div>
                            <InputLabel for="is_active" :value="$t('group.is_active')" />
                            <p class="disabled-input">
                                {{ group.is_active?$t('messages.yes'):$t('messages.no') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="group.wishlist_item" class="py-12" style="padding-top:0;">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8">
                    <h3 class="text-xl font-semibold leading-tight text-gray-800">
                        Item
                    </h3>
                    <div>
                        {{ $t('wishlist_item.name') }}: <NavLink :href="route('wishlist_items.show', {wishlist_item: group.wishlist_item.id})">{{ group.wishlist_item.name }}</NavLink>
                    </div>
                    <div>
                        {{ $t('wishlist_item.contributed_amount') }}: {{ contributedAmount }} / {{ group.wishlist_item.price }}$
                    </div>
                    <div>
                        {{ $t('wishlist_item.wishlist') }}: <NavLink :href="route('wishlists.show', {wishlist: group.wishlist_item.wishlist.id})">{{ group.wishlist_item.wishlist.name }}</NavLink>
                    </div>
                    <div>
                        <form @submit.prevent="updateContribution" class="mt-6 space-y-6">
                            <InputLabel for="contribution_amount" :value="$t('wishlist_item.personal_contribution')" />
                            <PriceInput
                                id="contribution_amount"
                                class="mt-1 block w-full"
                                v-model="contributionAmount"
                            />
                            <InputError class="mt-2" :message="errors.contribution_amount" />

                            <div class="flex items-center gap-4">
                                <PrimaryButton :disabled="loading">{{ $t('messages.save') }}</PrimaryButton>
                                <Transition
                                    enter-active-class="transition ease-in-out"
                                    enter-from-class="opacity-0"
                                    leave-active-class="transition ease-in-out"
                                    leave-to-class="opacity-0"
                                >
                                    <p
                                        v-if="saved"
                                        class="text-sm text-gray-600"
                                    >
                                        {{ $t('messages.saved') }}
                                    </p>
                                </Transition>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>


        <div class="py-12" style="padding-top:0;">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <h3 class="text-xl font-semibold leading-tight text-gray-800">
                    {{ $t('group.members') }}
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
                            {{ entity.id }}
                        </template>
                        <template #column1="{ entity }">
                            {{ entity.name }}
                        </template>
                        <template #column2="{ entity }">
                            {{ entity.email }}
                        </template>
                        <template v-if="group.wishlist_item" #column3="{ entity }">
                            {{ entity.contribution_amount }}$
                        </template>
                        <template #column4="{ entity }">

                        </template>
                    </table-component>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>

</template>

<style scoped>
</style>
