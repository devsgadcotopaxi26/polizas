<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link } from "@inertiajs/vue3";

const props = defineProps({
    stats: Object,
    proximas_vencer: Array,
    polizas_vencidas: Array,
});

const formatDate = (date) => {
    return new Date(date).toLocaleDateString("es-EC", {
        year: "numeric",
        month: "short",
        day: "numeric",
    });
};
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-slate-800 leading-tight">
                Panel de Control de Pólizas
            </h2>
        </template>

        <div class="py-12 bg-slate-50 min-h-screen">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Stats Cards -->
                <div
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8"
                >
                    <div
                        class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex items-center"
                    >
                        <div
                            class="p-3 rounded-xl bg-indigo-50 text-indigo-600 mr-4"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-8 w-8"
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
                            <p
                                class="text-sm font-medium text-slate-500 uppercase tracking-wider"
                            >
                                Total Pólizas
                            </p>
                            <p class="text-2xl font-bold text-slate-800">
                                {{ stats.total }}
                            </p>
                        </div>
                    </div>

                    <div
                        class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex items-center"
                    >
                        <div
                            class="p-3 rounded-xl bg-emerald-50 text-emerald-600 mr-4"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-8 w-8"
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
                        </div>
                        <div>
                            <p
                                class="text-sm font-medium text-slate-500 uppercase tracking-wider"
                            >
                                Activas
                            </p>
                            <p class="text-2xl font-bold text-slate-800">
                                {{ stats.activas }}
                            </p>
                        </div>
                    </div>

                    <div
                        class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex items-center"
                    >
                        <div
                            class="p-3 rounded-xl bg-rose-50 text-rose-600 mr-4"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-8 w-8"
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
                        </div>
                        <div>
                            <p
                                class="text-sm font-medium text-slate-500 uppercase tracking-wider"
                            >
                                Por Vencer (8d)
                            </p>
                            <p class="text-2xl font-bold text-slate-800">
                                {{ stats.proximas_vencer_count }}
                            </p>
                        </div>
                    </div>

                    <div
                        class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex items-center"
                    >
                        <div
                            class="p-3 rounded-xl bg-amber-50 text-amber-600 mr-4"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-8 w-8"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                />
                            </svg>
                        </div>
                        <div>
                            <p
                                class="text-sm font-medium text-slate-500 uppercase tracking-wider"
                            >
                                Valor Asegurado
                            </p>
                            <p class="text-2xl font-bold text-slate-800">
                                ${{
                                    parseFloat(
                                        stats.valor_total,
                                    ).toLocaleString()
                                }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Proximas a Vencer Table -->
                <div
                    class="bg-white shadow-xl sm:rounded-2xl border border-slate-100 overflow-hidden"
                >
                    <div
                        class="px-6 py-5 border-b border-slate-100 flex justify-between items-center"
                    >
                        <h3
                            class="text-lg font-bold text-slate-800 flex items-center"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-5 w-5 mr-2 text-rose-500"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                                />
                            </svg>
                            Pólizas Próximas a Vencer (Próximos 8 días)
                        </h3>
                        <Link
                            :href="route('polizas.index')"
                            class="text-sm text-indigo-600 hover:text-indigo-800 font-semibold transition"
                        >
                            Ver todas
                        </Link>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-100">
                            <thead class="bg-slate-50/50">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider"
                                    >
                                        Póliza
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider"
                                    >
                                        Aseguradora
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider"
                                    >
                                        Vencimiento
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider"
                                    >
                                        Valor
                                    </th>
                                    <th
                                        class="px-6 py-3 text-right text-xs font-bold text-slate-500 uppercase tracking-wider"
                                    >
                                        Acciones
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-slate-100">
                                <tr
                                    v-for="poliza in proximas_vencer"
                                    :key="poliza.id"
                                    @click="
                                        $inertia.visit(
                                            route('polizas.show', poliza.id),
                                        )
                                    "
                                    class="hover:bg-slate-50/50 transition cursor-pointer group"
                                >
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div
                                            class="text-sm font-bold text-slate-800 group-hover:text-indigo-600 transition"
                                        >
                                            {{ poliza.numero_poliza }}
                                        </div>
                                        <div class="text-xs text-slate-400">
                                            {{ poliza.categoria_poliza }} —
                                            {{ poliza.subtipo_poliza }}
                                        </div>
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm text-slate-600"
                                    >
                                        {{ poliza.sucursal?.aseguradora?.nombre_empresa ?? '—' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div
                                            class="text-sm font-bold text-rose-600"
                                        >
                                            {{
                                                formatDate(
                                                    poliza.fecha_vencimiento,
                                                )
                                            }}
                                        </div>
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm font-bold text-slate-700"
                                    >
                                        ${{
                                            parseFloat(
                                                poliza.valor_asegurado,
                                            ).toLocaleString()
                                        }}
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-right text-sm"
                                    >
                                        <Link
                                            :href="
                                                route('polizas.show', poliza.id)
                                            "
                                            class="inline-flex items-center px-3 py-1 bg-indigo-50 text-indigo-700 rounded-lg font-bold hover:bg-indigo-100 transition"
                                        >
                                            Ver Detalles
                                        </Link>
                                    </td>
                                </tr>
                                <tr v-if="proximas_vencer.length === 0">
                                    <td
                                        colspan="5"
                                        class="px-6 py-12 text-center text-slate-400"
                                    >
                                        <div class="flex flex-col items-center">
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                class="h-10 w-10 mb-2 opacity-20"
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
                                            <p>
                                                No hay pólizas venciendo en los
                                                próximos 8 días.
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pólizas Vencidas -->
                <div
                    v-if="polizas_vencidas && polizas_vencidas.length > 0"
                    class="bg-white shadow-xl sm:rounded-2xl border border-slate-100 overflow-hidden mt-8"
                >
                    <div
                        class="px-6 py-5 border-b border-slate-100 flex justify-between items-center"
                    >
                        <h3
                            class="text-lg font-bold text-slate-800 flex items-center"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-5 w-5 mr-2 text-red-500"
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
                            Pólizas Vencidas
                        </h3>
                        <Link
                            :href="
                                route('polizas.index', { estado: 'vencida' })
                            "
                            class="text-sm text-indigo-600 hover:text-indigo-800 font-semibold transition"
                        >
                            Ver todas
                        </Link>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-100">
                            <thead class="bg-slate-50/50">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider"
                                    >
                                        Póliza
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider"
                                    >
                                        Aseguradora
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider"
                                    >
                                        Vencimiento
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider"
                                    >
                                        Valor
                                    </th>
                                    <th
                                        class="px-6 py-3 text-right text-xs font-bold text-slate-500 uppercase tracking-wider"
                                    >
                                        Acciones
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-slate-100">
                                <tr
                                    v-for="poliza in polizas_vencidas"
                                    :key="poliza.id"
                                    @click="
                                        $inertia.visit(
                                            route('polizas.show', poliza.id),
                                        )
                                    "
                                    class="hover:bg-slate-50/50 transition cursor-pointer group"
                                >
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div
                                            class="text-sm font-bold text-slate-800 group-hover:text-indigo-600 transition"
                                        >
                                            {{ poliza.numero_poliza }}
                                        </div>
                                        <div class="text-xs text-slate-400">
                                            {{ poliza.categoria_poliza }} —
                                            {{ poliza.subtipo_poliza }}
                                        </div>
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm text-slate-600"
                                    >
                                        {{ poliza.sucursal?.aseguradora?.nombre_empresa ?? '—' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div
                                            class="text-sm font-bold text-red-600"
                                        >
                                            {{
                                                formatDate(
                                                    poliza.fecha_vencimiento,
                                                )
                                            }}
                                        </div>
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm font-bold text-slate-700"
                                    >
                                        ${{
                                            parseFloat(
                                                poliza.valor_asegurado,
                                            ).toLocaleString()
                                        }}
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-right text-sm"
                                    >
                                        <Link
                                            :href="
                                                route('polizas.show', poliza.id)
                                            "
                                            class="inline-flex items-center px-3 py-1 bg-indigo-50 text-indigo-700 rounded-lg font-bold hover:bg-indigo-100 transition"
                                        >
                                            Ver Detalles
                                        </Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
