<script setup>
import { ref } from "vue";

import Dropdown from "@/Components/Dropdown.vue";
import DropdownLink from "@/Components/DropdownLink.vue";
import NavLink from "@/Components/NavLink.vue";
import ResponsiveNavLink from "@/Components/ResponsiveNavLink.vue";
import { Link } from "@inertiajs/vue3";
import Footer from "@/Components/Footer.vue";

const showingNavigationDropdown = ref(false);
</script>

<template>
    <div>
        <div class="min-h-screen bg-gray-100 flex flex-col">
            <nav
                class="border-b border-white/10 bg-gradient-to-r from-brand-blue to-brand-red"
            >
                <!-- Primary Navigation Menu -->
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="flex h-16 justify-between">
                        <div class="flex">
                            <!-- Logo -->
                            <div class="flex shrink-0 items-center">
                                <Link :href="route('dashboard')">
                                    <img
                                        src="/images/logo-admin.png"
                                        alt="Logo Admin"
                                        class="block h-12 w-auto drop-shadow-md"
                                    />
                                </Link>
                            </div>

                            <!-- Navigation Links -->
                            <div
                                class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex"
                            >
                                <NavLink
                                    :href="route('dashboard')"
                                    :active="route().current('dashboard')"
                                >
                                    Dashboard
                                </NavLink>
                                <NavLink
                                    :href="route('polizas.index')"
                                    :active="route().current('polizas.*')"
                                >
                                    Pólizas
                                </NavLink>
                                <NavLink
                                    :href="route('aseguradoras.index')"
                                    :active="route().current('aseguradoras.*')"
                                >
                                    Aseguradoras

                                </NavLink>
                                <NavLink
                                    v-if="!($page.props.auth.user.roles && $page.props.auth.user.roles.includes('Gestor Tesorería Ambiente'))"
                                    :href="route('contratistas.index')"
                                    :active="route().current('contratistas.*')"
                                >
                                    Contratistas
                                </NavLink>
                                <NavLink
                                    :href="route('operadores-ambientales.index')"
                                    :active="route().current('operadores-ambientales.*')"
                                >
                                    Operadores Ambientales
                                </NavLink>
                                <NavLink
                                    v-if="!($page.props.auth.user.roles && $page.props.auth.user.roles.includes('Gestor Tesorería Ambiente'))"
                                    :href="route('contratos.index')"
                                    :active="route().current('contratos.*')"
                                >
                                    Contratos
                                </NavLink>
                                <NavLink
                                    v-if="!($page.props.auth.user.roles && $page.props.auth.user.roles.includes('Gestor Tesorería Ambiente'))"
                                    :href="route('administradores.index')"
                                    :active="route().current('administradores.*')"
                                >
                                    Administradores
                                </NavLink>
                                <NavLink
                                    v-if="
                                        $page.props.auth.user.roles &&
                                        ($page.props.auth.user.roles.includes('Administrador') ||
                                            $page.props.auth.user.roles.includes('Super Admin'))
                                    "
                                    :href="route('users.index')"
                                    :active="route().current('users.*')"
                                >
                                    Usuarios
                                </NavLink>

                                <!-- Configuración: solo para admins, no para Gestor Ambiental -->
                                <div
                                    class="relative inline-flex items-center"
                                    v-if="
                                        $page.props.auth.user.roles &&
                                        ($page.props.auth.user.roles.includes('Administrador') ||
                                            $page.props.auth.user.roles.includes('Super Admin'))
                                    "
                                >
                                    <Dropdown align="left" width="48">
                                        <template #trigger>
                                            <button
                                                type="button"
                                                class="inline-flex items-center px-1 pt-1 h-[64px] border-b-2 text-sm font-medium leading-5 transition duration-150 ease-in-out focus:outline-none"
                                                :class="route().current('configuracion.*') || route().current('ciudades.*') ? 'border-white text-white focus:border-white' : 'border-transparent text-white/70 hover:text-white hover:border-white/30 focus:text-white focus:border-white/30'"
                                            >
                                                Configuración
                                                <svg
                                                    class="ms-1 h-4 w-4"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 20 20"
                                                    fill="currentColor"
                                                >
                                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                        </template>

                                        <template #content>
                                            <DropdownLink :href="route('auditoria.index')">
                                                Auditoría Global
                                            </DropdownLink>
                                            <DropdownLink :href="route('configuracion.index')">
                                                Configuración Global
                                            </DropdownLink>
                                            <DropdownLink :href="route('ciudades.index')">
                                                Catálogo de Ciudades
                                            </DropdownLink>
                                        </template>
                                    </Dropdown>
                                </div>
                            </div>
                        </div>

                        <div class="hidden sm:ms-6 sm:flex sm:items-center">
                            <!-- Settings Dropdown -->
                            <div class="relative ms-3">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <span class="inline-flex rounded-md">
                                            <button
                                                type="button"
                                                class="inline-flex items-center rounded-md border border-transparent bg-white px-3 py-2 text-sm font-medium leading-4 text-gray-500 transition duration-150 ease-in-out hover:text-gray-700 focus:outline-none"
                                            >
                                                {{ $page.props.auth.user.name }}

                                                <svg
                                                    class="-me-0.5 ms-2 h-4 w-4"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 20 20"
                                                    fill="currentColor"
                                                >
                                                    <path
                                                        fill-rule="evenodd"
                                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                        clip-rule="evenodd"
                                                    />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <DropdownLink
                                            :href="route('profile.edit')"
                                        >
                                            Mi Perfil
                                        </DropdownLink>
                                        <DropdownLink
                                            :href="route('logout')"
                                            method="post"
                                            as="button"
                                        >
                                            Cerrar Sesión
                                        </DropdownLink>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>

                        <!-- Hamburger -->
                        <div class="-me-2 flex items-center sm:hidden">
                            <button
                                @click="
                                    showingNavigationDropdown =
                                        !showingNavigationDropdown
                                "
                                class="inline-flex items-center justify-center rounded-md p-2 text-gray-400 transition duration-150 ease-in-out hover:bg-gray-100 hover:text-gray-500 focus:bg-gray-100 focus:text-gray-500 focus:outline-none"
                            >
                                <svg
                                    class="h-6 w-6"
                                    stroke="currentColor"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        :class="{
                                            hidden: showingNavigationDropdown,
                                            'inline-flex':
                                                !showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16"
                                    />
                                    <path
                                        :class="{
                                            hidden: !showingNavigationDropdown,
                                            'inline-flex':
                                                showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Responsive Navigation Menu -->
                <div
                    :class="{
                        block: showingNavigationDropdown,
                        hidden: !showingNavigationDropdown,
                    }"
                    class="sm:hidden"
                >
                    <div class="space-y-1 pb-3 pt-2">
                        <ResponsiveNavLink
                            :href="route('dashboard')"
                            :active="route().current('dashboard')"
                        >
                            Dashboard
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                            :href="route('polizas.index')"
                            :active="route().current('polizas.*')"
                        >
                            Pólizas
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                            :href="route('aseguradoras.index')"
                            :active="route().current('aseguradoras.*')"
                        >
                            Aseguradoras

                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                            v-if="!($page.props.auth.user.roles && $page.props.auth.user.roles.includes('Gestor Tesorería Ambiente'))"
                            :href="route('contratistas.index')"
                            :active="route().current('contratistas.*')"
                        >
                            Contratistas
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                            :href="route('operadores-ambientales.index')"
                            :active="route().current('operadores-ambientales.*')"
                        >
                            Operadores Ambientales
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                            v-if="!($page.props.auth.user.roles && $page.props.auth.user.roles.includes('Gestor Tesorería Ambiente'))"
                            :href="route('contratos.index')"
                            :active="route().current('contratos.*')"
                        >
                            Contratos
                        </ResponsiveNavLink>
                        <div
                            v-if="
                                $page.props.auth.user.roles &&
                                ($page.props.auth.user.roles.includes('Administrador') ||
                                    $page.props.auth.user.roles.includes('Super Admin'))
                            "
                            class="pt-1"
                        >
                            <div class="px-4 py-2 font-bold text-gray-500 uppercase tracking-widest text-xs">Menu Configuración</div>
                            <ResponsiveNavLink
                                :href="route('auditoria.index')"
                                :active="route().current('auditoria.*')"
                                class="!pl-8 !py-2 text-sm"
                            >
                                Auditoría Global
                            </ResponsiveNavLink>
                            <ResponsiveNavLink
                                :href="route('configuracion.index')"
                                :active="route().current('configuracion.*')"
                                class="!pl-8 !py-2 text-sm"
                            >
                                Configuración Global
                            </ResponsiveNavLink>
                            <ResponsiveNavLink
                                :href="route('ciudades.index')"
                                :active="route().current('ciudades.*')"
                                class="!pl-8 !py-2 text-sm"
                            >
                                Catálogo de Ciudades
                            </ResponsiveNavLink>
                        </div>
                    </div>

                    <!-- Responsive Settings Options -->
                    <div class="border-t border-gray-200 pb-1 pt-4">
                        <div class="px-4">
                            <div class="text-base font-medium text-gray-800">
                                {{ $page.props.auth.user.name }}
                            </div>
                            <div class="text-sm font-medium text-gray-500">
                                {{ $page.props.auth.user.email }}
                            </div>
                        </div>

                        <div class="mt-3 space-y-1">
                            <ResponsiveNavLink :href="route('profile.edit')">
                                Mi Perfil
                            </ResponsiveNavLink>
                            <ResponsiveNavLink
                                :href="route('logout')"
                                method="post"
                                as="button"
                            >
                                Cerrar Sesión
                            </ResponsiveNavLink>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Heading -->
            <header class="bg-white shadow" v-if="$slots.header">
                <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main class="pb-16">
                <!-- Flash Messages -->
                <div
                    v-if="$page.props.flash.message"
                    class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 mt-4"
                >
                    <div
                        class="bg-emerald-100 border-l-4 border-emerald-500 text-emerald-800 p-4 rounded-r shadow-sm"
                    >
                        <div class="flex">
                            <svg
                                class="h-5 w-5 text-emerald-500 mr-2"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                                />
                            </svg>
                            <p class="font-bold">
                                {{ $page.props.flash.message }}
                            </p>
                        </div>
                    </div>
                </div>

                <div
                    v-if="$page.props.flash.error"
                    class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 mt-4"
                >
                    <div
                        class="bg-rose-100 border-l-4 border-rose-500 text-rose-800 p-4 rounded-r shadow-sm"
                    >
                        <div class="flex">
                            <svg
                                class="h-5 w-5 text-rose-500 mr-2"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                />
                            </svg>
                            <p class="font-bold">
                                {{ $page.props.flash.error }}
                            </p>
                        </div>
                    </div>
                </div>

                <slot />
            </main>

            <Footer />
        </div>
    </div>
</template>
