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
    contratistas: Array,
});

const showingModal = ref(false);
const editing = ref(null);
const search = ref("");

const filteredContratistas = computed(() => {
    if (!search.value) return props.contratistas;
    const q = search.value.toLowerCase();
    return props.contratistas.filter(
        (c) =>
            (c.nombre_cont || "").toLowerCase().includes(q) ||
            (c.correo_cont || "").toLowerCase().includes(q) ||
            (c.taxid || "").toLowerCase().includes(q),
    );
});

const form = useForm({
    nombre_cont: "",
    correo_cont: "",
    celular_cont: "",
    telefono_fijo: "",
    taxid: "",
});

const openModal = (contratista = null) => {
    editing.value = contratista;
    if (contratista) {
        form.nombre_cont = contratista.nombre_cont;
        form.correo_cont = contratista.correo_cont || "";
        form.celular_cont = contratista.celular_cont || "";
        form.telefono_fijo = contratista.telefono_fijo || "";
        form.taxid = contratista.taxid || "";
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
        form.put(route("contratistas.update", editing.value.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route("contratistas.store"), {
            onSuccess: () => closeModal(),
        });
    }
};

const deleteContratista = (contratista) => {
    if (confirm("¿Eliminar este contratista?")) {
        useForm({}).delete(route("contratistas.destroy", contratista.id));
    }
};
</script>

<template>
    <Head title="Contratistas" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Contratistas
                </h2>
                <button
                    @click="openModal()"
                    class="rounded-lg px-5 py-2.5 text-sm font-medium text-white shadow-md transition hover:opacity-90"
                    style="background-color: #024283"
                >
                    + Nuevo Contratista
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
                            placeholder="Buscar por nombre, cédula o RUC..."
                            class="block w-full pl-10 pr-3 py-2 border border-slate-200 rounded-xl leading-5 bg-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#024283] focus:border-[#024283] sm:text-sm transition duration-150 ease-in-out shadow-sm"
                        />
                    </div>
                </div>

                <!-- Empty State -->
                <div
                    v-if="filteredContratistas.length === 0"
                    class="rounded-xl bg-white p-12 text-center shadow-md"
                >
                    <div
                        class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-blue-50"
                    >
                        <svg
                            class="h-8 w-8 text-blue-400"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"
                            />
                        </svg>
                    </div>
                    <p class="text-gray-500">
                        No hay contratistas registrados.
                    </p>
                </div>

                <!-- Cards Grid -->
                <div v-else class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="contratista in filteredContratistas"
                        :key="contratista.id"
                        class="rounded-xl bg-white p-5 shadow-md transition hover:shadow-lg"
                    >
                        <div class="mb-3 flex items-start justify-between">
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex h-11 w-11 items-center justify-center rounded-full text-sm font-bold text-white"
                                    style="background-color: #024283"
                                >
                                    {{ contratista.nombre_cont.charAt(0) }}
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-900">
                                        {{ contratista.nombre_cont }}
                                    </h3>
                                    <p
                                        class="text-xs text-gray-500"
                                        v-if="contratista.taxid"
                                    >
                                        RUC: {{ contratista.taxid }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex gap-1">
                                <button
                                    @click="openModal(contratista)"
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
                                    @click="deleteContratista(contratista)"
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
                            <p v-if="contratista.correo_cont">
                                <span class="font-medium">Correo:</span>
                                {{ contratista.correo_cont }}
                            </p>
                            <p v-if="contratista.celular_cont">
                                <span class="font-medium">Celular:</span>
                                {{ contratista.celular_cont }}
                            </p>
                            <p v-if="contratista.telefono_fijo">
                                <span class="font-medium">Teléfono Fijo:</span>
                                {{ contratista.telefono_fijo }}
                            </p>
                            <p v-if="contratista.taxid">
                                <span class="font-medium">RUC/Tax ID:</span>
                                {{ contratista.taxid }}
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
                    {{ editing ? "Editar Contratista" : "Nuevo Contratista" }}
                </h3>
                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <InputLabel for="nombre_cont" value="Nombre *" />
                        <TextInput
                            id="nombre_cont"
                            v-model="form.nombre_cont"
                            type="text"
                            class="mt-1 block w-full"
                            maxlength="75"
                            required
                        />
                        <InputError
                            :message="form.errors.nombre_cont"
                            class="mt-1"
                        />
                    </div>
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
                            <InputError
                                :message="form.errors.taxid"
                                class="mt-1"
                            />
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
                            <InputError
                                :message="form.errors.telefono_fijo"
                                class="mt-1"
                            />
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel for="correo_cont" value="Correo" />
                            <TextInput
                                id="correo_cont"
                                v-model="form.correo_cont"
                                type="email"
                                class="mt-1 block w-full"
                                maxlength="50"
                            />
                            <InputError
                                :message="form.errors.correo_cont"
                                class="mt-1"
                            />
                        </div>
                        <div>
                            <InputLabel for="celular_cont" value="Celular" />
                            <TextInput
                                id="celular_cont"
                                v-model="form.celular_cont"
                                type="text"
                                class="mt-1 block w-full"
                                maxlength="10"
                            />
                            <InputError
                                :message="form.errors.celular_cont"
                                class="mt-1"
                            />
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 pt-4">
                        <SecondaryButton @click="closeModal"
                            >Cancelar</SecondaryButton
                        >
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
