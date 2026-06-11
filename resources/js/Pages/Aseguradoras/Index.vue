<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, useForm, router } from "@inertiajs/vue3";
import { ref, computed } from "vue";
import Modal from "@/Components/Modal.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";

const props = defineProps({
    aseguradoras: Array,
    ciudades: Array,
});

/* ─── BÚSQUEDA ─────────────────────────────────────── */
const search = ref("");
const filteredAseguradoras = computed(() => {
    if (!search.value) return props.aseguradoras;
    const q = search.value.toLowerCase();
    return props.aseguradoras.filter((a) =>
        (a.nombre_empresa || "").toLowerCase().includes(q)
    );
});

/* ─── MODAL EMPRESA ─────────────────────────────────── */
const showingEmpresaModal = ref(false);
const editingEmpresa = ref(null);

const formEmpresa = useForm({ nombre_empresa: "", activo: true });

const openEmpresaModal = (empresa = null) => {
    editingEmpresa.value = empresa;
    if (empresa) {
        formEmpresa.nombre_empresa = empresa.nombre_empresa;
        formEmpresa.activo = empresa.activo;
    } else {
        formEmpresa.reset();
    }
    showingEmpresaModal.value = true;
};

const closeEmpresaModal = () => {
    showingEmpresaModal.value = false;
    formEmpresa.reset();
    formEmpresa.clearErrors();
    editingEmpresa.value = null;
};

const submitEmpresa = () => {
    if (editingEmpresa.value) {
        formEmpresa.put(route("aseguradoras.update", editingEmpresa.value.id), {
            onSuccess: () => closeEmpresaModal(),
        });
    } else {
        formEmpresa.post(route("aseguradoras.store"), {
            onSuccess: () => closeEmpresaModal(),
        });
    }
};

const deleteEmpresa = (id) => {
    if (confirm("¿Seguro que deseas eliminar esta aseguradora y TODAS sus sucursales?")) {
        router.delete(route("aseguradoras.destroy", id));
    }
};

/* ─── DRAWER SUCURSALES ─────────────────────────────── */
const drawerOpen = ref(false);
const drawerAseguradora = ref(null);

const openDrawer = (aseguradora) => {
    drawerAseguradora.value = aseguradora;
    drawerOpen.value = true;
    closeSucursalForm();
};

const closeDrawer = () => {
    drawerOpen.value = false;
    drawerAseguradora.value = null;
    closeSucursalForm();
};

/* ─── FORMULARIO SUCURSAL ───────────────────────────── */
const sucursalFormVisible = ref(false);
const editingSucursal = ref(null);

const formSucursal = useForm({
    ciudad_id: "",
    nombre_contacto: "",
    correo1: "", correo2: "", correo3: "",
    correo4: "", correo5: "", correo6: "",
    celular1: "", celular2: "", celular3: "",
    telefono_fijo1: "", telefono_fijo2: "",
    extensiones: "",
});

const openSucursalForm = (sucursal = null) => {
    editingSucursal.value = sucursal;
    if (sucursal) {
        Object.keys(formSucursal.data()).forEach((key) => {
            formSucursal[key] = sucursal[key] ?? "";
        });
    } else {
        formSucursal.reset();
    }
    sucursalFormVisible.value = true;
};

const closeSucursalForm = () => {
    sucursalFormVisible.value = false;
    formSucursal.reset();
    formSucursal.clearErrors();
    editingSucursal.value = null;
};

const submitSucursal = () => {
    if (!drawerAseguradora.value) return;
    if (editingSucursal.value) {
        formSucursal.put(
            route("aseguradoras.sucursales.update", {
                aseguradora: drawerAseguradora.value.id,
                sucursal: editingSucursal.value.id,
            }),
            { onSuccess: () => closeSucursalForm() }
        );
    } else {
        formSucursal.post(
            route("aseguradoras.sucursales.store", drawerAseguradora.value.id),
            { onSuccess: () => closeSucursalForm() }
        );
    }
};

const deleteSucursal = (sucursal) => {
    if (!confirm("¿Eliminar esta sucursal?")) return;
    router.delete(
        route("aseguradoras.sucursales.destroy", {
            aseguradora: drawerAseguradora.value.id,
            sucursal: sucursal.id,
        })
    );
};

// Actualizar drawerAseguradora cuando los props cambien (después de mutations)
const currentDrawerAseguradora = computed(() =>
    drawerAseguradora.value
        ? props.aseguradoras.find((a) => a.id === drawerAseguradora.value.id) ?? drawerAseguradora.value
        : null
);
</script>

<template>
    <Head title="Aseguradoras" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-slate-800 leading-tight">
                    Compañías Aseguradoras
                </h2>
                <button
                    @click="openEmpresaModal()"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-[#024283] border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#003a7d] shadow-sm transition"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Nueva Aseguradora
                </button>
            </div>
        </template>

        <div class="py-12 bg-slate-50 min-h-screen">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

                <!-- Buscador -->
                <div class="mb-6">
                    <div class="relative w-full md:w-96">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </span>
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Buscar aseguradora..."
                            class="block w-full pl-10 pr-3 py-2 border border-slate-200 rounded-xl leading-5 bg-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#024283] focus:border-[#024283] sm:text-sm transition shadow-sm"
                        />
                    </div>
                </div>

                <!-- Grid de tarjetas -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div
                        v-for="aseguradora in filteredAseguradoras"
                        :key="aseguradora.id"
                        class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 flex flex-col"
                    >
                        <!-- Cabecera tarjeta -->
                        <div class="flex justify-between items-start mb-4">
                            <div class="h-12 w-12 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600 font-black text-xl shrink-0">
                                {{ aseguradora.nombre_empresa.charAt(0) }}
                            </div>
                            <span
                                :class="[
                                    'px-2.5 py-1 rounded-full text-xs font-bold uppercase tracking-widest',
                                    aseguradora.activo ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-50 text-slate-400',
                                ]"
                            >
                                {{ aseguradora.activo ? "Activa" : "Inactiva" }}
                            </span>
                        </div>

                        <h3 class="text-lg font-extrabold text-slate-800 mb-1 leading-tight flex-grow">
                            {{ aseguradora.nombre_empresa }}
                        </h3>

                        <!-- Contador de sucursales -->
                        <div class="flex items-center gap-1.5 mb-4 text-sm text-slate-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span class="font-medium">
                                {{ aseguradora.sucursales_count ?? aseguradora.sucursales?.length ?? 0 }}
                                {{ (aseguradora.sucursales_count ?? aseguradora.sucursales?.length ?? 0) === 1 ? 'sucursal' : 'sucursales' }}
                            </span>
                            <span v-if="aseguradora.sucursales?.length" class="text-slate-300">·</span>
                            <span v-if="aseguradora.sucursales?.length" class="truncate max-w-[140px]">
                                {{ aseguradora.sucursales.map(s => s.ciudad?.nombre ?? 'Sin ciudad').join(', ') }}
                            </span>
                        </div>

                        <!-- Acciones -->
                        <div class="flex justify-between gap-2 mt-auto border-t border-slate-50 pt-4">
                            <button
                                @click="openDrawer(aseguradora)"
                                class="flex-1 py-2 text-sm font-bold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 rounded-xl transition duration-150 flex items-center justify-center gap-1.5"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                                Sucursales
                            </button>
                            <button
                                @click="openEmpresaModal(aseguradora)"
                                class="px-3 py-2 text-sm font-bold text-slate-500 bg-slate-50 hover:bg-amber-50 hover:text-amber-600 rounded-xl transition duration-150"
                                title="Editar empresa"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                </svg>
                            </button>
                            <button
                                @click="deleteEmpresa(aseguradora.id)"
                                class="px-3 py-2 text-slate-300 hover:text-rose-500 transition"
                                title="Eliminar"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Estado vacío -->
                    <div
                        v-if="filteredAseguradoras.length === 0"
                        class="lg:col-span-3 bg-white p-20 rounded-3xl text-center border-2 border-dashed border-slate-100"
                    >
                        <div class="max-w-xs mx-auto">
                            <div class="h-20 w-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-6">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-slate-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                            </div>
                            <h4 class="text-xl font-bold text-slate-800 mb-2">Sin aseguradoras</h4>
                            <p class="text-slate-500 mb-6 font-medium">Comienza agregando las empresas aseguradoras.</p>
                            <button
                                @click="openEmpresaModal()"
                                class="bg-[#024283] text-white px-6 py-2.5 rounded-xl font-bold text-sm shadow-md hover:bg-[#003a7d] transition"
                            >
                                Agregar Primera
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ═══════════════ MODAL EMPRESA ═══════════════ -->
        <Modal :show="showingEmpresaModal" @close="closeEmpresaModal" maxWidth="md">
            <div class="p-8">
                <h3 class="text-2xl font-black text-slate-800 mb-6">
                    {{ editingEmpresa ? "Editar" : "Nueva" }} Aseguradora
                </h3>
                <form @submit.prevent="submitEmpresa" class="space-y-5">
                    <div>
                        <InputLabel for="nombre_empresa" value="Nombre de la Empresa" />
                        <TextInput
                            id="nombre_empresa"
                            type="text"
                            class="mt-1 block w-full"
                            v-model="formEmpresa.nombre_empresa"
                            required
                            placeholder="Ej: ASEGURADORA DEL SUR C.A."
                        />
                        <InputError :message="formEmpresa.errors.nombre_empresa" class="mt-2" />
                    </div>

                    <div v-if="editingEmpresa" class="flex items-center gap-2 py-3 border-t border-slate-50">
                        <input
                            type="checkbox"
                            id="activo_empresa"
                            v-model="formEmpresa.activo"
                            class="rounded border-slate-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                        />
                        <InputLabel for="activo_empresa" value="Aseguradora activa" class="!mb-0" />
                    </div>

                    <div class="flex items-center justify-end gap-4 pt-4 border-t border-slate-50">
                        <SecondaryButton @click="closeEmpresaModal" type="button">Cancelar</SecondaryButton>
                        <PrimaryButton :disabled="formEmpresa.processing">
                            {{ editingEmpresa ? "Actualizar" : "Guardar" }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- ═══════════════ DRAWER SUCURSALES ═══════════════ -->
        <!-- Overlay -->
        <Transition
            enter-active-class="transition-opacity duration-300"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-200"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="drawerOpen"
                class="fixed inset-0 bg-slate-900/40 z-40"
                @click="closeDrawer"
            />
        </Transition>

        <!-- Panel lateral -->
        <Transition
            enter-active-class="transition-transform duration-300 ease-out"
            enter-from-class="translate-x-full"
            enter-to-class="translate-x-0"
            leave-active-class="transition-transform duration-200 ease-in"
            leave-from-class="translate-x-0"
            leave-to-class="translate-x-full"
        >
            <div
                v-if="drawerOpen"
                class="fixed inset-y-0 right-0 w-full max-w-2xl bg-white shadow-2xl z-50 flex flex-col"
            >
                <!-- Cabecera drawer -->
                <div class="flex items-center justify-between p-6 border-b border-slate-100 bg-slate-50">
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-0.5">Sucursales de</p>
                        <h3 class="text-xl font-black text-slate-800 leading-tight">
                            {{ currentDrawerAseguradora?.nombre_empresa }}
                        </h3>
                    </div>
                    <div class="flex items-center gap-2">
                        <button
                            @click="openSucursalForm()"
                            class="inline-flex items-center gap-1.5 px-4 py-2 bg-[#024283] text-white text-sm font-bold rounded-lg hover:bg-[#003a7d] transition"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Nueva Sucursal
                        </button>
                        <button
                            @click="closeDrawer"
                            class="p-2 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Cuerpo scrollable -->
                <div class="flex-1 overflow-y-auto p-6 space-y-4">

                    <!-- Formulario inline nueva/editar sucursal -->
                    <Transition
                        enter-active-class="transition-all duration-200 ease-out"
                        enter-from-class="opacity-0 -translate-y-2"
                        enter-to-class="opacity-100 translate-y-0"
                        leave-active-class="transition-all duration-150 ease-in"
                        leave-from-class="opacity-100 translate-y-0"
                        leave-to-class="opacity-0 -translate-y-2"
                    >
                        <div
                            v-if="sucursalFormVisible"
                            class="bg-indigo-50 border border-indigo-100 rounded-2xl p-5"
                        >
                            <div class="flex justify-between items-center mb-4">
                                <h4 class="font-bold text-slate-700">
                                    {{ editingSucursal ? "Editar Sucursal" : "Nueva Sucursal" }}
                                </h4>
                                <button @click="closeSucursalForm" class="text-slate-400 hover:text-slate-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>

                            <form @submit.prevent="submitSucursal" class="space-y-4">
                                <!-- Ciudad y Contacto -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <InputLabel for="s_ciudad_id" value="Ciudad" />
                                        <select
                                            id="s_ciudad_id"
                                            v-model="formSucursal.ciudad_id"
                                            class="mt-1 block w-full border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm bg-white"
                                        >
                                            <option value="">Sin ciudad</option>
                                            <option v-for="ciudad in ciudades" :key="ciudad.id" :value="ciudad.id">
                                                {{ ciudad.nombre }}
                                            </option>
                                        </select>
                                        <InputError :message="formSucursal.errors.ciudad_id" class="mt-1" />
                                    </div>
                                    <div>
                                        <InputLabel for="s_nombre_contacto" value="Nombre de Contacto" />
                                        <TextInput
                                            id="s_nombre_contacto"
                                            type="text"
                                            class="mt-1 block w-full text-sm"
                                            v-model="formSucursal.nombre_contacto"
                                            placeholder="Ej: Juan Pérez"
                                        />
                                        <InputError :message="formSucursal.errors.nombre_contacto" class="mt-1" />
                                    </div>
                                </div>

                                <!-- Correos electrónicos -->
                                <div>
                                    <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">📧 Correos Electrónicos</p>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <div v-for="n in 6" :key="'correo' + n">
                                            <InputLabel :for="'s_correo' + n" :value="'Correo ' + n" class="text-xs" />
                                            <TextInput
                                                :id="'s_correo' + n"
                                                type="email"
                                                class="mt-1 block w-full text-sm"
                                                v-model="formSucursal['correo' + n]"
                                                :placeholder="'correo' + n + '@empresa.com'"
                                            />
                                            <InputError :message="formSucursal.errors['correo' + n]" class="mt-0.5" />
                                        </div>
                                    </div>
                                </div>

                                <!-- Teléfonos -->
                                <div>
                                    <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">📱 Teléfonos y Celulares</p>
                                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                        <div v-for="n in 3" :key="'celular' + n">
                                            <InputLabel :for="'s_celular' + n" :value="'Celular ' + n" class="text-xs" />
                                            <TextInput
                                                :id="'s_celular' + n"
                                                type="text"
                                                class="mt-1 block w-full text-sm"
                                                v-model="formSucursal['celular' + n]"
                                                maxlength="15"
                                                placeholder="09XXXXXXXX"
                                            />
                                        </div>
                                        <div>
                                            <InputLabel for="s_telefono_fijo1" value="Teléfono Fijo 1" class="text-xs" />
                                            <TextInput
                                                id="s_telefono_fijo1"
                                                type="text"
                                                class="mt-1 block w-full text-sm"
                                                v-model="formSucursal.telefono_fijo1"
                                                maxlength="15"
                                                placeholder="03XXXXXXX"
                                            />
                                        </div>
                                        <div>
                                            <InputLabel for="s_telefono_fijo2" value="Teléfono Fijo 2" class="text-xs" />
                                            <TextInput
                                                id="s_telefono_fijo2"
                                                type="text"
                                                class="mt-1 block w-full text-sm"
                                                v-model="formSucursal.telefono_fijo2"
                                                maxlength="15"
                                                placeholder="03XXXXXXX"
                                            />
                                        </div>
                                        <div>
                                            <InputLabel for="s_extensiones" value="Extensiones" class="text-xs" />
                                            <TextInput
                                                id="s_extensiones"
                                                type="text"
                                                class="mt-1 block w-full text-sm"
                                                v-model="formSucursal.extensiones"
                                                placeholder="Ej: 101, 203"
                                            />
                                        </div>
                                    </div>
                                </div>

                                <!-- Botones del form -->
                                <div class="flex justify-end gap-3 pt-2 border-t border-indigo-100">
                                    <SecondaryButton @click="closeSucursalForm" type="button" class="text-sm">
                                        Cancelar
                                    </SecondaryButton>
                                    <PrimaryButton :disabled="formSucursal.processing" class="text-sm">
                                        {{ editingSucursal ? "Actualizar" : "Guardar Sucursal" }}
                                    </PrimaryButton>
                                </div>
                            </form>
                        </div>
                    </Transition>

                    <!-- Lista de sucursales existentes -->
                    <div
                        v-if="!currentDrawerAseguradora?.sucursales?.length"
                        class="text-center py-16 text-slate-400"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-14 w-14 mx-auto mb-4 text-slate-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <p class="font-medium text-sm">No hay sucursales registradas.</p>
                        <p class="text-xs mt-1">Haz clic en "Nueva Sucursal" para agregar la primera.</p>
                    </div>

                    <div
                        v-for="sucursal in currentDrawerAseguradora?.sucursales"
                        :key="sucursal.id"
                        class="bg-white border border-slate-100 rounded-2xl p-5 shadow-sm"
                    >
                        <!-- Encabezado sucursal -->
                        <div class="flex justify-between items-start mb-3">
                            <div class="flex items-center gap-2">
                                <span class="h-8 w-8 rounded-lg bg-sky-50 flex items-center justify-center text-sky-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                </span>
                                <div>
                                    <p class="font-bold text-slate-700 text-sm">
                                        {{ sucursal.ciudad?.nombre ?? "Sin ciudad asignada" }}
                                    </p>
                                    <p v-if="sucursal.nombre_contacto" class="text-xs text-slate-400">
                                        Contacto: {{ sucursal.nombre_contacto }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex gap-1">
                                <button
                                    @click="openSucursalForm(sucursal)"
                                    class="p-1.5 rounded-lg text-slate-400 hover:text-amber-500 hover:bg-amber-50 transition"
                                    title="Editar sucursal"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                    </svg>
                                </button>
                                <button
                                    @click="deleteSucursal(sucursal)"
                                    class="p-1.5 rounded-lg text-slate-300 hover:text-rose-500 hover:bg-rose-50 transition"
                                    title="Eliminar sucursal"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Datos de contacto en píldoras -->
                        <div class="flex flex-wrap gap-2 mt-2">
                            <template v-for="n in 6" :key="'c_correo' + n">
                                <span
                                    v-if="sucursal['correo' + n]"
                                    class="inline-flex items-center gap-1 text-xs bg-blue-50 text-blue-600 rounded-full px-2.5 py-1 font-medium"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                    {{ sucursal["correo" + n] }}
                                </span>
                            </template>
                            <template v-for="n in 3" :key="'c_cel' + n">
                                <span
                                    v-if="sucursal['celular' + n]"
                                    class="inline-flex items-center gap-1 text-xs bg-emerald-50 text-emerald-600 rounded-full px-2.5 py-1 font-medium"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                    </svg>
                                    {{ sucursal["celular" + n] }}
                                </span>
                            </template>
                            <template v-for="n in 2" :key="'c_tel' + n">
                                <span
                                    v-if="sucursal['telefono_fijo' + n]"
                                    class="inline-flex items-center gap-1 text-xs bg-violet-50 text-violet-600 rounded-full px-2.5 py-1 font-medium"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                    </svg>
                                    {{ sucursal["telefono_fijo" + n] }}
                                    <span v-if="sucursal.extensiones" class="text-violet-400">ext. {{ sucursal.extensiones }}</span>
                                </span>
                            </template>
                            <span
                                v-if="!sucursal.correo1 && !sucursal.celular1 && !sucursal.telefono_fijo1"
                                class="text-xs text-slate-300 italic"
                            >
                                Sin datos de contacto aún
                            </span>
                        </div>
                    </div>

                </div>
            </div>
        </Transition>
    </AuthenticatedLayout>
</template>
