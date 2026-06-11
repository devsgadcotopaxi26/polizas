<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, useForm, Link } from "@inertiajs/vue3";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import SearchableSelect from "@/Components/SearchableSelect.vue";
import { computed, watch, onMounted } from "vue";

const props = defineProps({
    aseguradoras: Array,
    contratos: Array,
    operadores_ambientales: {
        type: Array,
        default: () => [],
    },
    categorias_subtipos: Object,
    categoria_labels: Object,
    subtipo_labels: Object,
    esGestorAmbiental: Boolean,
});

const form = useForm({
    codigo: "",
    numero_poliza: "",
    categoria_poliza: "",
    subtipo_poliza: "",
    valor_asegurado: "",
    fecha_inicio: "",
    fecha_vencimiento: "",
    contrato_id: "",
    operador_ambiental_id: "",
    codigo_proyecto_amb: "",
    sucursal_id: "",
    observaciones: "",
    estado: "vigente",
    fecha_acta_provisional: "",
    fecha_acta_definitiva: "",
    archivo_acta: null,
});

// Subtipos disponibles según la categoría seleccionada
const subtiposDisponibles = computed(() => {
    if (
        !form.categoria_poliza ||
        !props.categorias_subtipos[form.categoria_poliza]
    ) {
        return [];
    }
    return props.categorias_subtipos[form.categoria_poliza];
});

watch(
    () => form.categoria_poliza,
    (newVal) => {
        if (newVal === "ambiental") {
            form.subtipo_poliza = "fiel_cumplimiento_ambiental";
            form.contrato_id = "";
            form.codigo = "";
        } else {
            form.subtipo_poliza = "";
            form.codigo_proyecto_amb = "";
            form.operador_ambiental_id = "";
        }
    },
);

onMounted(() => {
    if (props.esGestorAmbiental) {
        form.categoria_poliza = "ambiental";
    }
});

const submit = () => {
    form.post(route("polizas.store"), {
        onSuccess: () => form.reset(),
    });
};

const contratosConLabel = computed(() =>
    props.contratos.map((c) => ({
        ...c,
        label: `${c.numero_contrato} — ${c.contratista?.nombre_cont || "Sin contratista"}`,
    })),
);

const sucursalesConLabel = computed(() =>
    props.aseguradoras.map((s) => ({
        ...s,
        label: `${s.aseguradora?.nombre_empresa || "Empresa Desconocida"} - ${s.ciudad?.nombre || "Sede Principal"}`,
    })),
);

const operadoresConLabel = computed(() =>
    props.operadores_ambientales.map((o) => ({
        ...o,
        label: `${o.nombre} - ${o.empresa}`,
    })),
);
</script>

<template>
    <Head title="Nueva Póliza" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link
                    :href="route('polizas.index')"
                    class="text-slate-400 hover:text-indigo-600 transition"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-6 w-6"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18"
                        />
                    </svg>
                </Link>
                <h2 class="font-semibold text-xl text-slate-800 leading-tight">
                    Registrar Nueva Póliza
                </h2>
            </div>
        </template>

        <div class="py-12 bg-slate-50 min-h-screen">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div
                    class="bg-white overflow-hidden shadow-xl sm:rounded-2xl border border-slate-100 p-8"
                >
                    <form @submit.prevent="submit" class="space-y-8">
                        <!-- Sección 1: Datos Base -->
                        <div>
                            <h3
                                class="text-lg font-bold text-slate-800 mb-4 pb-2 border-b border-slate-100 flex items-center"
                            >
                                <span
                                    class="w-8 h-8 rounded-lg bg-brand-blue/10 text-brand-blue flex items-center justify-center mr-3 text-sm font-black"
                                    >01</span
                                >
                                Información General
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <!-- Campo Código: oculto para Pólizas Ambientales -->
                                <div
                                    v-if="form.categoria_poliza !== 'ambiental'"
                                >
                                    <InputLabel for="codigo" value="Código" />
                                    <TextInput
                                        id="codigo"
                                        type="number"
                                        class="mt-1 block w-full"
                                        v-model="form.codigo"
                                        placeholder="Automático (Si está vacío)"
                                        autofocus
                                    />
                                    <InputError
                                        class="mt-2"
                                        :message="form.errors.codigo"
                                    />
                                </div>
                                <div>
                                    <InputLabel
                                        for="numero_poliza"
                                        value="Número de Póliza"
                                    />
                                    <TextInput
                                        id="numero_poliza"
                                        type="text"
                                        class="mt-1 block w-full"
                                        v-model="form.numero_poliza"
                                        required
                                    />
                                    <InputError
                                        class="mt-2"
                                        :message="form.errors.numero_poliza"
                                    />
                                </div>

                                <div>
                                    <InputLabel
                                        for="categoria_poliza"
                                        value="Categoría de Póliza"
                                    />
                                    <select
                                        v-if="!esGestorAmbiental"
                                        id="categoria_poliza"
                                        v-model="form.categoria_poliza"
                                        class="mt-1 block w-full border-slate-300 focus:border-brand-blue focus:ring-brand-blue rounded-md shadow-sm"
                                        required
                                    >
                                        <option value="">
                                            Seleccione una categoría
                                        </option>
                                        <option
                                            v-for="(
                                                subtipos, key
                                            ) in categorias_subtipos"
                                            :key="key"
                                            :value="key"
                                        >
                                            {{ categoria_labels[key] }}
                                        </option>
                                    </select>

                                    <!-- Badge o select bloqueado para gestor ambiental -->
                                    <div v-else class="mt-1">
                                        <div
                                            class="px-3 py-2 border border-slate-200 bg-slate-50 text-slate-500 rounded-md shadow-sm"
                                        >
                                            Pólizas Ambientales
                                        </div>
                                    </div>
                                    <InputError
                                        class="mt-2"
                                        :message="form.errors.categoria_poliza"
                                    />
                                </div>

                                <div>
                                    <InputLabel
                                        for="subtipo_poliza"
                                        value="Subtipo de Póliza"
                                    />
                                    <select
                                        v-if="!esGestorAmbiental"
                                        id="subtipo_poliza"
                                        v-model="form.subtipo_poliza"
                                        class="mt-1 block w-full border-slate-300 focus:border-brand-blue focus:ring-brand-blue rounded-md shadow-sm"
                                        required
                                        :disabled="!form.categoria_poliza"
                                    >
                                        <option
                                            value=""
                                            v-if="
                                                form.categoria_poliza !==
                                                'ambiental'
                                            "
                                        >
                                            {{
                                                form.categoria_poliza
                                                    ? "Seleccione un subtipo"
                                                    : "Primero seleccione una categoría"
                                            }}
                                        </option>
                                        <option
                                            v-for="subtipo in subtiposDisponibles"
                                            :key="subtipo"
                                            :value="subtipo"
                                        >
                                            {{ subtipo_labels[subtipo] }}
                                        </option>
                                    </select>

                                    <!-- Badge o select bloqueado para gestor ambiental -->
                                    <div v-else class="mt-1">
                                        <div
                                            class="px-3 py-2 border border-slate-200 bg-slate-50 text-slate-500 rounded-md shadow-sm"
                                        >
                                            Fiel Cumplimiento Ambiental
                                        </div>
                                    </div>
                                    <InputError
                                        class="mt-2"
                                        :message="form.errors.subtipo_poliza"
                                    />
                                </div>

                                <div>
                                    <InputLabel
                                        for="valor_asegurado"
                                        value="Valor Asegurado ($)"
                                    />
                                    <TextInput
                                        id="valor_asegurado"
                                        type="number"
                                        step="0.01"
                                        class="mt-1 block w-full"
                                        v-model="form.valor_asegurado"
                                        required
                                    />
                                    <InputError
                                        class="mt-2"
                                        :message="form.errors.valor_asegurado"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Sección 2: Fechas y Aseguradora -->
                        <div>
                            <h3
                                class="text-lg font-bold text-slate-800 mb-4 pb-2 border-b border-slate-100 flex items-center"
                            >
                                <span
                                    class="w-8 h-8 rounded-lg bg-brand-blue/10 text-brand-blue flex items-center justify-center mr-3 text-sm font-black"
                                    >02</span
                                >
                                Vigencia y Entidad
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <InputLabel
                                        for="fecha_inicio"
                                        value="Fecha de Inicio"
                                    />
                                    <TextInput
                                        id="fecha_inicio"
                                        type="date"
                                        class="mt-1 block w-full"
                                        v-model="form.fecha_inicio"
                                        required
                                    />
                                    <InputError
                                        class="mt-2"
                                        :message="form.errors.fecha_inicio"
                                    />
                                </div>

                                <div>
                                    <InputLabel
                                        for="fecha_vencimiento"
                                        value="Fecha de Vencimiento"
                                    />
                                    <TextInput
                                        id="fecha_vencimiento"
                                        type="date"
                                        class="mt-1 block w-full"
                                        v-model="form.fecha_vencimiento"
                                        required
                                    />
                                    <InputError
                                        class="mt-2"
                                        :message="form.errors.fecha_vencimiento"
                                    />
                                </div>

                                <div>
                                    <InputLabel
                                        for="sucursal_id"
                                        value="Aseguradora (Sucursal)"
                                    />
                                    <SearchableSelect
                                        v-model="form.sucursal_id"
                                        :options="sucursalesConLabel"
                                        label-key="label"
                                        value-key="id"
                                        placeholder="Seleccione una sucursal"
                                        search-placeholder="Buscar aseguradora/ciudad..."
                                    />
                                    <InputError
                                        class="mt-2"
                                        :message="form.errors.sucursal_id"
                                    />
                                </div>

                                <div v-if="form.categoria_poliza === 'ambiental'">
                                    <InputLabel
                                        for="operador_ambiental_id"
                                        value="Operador Ambiental"
                                    />
                                    <SearchableSelect
                                        v-model="form.operador_ambiental_id"
                                        :options="operadoresConLabel"
                                        label-key="label"
                                        value-key="id"
                                        placeholder="Seleccione un operador"
                                        search-placeholder="Buscar operador..."
                                    />
                                    <InputError
                                        class="mt-2"
                                        :message="form.errors.operador_ambiental_id"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Sección 3: Contrato y Actas -->
                        <div>
                            <h3
                                class="text-lg font-bold text-slate-800 mb-4 pb-2 border-b border-slate-100 flex items-center"
                            >
                                <span
                                    class="w-8 h-8 rounded-lg bg-brand-blue/10 text-brand-blue flex items-center justify-center mr-3 text-sm font-black"
                                    >03</span
                                >
                                Contrato y Estado
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Contrato Asociado: oculto para Pólizas Ambientales -->
                                <div
                                    v-if="form.categoria_poliza !== 'ambiental'"
                                >
                                    <InputLabel
                                        for="contrato_id"
                                        value="Contrato Asociado"
                                    />
                                    <SearchableSelect
                                        v-model="form.contrato_id"
                                        :options="contratosConLabel"
                                        label-key="label"
                                        value-key="id"
                                        placeholder="Sin contrato"
                                        search-placeholder="Buscar contrato..."
                                    />
                                    <InputError
                                        class="mt-2"
                                        :message="form.errors.contrato_id"
                                    />
                                </div>

                                <!-- Código Proyecto Ambiental: solo para Pólizas Ambientales -->
                                <div
                                    v-if="form.categoria_poliza === 'ambiental'"
                                >
                                    <InputLabel
                                        for="codigo_proyecto_amb"
                                        value="Código Proyecto Ambiental"
                                    />
                                    <TextInput
                                        id="codigo_proyecto_amb"
                                        type="text"
                                        class="mt-1 block w-full"
                                        v-model="form.codigo_proyecto_amb"
                                        placeholder="Ej: AMB-2024-001"
                                    />
                                    <InputError
                                        class="mt-2"
                                        :message="
                                            form.errors.codigo_proyecto_amb
                                        "
                                    />
                                </div>

                                <div>
                                    <InputLabel
                                        for="estado"
                                        value="Estado de la Póliza"
                                    />
                                    <select
                                        id="estado"
                                        v-model="form.estado"
                                        class="mt-1 block w-full border-slate-300 focus:border-brand-blue focus:ring-brand-blue rounded-md shadow-sm"
                                    >
                                        <option value="vigente">Vigente</option>
                                        <option value="acta_provisional">
                                            Acta Provisional
                                        </option>
                                        <option value="acta_definitiva">
                                            Acta Definitiva
                                        </option>
                                        <option value="liquidada">
                                            Liquidada
                                        </option>
                                    </select>
                                    <InputError
                                        class="mt-2"
                                        :message="form.errors.estado"
                                    />
                                </div>
                            </div>

                            <!-- Fecha Acta Provisional -->
                            <div
                                v-if="form.estado === 'acta_provisional'"
                                class="mt-4 pt-4 border-t border-slate-200"
                            >
                                <InputLabel
                                    for="fecha_acta_provisional"
                                    value="Fecha de Acta Provisional"
                                />
                                <TextInput
                                    id="fecha_acta_provisional"
                                    type="date"
                                    class="mt-1 block max-w-sm w-full"
                                    v-model="form.fecha_acta_provisional"
                                />
                                <InputError
                                    class="mt-2"
                                    :message="
                                        form.errors.fecha_acta_provisional
                                    "
                                />
                            </div>

                            <!-- Archivo Acta (Provisional o Definitiva) -->
                            <div
                                v-if="
                                    [
                                        'acta_provisional',
                                        'acta_definitiva',
                                    ].includes(form.estado)
                                "
                                class="mt-4 pt-4 border-t border-slate-200"
                            >
                                <InputLabel
                                    for="archivo_acta"
                                    value="Documento del Acta (PDF/DOC)"
                                />
                                <input
                                    type="file"
                                    id="archivo_acta"
                                    @input="
                                        form.archivo_acta =
                                            $event.target.files[0]
                                    "
                                    accept=".pdf,.doc,.docx"
                                    class="mt-1 block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 transition"
                                />
                                <InputError
                                    class="mt-2"
                                    :message="form.errors.archivo_acta"
                                />
                            </div>
                        </div>

                        <!-- Sección 4: Observaciones -->
                        <div>
                            <h3
                                class="text-lg font-bold text-slate-800 mb-4 pb-2 border-b border-slate-100 flex items-center"
                            >
                                <span
                                    class="w-8 h-8 rounded-lg bg-brand-blue/10 text-brand-blue flex items-center justify-center mr-3 text-sm font-black"
                                    >04</span
                                >
                                Notas Adicionales
                            </h3>
                            <div class="grid grid-cols-1 gap-6">
                                <div>
                                    <InputLabel
                                        for="observaciones"
                                        value="Observaciones"
                                    />
                                    <textarea
                                        id="observaciones"
                                        v-model="form.observaciones"
                                        rows="4"
                                        class="mt-1 block w-full border-slate-300 focus:border-brand-blue focus:ring-brand-blue rounded-md shadow-sm"
                                    ></textarea>
                                    <InputError
                                        class="mt-2"
                                        :message="form.errors.observaciones"
                                    />
                                </div>
                            </div>
                        </div>

                        <div
                            class="flex items-center justify-end gap-4 mt-8 pt-6 border-t border-slate-100"
                        >
                            <Link
                                :href="route('polizas.index')"
                                class="text-sm text-slate-600 hover:text-slate-900 transition font-medium"
                            >
                                Cancelar
                            </Link>
                            <!-- <PrimaryButton
                                class="bg-indigo-600 hover:bg-indigo-700 shadow-sm px-8"
                                :class="{ 'opacity-25': form.processing }"
                                :disabled="form.processing"
                            > -->

                            <PrimaryButton
                                class="shadow-sm px-8 !bg-[#024283] hover:!bg-[#003a7d] focus:!bg-[#003a7d] active:!bg-[#003a7d]"
                                :class="{ 'opacity-25': form.processing }"
                                :disabled="form.processing"
                            >
                                Registrar Póliza
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
