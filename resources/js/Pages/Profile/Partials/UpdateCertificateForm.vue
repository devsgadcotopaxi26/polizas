<script setup>
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import { useForm, usePage } from "@inertiajs/vue3";
import { ref } from "vue";

const user = usePage().props.auth.user;
const fileInput = ref(null);

const form = useForm({
    certificado: null,
});

const uploadCertificate = () => {
    form.post(route("profile.certificate.update"), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset("certificado");
            if (fileInput.value) {
                fileInput.value.value = null;
            }
        },
    });
};

const handleFileChange = (e) => {
    form.certificado = e.target.files[0];
};
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900">
                Certificado de Firma Electrónica (.p12 / .pfx)
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                Sube tu certificado de firma electrónica para poder firmar
                documentos digitales (Ej. Oficios de Renovación).
                <br /><strong>Nota de Seguridad:</strong> Tu certificado se
                almacena encriptado en el servidor y tu contraseña
                <strong>nunca se guarda</strong> en el sistema; te la
                solicitaremos cada vez que firmes.
            </p>
        </header>

        <form @submit.prevent="uploadCertificate" class="mt-6 space-y-6">
            <div
                v-if="user.certificado_path"
                class="p-4 mb-4 text-sm text-emerald-800 rounded-lg bg-emerald-50 flex items-center gap-3"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="w-6 h-6 text-emerald-600"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"
                    />
                </svg>
                <div>
                    <span class="font-medium">Certificado Configurado:</span> Ya
                    tienes un certificado digital subido. Puedes subir uno nuevo
                    para reemplazarlo.
                </div>
            </div>

            <div>
                <InputLabel
                    for="certificado"
                    value="Archivo del Certificado (.p12)"
                />

                <input
                    id="certificado"
                    ref="fileInput"
                    type="file"
                    class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer"
                    @change="handleFileChange"
                    required
                />

                <InputError :message="form.errors.certificado" class="mt-2" />
            </div>

            <div class="flex items-center gap-4">
                <PrimaryButton :disabled="form.processing"
                    >Subir Certificado</PrimaryButton
                >

                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p
                        v-if="form.recentlySuccessful"
                        class="text-sm text-gray-600"
                    >
                        Guardado.
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>
