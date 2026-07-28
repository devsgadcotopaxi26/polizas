<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, useForm, router } from "@inertiajs/vue3";
import { ref, computed } from "vue";
import Modal from "@/Components/Modal.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";

const props = defineProps({
    users: Array,
    roles: Array,
});

const showingModal = ref(false);
const editing = ref(null);
const search = ref("");

const filteredUsers = computed(() => {
    if (!search.value) return props.users;
    const q = search.value.toLowerCase();
    return props.users.filter(
        (u) =>
            (u.name || "").toLowerCase().includes(q) ||
            (u.email || "").toLowerCase().includes(q),
    );
});

const form = useForm({
    name: "",
    email: "",
    password: "",
    password_confirmation: "",
    role: "Usuario",
    is_active: true,
    must_change_password: true,
});

const openModal = (user = null) => {
    editing.value = user;
    if (user) {
        form.name = user.name;
        form.email = user.email;
        form.role =
            user.roles && user.roles.length > 0
                ? user.roles[0].name
                : "Usuario";
        form.password = "";
        form.password_confirmation = "";
        form.is_active = user.is_active !== undefined ? user.is_active : true;
        form.must_change_password = user.must_change_password !== undefined ? user.must_change_password : false;
    } else {
        form.reset();
        form.is_active = true;
        form.must_change_password = true;
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
        form.put(route("users.update", editing.value.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route("users.store"), {
            onSuccess: () => closeModal(),
        });
    }
};

const confirmingActionModal = ref(false);
const confirmActionData = ref({
    title: "",
    message: "",
    iconType: "amber",
    confirmText: "Continuar",
    confirmClass: "bg-indigo-600 hover:bg-indigo-700",
    action: null,
});

const openConfirmModal = ({ title, message, iconType, confirmText, confirmClass, action }) => {
    confirmActionData.value = {
        title,
        message,
        iconType: iconType || "amber",
        confirmText: confirmText || "Continuar",
        confirmClass: confirmClass || "bg-indigo-600 hover:bg-indigo-700",
        action,
    };
    confirmingActionModal.value = true;
};

const closeConfirmModal = () => {
    confirmingActionModal.value = false;
    confirmActionData.value.action = null;
};

const executeConfirmAction = () => {
    if (confirmActionData.value.action) {
        confirmActionData.value.action();
    }
    closeConfirmModal();
};

const deleteUser = (user) => {
    openConfirmModal({
        title: "Eliminar Usuario",
        message: `¿Estás completamente seguro de eliminar al usuario "${user.name}" (${user.email})? Esta acción es permanente e irreversible.`,
        iconType: "red",
        confirmText: "Sí, eliminar usuario",
        confirmClass: "bg-red-600 hover:bg-red-700 text-white",
        action: () => {
            router.delete(route("users.destroy", user.id));
        },
    });
};

const toggleStatus = (user) => {
    const isActivating = !user.is_active;
    openConfirmModal({
        title: isActivating ? "Activar Cuenta de Usuario" : "Inactivar Cuenta Temporalmente",
        message: isActivating
            ? `¿Estás seguro de activar nuevamente al usuario "${user.name}"? Podrá acceder al sistema y firmar documentos de acuerdo con sus permisos.`
            : `¿Estás seguro de inactivar la cuenta de "${user.name}" por vacaciones o subrogación? Se bloqueará su acceso y firma hasta que sea reactivada.`,
        iconType: isActivating ? "emerald" : "amber",
        confirmText: isActivating ? "Sí, activar cuenta" : "Sí, inactivar cuenta",
        confirmClass: isActivating ? "bg-emerald-600 hover:bg-emerald-700 text-white" : "bg-amber-600 hover:bg-amber-700 text-white",
        action: () => {
            router.patch(route("users.toggle_status", user.id), {}, { preserveScroll: true });
        },
    });
};

const togglePasswordChange = (user) => {
    const cancelando = user.must_change_password;
    openConfirmModal({
        title: cancelando ? "Cancelar Cambio de Contraseña" : "Exigir Cambio de Contraseña",
        message: cancelando
            ? `¿Estás seguro de cancelar la exigencia de cambio de contraseña para el usuario "${user.name}"? Ya no se le requerirá actualizar su clave al ingresar.`
            : `¿Estás seguro de exigir cambio obligatorio de contraseña a "${user.name}"? En su próximo inicio de sesión, el sistema le pedirá cambiar su contraseña de forma obligatoria.`,
        iconType: "purple",
        confirmText: cancelando ? "Sí, cancelar exigencia" : "Sí, exigir cambio",
        confirmClass: "bg-purple-600 hover:bg-purple-700 text-white",
        action: () => {
            router.patch(route("users.toggle_password_change", user.id), {}, { preserveScroll: true });
        },
    });
};
</script>

<template>
    <Head title="Usuarios del Sistema" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-slate-800 leading-tight">
                    Gestión de Usuarios
                </h2>
                <button
                    @click="openModal()"
                    class="inline-flex items-center px-4 py-2 bg-[#024283] border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#003a7d] shadow-sm transition"
                >
                    Nuevo Usuario
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
                            placeholder="Buscar por nombre o correo..."
                            class="block w-full pl-10 pr-3 py-2 border border-slate-200 rounded-xl leading-5 bg-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#024283] focus:border-[#024283] sm:text-sm transition duration-150 ease-in-out shadow-sm"
                        />
                    </div>
                </div>

                <!-- Tabla -->
                <div
                    class="bg-white overflow-hidden shadow-sm rounded-xl border border-slate-200 relative"
                >
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th
                                        scope="col"
                                        class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider"
                                    >
                                        Nombre
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider"
                                    >
                                        Email
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider"
                                    >
                                        Rol
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider"
                                    >
                                        Estado / Subrogación
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-6 py-4 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider"
                                    >
                                        Acciones
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-slate-200">
                                <tr
                                    v-for="user in filteredUsers"
                                    :key="user.id"
                                    class="hover:bg-slate-50 transition-colors duration-200"
                                >
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div
                                                class="h-10 w-10 flex-shrink-0"
                                            >
                                                <div
                                                    class="h-10 w-10 rounded-full bg-slate-200 flex items-center justify-center text-slate-600 font-bold"
                                                >
                                                    {{
                                                        user.name
                                                            .charAt(0)
                                                            .toUpperCase()
                                                    }}
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div
                                                    class="text-sm font-medium text-slate-900"
                                                >
                                                    {{ user.name }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-slate-600">
                                            {{ user.email }}
                                        </div>
                                        <span
                                            v-if="user.must_change_password"
                                            class="mt-1 px-2.5 py-0.5 inline-flex text-xs leading-4 font-semibold rounded-full bg-purple-100 text-purple-800"
                                            title="El usuario deberá cambiar su contraseña en el próximo inicio de sesión"
                                        >
                                            🔑 Cambio de clave requerido
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800"
                                        >
                                            {{
                                                user.roles &&
                                                user.roles.length > 0
                                                    ? user.roles[0].name
                                                    : "Ninguno"
                                            }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            v-if="user.is_active"
                                            class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-emerald-100 text-emerald-800"
                                        >
                                            Activo
                                        </span>
                                        <span
                                            v-else
                                            class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-amber-100 text-amber-800"
                                        >
                                            Inactivo (Subrogado / Vacaciones)
                                        </span>
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium"
                                    >
                                        <div
                                            class="flex justify-center space-x-2"
                                        >
                                            <button
                                                @click="togglePasswordChange(user)"
                                                :title="user.must_change_password ? 'Ya se le exigió cambio de clave (clic para cancelar)' : 'Pedir cambio de contraseña al usuario en su próximo ingreso'"
                                                class="p-2 rounded-lg transition-colors"
                                                :class="user.must_change_password ? 'text-purple-600 hover:text-purple-900 bg-purple-50 hover:bg-purple-100' : 'text-slate-500 hover:text-slate-900 bg-slate-100 hover:bg-slate-200'"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                                                </svg>
                                            </button>
                                            <button
                                                @click="toggleStatus(user)"
                                                :title="user.is_active ? 'Inactivar usuario (vacaciones o subrogado)' : 'Activar usuario'"
                                                class="p-2 rounded-lg transition-colors"
                                                :class="user.is_active ? 'text-amber-600 hover:text-amber-900 bg-amber-50 hover:bg-amber-100' : 'text-emerald-600 hover:text-emerald-900 bg-emerald-50 hover:bg-emerald-100'"
                                            >
                                                <svg v-if="user.is_active" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            </button>
                                            <button
                                                @click="openModal(user)"
                                                title="Editar usuario"
                                                class="text-indigo-600 hover:text-indigo-900 bg-indigo-50 hover:bg-indigo-100 p-2 rounded-lg transition-colors"
                                            >
                                                <svg
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
                                            </button>
                                            <button
                                                @click="deleteUser(user)"
                                                title="Eliminar usuario"
                                                class="text-red-600 hover:text-red-900 bg-red-50 hover:bg-red-100 p-2 rounded-lg transition-colors"
                                            >
                                                <svg
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
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal para Crear/Editar -->
        <Modal :show="showingModal" @close="closeModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-slate-900 mb-6">
                    {{ editing ? "Editar Usuario" : "Nuevo Usuario" }}
                </h2>

                <form @submit.prevent="submit" class="space-y-6">
                    <div>
                        <InputLabel for="name" value="Nombre Completo" />
                        <TextInput
                            id="name"
                            v-model="form.name"
                            type="text"
                            class="mt-1 block w-full"
                            required
                        />
                        <InputError :message="form.errors.name" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel for="email" value="Correo Electrónico" />
                        <TextInput
                            id="email"
                            v-model="form.email"
                            type="email"
                            class="mt-1 block w-full"
                            required
                        />
                        <InputError :message="form.errors.email" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel for="role" value="Rol en el Sistema" />
                        <select
                            id="role"
                            v-model="form.role"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            required
                        >
                            <option
                                v-for="role in props.roles"
                                :key="role"
                                :value="role"
                            >
                                {{ role }}
                            </option>
                        </select>
                        <InputError :message="form.errors.role" class="mt-2" />
                    </div>

                    <div class="space-y-3 bg-slate-50 p-4 rounded-xl border border-slate-200">
                        <div class="flex items-center gap-3">
                            <input
                                id="is_active"
                                v-model="form.is_active"
                                type="checkbox"
                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 w-5 h-5"
                            />
                            <label for="is_active" class="text-sm font-medium text-slate-700 cursor-pointer">
                                <strong>Cuenta activa en el sistema</strong> (Permitir inicio de sesión y firma de documentos)
                            </label>
                        </div>
                        <div class="flex items-center gap-3">
                            <input
                                id="must_change_password"
                                v-model="form.must_change_password"
                                type="checkbox"
                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 w-5 h-5"
                            />
                            <label for="must_change_password" class="text-sm font-medium text-slate-700 cursor-pointer">
                                <strong>Pedir cambio de contraseña</strong> (Exigir al usuario cambiar su clave en su próximo inicio de sesión)
                            </label>
                        </div>
                    </div>

                    <!-- Campos de contraseña (requeridos al crear, opcionales al editar) -->
                    <div>
                        <InputLabel
                            for="password"
                            :value="
                                editing
                                    ? 'Nueva Contraseña (Dejar en blanco para no cambiar)'
                                    : 'Contraseña'
                            "
                        />
                        <TextInput
                            id="password"
                            v-model="form.password"
                            type="password"
                            class="mt-1 block w-full"
                            :required="!editing"
                        />
                        <InputError
                            :message="form.errors.password"
                            class="mt-2"
                        />
                    </div>

                    <div>
                        <InputLabel
                            for="password_confirmation"
                            value="Confirmar Contraseña"
                        />
                        <TextInput
                            id="password_confirmation"
                            v-model="form.password_confirmation"
                            type="password"
                            class="mt-1 block w-full"
                            :required="!editing"
                        />
                        <InputError
                            :message="form.errors.password_confirmation"
                            class="mt-2"
                        />
                    </div>

                    <div class="mt-6 flex justify-end">
                        <button
                            type="button"
                            @click="closeModal"
                            class="mr-3 px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#024283]"
                        >
                            Cancelar
                        </button>
                        <button
                            type="submit"
                            class="px-4 py-2 bg-[#024283] border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-[#003a7d] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#024283]"
                            :disabled="form.processing"
                        >
                            {{ editing ? "Guardar Cambios" : "Crear Usuario" }}
                        </button>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Modal Estilizado de Confirmación para Acciones -->
        <Modal :show="confirmingActionModal" @close="closeConfirmModal" maxWidth="md">
            <div class="p-6">
                <div class="flex items-center gap-4 mb-4">
                    <div
                        class="flex-shrink-0 w-12 h-12 rounded-full flex items-center justify-center"
                        :class="{
                            'bg-amber-100 text-amber-600': confirmActionData.iconType === 'amber',
                            'bg-purple-100 text-purple-600': confirmActionData.iconType === 'purple',
                            'bg-red-100 text-red-600': confirmActionData.iconType === 'red',
                            'bg-emerald-100 text-emerald-600': confirmActionData.iconType === 'emerald'
                        }"
                    >
                        <svg v-if="confirmActionData.iconType === 'amber'" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <svg v-else-if="confirmActionData.iconType === 'purple'" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                        </svg>
                        <svg v-else-if="confirmActionData.iconType === 'red'" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-slate-800">
                        {{ confirmActionData.title }}
                    </h3>
                </div>

                <p class="text-sm text-slate-600 mb-6 leading-relaxed">
                    {{ confirmActionData.message }}
                </p>

                <div class="flex justify-end gap-3">
                    <button
                        type="button"
                        @click="closeConfirmModal"
                        class="px-4 py-2 bg-white border border-slate-300 rounded-lg text-sm font-semibold text-slate-700 hover:bg-slate-50 transition cursor-pointer"
                    >
                        Cancelar
                    </button>
                    <button
                        type="button"
                        @click="executeConfirmAction"
                        :class="confirmActionData.confirmClass"
                        class="px-4 py-2 border border-transparent rounded-lg text-sm font-semibold transition cursor-pointer shadow-sm"
                    >
                        {{ confirmActionData.confirmText }}
                    </button>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
