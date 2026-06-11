<script setup>
import { useForm, Head, router } from "@inertiajs/vue3";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";

const form = useForm({
    password: "",
    password_confirmation: "",
});

const submit = () => {
    form.post(route("password.change.update"), {
        onFinish: () => form.reset(),
    });
};
</script>

<template>
    <Head title="Cambiar Contraseña" />

    <div class="min-h-screen bg-gradient-to-br from-[#011e3c] via-[#024283] to-[#0369a1] flex items-center justify-center p-4">
        <!-- Card principal -->
        <div class="w-full max-w-md">
            <!-- Logo / Icono -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-20 h-20 rounded-2xl bg-white/10 backdrop-blur-sm border border-white/20 mb-4 shadow-xl">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-white">Cambio de Contraseña</h1>
                <p class="text-blue-200 text-sm mt-1">Requerido antes de continuar</p>
            </div>

            <!-- Alert informativo -->
            <div class="mb-6 flex items-start gap-3 bg-amber-400/20 border border-amber-400/40 rounded-xl px-4 py-3 backdrop-blur-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-amber-300 flex-shrink-0 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
                <p class="text-amber-200 text-sm leading-relaxed">
                    Por seguridad, debes establecer una nueva contraseña personal antes de acceder al sistema. La contraseña asignada por el administrador es temporal.
                </p>
            </div>

            <!-- Formulario -->
            <div class="bg-white/10 backdrop-blur-md border border-white/20 rounded-2xl p-8 shadow-2xl">
                <form @submit.prevent="submit" class="space-y-5">
                    <!-- Nueva contraseña -->
                    <div>
                        <InputLabel
                            for="password"
                            value="Nueva Contraseña"
                            class="text-white font-medium"
                        />
                        <TextInput
                            id="password"
                            type="password"
                            class="mt-1 block w-full bg-white/10 border-white/20 text-white placeholder-blue-300 focus:border-blue-300 focus:ring-blue-300 rounded-lg"
                            v-model="form.password"
                            placeholder="Mínimo 8 caracteres"
                            required
                            autofocus
                        />
                        <InputError class="mt-1.5" :message="form.errors.password" />
                    </div>

                    <!-- Confirmar contraseña -->
                    <div>
                        <InputLabel
                            for="password_confirmation"
                            value="Confirmar Nueva Contraseña"
                            class="text-white font-medium"
                        />
                        <TextInput
                            id="password_confirmation"
                            type="password"
                            class="mt-1 block w-full bg-white/10 border-white/20 text-white placeholder-blue-300 focus:border-blue-300 focus:ring-blue-300 rounded-lg"
                            v-model="form.password_confirmation"
                            placeholder="Repite tu nueva contraseña"
                            required
                        />
                        <InputError class="mt-1.5" :message="form.errors.password_confirmation" />
                    </div>

                    <!-- Indicadores de seguridad -->
                    <ul class="text-xs text-blue-200 space-y-1 pl-1">
                        <li :class="form.password.length >= 8 ? 'text-emerald-300' : ''" class="flex items-center gap-1.5 transition-colors">
                            <span class="text-base">{{ form.password.length >= 8 ? '✓' : '·' }}</span>
                            Mínimo 8 caracteres
                        </li>
                        <li :class="/[A-Z]/.test(form.password) ? 'text-emerald-300' : ''" class="flex items-center gap-1.5 transition-colors">
                            <span class="text-base">{{ /[A-Z]/.test(form.password) ? '✓' : '·' }}</span>
                            Al menos una mayúscula
                        </li>
                        <li :class="/[0-9]/.test(form.password) ? 'text-emerald-300' : ''" class="flex items-center gap-1.5 transition-colors">
                            <span class="text-base">{{ /[0-9]/.test(form.password) ? '✓' : '·' }}</span>
                            Al menos un número
                        </li>
                        <li :class="form.password && form.password === form.password_confirmation ? 'text-emerald-300' : ''" class="flex items-center gap-1.5 transition-colors">
                            <span class="text-base">{{ form.password && form.password === form.password_confirmation ? '✓' : '·' }}</span>
                            Las contraseñas coinciden
                        </li>
                    </ul>

                    <!-- Botón -->
                    <PrimaryButton
                        class="w-full justify-center !bg-white !text-[#024283] hover:!bg-blue-50 font-bold py-2.5 shadow-lg transition-all duration-150 !rounded-lg"
                        :class="{ 'opacity-60 cursor-not-allowed': form.processing }"
                        :disabled="form.processing"
                    >
                        <span v-if="!form.processing">Establecer Contraseña y Acceder</span>
                        <span v-else class="flex items-center gap-2">
                            <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 3 2.686 3 5.97..."/>
                            </svg>
                            Procesando...
                        </span>
                    </PrimaryButton>
                </form>
            </div>

            <!-- Logout link -->
            <div class="text-center mt-4">
                <button
                    type="button"
                    @click="router.post(route('logout'))"
                    class="text-blue-300 text-sm hover:text-white transition-colors"
                >
                    Cerrar sesión y volver
                </button>
            </div>
        </div>
    </div>
</template>
