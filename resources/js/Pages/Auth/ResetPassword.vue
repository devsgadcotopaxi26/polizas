<script setup>
import GuestLayout from "@/Layouts/GuestLayout.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import { Head, useForm } from "@inertiajs/vue3";

const props = defineProps({
    email: {
        type: String,
        required: true,
    },
    token: {
        type: String,
        required: true,
    },
});

const form = useForm({
    token: props.token,
    email: props.email,
    password: "",
    password_confirmation: "",
});

const submit = () => {
    form.post(route("password.store"), {
        onFinish: () => form.reset("password", "password_confirmation"),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Establecer Contraseña" />

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="email" value="Correo electrónico" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="mt-4">
                <InputLabel for="password" value="Contraseña" />

                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password"
                    required
                    autocomplete="new-password"
                />

                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="mt-4">
                <InputLabel
                    for="password_confirmation"
                    value="Confirmar Contraseña"
                />

                <TextInput
                    id="password_confirmation"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password_confirmation"
                    required
                    autocomplete="new-password"
                />

                <InputError
                    class="mt-2"
                    :message="form.errors.password_confirmation"
                />
            </div>

            <ul class="mt-3 text-xs text-slate-500 space-y-1 pl-1">
                <li :class="form.password.length >= 8 ? 'text-emerald-600' : ''" class="flex items-center gap-1.5 transition-colors">
                    <span class="text-base">{{ form.password.length >= 8 ? '✓' : '·' }}</span>
                    Mínimo 8 caracteres
                </li>
                <li :class="/[A-Z]/.test(form.password) ? 'text-emerald-600' : ''" class="flex items-center gap-1.5 transition-colors">
                    <span class="text-base">{{ /[A-Z]/.test(form.password) ? '✓' : '·' }}</span>
                    Al menos una mayúscula
                </li>
                <li :class="/[a-z]/.test(form.password) ? 'text-emerald-600' : ''" class="flex items-center gap-1.5 transition-colors">
                    <span class="text-base">{{ /[a-z]/.test(form.password) ? '✓' : '·' }}</span>
                    Al menos una minúscula
                </li>
                <li :class="/[0-9]/.test(form.password) ? 'text-emerald-600' : ''" class="flex items-center gap-1.5 transition-colors">
                    <span class="text-base">{{ /[0-9]/.test(form.password) ? '✓' : '·' }}</span>
                    Al menos un número
                </li>
                <li :class="form.password && form.password === form.password_confirmation ? 'text-emerald-600' : ''" class="flex items-center gap-1.5 transition-colors">
                    <span class="text-base">{{ form.password && form.password === form.password_confirmation ? '✓' : '·' }}</span>
                    Las contraseñas coinciden
                </li>
            </ul>

            <div class="mt-4 flex items-center justify-end">
                <PrimaryButton
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Guardar Contraseña
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
