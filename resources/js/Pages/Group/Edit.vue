<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {Head, useForm, usePage} from '@inertiajs/vue3';
import Form from "@/Pages/Group/Partials/Form.vue";
import TextInput from "@/Components/TextInput.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import IconTrash from "@/Components/Icons/IconTrash.vue";
import TableComponent from "@/Components/Table.vue";
import IconBase from "@/Components/Icons/IconBase.vue";
import * as bootstrap from "bootstrap";
import {trans} from "laravel-vue-i18n";
import usePaginationAndSorting from "@/pagination.js";
import Pagination from "@/Components/Pagination.vue";
import SelectInput from "@/Components/SelectInput.vue";
import axios from "axios";
import {ref} from "vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import Checkbox from "@/Components/Checkbox.vue";

const props = defineProps({
    group: {
        type: Object,
        required: true
    },
    activeUsersNotInCurrentGroup: {
        type: Object,
        required: true
    }
});
let activeUsersNotInCurrentGroup = ref(props.activeUsersNotInCurrentGroup);
const user = usePage().props.auth.user;
const headers = [
    { label: 'user.id', column: 'id' },
    { label: 'user.name', column: 'name' },
    { label: 'user.email_address', column: 'email' },
    {}, // link/unlink to the group
];

const {
    currentData,
    pagination,
    sortBy,
    sortDirection,
    getCurrentPageData,
    onPageChange,
    onSortChanged
} = usePaginationAndSorting('groups.get_current_group_users_data_page',{group_id: props.group.id});
//
const userForm = useForm({
    id: null,
    user: '',
    // name: '',
    // email: '',
});

const invitationForm = useForm({
    emails: [null],
    force_send_email_to_pending_invites: false,
});

const showModalToLinkOrUnlinkUser = (entity) => {
    if(entity) {
        userForm.id = entity.id;
    } else {
        userForm.id = null;
    }
};

const addEmailField = () => {
    if (invitationForm.emails.length < 10) { // limit to 10 if needed
        invitationForm.emails.push('');
    }
};

const removeEmailField = (index) => {
    if (invitationForm.emails.length > 1) {
        invitationForm.emails.splice(index, 1);
    }
};

const showModalToSendInviteLinkToUser = () => {
    invitationForm.emails = [null];
};

const closeModal = (modalName) => {
    const myModalEl = document.getElementById(modalName);
    const modal = bootstrap.Modal.getInstance(myModalEl)
    modal.hide();
};

const copyInvitationLink = async () => {
    try {
        const response = await axios.post(route('groups.generate_link', { group: props.group.id }));
        const link = response.data.url;

        if (navigator.clipboard && window.isSecureContext) {
            await navigator.clipboard.writeText(link);
        } else {
            // Fallback for insecure context or unsupported clipboard
            const textarea = document.createElement("textarea");
            textarea.value = link;
            textarea.style.position = "fixed";  // Prevent scrolling to bottom
            textarea.style.opacity = "0";
            document.body.appendChild(textarea);
            textarea.focus();
            textarea.select();
            document.execCommand("copy");
            document.body.removeChild(textarea);
        }

        alert(trans('group.invite_link_copied'));
    } catch (error) {
        console.error(error);
        alert(trans('group.error_generating_link'));
    }
};

const getActiveUsersNotInCurrentGroup = () => {
    axios
        .get(route('groups.get_active_users_not_in_current_group'), {
            params: {
                group_id: props.group.id
            }
        })
        .then(response => {
            activeUsersNotInCurrentGroup = response.data.users
        })
        .catch(console.error);
};

const removeUserFromGroup = (id) => {
    if(confirm(trans('group.are_you_sure_you_want_to_remove_this_user_from_this_group'))){
        userForm.delete(route('groups.remove_user_from_group', {group: props.group.id, user_id: id}),
            {
                preserveScroll: true,
            });

        getCurrentPageData(1);
        getActiveUsersNotInCurrentGroup();
    }
};

const removeInvitationFromGroup = (id) => {
    if(confirm(trans('group.are_you_sure_you_want_to_remove_this_invitation_from_this_group'))){
        userForm.delete(route('group_invitations.remove_invitation_from_group', {group_invitation: id}),
            {
                preserveScroll: true,
            });

        getCurrentPageData(1);
    }
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
</script>

<template>
    <Head title="Group" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                {{ $t('group.editing_group') }}: {{ group.name }}
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8">
                    <Form class="max-w-xl" :group="group"/>
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
                        Name: {{ group.wishlist_item.name }}
                    </div>
                </div>
            </div>
        </div>

        <div class="py-12" style="padding-top:0;">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <h3 class="text-xl font-semibold leading-tight text-gray-800">
                    {{ $t('group.members') }}
                </h3>

                <div style="display: flex; justify-content: space-between;">
                    <Pagination
                        :pagination="pagination"
                        @pageChanged="onPageChange"
                    />
                    <div v-show="!group.wishlist_item" style="align-content: flex-end;">
                        <PrimaryButton
                            v-if="user.is_admin"
                            type="button"
                            data-bs-toggle="modal"
                            data-bs-target="#manage_user_for_group_form_modal"
                            @click="showModalToLinkOrUnlinkUser(null)"
                        >
                            {{ $t('group.add_existing_user') }}
                        </PrimaryButton>
                        <PrimaryButton
                            v-if="user.id === group.user_id"
                             type="button"
                             data-bs-toggle="modal"
                             data-bs-target="#send_invitation_to_users_for_group_form_modal"
                             @click="showModalToSendInviteLinkToUser()"
                        >
                            {{ $t('group.invite_by_email') }}
                        </PrimaryButton>
                        <PrimaryButton
                            v-if="user.id === group.user_id"
                            type="button"
                            class="ml-2"
                            @click="copyInvitationLink"
                        >
                            {{ $t('group.copy_invite_link') }}
                        </PrimaryButton>
                    </div>

                </div>

                <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8">
                    <table-component :headers="headers"
                                     :data="currentData"
                                     :currentSortBy="sortBy"
                                     :currentSortDirection="sortDirection"
                                     @sortChanged="onSortChanged">
                        <template #column0="{ entity }">
                            {{ entity.public_id }}
                        </template>
                        <template #column1="{ entity }">
                            {{ entity.name }}
                        </template>
                        <template #column2="{ entity }">
                            {{ entity.email ?? '' }}
                        </template>
                        <template #column3="{ entity }">
                            <button
                                v-if="user.id === group.user_id && entity.type === 'user' && entity.id !== group.user_id"
                                @click="removeUserFromGroup(entity.id)"
                                class="nav-button delete"
                            >
                                <icon-base icon-name="trash">
                                    <icon-trash />
                                </icon-base>
                            </button>
                            <button
                                v-if="entity.type === 'invitation'"
                                @click="removeInvitationFromGroup(entity.id)"
                                class="nav-button delete"
                            >
                                <icon-base icon-name="trash">
                                    <icon-trash />
                                </icon-base>
                            </button>
                        </template>
                    </table-component>
                </div>
            </div>
        </div>

        <!-- Modal for sending email invitations -->
        <div class="modal fade" id="send_invitation_to_users_for_group_form_modal" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form @submit.prevent="invitationForm.post(route('groups.send_invitations', { group: group.id }), {
                        onBefore: () => {
                            invitationForm.emails = invitationForm.emails.filter(e => e.trim() !== '');
                        },
                        onSuccess: () => {
                            closeModal('send_invitation_to_users_for_group_form_modal');
                            invitationForm.emails = [null];
                            getCurrentPageData(1);
                        }
                    })">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalLabel">{{ $t('group.invite_users_by_email') }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div>
                                <InputLabel for="emails" :value="$t('group.enter_emails')" />
                                <div v-for="(email, index) in invitationForm.emails" :key="index" class="flex items-center gap-2 mb-2">
                                    <div class="flex-1">
                                        <TextInput
                                            type="email"
                                            class="w-full"
                                            v-model="invitationForm.emails[index]"
                                            :placeholder="$t('group.email_placeholder')"
                                            required
                                        />
                                        <InputError :message="invitationForm.errors[`emails.${index}`]" class="mt-1" />
                                    </div>
                                    <SecondaryButton type="button" @click="removeEmailField(index)" v-if="invitationForm.emails.length > 1">
                                        -
                                    </SecondaryButton>
                                    <PrimaryButton type="button" @click="addEmailField" v-if="index === invitationForm.emails.length - 1">
                                        +
                                    </PrimaryButton>
                                </div>
                            </div>
                            <div>
                                <InputLabel for="force_send_email_to_pending_invites" :value="$t('group.force_send_email_to_pending_invites')" />
                                <Checkbox
                                    id="force_send_email_to_pending_invites"
                                    v-model="invitationForm.force_send_email_to_pending_invites"
                                    :checked="invitationForm.force_send_email_to_pending_invites"
                                />
                            </div>
                        </div>
                        <div class="modal-footer">
                            <SecondaryButton type="button" data-bs-dismiss="modal">
                                {{ $t('messages.close') }}
                            </SecondaryButton>
                            <PrimaryButton :disabled="invitationForm.processing">
                                {{ $t('group.send_invites') }}
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal for adding existing users -->
        <div class="modal fade" id="manage_user_for_group_form_modal" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form @submit.prevent="userForm.post(route('groups.add_user_to_group', {group: group.id, user_id: userForm.user.id}),{
                        onSuccess: () => {
                                closeModal('manage_user_for_group_form_modal');
                                getCurrentPageData(1);
                                getActiveUsersNotInCurrentGroup();
                            },
                        });" class="mt-6 space-y-6">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalLabel">{{ $t('group.add_user_to_group') }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div>
                                <InputLabel for="user" :value="$t('user.user')" />
                                <SelectInput
                                    id="preferred_locale"
                                    class="mt-1 block w-full"
                                    required
                                    v-model="userForm.user"
                                    :options="activeUsersNotInCurrentGroup"
                                    :must-translate-option="false"
                                />
                                <InputError class="mt-2" :message="userForm.errors.user" />
                            </div>
                        </div>
                        <div class="modal-footer">
                            <SecondaryButton type="button" data-bs-dismiss="modal">{{ $t('messages.close') }}</SecondaryButton>
                            <div class="flex items-center gap-4">
                                <PrimaryButton :disabled="userForm.processing">
                                    {{ $t('messages.add') }}
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
