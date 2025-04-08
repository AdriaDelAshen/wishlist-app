<script setup xmlns="http://www.w3.org/1999/html">
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import {useForm} from '@inertiajs/vue3';
import Checkbox from "@/Components/Checkbox.vue";

const props = defineProps({
    user: {
        type: Object,
        required: false
    },
});

const form = useForm({
    id: props.user?.id || null,
    name: props.user?.name || '',
    email: props.user?.email || '',
    setup_password: false,
    is_active: props.user?.is_active || false,
    is_admin: props.user?.is_admin || false,
    password: '',
    password_confirmation: '',
});
</script>

<template>


    <section>
        <form @submit.prevent="user?form.put(route('users.update',{user: user})):form.post(route('users.store'))" class="mt-6 space-y-6">
            <div>
                <InputLabel for="name" :value="$t('user.name')" />
                <TextInput
                    id="name"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.name"
                    required
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
                />
                <InputError class="mt-2" :message="form.errors.email" />
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

            <div v-if="! user">
                <InputLabel for="setup_password" :value="$t('user.setup_password')" />
                <Checkbox
                    id="setup_password"
                    v-model="form.setup_password"
                    :checked="form.setup_password"
                />
                <InputError class="mt-2" :message="form.errors.setup_password" />
            </div>
            <div v-if="! user && form.setup_password">
                <InputLabel for="password" :value="$t('user.password')" />
                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password"
                    autocomplete="new-password"
                />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>
            <div v-if="! user && form.setup_password">
                <InputLabel for="password_confirmation" :value="$t('user.confirm_password')" />
                <TextInput
                    id="password_confirmation"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password_confirmation"
                    autocomplete="new-password"
                />
                <InputError class="mt-2" :message="form.errors.password_confirmation" />
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

<style scoped>

</style>
