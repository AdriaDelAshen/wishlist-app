<script setup xmlns="http://www.w3.org/1999/html">
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import {useForm} from '@inertiajs/vue3';
import Checkbox from "@/Components/Checkbox.vue";

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

var tomorrow = new Date(+new Date() + 86400000);
tomorrow = tomorrow.toISOString().split('T')[0];

const form = useForm({
    name: props.wishlist?.name || '',
    is_shared: props.wishlist?.is_shared || false,
    expiration_date: props.wishlist?.expiration_date || '',
    can_be_duplicated: props.wishlist?.can_be_duplicated || false,
});
</script>

<template>
    <section>
        <form @submit.prevent="wishlist?form.put(route('wishlists.update',{wishlist: wishlist.id})):form.post(route('wishlists.store'))" class="mt-6 space-y-6">
            <div>
                <InputLabel for="name" :value="$t('wishlist.name')" />
                <TextInput
                    id="name"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.name"
                    required
                    autocomplete="name"
                />
                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div>
                <InputLabel for="expiration_date" :value="$t('wishlist.expiration_date')" />
                <div class="relative">
                    <TextInput
                        id="expiration_date"
                        type="date"
                        class="mt-1 block w-full"
                        :style="'padding-right: 30px;'"
                        v-model="form.expiration_date"
                        :min="tomorrow"
                        required
                    />
                    <button
                        v-if="form.expiration_date"
                        @click="form.expiration_date = ''"
                        type="button"
                        class="absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none"
                    >
                        ✕
                    </button>
                </div>
                <InputError class="mt-2" :message="form.errors.expiration_date" />
            </div>

            <div>
                <InputLabel for="can_be_duplicated" :value="$t('wishlist.can_be_duplicated')" />
                <Checkbox
                    id="can_be_duplicated"
                    v-model="form.can_be_duplicated"
                    :checked="form.can_be_duplicated"
                />
            </div>

            <div v-if="wishlistItems && wishlistItems.length">
                <InputLabel for="is_shared" :value="$t('wishlist.is_shared')" />
                <Checkbox
                    id="is_shared"
                    v-model="form.is_shared"
                    :checked="form.is_shared"
                />
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
