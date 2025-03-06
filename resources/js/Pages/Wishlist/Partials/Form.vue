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

const form = useForm({
    id: props.wishlist?.id || null,
    name: props.wishlist?.name || '',
    is_shared: props.wishlist?.is_shared || false,
    expiration_date: props.wishlist?.expiration_date || '',
});
</script>

<template>


    <section>
        <form @submit.prevent="wishlist?form.put(route('wishlists.update',{wishlist: wishlist})):form.post(route('wishlists.store'))" class="mt-6 space-y-6">
            <div>
                <InputLabel for="name" value="Name" />
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
                <InputLabel for="expiration_date" value="Expiration date" />
                <TextInput
                    id="expiration_date"
                    type="date"
                    class="mt-1 block w-full"
                    v-model="form.expiration_date"
                    required
                />
                <InputError class="mt-2" :message="form.errors.expiration_date" />
            </div>

            <div v-if="wishlistItems && wishlistItems.length">
                <InputLabel for="is_shared" value="Is shared" />
                <Checkbox
                    id="is_shared"
                    v-model="form.is_shared"
                    :checked="form.is_shared"
                />
            </div>

            <div class="flex items-center gap-4">
                <PrimaryButton :disabled="form.processing">Save</PrimaryButton>
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
                        Saved.
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>

<style scoped>

</style>
