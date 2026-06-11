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
import SearchableSelect from "@/Components/SearchableSelect.vue";

const props = defineProps({
    contratos: Array,
    contratistas: Array,
    administradores: Array,
});

const showingModal = ref(false);
const editing = ref(null);
const search = ref("");

const filteredContratos = computed(() => {
    if (!search.value) return props.contratos;
    const q = search.value.toLowerCase();
    return props.contratos.filter(
        (c) =>
            (c.numero_contrato || "").toLowerCase().includes(q) ||
            (c.contratista?.nombre_cont || "").toLowerCase().includes(q) ||
            (c.objeto_contratacion || "").toLowerCase().includes(q) ||
            (c.administrador?.nombre || "").toLowerCase().includes(q),
    );
});

const form = useForm({
    numero_contrato: "",
    objeto_contratacion: "",
    valor_contrato: "",
    anticipo: "",
    contratista_id: "",
    administrador_id: "",
});

const openModal = (contrato = null) => {
    editing.value = contrato;
    if (contrato) {
        form.numero_contrato = contrato.numero_contrato;
        form.objeto_contratacion = contrato.objeto_contratacion || "";
        form.valor_contrato = contrato.valor_contrato || "";
        form.anticipo = contrato.anticipo || "";
        form.contratista_id = contrato.contratista_id || "";
        form.administrador_id = contrato.administrador_id || "";
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
        form.put(route("contratos.update", editing.value.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route("contratos.store"), {
            onSuccess: () => closeModal(),
        });
    }
};

const deleteContrato = (contrato) => {
    if (confirm("¿Eliminar este contrato?")) {
        useForm({}).delete(route("contratos.destroy", contrato.id));
    }
};

const formatCurrency = (value) => {
    if (!value) return "—";
    return new Intl.NumberFormat("es-EC", {
        style: "currency",
        currency: "USD",
    }).format(value);
};
</script>

<template>
    <Head title="Contratos" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Contratos
                </h2>
                <button
                    @click="openModal()"
                    class="rounded-lg px-5 py-2.5 text-sm font-medium text-white shadow-md transition hover:opacity-90"
                    style="background-color: #024283"
                >
                    + Nuevo Contrato
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
                            placeholder="Buscar por número, contratista u objeto..."
                            class="block w-full pl-10 pr-3 py-2 border border-slate-200 rounded-xl leading-5 bg-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#024283] focus:border-[#024283] sm:text-sm transition duration-150 ease-in-out shadow-sm"
                        />
                    </div>
                </div>

                <div
                    v-if="filteredContratos.length === 0"
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
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                            />
                        </svg>
                    </div>
                    <p class="text-gray-500">No hay contratos registrados.</p>
                </div>

                <div v-else class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="contrato in filteredContratos"
                        :key="contrato.id"
                        class="rounded-xl bg-white p-5 shadow-md transition hover:shadow-lg"
                    >
                        <div class="mb-3 flex items-start justify-between">
                            <div>
                                <h3 class="font-semibold text-gray-900">
                                    {{ contrato.numero_contrato }}
                                </h3>
                                <p
                                    class="text-xs text-gray-500"
                                    v-if="contrato.contratista"
                                >
                                    {{ contrato.contratista.nombre_cont }}
                                </p>
                            </div>
                            <div class="flex gap-1">
                                <button
                                    @click="openModal(contrato)"
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
                                    @click="deleteContrato(contrato)"
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
                            <p
                                v-if="contrato.objeto_contratacion"
                                class="line-clamp-2"
                            >
                                <span class="font-medium">Objeto:</span>
                                {{ contrato.objeto_contratacion }}
                            </p>
                            <p>
                                <span class="font-medium">Valor:</span>
                                {{ formatCurrency(contrato.valor_contrato) }}
                            </p>
                            <p v-if="contrato.anticipo">
                                <span class="font-medium">Anticipo:</span>
                                {{ formatCurrency(contrato.anticipo) }}
                            </p>
                            <p v-if="contrato.administrador">
                                <span class="font-medium">Admin:</span>
                                {{ contrato.administrador.nombre }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <Modal :show="showingModal" @close="closeModal" maxWidth="xl">
            <div class="max-h-[85vh] overflow-y-auto p-6">
                <h3 class="mb-6 text-lg font-semibold text-gray-900">
                    {{ editing ? "Editar Contrato" : "Nuevo Contrato" }}
                </h3>
                <form @submit.prevent="submit" class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel
                                for="numero_contrato"
                                value="Nº Contrato *"
                            />
                            <TextInput
                                id="numero_contrato"
                                v-model="form.numero_contrato"
                                type="text"
                                class="mt-1 block w-full"
                                maxlength="100"
                                required
                            />
                            <InputError
                                :message="form.errors.numero_contrato"
                                class="mt-1"
                            />
                        </div>
                        <div>
                            <InputLabel
                                for="contratista_id"
                                value="Contratista *"
                            />
                            <SearchableSelect
                                v-model="form.contratista_id"
                                :options="contratistas"
                                label-key="nombre_cont"
                                value-key="id"
                                placeholder="Seleccione contratista..."
                                search-placeholder="Buscar contratista..."
                            />
                            <InputError
                                :message="form.errors.contratista_id"
                                class="mt-1"
                            />
                        </div>
                    </div>

                    <div>
                        <InputLabel
                            for="objeto_contratacion"
                            value="Objeto de Contratación"
                        />
                        <textarea
                            id="objeto_contratacion"
                            v-model="form.objeto_contratacion"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            rows="3"
                        ></textarea>
                        <InputError
                            :message="form.errors.objeto_contratacion"
                            class="mt-1"
                        />
                    </div>

                    <div>
                        <div>
                            <InputLabel
                                for="valor_contrato"
                                value="Valor del Contrato"
                            />
                            <TextInput
                                id="valor_contrato"
                                v-model="form.valor_contrato"
                                type="number"
                                step="0.01"
                                min="0"
                                class="mt-1 block w-full"
                            />
                            <InputError
                                :message="form.errors.valor_contrato"
                                class="mt-1"
                            />
                        </div>
                    </div>

                    <div>
                        <InputLabel for="anticipo" value="Anticipo" />
                        <TextInput
                            id="anticipo"
                            v-model="form.anticipo"
                            type="number"
                            step="0.01"
                            min="0"
                            class="mt-1 block w-full"
                        />
                        <InputError
                            :message="form.errors.anticipo"
                            class="mt-1"
                        />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel
                                for="administrador_id"
                                value="Administrador"
                            />
                            <SearchableSelect
                                v-model="form.administrador_id"
                                :options="administradores"
                                label-key="nombre"
                                value-key="id"
                                placeholder="Sin asignar"
                                search-placeholder="Buscar administrador..."
                            />
                            <InputError
                                :message="form.errors.administrador_id"
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
