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
    ciudades: Array,
});

const showingModal = ref(false);
const editing = ref(null);
const search = ref("");

const filteredCiudades = computed(() => {
    if (!search.value) return props.ciudades;
    const q = search.value.toLowerCase();
    return props.ciudades.filter(
        (c) => (c.nombre || "").toLowerCase().includes(q)
    );
});

const form = useForm({
    nombre: "",
});

const openModal = (ciudad = null) => {
    editing.value = ciudad;
    if (ciudad) {
        form.nombre = ciudad.nombre;
    } else {
        form.reset();
    }
    showingModal.value = true;
};

const closeModal = () => {
    showingModal.value = false;
    form.reset();
    form.clearErrors();
    editing.value = null;
};

const submit = () => {
    if (editing.value) {
        form.put(route("ciudades.update", editing.value.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route("ciudades.store"), {
            onSuccess: () => closeModal(),
        });
    }
};

const deleteCiudad = (id) => {
    if (confirm("¿Estás seguro de eliminar esta ciudad? Las ciudades con aseguradoras asociadas no podrán ser eliminadas.")) {
        form.delete(route("ciudades.destroy", id));
    }
};
</script>

<template>
    <Head title="Ciudades" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-slate-800 leading-tight">
                    Catálogo de Ciudades
                </h2>
                <button
                    @click="openModal()"
                    class="inline-flex items-center px-4 py-2 bg-[#024283] border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#003a7d] shadow-sm transition"
                >
                    Nueva Ciudad
                </button>
            </div>
        </template>

        <div class="py-12 bg-slate-50 min-h-screen">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
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
                            placeholder="Buscar ciudad por nombre..."
                            class="block w-full pl-10 pr-3 py-2 border border-slate-200 rounded-xl leading-5 bg-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#024283] focus:border-[#024283] sm:text-sm transition duration-150 ease-in-out shadow-sm"
                        />
                    </div>
                </div>

                <div
                    class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-6"
                >
                    <div
                        v-for="ciudad in filteredCiudades"
                        :key="ciudad.id"
                        class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6 flex flex-col hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1"
                    >
                        <div
                            class="h-12 w-12 rounded-2xl bg-sky-50 flex items-center justify-center text-sky-600 font-black text-xl mb-4"
                        >
                            {{ ciudad.nombre.charAt(0).toUpperCase() }}
                        </div>

                        <h3
                            class="text-xl font-extrabold text-slate-800 mb-6 leading-tight flex-grow"
                        >
                            {{ ciudad.nombre }}
                        </h3>

                        <div class="flex justify-between gap-3 mt-auto">
                            <button
                                @click="openModal(ciudad)"
                                class="flex-1 py-2 text-sm font-bold text-slate-600 bg-slate-50 hover:bg-amber-50 hover:text-amber-600 rounded-xl transition duration-150 border border-transparent hover:border-amber-100"
                            >
                                Editar
                            </button>
                            <button
                                @click="deleteCiudad(ciudad.id)"
                                class="px-4 py-2 text-sm font-bold text-slate-400 hover:text-rose-600 transition"
                                title="Eliminar"
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
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div
                        v-if="filteredCiudades.length === 0"
                        class="md:col-span-3 lg:col-span-4 bg-white p-20 rounded-3xl text-center border-2 border-dashed border-slate-100"
                    >
                        <div class="max-w-xs mx-auto">
                            <div
                                class="h-20 w-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-6"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-10 w-10 text-slate-200"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
                                    />
                                </svg>
                            </div>
                            <h4 class="text-xl font-bold text-slate-800 mb-2">
                                Sin ciudades registradas
                            </h4>
                            <p class="text-slate-500 mb-6 font-medium">
                                Comienza agregando las ciudades donde se ubican las aseguradoras.
                            </p>
                            <button
                                @click="openModal()"
                                class="bg-[#024283] text-white px-6 py-2.5 rounded-xl font-bold text-sm shadow-md hover:bg-[#003a7d] transition"
                            >
                                Registrar Primera Ciudad
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <Modal :show="showingModal" @close="closeModal" maxWidth="md">
            <div class="p-8">
                <h3 class="text-2xl font-black text-slate-800 mb-6">
                    {{ editing ? "Editar" : "Nueva" }} Ciudad
                </h3>

                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Datos -->
                    <div>
                        <InputLabel
                            for="nombre"
                            value="Nombre de la Ciudad"
                        />
                        <TextInput
                            id="nombre"
                            type="text"
                            class="mt-1 block w-full"
                            v-model="form.nombre"
                            required
                        />
                        <InputError
                            :message="form.errors.nombre"
                            class="mt-2"
                        />
                    </div>

                    <div class="flex items-center justify-end gap-4 pt-4 border-t border-slate-50">
                        <SecondaryButton @click="closeModal" type="button"
                            >Cancelar</SecondaryButton
                        >
                        <PrimaryButton
                            class="bg-[#024283]"
                            :disabled="form.processing"
                        >
                            {{ editing ? "Actualizar" : "Guardar" }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
