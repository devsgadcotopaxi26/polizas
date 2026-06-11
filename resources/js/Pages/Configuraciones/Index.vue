<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, useForm } from "@inertiajs/vue3";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";

const props = defineProps({
    siguienteMinimoConfigurado: Number,
    ultimoOficioGenerado: Number,
    siguienteCalculado: Number,
    anio: Number,
    secuenciasPolizas: Object,
    categoriasLabels: Object,
    mailConfig: Object,
    diasAnticipacion: Number,
});

const form = useForm({
    siguiente_numero: props.siguienteCalculado,
});

const submit = () => {
    form.post(route("configuracion.oficios"), {
        preserveScroll: true,
        onSuccess: () => {
            // Se actualiza el formulario con el nuevo siguiente calculado si todo salió bien
            if (!form.hasErrors) {
                form.siguiente_numero = props.siguienteCalculado;
            }
        },
    });
};

const formPolizas = useForm({
    categoria: "",
    siguiente_numero: "",
});

const submitPoliza = (categoria) => {
    const sec = props.secuenciasPolizas[categoria];
    formPolizas.categoria = categoria;

    // Si el usuario borra y deja en blanco enviamos 1, sino agarramos lo de formPolizas.siguiente_numero que vamos a bindiar

    formPolizas.post(route("configuracion.polizas"), {
        preserveScroll: true,
        onSuccess: () => {
            // Limpiar después de éxito para que los inputs queden actualizados con la view
            formPolizas.reset();
        },
    });
};

// Necesitamos mantener un estado local temporal para cada input de póliza para no afectar otros
import { ref } from "vue";
const inputPolizas = ref({});

Object.keys(props.secuenciasPolizas || {}).forEach((cat) => {
    inputPolizas.value[cat] = props.secuenciasPolizas[cat].siguiente;
});

const formMail = useForm({
    mail_host: props.mailConfig?.mail_host || '',
    mail_port: props.mailConfig?.mail_port || '',
    mail_username: props.mailConfig?.mail_username || '',
    mail_password: props.mailConfig?.mail_password || '',
    mail_encryption: props.mailConfig?.mail_encryption || '',
    mail_from_address: props.mailConfig?.mail_from_address || '',
    mail_from_name: props.mailConfig?.mail_from_name || '',
});

const submitMail = () => {
    formMail.post(route("configuracion.correo"), {
        preserveScroll: true,
    });
};

const formDias = useForm({
    dias: props.diasAnticipacion ?? 8,
});

const submitDias = () => {
    formDias.post(route("configuracion.dias_anticipacion"), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Configuración del Sistema" />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="text-xl font-bold leading-tight text-brand-blue uppercase"
            >
                ⚙️ Configuración Global del Sistema
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Tarjeta de Configuración de Secuencias -->
                <div
                    class="bg-white overflow-hidden shadow-xl sm:rounded-2xl border border-slate-100"
                >
                    <div class="p-8 sm:p-10">
                        <div class="flex items-center gap-4 mb-6">
                            <div
                                class="p-3 bg-indigo-50 text-indigo-600 rounded-xl"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="w-8 h-8"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"
                                    />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-2xl font-bold text-slate-800">
                                    Secuencia de Oficios ({{ props.anio }})
                                </h3>
                                <p class="text-sm text-slate-500 mt-1">
                                    Controla el número correlativo con el que se
                                    generará el próximo oficio oficial. Útil
                                    para enmendar saltos o vacíos en la
                                    numeración.
                                </p>
                            </div>
                        </div>

                        <!-- Panel de información actual -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                            <div
                                class="bg-slate-50 p-6 rounded-2xl border border-slate-100 flex justify-between items-center"
                            >
                                <div>
                                    <p
                                        class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1"
                                    >
                                        Último Creado
                                    </p>
                                    <p
                                        class="text-3xl font-black text-slate-700"
                                    >
                                        N° {{ props.ultimoOficioGenerado || 0 }}
                                    </p>
                                </div>
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-10 w-10 text-slate-300"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"
                                    />
                                </svg>
                            </div>

                            <div
                                class="bg-brand-blue/5 p-6 rounded-2xl border border-brand-blue/10 flex justify-between items-center"
                            >
                                <div>
                                    <p
                                        class="text-xs font-bold text-brand-blue/70 uppercase tracking-widest mb-1"
                                    >
                                        Próximo a Generar
                                    </p>
                                    <p
                                        class="text-3xl font-black text-brand-blue"
                                    >
                                        N° {{ props.siguienteCalculado }}
                                    </p>
                                </div>
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-10 w-10 text-brand-blue/30"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M13 10V3L4 14h7v7l9-11h-7z"
                                    />
                                </svg>
                            </div>
                        </div>

                        <!-- Formulario de ajuste -->
                        <form
                            @submit.prevent="submit"
                            class="bg-white border border-slate-200 shadow-[0_8px_30px_rgb(0,0,0,0.04)] rounded-2xl p-6 lg:p-8"
                        >
                            <div
                                class="flex flex-col md:flex-row gap-6 items-end"
                            >
                                <div class="flex-1 w-full">
                                    <InputLabel
                                        for="siguiente_numero"
                                        value="Configurar siguiente número"
                                        class="text-sm font-bold text-slate-700"
                                    />
                                    <p class="text-xs text-slate-500 mb-3">
                                        Ingresa un número mayor al último credo
                                        (N° {{ props.ultimoOficioGenerado }})
                                        para forzar un salto en la secuencia.
                                    </p>
                                    <TextInput
                                        id="siguiente_numero"
                                        type="number"
                                        class="mt-1 block w-full text-lg font-bold !py-3"
                                        v-model="form.siguiente_numero"
                                        required
                                        :min="props.ultimoOficioGenerado + 1"
                                    />
                                    <InputError
                                        class="mt-2"
                                        :message="form.errors.siguiente_numero"
                                    />
                                </div>
                                <PrimaryButton
                                    class="!py-3.5 !px-8 text-base shrink-0 mb-1"
                                    :class="{ 'opacity-25': form.processing }"
                                    :disabled="form.processing"
                                >
                                    Actualizar Secuencia
                                </PrimaryButton>
                            </div>

                            <div
                                class="mt-6 p-4 bg-amber-50 rounded-xl border border-amber-100 flex items-start gap-3"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-6 w-6 text-amber-500 shrink-0 mt-0.5"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                                    />
                                </svg>
                                <div>
                                    <p class="text-sm font-bold text-amber-800">
                                        Cuidado con los saltos excesivos
                                    </p>
                                    <p class="text-xs text-amber-700 mt-0.5">
                                        Forzar estrepitosamente la secuencia
                                        dejará números de oficios en blanco
                                        (inexistentes) que no podrán recuperarse
                                        automáticamente.
                                    </p>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Tarjeta de Configuración de Secuencias de Pólizas -->
                <div
                    class="mt-8 bg-white overflow-hidden shadow-xl sm:rounded-2xl border border-slate-100"
                >
                    <div class="p-8 sm:p-10">
                        <div class="flex items-center gap-4 mb-6">
                            <div
                                class="p-3 bg-emerald-50 text-emerald-600 rounded-xl"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="w-8 h-8"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"
                                    />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-2xl font-bold text-slate-800">
                                    Secuencia de Códigos de Pólizas
                                </h3>
                                <p class="text-sm text-slate-500 mt-1">
                                    Asigna un salto a la base con la que
                                    iniciarán los códigos numéricos por cada
                                    categoría de póliza.
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-6">
                            <div
                                v-for="(
                                    secuencia, categoria
                                ) in secuenciasPolizas"
                                :key="categoria"
                                class="bg-slate-50 border border-slate-200 shadow-[0_4px_20px_rgb(0,0,0,0.03)] rounded-2xl p-6"
                            >
                                <form @submit.prevent="submitPoliza(categoria)">
                                    <h4
                                        class="text-xl font-bold text-slate-700 uppercase tracking-wide mb-4"
                                    >
                                        {{ secuencia.label }}
                                    </h4>

                                    <div
                                        class="flex flex-col md:flex-row gap-6 items-center"
                                    >
                                        <div
                                            class="flex-1 w-full bg-white p-4 rounded-xl border border-slate-100 shadow-sm flex items-center justify-between"
                                        >
                                            <div>
                                                <p
                                                    class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1"
                                                >
                                                    Último Código Generado
                                                </p>
                                                <p
                                                    class="text-2xl font-black text-slate-600"
                                                >
                                                    N° {{ secuencia.ultimo }}
                                                </p>
                                            </div>
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                class="h-8 w-8 text-slate-200"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"
                                                />
                                            </svg>
                                        </div>

                                        <div class="flex-1 w-full">
                                            <InputLabel
                                                :for="'poliza_' + categoria"
                                                value="Siguiente a Generar"
                                                class="text-sm font-bold text-slate-700"
                                            />
                                            <div
                                                class="flex gap-2 items-start mt-1"
                                            >
                                                <div class="flex-1">
                                                    <TextInput
                                                        :id="
                                                            'poliza_' +
                                                            categoria
                                                        "
                                                        type="number"
                                                        class="block w-full text-lg font-bold !py-3"
                                                        v-model="
                                                            inputPolizas[
                                                                categoria
                                                            ]
                                                        "
                                                        @input="
                                                            formPolizas.siguiente_numero =
                                                                inputPolizas[
                                                                    categoria
                                                                ]
                                                        "
                                                        required
                                                        :min="
                                                            secuencia.ultimo + 1
                                                        "
                                                    />
                                                    <InputError
                                                        v-if="
                                                            formPolizas.categoria ===
                                                            categoria
                                                        "
                                                        class="mt-2"
                                                        :message="
                                                            formPolizas.errors
                                                                .siguiente_numero
                                                        "
                                                    />
                                                </div>
                                                <PrimaryButton
                                                    class="!py-3.5 !px-6 text-base shrink-0 border-emerald-600 bg-emerald-600 hover:bg-emerald-700 focus:bg-emerald-700 active:bg-emerald-800"
                                                    :class="{
                                                        'opacity-25':
                                                            formPolizas.processing,
                                                    }"
                                                    :disabled="
                                                        formPolizas.processing
                                                    "
                                                    @click="
                                                        formPolizas.siguiente_numero =
                                                            inputPolizas[
                                                                categoria
                                                            ]
                                                    "
                                                >
                                                    Fijar
                                                </PrimaryButton>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tarjeta de Días de Anticipación para Generación de Oficios -->
                <div class="mt-8 bg-white overflow-hidden shadow-xl sm:rounded-2xl border border-slate-100">
                    <div class="p-8 sm:p-10">
                        <div class="flex items-center gap-4 mb-6">
                            <div class="p-3 bg-amber-50 text-amber-600 rounded-xl">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-2xl font-bold text-slate-800">Anticipación para Generación de Oficios</h3>
                                <p class="text-sm text-slate-500 mt-1">
                                    Define cuántos días antes del vencimiento el sistema generará automáticamente el oficio de renovación.
                                    Aplica únicamente a pólizas de <strong>Obras</strong> y <strong>Proveedores</strong>.
                                </p>
                            </div>
                        </div>

                        <div class="bg-slate-50 border border-slate-200 rounded-2xl p-6">
                            <div class="flex flex-col md:flex-row gap-6 items-center mb-6">
                                <div class="flex-1 w-full bg-white p-4 rounded-xl border border-slate-100 shadow-sm flex items-center justify-between">
                                    <div>
                                        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Configuración Actual</p>
                                        <p class="text-3xl font-black text-amber-600">{{ props.diasAnticipacion }} días</p>
                                    </div>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-slate-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div class="flex-1 w-full bg-amber-50 p-4 rounded-xl border border-amber-100 text-sm text-amber-800">
                                    <p class="font-bold mb-1">¿Cómo funciona?</p>
                                    <p>El cron diario buscará pólizas que venzan en los próximos <strong>{{ formDias.dias }}</strong> día(s) y generará su oficio base automáticamente para que el Gestor y el Tesorero puedan firmarlo.</p>
                                </div>
                            </div>

                            <form @submit.prevent="submitDias" class="flex flex-col md:flex-row gap-4 items-end">
                                <div class="flex-1 w-full">
                                    <InputLabel for="dias_anticipacion" value="Nuevo valor (días)" class="text-sm font-bold text-slate-700" />
                                    <p class="text-xs text-slate-500 mb-2">Rango permitido: 1 a 60 días.</p>
                                    <TextInput
                                        id="dias_anticipacion"
                                        type="number"
                                        class="mt-1 block w-full text-lg font-bold !py-3"
                                        v-model="formDias.dias"
                                        required
                                        min="1"
                                        max="60"
                                    />
                                    <InputError class="mt-2" :message="formDias.errors.dias" />
                                </div>
                                <PrimaryButton
                                    class="!py-3.5 !px-8 text-base shrink-0 mb-1 bg-amber-600 border-amber-600 hover:bg-amber-700 focus:bg-amber-700 active:bg-amber-800"
                                    :class="{ 'opacity-25': formDias.processing }"
                                    :disabled="formDias.processing"
                                >
                                    Guardar
                                </PrimaryButton>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Tarjeta de Configuración de Correo Electrónico -->
                <div class="mt-8 bg-white overflow-hidden shadow-xl sm:rounded-2xl border border-slate-100">
                    <div class="p-8 sm:p-10">
                        <div class="flex items-center gap-4 mb-6">
                            <div class="p-3 bg-red-50 text-red-600 rounded-xl">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-2xl font-bold text-slate-800">Credenciales de Correo (SMTP)</h3>
                                <p class="text-sm text-slate-500 mt-1">Configura la cuenta institucional remitente y la clave de aplicación desde donde se despacharán los oficios.</p>
                            </div>
                        </div>

                        <form @submit.prevent="submitMail" class="bg-slate-50 border border-slate-200 shadow-[0_4px_20px_rgb(0,0,0,0.03)] rounded-2xl p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <InputLabel for="mail_username" value="Correo Emisor (Username)" />
                                    <TextInput id="mail_username" type="email" class="mt-1 block w-full" v-model="formMail.mail_username" required placeholder="ejemplo@cotopaxi.gob.ec" />
                                    <InputError class="mt-2" :message="formMail.errors.mail_username" />
                                </div>
                                <div>
                                    <InputLabel for="mail_password" value="Contraseña / App Password" />
                                    <TextInput id="mail_password" type="password" class="mt-1 block w-full" v-model="formMail.mail_password" required placeholder="Contraseña de aplicación" />
                                    <InputError class="mt-2" :message="formMail.errors.mail_password" />
                                </div>
                                <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <InputLabel for="mail_from_name" value="Nombre del Remitente" />
                                        <TextInput id="mail_from_name" type="text" class="mt-1 block w-full" v-model="formMail.mail_from_name" required placeholder="SISTEMA DE PÓLIZAS GADPC" />
                                        <InputError class="mt-2" :message="formMail.errors.mail_from_name" />
                                    </div>
                                    <div class="hidden">
                                        <InputLabel for="mail_from_address" value="Dirección Remitente (Alias)" />
                                        <TextInput id="mail_from_address" type="email" class="mt-1 block w-full bg-slate-100" v-model="formMail.mail_from_address" required readonly/>
                                    </div>
                                </div>
                                
                                <div class="md:col-span-2 pt-4 border-t border-slate-200 mt-2">
                                    <p class="text-sm font-bold text-slate-700 mb-4">Parámetros Técnicos del Servidor (Avanzado)</p>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                        <div>
                                            <InputLabel for="mail_host" value="Host SMTP" />
                                            <TextInput id="mail_host" type="text" class="mt-1 block w-full" v-model="formMail.mail_host" required />
                                            <InputError class="mt-2" :message="formMail.errors.mail_host" />
                                        </div>
                                        <div>
                                            <InputLabel for="mail_port" value="Puerto" />
                                            <TextInput id="mail_port" type="number" class="mt-1 block w-full" v-model="formMail.mail_port" required />
                                            <InputError class="mt-2" :message="formMail.errors.mail_port" />
                                        </div>
                                        <div>
                                            <InputLabel for="mail_encryption" value="Cifrado" />
                                            <select id="mail_encryption" v-model="formMail.mail_encryption" class="mt-1 block w-full border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                                <option value="tls">TLS</option>
                                                <option value="ssl">SSL</option>
                                                <option value="null">Ninguno</option>
                                            </select>
                                            <InputError class="mt-2" :message="formMail.errors.mail_encryption" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex items-center justify-end mt-6 pt-6 border-t border-slate-200">
                                <PrimaryButton class="!py-3 !px-8 text-base bg-red-600 border-red-600 hover:bg-red-700 focus:bg-red-700 active:bg-red-800" :class="{ 'opacity-25': formMail.processing }" :disabled="formMail.processing" @click="formMail.mail_from_address = formMail.mail_username">
                                    Modificar Credenciales
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
