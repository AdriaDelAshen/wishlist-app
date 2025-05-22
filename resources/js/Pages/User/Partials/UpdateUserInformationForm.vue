<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Link, useForm } from '@inertiajs/vue3';
import Checkbox from "@/Components/Checkbox.vue";
import SelectInput from "@/Components/SelectInput.vue";
import { useLocalesStore } from "@/Stores/localesStore.js";
import { storeToRefs } from "pinia";

const props = defineProps({
    user: {
        type: Object,
        required: true
    },
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
    options: {
        type: Object,
        required: true,
    },
});

const localesStore = useLocalesStore();
const { locale, currentUser } = storeToRefs(localesStore);
const { changeLocale } = localesStore;

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    birthday_date: props.user.birthday_date,
    preferred_locale: props.user.preferred_locale,
    is_active: props.user?.is_active || false,
    is_admin: props.user?.is_admin || false
});
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900">
                {{ $t('user.user_information') }}
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                {{ $t('user.update_the_user_information_and_email_address') }}
            </p>
        </header>

        <form
            @submit.prevent="form.patch(route('users.update', {user: user.id}), {
                onSuccess: () => {
                    if(currentUser.id === user.id) {
                        changeLocale(form.preferred_locale);
                    }
                }
            })"
            class="mt-6 space-y-6"
        >
            <div>
                <InputLabel for="name" :value="$t('user.name')" />
                <TextInput
                    id="name"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                />
                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div>
                <InputLabel for="email" :value="$t('user.email_address')" />
                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    required
                    autocomplete="username"
                />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div>
                <InputLabel for="birthday_date" :value="$t('user.birthday_date')" />
                <TextInput
                    id="birthday_date"
                    type="date"
                    class="mt-1 block w-full"
                    v-model="form.birthday_date"
                />
                <InputError class="mt-2" :message="form.errors.birthday_date" />
            </div>

            <div>
                <InputLabel for="preferred_locale" :value="$t('user.preferred_locale')" />
                <SelectInput
                    id="preferred_locale"
                    class="mt-1 block w-full"
                    v-model="form.preferred_locale"
                    :options="options"
                    :must-translate-option="true"
                />
                <InputError class="mt-2" :message="form.errors.preferred_locale" />
            </div>

            <div v-if="currentUser.id === user.id && mustVerifyEmail && user.email_verified_at === null">
                <p class="mt-2 text-sm text-gray-800">
                    {{ $t('user.your_email_address_is_unverified') }}
                    <Link
                        :href="route('verification.send')"
                        method="post"
                        as="button"
                        class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    >
                        {{ $t('user.click_here_to_resend_the_verification_email') }}
                    </Link>
                </p>

                <div
                    v-show="status === 'verification-link-sent'"
                    class="mt-2 text-sm font-medium text-green-600"
                >
                    {{ $t('user.a_new_verification_link_has_been_sent') }}
                </div>
            </div>


            <div>
                <InputLabel for="is_active" :value="$t('user.active')" />
                <Checkbox
                    id="is_active"
                    v-model="form.is_active"
                    :checked="form.is_active"
                />
                <InputError class="mt-2" :message="form.errors.is_active" />
            </div>

            <div>
                <InputLabel for="is_admin" :value="$t('user.is_administrator')" />
                <Checkbox
                    id="is_admin"
                    v-model="form.is_admin"
                    :checked="form.is_admin"
                />
                <InputError class="mt-2" :message="form.errors.is_admin" />
            </div>

            <div class="flex items-center gap-4">
                <PrimaryButton :disabled="form.processing">{{ $t('messages.save') }}</PrimaryButton>
                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p
                        v-if="form.recentlySuccessful"
                        class="text-sm text-gray-600"
                    >
                        {{ $t('messages.saved') }}
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>
