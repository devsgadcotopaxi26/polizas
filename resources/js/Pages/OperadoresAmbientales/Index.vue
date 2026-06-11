<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, useForm } from "@inertiajs/vue3";
import { ref, computed } from "vue";
import Modal from "@/Components/Modal.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";

const props = defineProps({
    operadores: Array,
});

const showingModal = ref(false);
const editing = ref(null);
const search = ref("");

const filteredOperadores = computed(() => {
    if (!search.value) return props.operadores;
    const q = search.value.toLowerCase();
    return props.operadores.filter(
        (o) =>
            (o.nombre || "").toLowerCase().includes(q) ||
            (o.empresa || "").toLowerCase().includes(q) ||
            (o.correo || "").toLowerCase().includes(q) ||
            (o.taxid || "").toLowerCase().includes(q),
    );
});

const form = useForm({
    nombre: "",
    empresa: "",
    correo: "",
    celular: "",
    telefono_fijo: "",
    taxid: "",
});

const openModal = (operador = null) => {
    editing.value = operador;
    if (operador) {
        form.nombre        = operador.nombre;
        form.empresa       = operador.empresa || "";
        form.correo        = operador.correo || "";
        form.celular       = operador.celular || "";
        form.telefono_fijo = operador.telefono_fijo || "";
        form.taxid         = operador.taxid || "";
    } else {
        form.reset();
    }
    showingModal.value = true;
};

const closeModal = () => {
    showingModal.value = false;
    editing.value = null;
    form.reset();
    form.clearErrors();
};

const submit = () => {
    if (editing.value) {
        form.put(route("operadores-ambientales.update", editing.value.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route("operadores-ambientales.store"), {
            onSuccess: () => closeModal(),
        });
    }
};

const deleteOperador = (operador) => {
    if (confirm("¿Eliminar este operador ambiental?")) {
        useForm({}).delete(route("operadores-ambientales.destroy", operador.id));
    }
};
</script>

<template>
    <Head title="Operadores Ambientales" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Operadores Ambientales
                </h2>
                <button
                    @click="openModal()"
                    class="rounded-lg px-5 py-2.5 text-sm font-medium text-white shadow-md transition hover:opacity-90"
                    style="background-color: #024283"
                >
                    + Nuevo Operador
                </button>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Buscador -->
                <div class="mb-6">
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
                            placeholder="Buscar por nombre, empresa o RUC..."
                            class="block w-full pl-10 pr-3 py-2 border border-slate-200 rounded-xl leading-5 bg-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#024283] focus:border-[#024283] sm:text-sm transition duration-150 ease-in-out shadow-sm"
                        />
                    </div>
                </div>

                <!-- Empty State -->
                <div
                    v-if="filteredOperadores.length === 0"
                    class="rounded-xl bg-white p-12 text-center shadow-md"
                >
                    <div
                        class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-green-50"
                    >
                        <svg
                            class="h-8 w-8 text-green-500"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                            />
                        </svg>
                    </div>
                    <p class="text-gray-500">
                        No hay operadores ambientales registrados.
                    </p>
                </div>

                <!-- Cards Grid -->
                <div v-else class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="operador in filteredOperadores"
                        :key="operador.id"
                        class="rounded-xl bg-white p-5 shadow-md transition hover:shadow-lg"
                    >
                        <div class="mb-3 flex items-start justify-between">
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex h-11 w-11 items-center justify-center rounded-full text-sm font-bold text-white"
                                    style="background-color: #2d7a2d"
                                >
                                    {{ operador.nombre.charAt(0).toUpperCase() }}
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-900">
                                        {{ operador.nombre }}
                                    </h3>
                                    <p
                                        class="text-xs text-green-700 font-medium"
                                        v-if="operador.empresa"
                                    >
                                        {{ operador.empresa }}
                                    </p>
                                    <p
                                        class="text-xs text-gray-500"
                                        v-if="operador.taxid"
                                    >
                                        RUC: {{ operador.taxid }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex gap-1">
                                <button
                                    @click="openModal(operador)"
                                    class="rounded-lg p-1.5 text-gray-400 hover:bg-blue-50 hover:text-blue-600"
                                    title="Editar"
                                >
                                    <svg
                                        class="h-4 w-4"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                                        />
                                    </svg>
                                </button>
                                <button
                                    @click="deleteOperador(operador)"
                                    class="rounded-lg p-1.5 text-gray-400 hover:bg-red-50 hover:text-red-600"
                                    title="Eliminar"
                                >
                                    <svg
                                        class="h-4 w-4"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                        />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div class="space-y-1.5 text-sm text-gray-600">
                            <p v-if="operador.correo">
                                <span class="font-medium">Correo:</span>
                                {{ operador.correo }}
                            </p>
                            <p v-if="operador.celular">
                                <span class="font-medium">Celular:</span>
                                {{ operador.celular }}
                            </p>
                            <p v-if="operador.telefono_fijo">
                                <span class="font-medium">Teléfono Fijo:</span>
                                {{ operador.telefono_fijo }}
                            </p>
                            <p v-if="operador.taxid">
                                <span class="font-medium">RUC/Tax ID:</span>
                                {{ operador.taxid }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <Modal :show="showingModal" @close="closeModal" maxWidth="lg">
            <div class="p-6">
                <h3 class="mb-6 text-lg font-semibold text-gray-900">
                    {{ editing ? "Editar Operador Ambiental" : "Nuevo Operador Ambiental" }}
                </h3>
                <form @submit.prevent="submit" class="space-y-4">
                    <!-- Nombre -->
                    <div>
                        <InputLabel for="nombre" value="Nombre *" />
                        <TextInput
                            id="nombre"
                            v-model="form.nombre"
                            type="text"
                            class="mt-1 block w-full"
                            maxlength="75"
                            required
                        />
                        <InputError :message="form.errors.nombre" class="mt-1" />
                    </div>

                    <!-- Empresa -->
                    <div>
                        <InputLabel for="empresa" value="Empresa" />
                        <TextInput
                            id="empresa"
                            v-model="form.empresa"
                            type="text"
                            class="mt-1 block w-full"
                            maxlength="150"
                            placeholder="Nombre de la empresa u organización"
                        />
                        <InputError :message="form.errors.empresa" class="mt-1" />
                    </div>

                    <!-- Cédula/RUC y Teléfono Fijo -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel for="taxid" value="Cédula / RUC" />
                            <TextInput
                                id="taxid"
                                v-model="form.taxid"
                                type="text"
                                class="mt-1 block w-full"
                                maxlength="15"
                            />
                            <InputError :message="form.errors.taxid" class="mt-1" />
                        </div>
                        <div>
                            <InputLabel for="telefono_fijo" value="Teléfono Fijo" />
                            <TextInput
                                id="telefono_fijo"
                                v-model="form.telefono_fijo"
                                type="text"
                                class="mt-1 block w-full"
                                maxlength="15"
                            />
                            <InputError :message="form.errors.telefono_fijo" class="mt-1" />
                        </div>
                    </div>

                    <!-- Correo y Celular -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel for="correo" value="Correo" />
                            <TextInput
                                id="correo"
                                v-model="form.correo"
                                type="email"
                                class="mt-1 block w-full"
                                maxlength="50"
                            />
                            <InputError :message="form.errors.correo" class="mt-1" />
                        </div>
                        <div>
                            <InputLabel for="celular" value="Celular" />
                            <TextInput
                                id="celular"
                                v-model="form.celular"
                                type="text"
                                class="mt-1 block w-full"
                                maxlength="10"
                            />
                            <InputError :message="form.errors.celular" class="mt-1" />
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-4">
                        <SecondaryButton @click="closeModal">Cancelar</SecondaryButton>
                        <PrimaryButton
                            :disabled="form.processing"
                            style="background-color: #024283"
                        >
                            {{ editing ? "Actualizar" : "Guardar" }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
