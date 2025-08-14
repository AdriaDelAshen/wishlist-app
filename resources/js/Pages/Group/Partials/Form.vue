<script setup xmlns="http://www.w3.org/1999/html">
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import {useForm} from '@inertiajs/vue3';
import Checkbox from "@/Components/Checkbox.vue";

const props = defineProps({
    group: {
        type: Object,
        required: false
    }
});

const form = useForm({
    name: props.group?.name || '',
    description: props.group?.description || '',
    is_private: props.group?.is_private || true,
    is_active: props.group?.is_active || true,
});
</script>

<template>
    <section>
        <form @submit.prevent="group?form.put(route('groups.update',{group: group})):form.post(route('groups.store'))" class="mt-6 space-y-6">
            <div>
                <InputLabel for="name" :value="$t('group.name')" />
                <TextInput
                    id="name"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.name"
                    required
                    autocomplete="name"
                    :disabled="group && !!group.wishlist_item"
                />
                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div>
                <InputLabel for="description" :value="$t('group.description')" />
                <TextInput
                    id="description"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.description"
                />
                <InputError class="mt-2" :message="form.errors.description" />
            </div>

            <div v-show="group && !!group.wishlist_item">
                <InputLabel for="is_private" :value="$t('group.is_private')" />
                <Checkbox
                    id="is_private"
                    v-model="form.is_private"
                    :checked="form.is_private"
                />
            </div>

            <div v-show="group && !!group.wishlist_item">
                <InputLabel for="is_active" :value="$t('group.is_active')" />
                <Checkbox
                    id="is_active"
                    v-model="form.is_active"
                    :checked="form.is_active"
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
