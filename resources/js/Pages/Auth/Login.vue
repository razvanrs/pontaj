<template>
    <Head title="Autentificare" />

    <AuthenticationCard>

        <div v-if="status" class="mb-4 font-medium text-sm text-green-600">
            {{ status }}
        </div>

        <form @submit.prevent="submit">
            <div class="space-y-2">
                <InputLabel for="email" value="Email" />
                <TextInput
                    id="email"
                    v-model="form.email"
                    type="email"
                    class="block w-full"
                    required
                    autofocus
                    autocomplete="username"
                />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="space-y-2 mt-4">
                <InputLabel for="password" value="Parolă" />
                <TextInput
                    id="password"
                    v-model="form.password"
                    type="password"
                    class="block w-full"
                    required
                    autocomplete="current-password"
                />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="block mt-4">

            </div>

            <div class="flex items-center justify-between mt-6">
                <label class="flex items-center">
                    <Checkbox v-model:checked="form.remember" name="remember" />
                    <span class="ml-2 text-sm text-gray-600">Ține-mă minte</span>
                </label>

                <PrimaryButton class="ml-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    Intră în cont
                </PrimaryButton>
            </div>
        </form>
    </AuthenticationCard>
</template>

<script setup>

import { Head, useForm } from '@inertiajs/vue3'
import AuthenticationCard from '@/Components/elements/AuthenticationCard.vue'
import Checkbox from '@/Components/elements/Checkbox.vue'
import InputError from '@/Components/elements/InputError.vue'
import InputLabel from '@/Components/elements/InputLabel.vue'
import PrimaryButton from '@/Components/elements/PrimaryButton.vue'
import TextInput from '@/Components/elements/TextInput.vue'

defineProps({
    canResetPassword: Boolean,
    status: String,
})

const form = useForm({
    email: null,
    password: null,
    remember: false,
})

const submit = () => {
    form.transform(data => ({
        ...data,
        remember: form.remember ? 'on' : '',
    })).post(route('login'), {
        onFinish: () => form.reset('password'),
    })
}

</script>
