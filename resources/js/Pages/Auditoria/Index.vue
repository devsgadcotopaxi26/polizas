<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, router } from "@inertiajs/vue3";
import { ref, watch } from "vue";

function debounce(fn, delay) {
    let timer;
    return (...args) => { clearTimeout(timer); timer = setTimeout(() => fn(...args), delay); };
}

const props = defineProps({
    actividades: Object,
    modelos: Array,
    usuarios: Array,
    filters: Object,
});

const search     = ref(props.filters.search || "");
const causer_id  = ref(props.filters.causer_id || "");
const log_name   = ref(props.filters.log_name || "");
const event      = ref(props.filters.event || "");
const fecha_desde = ref(props.filters.fecha_desde || "");
const fecha_hasta = ref(props.filters.fecha_hasta || "");

watch(
    [search, causer_id, log_name, event, fecha_desde, fecha_hasta],
    debounce(([valSearch, valCauser, valLog, valEvent, valDesde, valHasta]) => {
        router.get(
            route("auditoria.index"),
            {
                search:      valSearch || null,
                causer_id:   valCauser || null,
                log_name:    valLog || null,
                event:       valEvent || null,
                fecha_desde: valDesde || null,
                fecha_hasta: valHasta || null,
            },
            { preserveState: true, replace: true },
        );
    }, 300),
);

const eventLabel = (event) => {
    const labels = { created: "Creó", updated: "Editó", deleted: "Eliminó" };
    return labels[event] || event;
};

const eventColor = (event) => {
    const colors = {
        created: "bg-emerald-100 text-emerald-700 border-emerald-200",
        updated: "bg-blue-100 text-blue-700 border-blue-200",
        deleted: "bg-red-100 text-red-700 border-red-200",
    };
    return colors[event] || "bg-slate-100 text-slate-600 border-slate-200";
};

const TZ = "America/Guayaquil";

const formatDateTime = (dt) => {
    if (!dt) return "—";
    return new Date(dt).toLocaleString("es-EC", {
        year: "numeric", month: "short", day: "numeric",
        hour: "2-digit", minute: "2-digit",
        timeZone: TZ,
    });
};

const ISO_RE = /^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/;

const formatValue = (val) => {
    if (val === null || val === undefined) return "";
    const s = String(val);
    if (ISO_RE.test(s)) {
        return new Date(s).toLocaleString("es-EC", {
            year: "numeric", month: "2-digit", day: "2-digit",
            hour: "2-digit", minute: "2-digit", second: "2-digit",
            timeZone: TZ,
        });
    }
    return s;
};

const formatProperties = (properties) => {
    if (!properties) return null;
    const attrs = properties.attributes || {};
    const old   = properties.old || {};
    const lines = [];
    for (const key of Object.keys(attrs)) {
        const prev = old[key];
        const curr = attrs[key];
        if (prev !== undefined && prev !== curr) {
            lines.push({ key, prev: formatValue(prev), curr: formatValue(curr) });
        }
    }
    return lines.length ? lines : null;
};

const limpiarFiltros = () => {
    search.value = "";
    causer_id.value = "";
    log_name.value = "";
    event.value = "";
    fecha_desde.value = "";
    fecha_hasta.value = "";
};
</script>

<template>
    <Head title="Auditoría del Sistema" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-bold leading-tight text-brand-blue uppercase">
                Auditoría Global del Sistema
            </h2>
        </template>

        <div class="py-10">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">

                <!-- Filtros -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Buscar..."
                            class="block w-full rounded-xl border-slate-200 text-sm shadow-sm focus:ring-brand-blue focus:border-brand-blue"
                        />
                        <select v-model="causer_id" class="block w-full rounded-xl border-slate-200 text-sm shadow-sm focus:ring-brand-blue focus:border-brand-blue">
                            <option value="">Todos los usuarios</option>
                            <option v-for="u in usuarios" :key="u.id" :value="u.id">{{ u.name }}</option>
                        </select>
                        <select v-model="log_name" class="block w-full rounded-xl border-slate-200 text-sm shadow-sm focus:ring-brand-blue focus:border-brand-blue">
                            <option value="">Todos los módulos</option>
                            <option v-for="m in modelos" :key="m" :value="m">{{ m }}</option>
                        </select>
                        <select v-model="event" class="block w-full rounded-xl border-slate-200 text-sm shadow-sm focus:ring-brand-blue focus:border-brand-blue">
                            <option value="">Todas las acciones</option>
                            <option value="created">Creó</option>
                            <option value="updated">Editó</option>
                            <option value="deleted">Eliminó</option>
                        </select>
                        <input v-model="fecha_desde" type="date" class="block w-full rounded-xl border-slate-200 text-sm shadow-sm focus:ring-brand-blue focus:border-brand-blue" />
                        <input v-model="fecha_hasta" type="date" class="block w-full rounded-xl border-slate-200 text-sm shadow-sm focus:ring-brand-blue focus:border-brand-blue" />
                    </div>
                    <div class="flex justify-between items-center mt-4">
                        <p class="text-xs text-slate-400">
                            {{ actividades.total }} registro(s) encontrado(s)
                        </p>
                        <button @click="limpiarFiltros" class="text-xs text-slate-500 hover:text-red-500 underline transition">
                            Limpiar filtros
                        </button>
                    </div>
                </div>

                <!-- Tabla -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-100">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-5 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Fecha / Hora</th>
                                    <th class="px-5 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Usuario</th>
                                    <th class="px-5 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Acción</th>
                                    <th class="px-5 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Módulo</th>
                                    <th class="px-5 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Registro ID</th>
                                    <th class="px-5 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Cambios</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                <tr v-if="actividades.data.length === 0">
                                    <td colspan="6" class="px-5 py-10 text-center text-slate-400 text-sm">
                                        No hay registros de auditoría para los filtros seleccionados.
                                    </td>
                                </tr>
                                <tr
                                    v-for="act in actividades.data"
                                    :key="act.id"
                                    class="hover:bg-slate-50 transition"
                                >
                                    <td class="px-5 py-3 text-xs text-slate-500 whitespace-nowrap">
                                        {{ formatDateTime(act.created_at) }}
                                    </td>
                                    <td class="px-5 py-3 text-sm font-medium text-slate-700 whitespace-nowrap">
                                        {{ act.causer?.name || "Sistema" }}
                                    </td>
                                    <td class="px-5 py-3 whitespace-nowrap">
                                        <span :class="['px-2 py-0.5 text-xs font-semibold rounded-full border', eventColor(act.event)]">
                                            {{ eventLabel(act.event) }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3 text-sm text-slate-600 whitespace-nowrap font-medium">
                                        {{ act.log_name }}
                                    </td>
                                    <td class="px-5 py-3 text-xs text-slate-400 whitespace-nowrap">
                                        #{{ act.subject_id || "—" }}
                                    </td>
                                    <td class="px-5 py-3 text-xs text-slate-600 max-w-xs">
                                        <template v-if="formatProperties(act.properties)">
                                            <div
                                                v-for="line in formatProperties(act.properties)"
                                                :key="line.key"
                                                class="mb-0.5"
                                            >
                                                <span class="font-semibold text-slate-700">{{ line.key }}:</span>
                                                <span class="text-red-500 line-through ml-1">{{ line.prev }}</span>
                                                <span class="mx-1 text-slate-300">→</span>
                                                <span class="text-emerald-600">{{ line.curr }}</span>
                                            </div>
                                        </template>
                                        <template v-else-if="act.event === 'created'">
                                            <span class="text-emerald-600">Registro nuevo</span>
                                        </template>
                                        <template v-else-if="act.event === 'deleted'">
                                            <span class="text-red-500">Registro eliminado</span>
                                        </template>
                                        <span v-else class="text-slate-300">—</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación -->
                    <div v-if="actividades.last_page > 1" class="px-5 py-4 border-t border-slate-100 flex items-center justify-between">
                        <p class="text-xs text-slate-400">
                            Página {{ actividades.current_page }} de {{ actividades.last_page }}
                        </p>
                        <div class="flex gap-2">
                            <a
                                v-if="actividades.prev_page_url"
                                :href="actividades.prev_page_url"
                                class="px-3 py-1.5 text-xs rounded-lg border border-slate-200 hover:bg-slate-50 transition text-slate-600"
                            >← Anterior</a>
                            <a
                                v-if="actividades.next_page_url"
                                :href="actividades.next_page_url"
                                class="px-3 py-1.5 text-xs rounded-lg border border-slate-200 hover:bg-slate-50 transition text-slate-600"
                            >Siguiente →</a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
