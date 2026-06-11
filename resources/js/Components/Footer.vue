<script setup>
import { usePage } from "@inertiajs/vue3";

const page = usePage();
const appName = page.props.appName || "Sistema de Pólizas";
const currentYear = new Date().getFullYear();
</script>

<template>
    <footer
        class="bg-[#024283] border-t border-white/10 py-3 fixed bottom-0 left-0 right-0 z-50 shadow-[0_-4px_20px_-5px_rgba(0,0,0,0.3)]"
    >
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div
                class="flex flex-col md:flex-row justify-between items-center gap-4"
            >
                <!-- Branding & Logo -->
                <div class="flex items-center space-x-3">
                    <div
                        class="p-1.5 bg-white/10 rounded-lg shadow-sm shrink-0 border border-white/10"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="w-4 h-4 text-white"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                            />
                        </svg>
                    </div>
                    <div>
                        <h3
                            class="text-[10px] font-black text-white uppercase tracking-wider leading-none"
                        >
                            {{ appName }}
                        </h3>
                        <p
                            class="text-[9px] text-blue-200/70 font-medium uppercase tracking-tighter"
                        >
                            GAD PROVINCIAL DE COTOPAXI
                        </p>
                    </div>
                </div>

                <!-- Imagen Centrada -->
                <div class="hidden md:flex justify-center items-center flex-1">
                    <img
                        src="/images/logo_login.svg"
                        alt="Logo Cotopaxi"
                        class="h-8 w-auto opacity-90 drop-shadow-sm"
                    />
                </div>

                <!-- Version & Copyright -->
                <div class="text-center md:text-right flex items-center gap-4">
                    <p
                        class="hidden sm:block text-[9px] font-bold text-white/30 uppercase tracking-[0.2em]"
                    >
                        Versión {{ page.props.app_version || "1.0.1" }}
                    </p>
                    <p class="text-[11px] text-blue-100/90 font-medium">
                        &copy; {{ currentYear }}
                        <span class="text-white font-bold">GAD Cotopaxi</span>.
                    </p>
                </div>
            </div>
        </div>
    </footer>
</template>
