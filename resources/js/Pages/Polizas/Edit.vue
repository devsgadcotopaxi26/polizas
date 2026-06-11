<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, useForm, Link } from "@inertiajs/vue3";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import SearchableSelect from "@/Components/SearchableSelect.vue";
import { computed, watch } from "vue";

const props = defineProps({
    poliza: Object,
    renovacion_de: Object,
    aseguradoras: Array,
    contratos: Array,
    operadores_ambientales: {
        type: Array,
        default: () => [],
    },
    categorias_subtipos: Object,
    categoria_labels: Object,
    subtipo_labels: Object,
});

const form = useForm({
    numero_poliza: props.poliza.numero_poliza,
    categoria_poliza: props.poliza.categoria_poliza,
    subtipo_poliza: props.poliza.subtipo_poliza,
    valor_asegurado: props.poliza.valor_asegurado,
    anticipo: props.poliza.anticipo || "",
    fecha_inicio: props.poliza.fecha_inicio
        ? String(props.poliza.fecha_inicio).substring(0, 10)
        : "",
    fecha_vencimiento: props.poliza.fecha_vencimiento
        ? String(props.poliza.fecha_vencimiento).substring(0, 10)
        : "",
    contrato_id: props.poliza.contrato_id || "",
    operador_ambiental_id: props.poliza.operador_ambiental_id || "",
    codigo_proyecto_amb: props.poliza.codigo_proyecto_amb || "",
    sucursal_id: props.poliza.sucursal_id,
    estado: props.poliza.estado,
    observaciones: props.poliza.observaciones || "",
    fecha_acta_provisional: props.poliza.fecha_acta_provisional
        ? String(props.poliza.fecha_acta_provisional).substring(0, 10)
        : "",
    fecha_acta_definitiva: props.poliza.fecha_acta_definitiva
        ? String(props.poliza.fecha_acta_definitiva).substring(0, 10)
        : "",
    archivo_acta: null,
    archivo_renovacion: null,
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

// Establecer subtipo automáticamente o limpiar cuando cambia la categoría (solo si el usuario cambió manualmente)
let isInitialLoad = true;
watch(
    () => form.categoria_poliza,
    (newVal) => {
        if (isInitialLoad) {
            isInitialLoad = false;
            return;
        }
        if (newVal === "ambiental") {
            form.subtipo_poliza = "fiel_cumplimiento_ambiental";
            form.contrato_id = "";
        } else {
            form.subtipo_poliza = "";
            form.codigo_proyecto_amb = "";
            form.operador_ambiental_id = "";
        }
    },
);

const submit = () => {
    form.transform((data) => ({
        ...data,
        _method: "put",
    })).post(route("polizas.update", props.poliza.id));
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
    <Head title="Editar Póliza" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link
                    :href="route('polizas.show', poliza.id)"
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
                    Editar Póliza #{{ poliza.numero_poliza }}
                </h2>
            </div>
        </template>

        <div class="py-12 bg-slate-50 min-h-screen">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div
                    class="bg-white overflow-hidden shadow-xl sm:rounded-2xl border border-slate-100 p-8"
                >
                    <form @submit.prevent="submit" class="space-y-8">
                        <!-- Estado -->
                        <div
                            class="p-4 rounded-xl bg-slate-50 border border-slate-200"
                        >
                            <div class="flex items-center justify-between">
                                <div>
                                    <h4 class="font-bold text-slate-800">
                                        Estado de la Póliza
                                    </h4>
                                    <p class="text-xs text-slate-500">
                                        Actualiza el estado actual de cobertura
                                    </p>
                                </div>
                                <div class="flex flex-col gap-2 relative">
                                    <select
                                        v-model="form.estado"
                                        class="block w-48 pl-3 pr-10 py-2 text-base border-slate-300 focus:outline-none focus:ring-brand-blue focus:border-brand-blue sm:text-sm rounded-lg shadow-sm"
                                    >
                                        <option value="vigente">Vigente</option>
                                        <option value="acta_provisional">
                                            Acta Provisional
                                        </option>
                                        <option value="acta_definitiva">
                                            Acta Definitiva
                                        </option>
                                        <option value="original">
                                            Original
                                        </option>
                                        <option value="renovada">
                                            Renovada
                                        </option>
                                        <option value="liquidada">
                                            Liquidada
                                        </option>
                                    </select>

                                    <div
                                        v-if="
                                            poliza.estado_actual === 'vencida'
                                        "
                                        class="absolute -bottom-8 right-0 text-xs font-bold text-red-600 bg-red-50 border border-red-200 px-2 py-1 rounded-md shadow-sm whitespace-nowrap"
                                    >
                                        ⚠️ Automáticamente Vencida
                                    </div>
                                </div>
                            </div>

                            <!-- Fechas Acta Provisional -->
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

                            <!-- Fechas Acta Definitiva -->
                            <div
                                v-if="form.estado === 'acta_definitiva'"
                                class="mt-4 pt-4 border-t border-slate-200"
                            >
                                <InputLabel
                                    for="fecha_acta_definitiva"
                                    value="Fecha de Acta Definitiva"
                                />
                                <TextInput
                                    id="fecha_acta_definitiva"
                                    type="date"
                                    class="mt-1 block max-w-sm w-full"
                                    v-model="form.fecha_acta_definitiva"
                                />
                                <InputError
                                    class="mt-2"
                                    :message="form.errors.fecha_acta_definitiva"
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
                                <div v-if="poliza.archivo_acta" class="mb-2">
                                    <p class="text-sm text-slate-500">
                                        Archivo actual:
                                        <span
                                            class="font-semibold text-slate-700"
                                            >Cargado</span
                                        >. (Selecciona uno nuevo si deseas
                                        reemplazarlo)
                                    </p>
                                </div>
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
                                    v-if="
                                        poliza.categoria_poliza !== 'ambiental'
                                    "
                                >
                                    <InputLabel value="Código" />
                                    <div
                                        class="mt-1 block w-full px-3 py-2 border border-slate-200 rounded-md bg-slate-50 text-slate-700 text-sm font-bold"
                                    >
                                        {{ poliza.codigo }}
                                    </div>
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
                                        id="categoria_poliza"
                                        v-model="form.categoria_poliza"
                                        class="mt-1 block w-full border-slate-300 focus:border-brand-blue focus:ring-brand-blue rounded-md shadow-sm"
                                        :class="{'bg-slate-100 text-slate-500 cursor-not-allowed': $page.props.auth.user.roles && $page.props.auth.user.roles.includes('Gestor Tesorería Ambiente')}"
                                        required
                                        :disabled="$page.props.auth.user.roles && $page.props.auth.user.roles.includes('Gestor Tesorería Ambiente')"
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
                                        id="subtipo_poliza"
                                        v-model="form.subtipo_poliza"
                                        class="mt-1 block w-full border-slate-300 focus:border-brand-blue focus:ring-brand-blue rounded-md shadow-sm"
                                        :class="{'bg-slate-100 text-slate-500 cursor-not-allowed': $page.props.auth.user.roles && $page.props.auth.user.roles.includes('Gestor Tesorería Ambiente')}"
                                        required
                                        :disabled="$page.props.auth.user.roles && $page.props.auth.user.roles.includes('Gestor Tesorería Ambiente')"
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

                                <div>
                                    <InputLabel
                                        for="anticipo"
                                        value="Anticipo ($)"
                                    />
                                    <TextInput
                                        id="anticipo"
                                        type="number"
                                        step="0.01"
                                        class="mt-1 block w-full"
                                        v-model="form.anticipo"
                                    />
                                    <InputError
                                        class="mt-2"
                                        :message="form.errors.anticipo"
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
                                Contrato y Documentación
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
                            </div>
                        </div>

                        <!-- Sección 3.5: Archivo de Renovación (Solo si es póliza renovada) -->
                        <div
                            v-if="renovacion_de"
                            class="mt-6 p-5 rounded-xl border-2 border-dashed transition-all duration-300"
                            :class="
                                renovacion_de.archivo_renovacion
                                    ? 'border-emerald-300 bg-emerald-50/50'
                                    : 'border-amber-300 bg-amber-50/50'
                            "
                        >
                            <div class="flex items-start gap-3 mb-4">
                                <div
                                    class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0"
                                    :class="
                                        renovacion_de.archivo_renovacion
                                            ? 'bg-emerald-100 text-emerald-600'
                                            : 'bg-amber-100 text-amber-600'
                                    "
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-5 w-5"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                        stroke-width="2"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                        />
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <h4
                                        class="font-bold text-slate-800 text-sm"
                                    >
                                        PDF de Renovación
                                    </h4>
                                    <p class="text-xs text-slate-500 mt-0.5">
                                        Esta póliza es una renovación de
                                        <span
                                            class="font-semibold text-slate-700"
                                        >
                                            {{
                                                renovacion_de.poliza_original
                                                    ?.numero_poliza ||
                                                "Póliza Original"
                                            }}
                                        </span>
                                    </p>
                                </div>
                            </div>

                            <!-- Estado actual del PDF -->
                            <div
                                v-if="renovacion_de.archivo_renovacion"
                                class="mb-4"
                            >
                                <div
                                    class="flex items-center gap-3 p-3 bg-white rounded-lg border border-emerald-200"
                                >
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2">
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                class="h-4 w-4 text-emerald-500"
                                                viewBox="0 0 20 20"
                                                fill="currentColor"
                                            >
                                                <path
                                                    fill-rule="evenodd"
                                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                    clip-rule="evenodd"
                                                />
                                            </svg>
                                            <span
                                                class="text-sm font-semibold text-emerald-700"
                                                >PDF cargado</span
                                            >
                                            <span
                                                v-if="
                                                    renovacion_de.estado_firma_asesor
                                                "
                                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-700"
                                            >
                                                <svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    class="h-3 w-3"
                                                    viewBox="0 0 20 20"
                                                    fill="currentColor"
                                                >
                                                    <path
                                                        fill-rule="evenodd"
                                                        d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                        clip-rule="evenodd"
                                                    />
                                                </svg>
                                                Firmado
                                            </span>
                                        </div>
                                        <p class="text-xs text-slate-500 mt-1">
                                            Selecciona un nuevo PDF si deseas
                                            reemplazarlo.
                                            <span
                                                v-if="
                                                    renovacion_de.estado_firma_asesor
                                                "
                                                class="text-amber-600 font-semibold"
                                            >
                                                ⚠️ La firma se invalidará al
                                                reemplazar.
                                            </span>
                                        </p>
                                    </div>
                                    <a
                                        :href="
                                            route(
                                                'polizas.renovacion_pdf',
                                                poliza.id,
                                            )
                                        "
                                        target="_blank"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-indigo-700 bg-indigo-50 rounded-lg hover:bg-indigo-100 transition border border-indigo-200"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="h-3.5 w-3.5"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                            stroke-width="2"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                            />
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                                            />
                                        </svg>
                                        Ver PDF actual
                                    </a>
                                </div>
                            </div>

                            <div v-else class="mb-4">
                                <div
                                    class="flex items-center gap-2 p-3 bg-amber-50 rounded-lg border border-amber-200"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-4 w-4 text-amber-500"
                                        viewBox="0 0 20 20"
                                        fill="currentColor"
                                    >
                                        <path
                                            fill-rule="evenodd"
                                            d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                            clip-rule="evenodd"
                                        />
                                    </svg>
                                    <span
                                        class="text-sm font-medium text-amber-700"
                                        >No se ha subido un PDF de renovación
                                        aún.</span
                                    >
                                </div>
                            </div>

                            <!-- Input para subir/reemplazar PDF -->
                            <div>
                                <InputLabel
                                    for="archivo_renovacion"
                                    :value="
                                        renovacion_de.archivo_renovacion
                                            ? 'Reemplazar PDF de Renovación'
                                            : 'Subir PDF de Renovación'
                                    "
                                />
                                <input
                                    type="file"
                                    id="archivo_renovacion"
                                    @input="
                                        form.archivo_renovacion =
                                            $event.target.files[0]
                                    "
                                    accept=".pdf"
                                    class="mt-1 block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 transition"
                                />
                                <InputError
                                    class="mt-2"
                                    :message="form.errors.archivo_renovacion"
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
                                :href="route('polizas.show', poliza.id)"
                                class="text-sm text-slate-600 hover:text-slate-900 transition font-medium"
                            >
                                Cancelar
                            </Link>
                            <PrimaryButton
                                class="shadow-sm px-8 !bg-[#024283] hover:!bg-[#003a7d] focus:!bg-[#003a7d] active:!bg-[#003a7d]"
                                :class="{ 'opacity-25': form.processing }"
                                :disabled="form.processing"
                            >
                                Guardar Cambios
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
