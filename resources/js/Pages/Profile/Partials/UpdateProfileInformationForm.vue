<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Link, useForm } from '@inertiajs/vue3';
import SelectInput from "@/Components/SelectInput.vue";
import { useLocalesStore } from '@/Stores/localesStore.js';
import { storeToRefs } from "pinia";
import Checkbox from "@/Components/Checkbox.vue";

defineProps({
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
    name: currentUser.value.name,
    email: currentUser.value.email,
    birthday_date: currentUser.value.birthday_date,
    preferred_locale: currentUser.value.preferred_locale,
    wants_birthday_notifications: currentUser.value.wants_birthday_notifications,
});

</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900">
                {{ $t('profile.profile_information') }}
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                {{ $t('profile.update_your_account_profile_information_and_email_address') }}
            </p>
        </header>

        <form
            @submit.prevent="form.patch(route('profile.update'), {
                onSuccess: () => {
                    changeLocale(form.preferred_locale);
                }
            })"
            class="mt-6 space-y-6"
        >
            <div>
                <InputLabel for="name" :value="$t('profile.name')" />
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
                <InputLabel for="email" :value="$t('profile.email_address')" />
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

            <div v-if="mustVerifyEmail && currentUser.email_verified_at === null">
                <p class="mt-2 text-sm text-gray-800">
                    {{ $t('profile.your_email_address_is_unverified') }}
                    <Link
                        :href="route('verification.send')"
                        method="post"
                        as="button"
                        class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    >
                        {{ $t('profile.click_here_to_resend_the_verification_email') }}
                    </Link>
                </p>

                <div
                    v-show="status === 'verification-link-sent'"
                    class="mt-2 text-sm font-medium text-green-600"
                >
                    {{ $t('profile.a_new_verification_link_has_been_sent') }}
                </div>
            </div>

            <div>
                <InputLabel for="birthday_date" :value="$t('profile.birthday_date')" />
                <div class="relative">
                    <TextInput
                        id="birthday_date"
                        type="date"
                        class="mt-1 block w-full"
                        :style="'padding-right: 30px;'"
                        v-model="form.birthday_date"
                    />
                    <button
                        v-if="form.birthday_date"
                        @click="form.birthday_date = ''"
                        type="button"
                        class="absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none"
                    >
                        ✕
                    </button>
                </div>
                <InputError class="mt-2" :message="form.errors.birthday_date" />
            </div>

            <div>
                <InputLabel for="wants_birthday_notifications" :value="$t('profile.wants_birthday_notifications')" />
                <Checkbox
                    id="wants_birthday_notifications"
                    v-model="form.wants_birthday_notifications"
                    :checked="form.wants_birthday_notifications"
                />
                <InputError class="mt-2" :message="form.errors.wants_birthday_notifications" />
            </div>

            <div>
                <InputLabel for="preferred_locale" :value="$t('profile.preferred_locale')" />
                <SelectInput
                    id="preferred_locale"
                    class="mt-1 block w-full"
                    v-model="form.preferred_locale"
                    :options="options"
                    :must-translate-option="true"
                />
                <InputError class="mt-2" :message="form.errors.preferred_locale" />
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
