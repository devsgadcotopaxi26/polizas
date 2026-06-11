<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { ref, watch, computed } from "vue";

// Simple native debounce
function debounce(fn, delay) {
    let timeoutId;
    return (...args) => {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => fn(...args), delay);
    };
}

const props = defineProps({
    polizas: Object,
    filters: Object,
    esGestorAmbiental: Boolean,
    esAsesor: Boolean,
});

const search = ref(props.filters.search);
const estado = ref(props.filters.estado || "");
const categoria = ref(props.filters.categoria || "");
const subtipo = ref(props.filters.subtipo || "");
const bandeja_tesorero = ref(
    props.filters.bandeja_tesorero === "true" ||
        props.filters.bandeja_tesorero === true,
);
const bandeja_asesor = ref(
    props.filters.bandeja_asesor === "true" ||
        props.filters.bandeja_asesor === true,
);
const bandeja_gestor_envio = ref(
    props.filters.bandeja_gestor_envio === "true" ||
        props.filters.bandeja_gestor_envio === true,
);
const sort_by  = ref(props.filters.sort_by  || "created_at");
const sort_dir = ref(props.filters.sort_dir || "desc");

const toggleSort = (column) => {
    if (sort_by.value === column) {
        sort_dir.value = sort_dir.value === "asc" ? "desc" : "asc";
    } else {
        sort_by.value  = column;
        sort_dir.value = "asc";
    }
};

const subtiposMap = {
    ambiental: [
        {
            value: "fiel_cumplimiento_ambiental",
            label: "Fiel Cumplimiento Ambiental",
        },
    ],
    obras: [
        { value: "fiel_cumplimiento", label: "Fiel Cumplimiento" },
        { value: "buen_uso", label: "Buen Uso" },
    ],
    proveedores: [
        { value: "fiel_cumplimiento", label: "Fiel Cumplimiento" },
        { value: "buen_uso", label: "Buen Uso" },
    ],
};

const subtipoOptions = computed(() => {
    if (categoria.value && subtiposMap[categoria.value]) {
        return subtiposMap[categoria.value];
    }
    return [
        {
            value: "fiel_cumplimiento_ambiental",
            label: "Fiel Cumplimiento Ambiental",
        },
        { value: "fiel_cumplimiento", label: "Fiel Cumplimiento" },
        { value: "buen_uso", label: "Buen Uso" },
    ];
});

watch(categoria, (val) => {
    if (val && subtiposMap[val]) {
        const opciones = subtiposMap[val];
        if (opciones.length === 1) {
            subtipo.value = opciones[0].value;
        } else if (!opciones.find((o) => o.value === subtipo.value)) {
            subtipo.value = "";
        }
    } else {
        subtipo.value = "";
    }
});

watch(
    [
        search,
        estado,
        categoria,
        subtipo,
        bandeja_tesorero,
        bandeja_asesor,
        bandeja_gestor_envio,
        sort_by,
        sort_dir,
    ],
    debounce(
        ([
            valSearch,
            valEstado,
            valCategoria,
            valSubtipo,
            valBandejaT,
            valBandejaA,
            valBandejaGE,
            valSortBy,
            valSortDir,
        ]) => {
            router.get(
                route("polizas.index"),
                {
                    search: valSearch,
                    estado: valEstado,
                    categoria: valCategoria,
                    subtipo: valSubtipo,
                    bandeja_tesorero: valBandejaT ? "true" : null,
                    bandeja_asesor: valBandejaA ? "true" : null,
                    bandeja_gestor_envio: valBandejaGE ? "true" : null,
                    sort_by: valSortBy,
                    sort_dir: valSortDir,
                },
                {
                    preserveState: true,
                    replace: true,
                },
            );
        },
        300,
    ),
);

const getStatusColor = (status) => {
    switch (status) {
        case "vigente":
            return "bg-emerald-100 text-emerald-800 border-emerald-200";
        case "acta_provisional":
            return "bg-blue-100 text-blue-800 border-blue-200";
        case "acta_definitiva":
            return "bg-amber-100 text-amber-800 border-amber-200";
        case "liquidada":
            return "bg-rose-100 text-rose-800 border-rose-200";
        case "renovada":
            return "bg-purple-100 text-purple-800 border-purple-200";
        case "vencida":
            return "bg-red-100 text-red-800 border-red-200";
        default:
            return "bg-gray-100 text-gray-800 border-gray-200";
    }
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString("es-EC", {
        year: "numeric",
        month: "short",
        day: "numeric",
    });
};
</script>

<template>
    <Head :title="esGestorAmbiental ? 'Pólizas Ambientales' : 'Listado de Pólizas'" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <span v-if="esGestorAmbiental" class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-green-100 text-green-800 border border-green-200">🌿 Módulo Ambiental</span>
                    <h2 class="font-semibold text-xl text-slate-800 leading-tight">
                        {{ esGestorAmbiental ? 'Pólizas Ambientales' : 'Gestión de Pólizas' }}
                    </h2>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <a
                        :href="route('polizas.export_excel', { search, estado, categoria, subtipo, bandeja_tesorero: bandeja_tesorero ? 'true' : null, bandeja_asesor: bandeja_asesor ? 'true' : null, bandeja_gestor_envio: bandeja_gestor_envio ? 'true' : null })"
                        class="inline-flex items-center px-4 py-2 bg-emerald-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-emerald-700 active:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-sm"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Excel
                    </a>
                    
                    <a
                        :href="route('polizas.export_pdf', { search, estado, categoria, subtipo, bandeja_tesorero: bandeja_tesorero ? 'true' : null, bandeja_asesor: bandeja_asesor ? 'true' : null, bandeja_gestor_envio: bandeja_gestor_envio ? 'true' : null })"
                        target="_blank"
                        class="inline-flex items-center px-4 py-2 bg-rose-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-rose-700 active:bg-rose-800 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-sm"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                        PDF
                    </a>

                    <!-- Nueva Póliza -->
                    <Link
                        :href="route('polizas.create')"
                        class="inline-flex items-center px-4 py-2 bg-[#024283] border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#003a7d] active:bg-[#001f45] focus:outline-none focus:ring-2 focus:ring-[#024283] focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150 shadow-sm ml-2"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Nueva Póliza
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12 bg-slate-50 min-h-screen">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Bandeja Tesorero Toggle -->
                <div class="mb-4 flex flex-wrap gap-4">
                    <button
                        v-if="
                            $page.props.auth.user.roles &&
                            $page.props.auth.user.roles.includes('Tesorero')
                        "
                        @click="bandeja_tesorero = !bandeja_tesorero"
                        :class="[
                            'inline-flex items-center px-4 py-2 border rounded-xl font-bold text-xs uppercase tracking-widest transition shadow-sm',
                            bandeja_tesorero
                                ? 'bg-[#024283] border-transparent text-white hover:bg-[#003a7d]'
                                : 'bg-white border-slate-200 text-slate-700 hover:bg-slate-50',
                        ]"
                    >
                        <span v-if="bandeja_tesorero" class="mr-2">🔙</span>
                        <span v-else class="mr-2">🛡️</span>
                        {{
                            bandeja_tesorero
                                ? "Ver Todas las Pólizas"
                                : "Bandeja: Pólizas Pendientes de Mi Firma"
                        }}
                    </button>

                    <!-- Bandeja Asesor Prefectura Toggle -->
                    <button
                        v-if="
                            $page.props.auth.user.roles &&
                            $page.props.auth.user.roles.includes(
                                'Asesor Prefectura',
                            )
                        "
                        @click="bandeja_asesor = !bandeja_asesor"
                        :class="[
                            'inline-flex items-center px-4 py-2 border rounded-xl font-bold text-xs uppercase tracking-widest transition shadow-sm',
                            bandeja_asesor
                                ? 'bg-emerald-700 border-transparent text-white hover:bg-emerald-800'
                                : 'bg-white border-slate-200 text-slate-700 hover:bg-slate-50',
                        ]"
                    >
                        <span v-if="bandeja_asesor" class="mr-2">🔙</span>
                        <span v-else class="mr-2">📝</span>
                        {{
                            bandeja_asesor
                                ? "Ver Todas las Pólizas"
                                : "Bandeja: Renovaciones Pendientes"
                        }}
                    </button>

                    <!-- Bandeja Gestor Envío de Oficios -->
                    <button
                        v-if="
                            $page.props.auth.user.roles &&
                            $page.props.auth.user.roles.includes(
                                'Gestor de Tesorería',
                            )
                        "
                        @click="bandeja_gestor_envio = !bandeja_gestor_envio"
                        :class="[
                            'inline-flex items-center px-4 py-2 border rounded-xl font-bold text-xs uppercase tracking-widest transition shadow-sm',
                            bandeja_gestor_envio
                                ? 'bg-blue-700 border-transparent text-white hover:bg-blue-800'
                                : 'bg-white border-slate-200 text-slate-700 hover:bg-slate-50',
                        ]"
                    >
                        <span v-if="bandeja_gestor_envio" class="mr-2">🔙</span>
                        <span v-else class="mr-2">📧</span>
                        {{
                            bandeja_gestor_envio
                                ? "Ver Todas las Pólizas"
                                : "Oficios Listos para Enviar"
                        }}
                    </button>
                </div>

                <!-- Filtros -->
                <div
                    class="mb-6 flex flex-col md:flex-row gap-4 items-center justify-between"
                >
                    <div class="relative w-full md:w-96">
                        <span
                            class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
                        >
                            <svg
                                class="h-5 w-5 text-gray-400"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                                />
                            </svg>
                        </span>
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Buscar por número o contratista..."
                            class="block w-full pl-10 pr-3 py-2 border border-slate-200 rounded-xl leading-5 bg-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#024283] focus:border-[#024283] sm:text-sm transition duration-150 ease-in-out shadow-sm"
                        />
                    </div>

                    <!-- Filtros: para Gestor Ambiental solo se muestra el filtro de estado -->
                    <div
                        class="flex flex-wrap items-center gap-3 w-full md:w-auto"
                    >
                        <!-- Filtro Categoria: oculto para Gestor Ambiental (siempre filtra por ambiental en el backend) -->
                        <select
                            v-if="!esGestorAmbiental"
                            v-model="categoria"
                            class="block w-full md:w-44 pl-3 pr-10 py-2 text-base border-slate-200 focus:outline-none focus:ring-[#024283] focus:border-[#024283] sm:text-sm rounded-xl transition duration-150 ease-in-out shadow-sm bg-white"
                        >
                            <option value="">Todos los tipos</option>
                            <option value="ambiental">Ambiental</option>
                            <option value="obras">Obras</option>
                            <option value="proveedores">Proveedores</option>
                        </select>

                        <!-- Badge fijo de categoria para Gestor Ambiental -->
                        <span v-if="esGestorAmbiental" class="inline-flex items-center px-3 py-2 rounded-xl text-sm font-semibold bg-green-50 text-green-800 border border-green-200">
                            🌿 Ambiental
                        </span>

                        <!-- Filtro Subtipo: oculto para Gestor Ambiental (solo tienen fiel_cumplimiento_ambiental) -->
                        <select
                            v-if="!esGestorAmbiental"
                            v-model="subtipo"
                            class="block w-full md:w-52 pl-3 pr-10 py-2 text-base border-slate-200 focus:outline-none focus:ring-[#024283] focus:border-[#024283] sm:text-sm rounded-xl transition duration-150 ease-in-out shadow-sm bg-white"
                        >
                            <option value="">Todos los subtipos</option>
                            <option
                                v-for="op in subtipoOptions"
                                :key="op.value"
                                :value="op.value"
                            >
                                {{ op.label }}
                            </option>
                        </select>

                        <!-- Filtro Estado: siempre visible, pero restringido para Gestor Ambiental -->
                        <select
                            v-model="estado"
                            class="block w-full md:w-48 pl-3 pr-10 py-2 text-base border-slate-200 focus:outline-none focus:ring-[#024283] focus:border-[#024283] sm:text-sm rounded-xl transition duration-150 ease-in-out shadow-sm bg-white"
                        >
                            <option value="">Todos los estados</option>
                            <option value="vigente">Vigente</option>
                            <option value="vencida">Vencida</option>
                            <!-- Las siguientes opciones no aplican a pólizas ambientales -->
                            <option v-if="!esGestorAmbiental" value="acta_provisional">
                                Acta Provisional
                            </option>
                            <option v-if="!esGestorAmbiental" value="acta_definitiva">
                                Acta Definitiva
                            </option>
                            <option v-if="!esGestorAmbiental" value="liquidada">Liquidada</option>
                            <option v-if="!esGestorAmbiental" value="original">Original</option>
                            <option v-if="!esGestorAmbiental" value="renovada">Renovada</option>
                        </select>
                    </div>
                </div>

                <!-- Tabla -->
                <div
                    class="bg-white overflow-hidden shadow-xl sm:rounded-2xl border border-slate-100"
                >
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th
                                        scope="col"
                                        class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider cursor-pointer select-none hover:bg-slate-100 transition"
                                        @click="toggleSort('codigo')"
                                    >
                                        <span class="flex items-center gap-1">
                                            Número / Tipo
                                            <span class="text-slate-400">
                                                <template v-if="sort_by === 'codigo'">{{ sort_dir === 'asc' ? '↑' : '↓' }}</template>
                                                <template v-else>↕</template>
                                            </span>
                                        </span>
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider"
                                    >
                                        {{ esGestorAmbiental ? 'Operador Ambiental' : 'Contratista' }}
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider cursor-pointer select-none hover:bg-slate-100 transition"
                                        @click="toggleSort('fecha_vencimiento')"
                                    >
                                        <span class="flex items-center gap-1">
                                            Vencimiento
                                            <span class="text-slate-400">
                                                <template v-if="sort_by === 'fecha_vencimiento'">{{ sort_dir === 'asc' ? '↑' : '↓' }}</template>
                                                <template v-else>↕</template>
                                            </span>
                                        </span>
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider cursor-pointer select-none hover:bg-slate-100 transition"
                                        @click="toggleSort('valor_asegurado')"
                                    >
                                        <span class="flex items-center gap-1">
                                            Valor
                                            <span class="text-slate-400">
                                                <template v-if="sort_by === 'valor_asegurado'">{{ sort_dir === 'asc' ? '↑' : '↓' }}</template>
                                                <template v-else>↕</template>
                                            </span>
                                        </span>
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider cursor-pointer select-none hover:bg-slate-100 transition"
                                        @click="toggleSort('estado')"
                                    >
                                        <span class="flex items-center gap-1">
                                            Estado
                                            <span class="text-slate-400">
                                                <template v-if="sort_by === 'estado'">{{ sort_dir === 'asc' ? '↑' : '↓' }}</template>
                                                <template v-else>↕</template>
                                            </span>
                                        </span>
                                    </th>
                                    <th
                                        v-if="esAsesor"
                                        scope="col"
                                        class="px-6 py-4 text-center text-xs font-bold text-slate-500 uppercase tracking-wider"
                                    >
                                        Renovación Firmada
                                    </th>
                                    <th scope="col" class="relative px-6 py-4">
                                        <span class="sr-only">Acciones</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-slate-100">
                                <tr
                                    v-for="poliza in polizas.data"
                                    :key="poliza.id"
                                    class="hover:bg-slate-50/50 transition duration-150"
                                >
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex flex-col">
                                            <div
                                                class="flex items-center gap-2"
                                            >
                                                <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded-md bg-slate-100 text-xs font-bold text-slate-500"
                                                >
                                                    #{{ poliza.codigo }}
                                                </span>
                                                <span
                                                    class="text-sm font-bold text-[#024283]"
                                                >
                                                    {{ poliza.numero_poliza }}
                                                </span>
                                            </div>
                                            <div
                                                class="text-xs text-slate-500 mt-0.5"
                                            >
                                                {{ poliza.categoria_poliza }} —
                                                {{ poliza.subtipo_poliza }}
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div
                                                class="flex-shrink-0 h-8 w-8 rounded-full bg-slate-100 flex items-center justify-center text-[#024283] font-bold text-xs uppercase"
                                            >
                                                <template v-if="esGestorAmbiental">
                                                    {{
                                                        poliza.operador_ambiental?.nombre?.charAt(0) || "—"
                                                    }}
                                                </template>
                                                <template v-else>
                                                    {{
                                                        poliza.contrato?.contratista?.nombre_cont?.charAt(0) || "—"
                                                    }}
                                                </template>
                                            </div>
                                            <div class="ml-3">
                                                <div
                                                    class="text-sm font-medium text-slate-900"
                                                >
                                                    <template v-if="esGestorAmbiental">
                                                        {{
                                                            poliza.operador_ambiental?.nombre || "Sin operador"
                                                        }}
                                                    </template>
                                                    <template v-else>
                                                        {{
                                                            poliza.contrato?.contratista?.nombre_cont || "Sin contrato"
                                                        }}
                                                    </template>
                                                </div>
                                                <div
                                                    v-if="!esGestorAmbiental && poliza.contrato"
                                                    class="text-xs text-slate-500"
                                                >
                                                    {{
                                                        poliza.contrato.numero_contrato
                                                    }}
                                                </div>
                                                <div
                                                    v-if="esGestorAmbiental && poliza.operador_ambiental"
                                                    class="text-xs text-slate-500"
                                                >
                                                    {{
                                                        poliza.operador_ambiental.empresa
                                                    }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div
                                            class="text-sm text-slate-600 font-medium"
                                        >
                                            {{
                                                formatDate(
                                                    poliza.fecha_vencimiento,
                                                )
                                            }}
                                        </div>
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm text-slate-600 font-bold"
                                    >
                                        ${{
                                            parseFloat(
                                                poliza.valor_asegurado,
                                            ).toLocaleString()
                                        }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            :class="[
                                                'px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full border',
                                                getStatusColor(
                                                    poliza.estado_actual,
                                                ),
                                            ]"
                                        >
                                            {{
                                                poliza.estado_actual
                                                    .replace("_", " ")
                                                    .replace(/\b\w/g, (c) =>
                                                        c.toUpperCase(),
                                                    )
                                            }}
                                        </span>
                                    </td>
                                    <!-- Columna Renovación Firmada: solo visible para Asesor Prefectura -->
                                    <td
                                        v-if="esAsesor"
                                        class="px-6 py-4 whitespace-nowrap text-center"
                                    >
                                        <span
                                            v-if="poliza.renovacion_de"
                                            :class="[
                                                'px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full border',
                                                poliza.renovacion_de.estado_firma_asesor
                                                    ? 'bg-emerald-50 text-emerald-700 border-emerald-200'
                                                    : 'bg-amber-50 text-amber-700 border-amber-200',
                                            ]"
                                        >
                                            {{ poliza.renovacion_de.estado_firma_asesor ? 'Sí' : 'No' }}
                                        </span>
                                        <span v-else class="text-slate-300 text-xs">—</span>
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium"
                                    >
                                        <div class="flex justify-end gap-2">
                                            <Link
                                                :href="
                                                    route(
                                                        'polizas.show',
                                                        poliza.id,
                                                    )
                                                "
                                                class="text-slate-400 hover:text-[#024283] transition p-1"
                                            >
                                                <svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    class="h-5 w-5"
                                                    fill="none"
                                                    viewBox="0 0 24 24"
                                                    stroke="currentColor"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                                    />
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                                                    />
                                                </svg>
                                            </Link>
                                            <Link
                                                v-if="!esAsesor"
                                                :href="
                                                    route(
                                                        'polizas.edit',
                                                        poliza.id,
                                                    )
                                                "
                                                class="text-slate-400 hover:text-amber-600 transition p-1"
                                            >
                                                <svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    class="h-5 w-5"
                                                    fill="none"
                                                    viewBox="0 0 24 24"
                                                    stroke="currentColor"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                                                    />
                                                </svg>
                                            </Link>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="polizas.data.length === 0">
                                    <td
                                        colspan="6"
                                        class="px-6 py-10 text-center text-slate-500"
                                    >
                                        <div class="flex flex-col items-center">
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                class="h-12 w-12 text-slate-200 mb-2"
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
                                            <p>
                                                No se encontraron pólizas
                                                registradas.
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Paginación -->
                <div class="mt-6">
                    <div class="flex flex-wrap -mb-1">
                        <template
                            v-for="(link, key) in polizas.links"
                            :key="key"
                        >
                            <div
                                v-if="link.url === null"
                                class="mr-1 mb-1 px-4 py-3 text-sm leading-4 text-gray-400 border border-slate-200 rounded-lg"
                            >
                                <svg
                                    v-if="
                                        link.label.includes('previous') ||
                                        link.label.includes('anterior')
                                    "
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-4 w-4"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M15 19l-7-7 7-7"
                                    />
                                </svg>
                                <svg
                                    v-else-if="
                                        link.label.includes('next') ||
                                        link.label.includes('siguiente')
                                    "
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-4 w-4"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M9 5l7 7-7 7"
                                    />
                                </svg>
                                <span v-else v-html="link.label"></span>
                            </div>
                            <Link
                                v-else
                                :href="link.url"
                                class="mr-1 mb-1 px-4 py-3 text-sm font-medium leading-4 border border-slate-200 rounded-lg hover:bg-white focus:border-[#024283] focus:text-[#024283] transition duration-150"
                                :class="{
                                    'bg-[#024283] text-white hover:bg-[#003a7d]':
                                        link.active,
                                }"
                            >
                                <svg
                                    v-if="
                                        link.label.includes('previous') ||
                                        link.label.includes('anterior')
                                    "
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-4 w-4"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M15 19l-7-7 7-7"
                                    />
                                </svg>
                                <svg
                                    v-else-if="
                                        link.label.includes('next') ||
                                        link.label.includes('siguiente')
                                    "
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-4 w-4"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M9 5l7 7-7 7"
                                    />
                                </svg>
                                <span v-else v-html="link.label"></span>
                            </Link>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
