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
    administradores: Array,
});

const showingModal = ref(false);
const editing = ref(null);
const search = ref("");

const filteredAdministradores = computed(() => {
    if (!search.value) return props.administradores;
    const q = search.value.toLowerCase();
    return props.administradores.filter((a) =>
        (a.nombre || "").toLowerCase().includes(q),
    );
});

const form = useForm({
    nombre: "",
    activo: true,
});

const openModal = (admin = null) => {
    editing.value = admin;
    if (admin) {
        form.nombre = admin.nombre;
        form.activo = admin.activo;
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
        form.put(route("administradores.update", editing.value.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route("administradores.store"), {
            onSuccess: () => closeModal(),
        });
    }
};

const deleteAdmin = (admin) => {
    if (confirm("¿Eliminar este administrador?")) {
        useForm({}).delete(route("administradores.destroy", admin.id));
    }
};
</script>

<template>
    <Head title="Administradores" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Administradores de Contrato
                </h2>
                <button
                    @click="openModal()"
                    class="rounded-lg px-5 py-2.5 text-sm font-medium text-white shadow-md transition hover:opacity-90"
                    style="background-color: #024283"
                >
                    + Nuevo Administrador
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
                            placeholder="Buscar por nombre..."
                            class="block w-full pl-10 pr-3 py-2 border border-slate-200 rounded-xl leading-5 bg-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#024283] focus:border-[#024283] sm:text-sm transition duration-150 ease-in-out shadow-sm"
                        />
                    </div>
                </div>

                <div
                    v-if="filteredAdministradores.length === 0"
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
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                            />
                        </svg>
                    </div>
                    <p class="text-gray-500">
                        No hay administradores registrados.
                    </p>
                </div>

                <div
                    v-else
                    class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
                >
                    <div
                        v-for="admin in filteredAdministradores"
                        :key="admin.id"
                        class="flex items-center justify-between rounded-xl bg-white px-5 py-4 shadow-md transition hover:shadow-lg"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-full text-sm font-bold text-white"
                                style="background-color: #024283"
                            >
                                {{ admin.nombre.charAt(0) }}
                            </div>
                            <div>
                                <p class="font-semibold text-gray-900">
                                    {{ admin.nombre }}
                                </p>
                                <span
                                    class="inline-block rounded-full px-2 py-0.5 text-xs font-medium"
                                    :class="
                                        admin.activo
                                            ? 'bg-green-100 text-green-700'
                                            : 'bg-red-100 text-red-700'
                                    "
                                >
                                    {{ admin.activo ? "Activo" : "Inactivo" }}
                                </span>
                            </div>
                        </div>
                        <div class="flex gap-1">
                            <button
                                @click="openModal(admin)"
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
                                @click="deleteAdmin(admin)"
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
                </div>
            </div>
        </div>

        <!-- Modal -->
        <Modal :show="showingModal" @close="closeModal" maxWidth="md">
            <div class="p-6">
                <h3 class="mb-6 text-lg font-semibold text-gray-900">
                    {{
                        editing ? "Editar Administrador" : "Nuevo Administrador"
                    }}
                </h3>
                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <InputLabel for="nombre" value="Nombre completo *" />
                        <TextInput
                            id="nombre"
                            v-model="form.nombre"
                            type="text"
                            class="mt-1 block w-full"
                            maxlength="100"
                            required
                        />
                        <InputError
                            :message="form.errors.nombre"
                            class="mt-1"
                        />
                    </div>
                    <div v-if="editing" class="flex items-center gap-2">
                        <input
                            id="activo"
                            type="checkbox"
                            v-model="form.activo"
                            class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500"
                        />
                        <InputLabel for="activo" value="Activo" />
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
