<script setup>
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
import { Link } from "@inertiajs/vue3";
</script>

<template>
    <div
        class="flex min-h-screen flex-col items-center justify-center bg-gradient-to-br from-brand-blue via-slate-900 to-brand-red pt-6 sm:pt-0"
    >
        <div class="mb-6">
            <Link href="/">
                <ApplicationLogo
                    class="h-auto w-72 fill-current text-white drop-shadow-lg"
                />
            </Link>
        </div>

        <div
            class="w-full overflow-hidden bg-white/95 backdrop-blur-sm px-8 py-8 shadow-2xl sm:max-w-md sm:rounded-2xl border border-white/20"
        >
            <div class="mb-6 text-center">
                <h1 class="text-2xl font-bold text-slate-800">Bienvenido</h1>
                <p class="text-slate-500 text-sm mt-1">
                    Ingresa tus credenciales para continuar
                </p>
            </div>
            <slot />
        </div>

        <div class="mt-8 text-white text-xs text-center">
            &copy; {{ new Date().getFullYear() }} Sistema de Gestión de Pólizas.
            Todos los derechos reservados.
        </div>
    </div>
</template>
