<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, useForm, router } from "@inertiajs/vue3";
import Modal from "@/Components/Modal.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";
import axios from "axios";
import { ref, markRaw } from "vue";
import * as pdfjsLib from "pdfjs-dist";
import pdfWorker from "pdfjs-dist/build/pdf.worker.mjs?url";

// Configurar el worker de PDF.js
pdfjsLib.GlobalWorkerOptions.workerSrc = pdfWorker;

const isContractModalOpen = ref(false);

const handleGenerateClick = () => {
    if (!props.poliza.oficio_path) {
        const onFocus = () => {
            router.reload({ only: ["poliza"] });
            window.removeEventListener("focus", onFocus);
        };
        setTimeout(() => {
            window.addEventListener("focus", onFocus);
        }, 500);
    }
};

const openContractModal = () => {
    isContractModalOpen.value = true;
};

const closeContractModal = () => {
    isContractModalOpen.value = false;
};

const props = defineProps({
    poliza: Object,
    renovacion_de: Object,
    renovacion_hecha: Object,
    historial_renovaciones: Array,
    contador_renovaciones: Number,
});

// === MODAL DE ENVÍO DE EMAIL ===
const isEmailModalOpen = ref(false);
const isSendingEmail = ref(false);
const emailSuccess = ref("");
const emailError = ref("");

const emailForm = ref({
    asunto: "",
    cuerpo: "",
    to: [],
    cc: [],
});

const openEmailModal = () => {
    const aseg = props.poliza.aseguradora;
    const esSegundo = !!props.poliza.oficio_email_1_at;
    const tipoAviso = esSegundo ? 'Segundo Aviso' : 'Primer Aviso';
    emailForm.value = {
        asunto: `[${tipoAviso}] Oficio de Renovación de Póliza N.° ${props.poliza.numero_poliza}`,
        cuerpo: esSegundo
            ? `Estimados Señores:\n\nLes recordamos que la póliza N.° ${props.poliza.numero_poliza} vence HOY. Adjunto encontrarán el oficio de renovación debidamente firmado.\n\nAtentamente,\nGestoría de Tesorería\nPrefectura de Cotopaxi`
            : `Estimados Señores:\n\nAdjunto el oficio de renovación de la póliza N.° ${props.poliza.numero_poliza}, debidamente firmado.\n\nAtentamente,\nGestoría de Tesorería\nPrefectura de Cotopaxi`,
        to: aseg && aseg.correo1 ? [aseg.correo1] : [],
        cc: [],
    };
    emailSuccess.value = "";
    emailError.value = "";
    isEmailModalOpen.value = true;
};

const closeEmailModal = () => {
    isEmailModalOpen.value = false;
};

const getCorreosAseguradora = () => {
    const aseg = props.poliza.sucursal;
    if (!aseg) return [];
    return [
        aseg.correo1,
        aseg.correo2,
        aseg.correo3,
        aseg.correo4,
        aseg.correo5,
        aseg.correo6,
    ].filter((c) => c && c.trim() !== "");
};

const sendEmail = async () => {
    if (emailForm.value.to.length === 0) {
        emailError.value =
            "Debe seleccionar al menos un destinatario principal.";
        return;
    }
    isSendingEmail.value = true;
    emailError.value = "";
    emailSuccess.value = "";
    try {
        await axios.post(
            route("polizas.enviar_oficio", props.poliza.id),
            emailForm.value,
        );
        emailSuccess.value = "✅ Correo enviado exitosamente.";
        setTimeout(() => {
            closeEmailModal();
            router.reload({ only: ["poliza"] });
        }, 1500);
    } catch (err) {
        emailError.value =
            err.response?.data?.message ?? "Error al enviar el correo.";
    } finally {
        isSendingEmail.value = false;
    }
};

const isSignModalOpen = ref(false);
const isSigning = ref(false);
const passwordError = ref("");
const passwordInput = ref(null);

const signForm = useForm({
    password_certificado: "",
});

const pdfCanvas = ref(null);
const currentPageNum = ref(1);
const totalPages = ref(0);
const pdfDoc = ref(null);
const renderTask = ref(null);
const sigX = ref(null);
const sigY = ref(null);
const sigPage = ref(null);
const pdfRenderScale = ref(1.2);
const isLoadingPdf = ref(false);

const loadPdf = async (url) => {
    isLoadingPdf.value = true;
    try {
        const loadingTask = pdfjsLib.getDocument(url);
        const doc = await loadingTask.promise;
        pdfDoc.value = markRaw(doc);
        totalPages.value = doc.numPages;
        renderPage(1);
    } catch (e) {
        console.error("Error cargando PDF para vista previa:", e);
    } finally {
        isLoadingPdf.value = false;
    }
};

const renderPage = async (num) => {
    if (!pdfDoc.value) return;
    const page = await pdfDoc.value.getPage(num);
    const viewport = page.getViewport({ scale: pdfRenderScale.value });
    const canvas = pdfCanvas.value;
    if (!canvas) return;

    const ctx = canvas.getContext("2d");
    canvas.height = viewport.height;
    canvas.width = viewport.width;

    if (renderTask.value) {
        await renderTask.value.promise.catch(() => {});
    }

    renderTask.value = page.render({
        canvasContext: ctx,
        viewport: viewport,
    });
};

const prevPage = () => {
    if (currentPageNum.value <= 1) return;
    currentPageNum.value--;
    renderPage(currentPageNum.value);
};

const nextPage = () => {
    if (currentPageNum.value >= totalPages.value) return;
    currentPageNum.value++;
    renderPage(currentPageNum.value);
};

const handleCanvasClick = (event) => {
    const canvas = pdfCanvas.value;
    const rect = canvas.getBoundingClientRect();
    const x = event.clientX - rect.left;
    const y = event.clientY - rect.top;

    // Convert pixels to PDF points (1 pt = 1/72 inch, viewport adjusts them)
    // pyHanko takes origin at bottom-left, so Y needs to be inverted from PDF height points
    // First convert to PDF points:
    const pdfX = x / pdfRenderScale.value;
    const pdfY = y / pdfRenderScale.value;
    const pdfHeight = canvas.height / pdfRenderScale.value;

    sigX.value = pdfX;
    // Invert Y for pyHanko
    sigY.value = pdfHeight - pdfY;
    sigPage.value = currentPageNum.value;
};

const signingContext = ref("oficio"); // 'oficio' o 'renovacion'

const openSignModal = (context = "oficio") => {
    signingContext.value = context;
    isSignModalOpen.value = true;
    sigX.value = null;
    sigY.value = null;
    sigPage.value = null;
    currentPageNum.value = 1;

    setTimeout(() => {
        if (passwordInput.value) {
            passwordInput.value.focus();
        }
    }, 100);

    // Decidir URL del PDF según el contexto
    const url =
        context === "oficio"
            ? route("polizas.oficio_pdf", props.poliza.id)
            : route("polizas.renovacion_pdf", props.poliza.id);

    loadPdf(url);
};

const closeSignModal = () => {
    isSignModalOpen.value = false;
    signForm.reset();
    passwordError.value = "";
    sigX.value = null;
    sigY.value = null;
    sigPage.value = null;
    pdfDoc.value = null;
    signingContext.value = "oficio";
};

const isRenewModalOpen = ref(false);
const renewForm = useForm({
    valor_asegurado: props.poliza.valor_asegurado,
    fecha_inicio: new Date().toISOString().split("T")[0],
    fecha_vencimiento: new Date(
        new Date().setFullYear(new Date().getFullYear() + 1),
    )
        .toISOString()
        .split("T")[0],
    observaciones: "",
    archivo_renovacion: null,
});

const openRenewModal = () => {
    renewForm.valor_asegurado = props.poliza.valor_asegurado;
    renewForm.fecha_inicio = new Date().toISOString().split("T")[0];
    renewForm.fecha_vencimiento = new Date(
        new Date().setFullYear(new Date().getFullYear() + 1),
    )
        .toISOString()
        .split("T")[0];
    renewForm.observaciones = "";
    renewForm.archivo_renovacion = null;
    isRenewModalOpen.value = true;
};

const closeRenewModal = () => {
    isRenewModalOpen.value = false;
    renewForm.reset();
};

const submitRenewal = () => {
    renewForm.post(route("polizas.renovar", props.poliza.id), {
        onSuccess: () => closeRenewModal(),
        forceFormData: true, // Necesario para subir archivos
    });
};

const handleRenewalFileUpload = (e) => {
    renewForm.archivo_renovacion = e.target.files[0];
};

const submitSignature = async () => {
    if (!signForm.password_certificado || sigX.value === null) {
        passwordError.value =
            sigX.value === null
                ? "Por favor haz clic en el PDF para ubicar tu firma."
                : "";
        return;
    }

    isSigning.value = true;
    passwordError.value = "";

    try {
        const postRoute =
            signingContext.value === "oficio"
                ? route("polizas.oficio_pdf", props.poliza.id)
                : route("polizas.renovacion_firmar", props.poliza.id);

        // El oficio devuelve el blob, la renovación devuelve json
        const isJson = signingContext.value === "renovacion";

        const response = await axios.post(
            postRoute,
            {
                password_certificado: signForm.password_certificado,
                sig_x: Math.round(sigX.value),
                sig_y: Math.round(sigY.value),
                sig_page: sigPage.value,
            },
            {
                responseType: "blob",
                headers: {
                    Accept: "application/json, application/pdf",
                },
            },
        );

        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement("a");
        link.href = url;
        const fileName =
            signingContext.value === "oficio"
                ? `Oficio_Renovacion_${props.poliza.numero_poliza}_Firmado.pdf`
                : `Renovacion_${props.poliza.numero_poliza}_Firmada.pdf`;

        link.setAttribute("download", fileName);
        document.body.appendChild(link);
        link.click();
        link.remove();
        window.URL.revokeObjectURL(url);

        closeSignModal();
        router.reload({ only: ["poliza", "renovacion_de"] });
    } catch (error) {
        if (error.response && error.response.data) {
            if (error.response.data instanceof Blob) {
                const reader = new FileReader();
                reader.onload = () => {
                    try {
                        const result = JSON.parse(reader.result);
                        passwordError.value =
                            result.message ||
                            "Contraseña incorrecta o error al firmar.";
                    } catch (e) {
                        passwordError.value = "Error desconocido del servidor.";
                    }
                };
                reader.readAsText(error.response.data);
            } else {
                // Es JSON y ya fue decodificado por Axios
                passwordError.value =
                    error.response.data.message ||
                    "Error al procesar la firma.";
            }
        } else {
            passwordError.value = "Error de red o servidor no disponible.";
        }
    } finally {
        isSigning.value = false;
    }
};

const getStatusColor = (status) => {
    switch (status) {
        case "vigente":
            return "bg-emerald-100 text-emerald-800 border-emerald-200";
        case "acta_provisional":
            return "bg-blue-100 text-blue-800 border-blue-200";
        case "acta_definitiva":
            return "bg-amber-100 text-amber-800 border-amber-200";
        case "liquidada":
            return "bg-rose-100 text-rose-800 border-rose-200";
        case "renovada":
            return "bg-purple-100 text-purple-800 border-purple-200";
        case "vencida":
            return "bg-red-100 text-red-800 border-red-200";
        default:
            return "bg-gray-100 text-gray-800 border-gray-200";
    }
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString("es-EC", {
        year: "numeric",
        month: "long",
        day: "numeric",
    });
};

// === ELIMINAR RENOVACIÓN ===
const renovacionPendienteEliminar = ref(null);

const confirmarEliminarRenovacion = (renovacion) => {
    renovacionPendienteEliminar.value = renovacion;
};

const cancelarEliminarRenovacion = () => {
    renovacionPendienteEliminar.value = null;
};

const ejecutarEliminarRenovacion = () => {
    if (!renovacionPendienteEliminar.value) return;
    router.delete(
        route('polizas.eliminar_renovacion', renovacionPendienteEliminar.value.id),
        {
            onSuccess: () => {
                renovacionPendienteEliminar.value = null;
            },
            onError: () => {
                renovacionPendienteEliminar.value = null;
            },
        }
    );
};
</script>

<template>
    <Head :title="'Póliza ' + poliza.numero_poliza" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-4">
                    <Link
                        :href="route('polizas.index')"
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
                    <h2
                        class="font-semibold text-xl text-slate-800 leading-tight"
                    >
                        Detalles de Póliza
                    </h2>
                </div>
                <div class="flex gap-3">
                    <!-- Botón de Oficio Base (Oculto para Asesor y Gestor Tesorería Ambiente temporalmente) -->
                    <a
                        v-if="
                            !(
                                $page.props.auth.user.roles &&
                                ($page.props.auth.user.roles.includes('Prefecto/a') ||
                                 $page.props.auth.user.roles.includes('Gestor Tesorería Ambiente'))
                            )
                        "
                        :href="route('polizas.oficio_pdf', { poliza: poliza.id, t: new Date().getTime() })"
                        target="_blank"
                        @click="handleGenerateClick"
                        class="inline-flex items-center px-4 py-2 bg-slate-600 border border-transparent rounded-xl font-bold text-xs text-white uppercase tracking-widest hover:bg-slate-700 transition shadow-sm"
                    >
                        📄
                        {{
                            poliza.oficio_path
                                ? "Ver Oficio Actual"
                                : "Generar Oficio Base"
                        }}
                    </a>

                    <!-- Botón para Regenerar Oficio Base con información corregida (Solo Gestora, sin firmas) -->
                    <Link
                        v-if="
                            poliza.oficio_path &&
                            !poliza.oficio_firmado_gestor &&
                            !poliza.oficio_firmado_tesorero &&
                            !(
                                $page.props.auth.user.roles &&
                                ($page.props.auth.user.roles.includes('Prefecto/a') ||
                                 $page.props.auth.user.roles.includes('Gestor Tesorería Ambiente'))
                            )
                        "
                        :href="route('polizas.regenerar_oficio', poliza.id)"
                        method="post"
                        as="button"
                        class="inline-flex items-center px-4 py-2 bg-amber-500 border border-transparent rounded-xl font-bold text-xs text-white uppercase tracking-widest hover:bg-amber-600 transition shadow-sm"
                        preserve-scroll
                    >
                        🔄 Regenerar Corregido
                    </Link>

                    <!-- Botones Condicionales de Firma basados en Rol -->
                    <button
                        v-if="
                            poliza.oficio_path &&
                            !poliza.oficio_firmado_gestor &&
                            $page.props.auth.user.roles &&
                            $page.props.auth.user.roles.includes(
                                'Gestor de Tesorería',
                            )
                        "
                        @click="openSignModal('oficio')"
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-xl font-bold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 transition shadow-sm"
                    >
                        🖋️ Firmar como Gestora
                    </button>

                    <button
                        v-if="
                            poliza.oficio_path &&
                            !poliza.oficio_firmado_tesorero &&
                            $page.props.auth.user.roles &&
                            $page.props.auth.user.roles.includes('Tesorero')
                        "
                        @click="openSignModal('oficio')"
                        class="inline-flex items-center px-4 py-2 bg-[#024283] border border-transparent rounded-xl font-bold text-xs text-white uppercase tracking-widest hover:bg-[#003a7d] transition shadow-sm"
                    >
                        🖋️ Firmar como Tesorero
                    </button>

                    <!-- Botón para Prefecto/a (para pólizas que son renovación de otra y falta firma) -->
                    <button
                        v-if="
                            renovacion_de &&
                            !renovacion_de.estado_firma_asesor &&
                            renovacion_de.archivo_renovacion &&
                            $page.props.auth.user.roles &&
                            $page.props.auth.user.roles.includes(
                                'Prefecto/a',
                            )
                        "
                        @click="openSignModal('renovacion')"
                        class="inline-flex items-center px-4 py-2 bg-emerald-700 border border-transparent rounded-xl font-bold text-xs text-white uppercase tracking-widest hover:bg-emerald-800 transition shadow-sm"
                    >
                        🖋️ Firmar Renovación como Asesor
                    </button>

                    <!-- Botón Email: Estado 1 - Ningún aviso enviado -->
                    <button
                        v-if="
                            poliza.oficio_firmado_gestor &&
                            poliza.oficio_firmado_tesorero &&
                            !poliza.oficio_email_1_at &&
                            $page.props.auth.user.roles &&
                            $page.props.auth.user.roles.includes('Gestor de Tesorería')
                        "
                        @click="openEmailModal"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-xl font-bold text-xs text-white uppercase tracking-widest hover:bg-blue-700 transition shadow-sm"
                    >
                        📧 Enviar Primer Aviso
                    </button>

                    <!-- Botón Email: Estado 2 - Segundo aviso pendiente -->
                    <button
                        v-else-if="
                            poliza.oficio_firmado_gestor &&
                            poliza.oficio_firmado_tesorero &&
                            poliza.oficio_email_1_at &&
                            !poliza.oficio_email_2_at &&
                            $page.props.auth.user.roles &&
                            $page.props.auth.user.roles.includes('Gestor de Tesorería')
                        "
                        @click="openEmailModal"
                        class="inline-flex items-center px-4 py-2 bg-amber-600 border border-transparent rounded-xl font-bold text-xs text-white uppercase tracking-widest hover:bg-amber-700 transition shadow-sm"
                    >
                        📨 Enviar Segundo Aviso
                    </button>

                    <!-- Estado 3: Ambos enviados -->
                    <span
                        v-else-if="
                            poliza.oficio_firmado_gestor &&
                            poliza.oficio_firmado_tesorero &&
                            poliza.oficio_email_1_at &&
                            poliza.oficio_email_2_at &&
                            $page.props.auth.user.roles &&
                            $page.props.auth.user.roles.includes('Gestor de Tesorería')
                        "
                        class="inline-flex items-center px-3 py-2 bg-emerald-50 border border-emerald-200 rounded-xl text-xs text-emerald-700 font-semibold"
                    >
                        ✅ Avisos enviados
                    </span>

                    <!-- Botón para ver el PDF de Renovación (si existe) -->
                    <!-- Escenario 1: Esta póliza ES la renovación (renovacion_de tiene el archivo) -->
                    <a
                        v-if="renovacion_de && renovacion_de.archivo_renovacion"
                        :href="route('polizas.renovacion_pdf', poliza.id)"
                        target="_blank"
                        class="inline-flex items-center px-4 py-2 bg-emerald-100 border border-emerald-200 rounded-xl font-bold text-xs text-emerald-700 uppercase tracking-widest hover:bg-emerald-200 transition shadow-sm"
                    >
                        📄 Ver Archivo de Renovación
                        <span
                            v-if="renovacion_de.estado_firma_asesor"
                            class="ml-1 text-[10px] bg-emerald-500 text-white px-1 rounded"
                            >FIRMADO</span
                        >
                    </a>

                    <!-- Escenario 2: Esta póliza es la ORIGINAL (sin renovacion_de propio) y tiene una renovación hecha -->
                    <a
                        v-if="
                            !renovacion_de &&
                            renovacion_hecha &&
                            renovacion_hecha.archivo_renovacion
                        "
                        :href="
                            route(
                                'polizas.renovacion_pdf',
                                renovacion_hecha.poliza_nueva_id,
                            )
                        "
                        target="_blank"
                        class="inline-flex items-center px-4 py-2 bg-emerald-100 border border-emerald-200 rounded-xl font-bold text-xs text-emerald-700 uppercase tracking-widest hover:bg-emerald-200 transition shadow-sm"
                    >
                        📄 Ver Archivo de Renovación
                        <span
                            v-if="renovacion_hecha.estado_firma_asesor"
                            class="ml-1 text-[10px] bg-emerald-500 text-white px-1 rounded"
                            >FIRMADO</span
                        >
                    </a>

                    <button
                        v-if="
                            [
                                'vigente',

                                'acta_provisional',
                                'acta_definitiva',
                            ].includes(poliza.estado) &&
                            !(
                                $page.props.auth.user.roles &&
                                $page.props.auth.user.roles.includes(
                                    'Prefecto/a',
                                )
                            )
                        "
                        @click="openRenewModal"
                        class="inline-flex items-center px-4 py-2 bg-emerald-600 border border-transparent rounded-xl font-bold text-xs text-white uppercase tracking-widest hover:bg-emerald-700 transition shadow-sm"
                    >
                        🔄 Registrar Renovación
                    </button>
                    <Link
                        v-if="
                            !(
                                $page.props.auth.user.roles &&
                                $page.props.auth.user.roles.includes(
                                    'Prefecto/a',
                                )
                            )
                        "
                        :href="route('polizas.edit', poliza.id)"
                        class="inline-flex items-center px-4 py-2 bg-white border border-slate-200 rounded-xl font-bold text-xs text-slate-700 uppercase tracking-widest hover:bg-slate-50 transition shadow-sm"
                    >
                        Editar Póliza
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12 bg-slate-50 min-h-screen">
            <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
                <!-- Alerta de Renovaciones -->
                <div v-if="(renovacion_de && renovacion_de.poliza_original) || (renovacion_hecha && renovacion_hecha.poliza_nueva)" class="mb-8">
                    <div
                        v-if="renovacion_de && renovacion_de.poliza_original"
                        class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-r-xl"
                    >
                        <div class="flex items-center">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-5 w-5 text-blue-500 mr-3"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                />
                            </svg>
                            <p class="text-sm text-blue-700">
                                Esta póliza es una renovación de
                                <Link
                                    :href="
                                        route(
                                            'polizas.show',
                                            renovacion_de.poliza_original.id,
                                        )
                                    "
                                    class="font-bold underline hover:text-blue-900"
                                >
                                    {{
                                        renovacion_de.poliza_original
                                            .numero_poliza
                                    }}
                                </Link>
                            </p>
                        </div>
                    </div>

                    <div
                        v-if="renovacion_hecha && renovacion_hecha.poliza_nueva"
                        class="bg-indigo-50 border-l-4 border-indigo-500 p-4 rounded-r-xl"
                    >
                        <div class="flex items-center">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-5 w-5 text-indigo-500 mr-3"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                                />
                            </svg>
                            <p class="text-sm text-indigo-700">
                                Esta póliza fue renovada. Nueva póliza:

                                <Link
                                    :href="
                                        route(
                                            'polizas.show',
                                            renovacion_hecha.poliza_nueva.id,
                                        )
                                    "
                                    class="font-bold underline hover:text-indigo-900"
                                >
                                    {{
                                        renovacion_hecha.poliza_nueva
                                            .numero_poliza
                                    }}
                                </Link>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Main Content -->
                    <div class="lg:col-span-2 space-y-8">
                        <div
                            class="bg-white rounded-3xl shadow-sm border border-slate-100 p-8"
                        >
                            <div class="flex justify-between items-start mb-8">
                                <div>
                                    <span
                                        :class="[
                                            'px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider border mb-4 inline-block',
                                            getStatusColor(
                                                poliza.estado_actual,
                                            ),
                                        ]"
                                    >
                                        {{
                                            poliza.estado_actual.replace(
                                                "_",
                                                " ",
                                            )
                                        }}
                                    </span>
                                    <h1
                                        class="text-3xl font-extrabold text-slate-900 flex items-center gap-3"
                                    >
                                        <span
                                            class="inline-flex items-center px-3 py-1 rounded-lg bg-slate-100 text-lg font-bold text-slate-500"
                                        >
                                            #{{ poliza.codigo }}
                                        </span>
                                        {{ poliza.numero_poliza }}
                                        <span
                                            v-if="contador_renovaciones > 0"
                                            class="ml-3 inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-purple-100 text-purple-800 border border-purple-200"
                                        >
                                            {{ contador_renovaciones }}
                                            Renovación(es)
                                        </span>
                                    </h1>
                                    <p
                                        class="text-lg text-slate-500 font-medium"
                                    >
                                        {{ poliza.categoria_poliza }} —
                                        {{ poliza.subtipo_poliza }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p
                                        class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1"
                                    >
                                        Valor Asegurado
                                    </p>
                                    <p
                                        class="text-3xl font-black text-indigo-600"
                                    >
                                        ${{
                                            parseFloat(
                                                poliza.valor_asegurado,
                                            ).toLocaleString()
                                        }}
                                    </p>
                                    <p
                                        v-if="poliza.anticipo"
                                        class="text-sm font-medium text-slate-500 mt-1"
                                    >
                                        Anticipo: ${{
                                            parseFloat(
                                                poliza.anticipo,
                                            ).toLocaleString()
                                        }}
                                    </p>
                                </div>
                            </div>

                            <div v-if="poliza.categoria_poliza === 'ambiental' && poliza.codigo_proyecto_amb" class="mb-6 px-6 py-4 bg-emerald-50/50 rounded-2xl border border-emerald-100 flex items-center justify-between shadow-sm">
                                <div class="flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-emerald-500 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                    </svg>
                                    <span class="text-sm font-bold text-emerald-800 uppercase tracking-widest">Código Proyecto Ambiental</span>
                                </div>
                                <span class="font-extrabold text-emerald-900 text-lg">{{ poliza.codigo_proyecto_amb }}</span>
                            </div>

                            <div
                                class="grid grid-cols-2 gap-8 py-8 border-y border-slate-50"
                            >
                                <div>
                                    <p
                                        class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-2"
                                    >
                                        Vigencia Desde
                                    </p>
                                    <p class="text-lg font-bold text-slate-800">
                                        {{ formatDate(poliza.fecha_inicio) }}
                                    </p>
                                </div>
                                <div>
                                    <p
                                        class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-2"
                                    >
                                        Fecha Vencimiento
                                    </p>
                                    <p class="text-lg font-bold text-rose-600">
                                        {{
                                            formatDate(poliza.fecha_vencimiento)
                                        }}
                                    </p>
                                </div>
                            </div>

                            <div class="mt-8">
                                <h4
                                    class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4"
                                >
                                    Observaciones
                                </h4>
                                <div
                                    class="bg-slate-50 rounded-2xl p-6 text-slate-700 leading-relaxed border border-slate-100"
                                >
                                    {{
                                        poliza.observaciones ||
                                        "No hay observaciones adicionales."
                                    }}
                                </div>
                            </div>
                        </div>

                        <!-- Contrato & Contratista / Operador Ambiental Section -->
                        <div v-if="poliza.categoria_poliza !== 'ambiental'"
                            class="bg-white rounded-3xl shadow-sm border border-slate-100 p-8"
                        >
                            <h3
                                class="text-lg font-bold text-slate-800 mb-6 flex items-center"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-5 w-5 mr-3 text-indigo-500"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                    />
                                </svg>
                                Contrato y Contratista
                            </h3>

                            <div v-if="poliza.contrato" class="space-y-4">
                                <div
                                    @click="openContractModal"
                                    class="flex items-center p-4 bg-indigo-50/50 rounded-2xl border border-indigo-100 cursor-pointer hover:bg-indigo-50 transition-colors duration-200 group"
                                >
                                    <div
                                        class="h-16 w-16 rounded-full bg-white flex items-center justify-center text-indigo-600 text-2xl font-black shadow-sm border border-indigo-100"
                                    >
                                        {{
                                            poliza.contrato.contratista?.nombre_cont?.charAt(
                                                0,
                                            ) || "C"
                                        }}
                                    </div>
                                    <div class="ml-6">
                                        <div class="flex items-center gap-2">
                                            <p
                                                class="text-xl font-extrabold text-slate-800 group-hover:text-indigo-700 transition-colors"
                                            >
                                                {{
                                                    poliza.contrato.contratista
                                                        ?.nombre_cont
                                                }}
                                            </p>
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                class="h-4 w-4 text-indigo-400 opacity-0 group-hover:opacity-100 transition-opacity"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"
                                                />
                                            </svg>
                                        </div>
                                        <p class="text-sm text-slate-500 mt-1">
                                            Contrato:
                                            <span class="font-semibold">{{
                                                poliza.contrato.numero_contrato
                                            }}</span>
                                        </p>
                                        <div class="flex flex-wrap gap-4 mt-1">
                                            <span
                                                v-if="
                                                    poliza.contrato.contratista
                                                        ?.correo_cont
                                                "
                                                class="flex items-center text-sm text-slate-500"
                                            >
                                                📧
                                                {{
                                                    poliza.contrato.contratista
                                                        .correo_cont
                                                }}
                                            </span>
                                            <span
                                                v-if="
                                                    poliza.contrato.contratista
                                                        ?.celular_cont
                                                "
                                                class="flex items-center text-sm text-slate-500"
                                            >
                                                📱
                                                {{
                                                    poliza.contrato.contratista
                                                        .celular_cont
                                                }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div
                                    v-if="poliza.contrato.administrador"
                                    class="p-3 bg-slate-50 rounded-xl border border-slate-100 text-sm text-slate-600"
                                >
                                    <span class="font-semibold"
                                        >Administrador:</span
                                    >
                                    {{ poliza.contrato.administrador.nombre }}
                                </div>
                            </div>
                            <div v-else class="text-sm text-slate-400 italic">
                                Sin contrato asociado.
                            </div>
                        </div>
                        
                        <!-- Operador Ambiental Section -->
                        <div v-else
                            class="bg-white rounded-3xl shadow-sm border border-slate-100 p-8"
                        >
                            <h3 class="text-lg font-bold text-slate-800 mb-6 flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                Operador Ambiental
                            </h3>

                            <div v-if="poliza.operador_ambiental" class="flex items-center p-4 bg-emerald-50/50 rounded-2xl border border-emerald-100">
                                <div class="h-16 w-16 rounded-full bg-white flex items-center justify-center text-emerald-600 text-2xl font-black shadow-sm border border-emerald-100">
                                    {{ poliza.operador_ambiental.nombre.charAt(0) }}
                                </div>
                                <div class="ml-6">
                                    <div class="flex items-center gap-2">
                                        <p class="text-xl font-extrabold text-slate-800">
                                            {{ poliza.operador_ambiental.nombre }} - {{ poliza.operador_ambiental.empresa }}
                                        </p>
                                    </div>
                                    <div class="flex flex-wrap gap-4 mt-2">
                                        <span v-if="poliza.operador_ambiental.correo" class="flex items-center text-sm text-slate-500">
                                            📧 {{ poliza.operador_ambiental.correo }}
                                        </span>
                                        <span v-if="poliza.operador_ambiental.celular" class="flex items-center text-sm text-slate-500">
                                            📱 {{ poliza.operador_ambiental.celular }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="text-sm text-slate-400 italic">
                                Sin operador ambiental asociado.
                            </div>
                        </div>

                        <!-- Fechas Acta Provisional -->
                        <div
                            v-if="poliza.fecha_acta_provisional"
                            class="bg-blue-50 rounded-2xl border border-blue-200 p-6"
                        >
                            <h3
                                class="text-sm font-bold text-blue-700 uppercase tracking-wider mb-3"
                            >
                                Acta Provisional
                            </h3>
                            <div class="grid grid-cols-1 gap-4">
                                <div>
                                    <p
                                        class="text-xs text-blue-500 font-semibold"
                                    >
                                        Fecha
                                    </p>
                                    <p
                                        class="text-sm font-bold text-blue-800 mt-1"
                                    >
                                        {{
                                            formatDate(
                                                poliza.fecha_acta_provisional,
                                            )
                                        }}
                                    </p>
                                </div>
                            </div>
                            <div
                                v-if="
                                    poliza.archivo_acta &&
                                    (poliza.estado === 'acta_provisional' ||
                                        poliza.estado_actual === 'vencida')
                                "
                                class="mt-4 pt-4 border-t border-blue-200/50"
                            >
                                <a
                                    :href="`/storage/${poliza.archivo_acta}`"
                                    target="_blank"
                                    class="inline-flex items-center text-sm font-bold text-blue-700 hover:text-blue-900 transition"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-5 w-5 mr-2"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"
                                        />
                                    </svg>
                                    Ver Documento de Acta
                                </a>
                            </div>
                        </div>

                        <!-- Fechas Acta Definitiva -->
                        <div
                            v-if="poliza.fecha_acta_definitiva"
                            class="bg-amber-50 rounded-2xl border border-amber-200 p-6"
                        >
                            <h3
                                class="text-sm font-bold text-amber-700 uppercase tracking-wider mb-3"
                            >
                                Acta Definitiva
                            </h3>
                            <div class="grid grid-cols-1 gap-4">
                                <div>
                                    <p
                                        class="text-xs text-amber-500 font-semibold"
                                    >
                                        Fecha
                                    </p>
                                    <p
                                        class="text-sm font-bold text-amber-800 mt-1"
                                    >
                                        {{
                                            formatDate(
                                                poliza.fecha_acta_definitiva,
                                            )
                                        }}
                                    </p>
                                </div>
                            </div>
                            <div
                                v-if="
                                    poliza.archivo_acta &&
                                    (poliza.estado === 'acta_definitiva' ||
                                        poliza.estado_actual === 'vencida')
                                "
                                class="mt-4 pt-4 border-t border-amber-200/50"
                            >
                                <a
                                    :href="`/storage/${poliza.archivo_acta}`"
                                    target="_blank"
                                    class="inline-flex items-center text-sm font-bold text-amber-700 hover:text-amber-900 transition"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-5 w-5 mr-2"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"
                                        />
                                    </svg>
                                    Ver Documento de Acta
                                </a>
                            </div>
                        </div>

                        <!-- Tabla Historial de Renovaciones -->
                        <div
                            v-if="
                                historial_renovaciones &&
                                historial_renovaciones.length > 0
                            "
                            class="bg-white rounded-3xl shadow-sm border border-slate-100 p-8 col-span-1 lg:col-span-2"
                        >
                            <h3
                                class="text-lg font-bold text-slate-800 mb-6 flex items-center justify-between"
                            >
                                <span class="flex items-center gap-2">
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-6 w-6 text-purple-500 mr-2"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                                        />
                                    </svg>
                                    Historial de Renovaciones
                                </span>
                                <span
                                    class="bg-purple-100 text-purple-700 font-bold px-3 py-1 rounded-full text-sm"
                                >
                                    Total: {{ historial_renovaciones.length }}
                                </span>
                            </h3>

                            <div
                                class="overflow-x-auto rounded-xl border border-slate-200"
                            >
                                <table class="w-full text-left border-collapse">
                                    <thead>
                                        <tr
                                            class="bg-slate-50 text-slate-500 text-xs font-bold uppercase tracking-wider"
                                        >
                                            <th
                                                class="p-4 border-b border-slate-200"
                                            >
                                                No.
                                            </th>
                                            <th
                                                class="p-4 border-b border-slate-200"
                                            >
                                                Póliza
                                            </th>
                                            <th
                                                class="p-4 border-b border-slate-200"
                                            >
                                                Fecha
                                            </th>
                                            <th
                                                class="p-4 border-b border-slate-200 text-right"
                                            >
                                                Estado
                                            </th>
                                            <th
                                                v-if="$page.props.auth.user.roles && ($page.props.auth.user.roles.includes('Administrador') || $page.props.auth.user.roles.includes('Super Admin') || $page.props.auth.user.roles.includes('Gestor de Tesorería'))"
                                                class="p-4 border-b border-slate-200 text-center"
                                            >
                                                Acciones
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody
                                        class="text-sm divide-y divide-slate-100"
                                    >
                                        <tr
                                            v-for="(
                                                renovacion, index
                                            ) in historial_renovaciones"
                                            :key="index"
                                            class="hover:bg-slate-50 transition-colors"
                                        >
                                            <td
                                                class="p-4 text-slate-400 font-medium"
                                            >
                                                #{{ index + 1 }}
                                            </td>
                                            <td class="p-4">
                                                <Link
                                                    :href="
                                                        route(
                                                            'polizas.show',
                                                            renovacion.id,
                                                        )
                                                    "
                                                    class="font-bold text-indigo-600 hover:text-indigo-800 transition-colors flex items-center gap-1"
                                                >
                                                    {{
                                                        renovacion.numero_poliza
                                                    }}
                                                    <svg
                                                        xmlns="http://www.w3.org/2000/svg"
                                                        class="h-3 w-3"
                                                        fill="none"
                                                        viewBox="0 0 24 24"
                                                        stroke="currentColor"
                                                    >
                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"
                                                        />
                                                    </svg>
                                                </Link>
                                            </td>
                                            <td class="p-4 text-slate-600">
                                                {{
                                                    formatDate(
                                                        renovacion.created_at,
                                                    )
                                                }}
                                            </td>
                                            <td class="p-4 text-right">
                                                <span
                                                    :class="[
                                                        'inline-flex px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider',
                                                        getStatusColor(
                                                            renovacion.estado,
                                                        ),
                                                    ]"
                                                >
                                                    {{ renovacion.estado }}
                                                </span>
                                            </td>
                                            <!-- Columna acciones: solo para admin y gestor -->
                                            <td
                                                v-if="$page.props.auth.user.roles && ($page.props.auth.user.roles.includes('Administrador') || $page.props.auth.user.roles.includes('Super Admin') || $page.props.auth.user.roles.includes('Gestor de Tesorería'))"
                                                class="p-4 text-center"
                                            >
                                                <button
                                                    @click="confirmarEliminarRenovacion(renovacion)"
                                                    class="inline-flex items-center gap-1 px-2 py-1 text-xs font-semibold text-red-600 bg-red-50 border border-red-200 rounded-lg hover:bg-red-100 transition"
                                                    title="Eliminar renovación"
                                                >
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                    Eliminar
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-8">
                        <!-- Entity Info -->
                        <div
                            class="bg-indigo-900 rounded-3xl shadow-xl p-8 text-white relative overflow-hidden"
                        >
                            <div class="relative z-10">
                                <h3
                                    class="text-indigo-200 text-xs font-bold uppercase tracking-widest mb-6"
                                >
                                    Detalles Institucionales
                                </h3>
                                <div class="mb-8">
                                    <p
                                        class="text-xs text-indigo-300 font-bold uppercase mb-1"
                                    >
                                        Aseguradora
                                    </p>
                                    <p class="text-xl font-bold">
                                        {{ poliza.sucursal?.aseguradora?.nombre_empresa }}
                                        <span class="text-sm font-normal opacity-75 block mt-1">
                                            Sucursal: {{ poliza.sucursal?.ciudad?.nombre || 'Central' }}
                                        </span>
                                    </p>
                                </div>
                                <div>
                                    <p
                                        class="text-xs text-indigo-300 font-bold uppercase mb-1"
                                    >
                                        Registrado por
                                    </p>
                                    <p class="text-lg font-bold">
                                        {{ poliza.usuario.name }}
                                    </p>
                                    <p
                                        class="text-xs text-indigo-200 opacity-50"
                                    >
                                        {{ formatDate(poliza.created_at) }}
                                    </p>
                                </div>
                            </div>
                            <!-- Decorative background -->
                            <div
                                class="absolute -right-16 -bottom-16 w-48 h-48 bg-white/5 rounded-full blur-2xl"
                            ></div>
                            <div
                                class="absolute -left-12 -top-12 w-32 h-32 bg-white/5 rounded-full blur-xl"
                            ></div>
                        </div>

                        <!-- Notification Status -->
                        <div
                            v-if="!($page.props.auth.user.roles && $page.props.auth.user.roles.includes('Gestor Tesorería Ambiente'))"
                            class="bg-white rounded-3xl shadow-sm border border-slate-100 p-8"
                        >
                            <h3
                                class="text-sm font-bold text-slate-800 mb-4 uppercase tracking-wider"
                            >
                                Historial de Notificaciones
                            </h3>
                            <div class="space-y-4">
                                <!-- Primer Aviso -->
                                <div
                                    v-if="poliza.oficio_email_1_at"
                                    class="flex gap-4 p-4 rounded-2xl bg-emerald-50 border border-emerald-100"
                                >
                                    <div
                                        class="p-2 bg-white rounded-lg text-emerald-600 shrink-0 h-9"
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
                                                d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                                            />
                                        </svg>
                                    </div>
                                    <div>
                                        <p
                                            class="text-sm font-bold text-emerald-800"
                                        >
                                            Primer Aviso Enviado
                                        </p>
                                        <p class="text-xs text-emerald-600">
                                            {{ formatDate(poliza.oficio_email_1_at) }}
                                        </p>
                                    </div>
                                </div>
                                <div
                                    v-else
                                    class="flex gap-4 p-4 rounded-2xl bg-slate-50 border border-slate-100 opacity-75"
                                >
                                    <div
                                        class="p-2 bg-white rounded-lg text-slate-400 shrink-0 h-9"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-sm font-bold text-slate-800">
                                            Primer Aviso Pendiente
                                        </p>
                                    </div>
                                </div>

                                <!-- Segundo Aviso -->
                                <div
                                    v-if="poliza.oficio_email_2_at"
                                    class="flex gap-4 p-4 rounded-2xl bg-indigo-50 border border-indigo-100"
                                >
                                    <div
                                        class="p-2 bg-white rounded-lg text-indigo-600 shrink-0 h-9"
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
                                                d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                                            />
                                        </svg>
                                    </div>
                                    <div>
                                        <p
                                            class="text-sm font-bold text-indigo-800"
                                        >
                                            Segundo Aviso Enviado
                                        </p>
                                        <p class="text-xs text-indigo-600">
                                            {{ formatDate(poliza.oficio_email_2_at) }}
                                        </p>
                                    </div>
                                </div>
                                <div
                                    v-else-if="poliza.oficio_email_1_at"
                                    class="flex gap-4 p-4 rounded-2xl bg-slate-50 border border-slate-100 opacity-75"
                                >
                                    <div
                                        class="p-2 bg-white rounded-lg text-slate-400 shrink-0 h-9"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-sm font-bold text-slate-800">
                                            Segundo Aviso Pendiente
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>

    <!-- Modal Confirmación Eliminar Renovación -->
    <Modal
        :show="renovacionPendienteEliminar !== null"
        @close="cancelarEliminarRenovacion"
        maxWidth="sm"
    >
        <div class="bg-white rounded-2xl overflow-hidden">
            <div class="bg-red-600 px-6 py-4 flex justify-between items-center text-white">
                <h3 class="text-lg font-bold flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    Eliminar Renovación
                </h3>
                <button @click="cancelarEliminarRenovacion" class="text-white/70 hover:text-white transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="p-6">
                <p class="text-slate-700 mb-2">
                    ¿Estás seguro de que deseas eliminar la renovación
                    <span class="font-black text-red-600">{{ renovacionPendienteEliminar?.numero_poliza }}</span>?
                </p>
                <div class="mt-3 p-3 bg-amber-50 border border-amber-200 rounded-lg text-xs text-amber-800 leading-relaxed">
                    <strong>⚠️ Esta acción:</strong>
                    <ul class="list-disc list-inside mt-1 space-y-0.5">
                        <li>Eliminará permanentemente la póliza y su registro de renovación.</li>
                        <li>El número (ej. R18) quedará libre — las demás renovaciones mantienen su numeración.</li>
                        <li>Revertirá el estado de la póliza anterior a <strong>vigente</strong> si ya no tiene renovaciones activas.</li>
                    </ul>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button
                        @click="cancelarEliminarRenovacion"
                        class="px-4 py-2 text-sm font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg transition"
                    >
                        Cancelar
                    </button>
                    <button
                        @click="ejecutarEliminarRenovacion"
                        class="px-4 py-2 text-sm font-bold text-white bg-red-600 hover:bg-red-700 rounded-lg transition flex items-center gap-2"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Sí, Eliminar
                    </button>
                </div>
            </div>
        </div>
    </Modal>

    <!-- Modal Detalles del Contrato -->
    <Modal
        :show="isContractModalOpen"
        @close="closeContractModal"
        maxWidth="2xl"
    >
        <div
            v-if="poliza.contrato"
            class="bg-white rounded-2xl overflow-hidden"
        >
            <!-- Header Modal -->
            <div
                class="bg-indigo-600 px-6 py-4 flex justify-between items-center text-white"
            >
                <h3 class="text-lg font-bold flex items-center">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-5 w-5 mr-2 opacity-80"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                        />
                    </svg>
                    Detalles del Contrato
                </h3>
                <button
                    @click="closeContractModal"
                    class="text-indigo-200 hover:text-white transition-colors rounded-full p-1 hover:bg-indigo-700"
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
                            d="M6 18L18 6M6 6l12 12"
                        />
                    </svg>
                </button>
            </div>

            <div class="p-6">
                <!-- Información Principal -->
                <div
                    class="flex items-start gap-4 mb-6 pb-6 border-b border-slate-100"
                >
                    <div
                        class="h-16 w-16 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600 text-2xl font-black border border-indigo-100 flex-shrink-0"
                    >
                        {{
                            poliza.contrato.contratista?.nombre_cont?.charAt(
                                0,
                            ) || "C"
                        }}
                    </div>
                    <div>
                        <h4 class="text-xl font-bold text-slate-800">
                            {{ poliza.contrato.contratista?.nombre_cont }}
                        </h4>
                        <div class="mt-2 space-y-1">
                            <p
                                class="text-sm text-slate-600 flex items-center gap-2"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-4 w-4 text-slate-400"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"
                                    />
                                </svg>
                                <span class="font-medium text-slate-500"
                                    >Contrato:</span
                                >
                                {{ poliza.contrato.numero_contrato }}
                            </p>
                            <p
                                class="text-sm text-slate-600 flex items-center gap-2"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-4 w-4 text-slate-400"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"
                                    />
                                </svg>
                                <span class="font-medium text-slate-500"
                                    >RUC/CI:</span
                                >
                                {{
                                    poliza.contrato.contratista?.ruc_cont ||
                                    "N/A"
                                }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Detalles Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Datos de Contacto -->
                    <div
                        class="bg-slate-50 rounded-xl p-4 border border-slate-100"
                    >
                        <h5
                            class="text-sm font-bold text-slate-700 uppercase tracking-wider mb-3 flex items-center gap-2"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-4 w-4 text-slate-400"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                                />
                            </svg>
                            Contacto
                        </h5>
                        <ul class="space-y-3">
                            <li class="flex flex-col">
                                <span
                                    class="text-xs font-semibold text-slate-500"
                                    >Correo Electrónico</span
                                >
                                <span class="text-sm text-slate-800">{{
                                    poliza.contrato.contratista?.correo_cont ||
                                    "No registrado"
                                }}</span>
                            </li>
                            <li class="flex flex-col">
                                <span
                                    class="text-xs font-semibold text-slate-500"
                                    >Teléfono Celular</span
                                >
                                <span class="text-sm text-slate-800">{{
                                    poliza.contrato.contratista?.celular_cont ||
                                    "No registrado"
                                }}</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Datos del Administrador -->
                    <div
                        class="bg-indigo-50 rounded-xl p-4 border border-indigo-100"
                    >
                        <h5
                            class="text-sm font-bold text-indigo-800 uppercase tracking-wider mb-3 flex items-center gap-2"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-4 w-4 text-indigo-400"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                                />
                            </svg>
                            Administrador del Contrato
                        </h5>
                        <div
                            v-if="poliza.contrato.administrador"
                            class="space-y-3"
                        >
                            <li class="flex flex-col">
                                <span
                                    class="text-xs font-semibold text-indigo-500"
                                    >Nombre</span
                                >
                                <span
                                    class="text-sm font-medium text-indigo-900"
                                    >{{
                                        poliza.contrato.administrador.nombre
                                    }}</span
                                >
                            </li>
                            <li class="flex flex-col">
                                <span
                                    class="text-xs font-semibold text-indigo-500"
                                    >Cargo</span
                                >
                                <span class="text-sm text-indigo-800">{{
                                    poliza.contrato.administrador.rol ||
                                    "Administrador"
                                }}</span>
                            </li>
                            <li class="flex flex-col">
                                <span
                                    class="text-xs font-semibold text-indigo-500"
                                    >Email</span
                                >
                                <span class="text-sm text-indigo-800">{{
                                    poliza.contrato.administrador.email ||
                                    "No registrado"
                                }}</span>
                            </li>
                        </div>
                        <div v-else class="text-sm text-indigo-600 italic py-2">
                            Sin administrador asignado.
                        </div>
                    </div>
                </div>

                <!-- Objeto del Contrato -->
                <div
                    class="mt-6 bg-slate-50 rounded-xl p-4 border border-slate-100"
                >
                    <h5
                        class="text-sm font-bold text-slate-700 uppercase tracking-wider mb-2 flex items-center gap-2"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-4 w-4 text-slate-400"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                            />
                        </svg>
                        Objeto del Contrato
                    </h5>
                    <p
                        class="text-sm text-slate-600 leading-relaxed whitespace-pre-wrap"
                    >
                        {{
                            poliza.contrato.objeto_contrato ||
                            "No registrado en la base de datos."
                        }}
                    </p>
                </div>
            </div>

            <div
                class="bg-slate-50 px-6 py-4 flex justify-end border-t border-slate-100"
            >
                <button
                    @click="closeContractModal"
                    class="px-5 py-2.5 bg-white border border-slate-200 rounded-xl shadow-sm text-sm font-semibold text-slate-700 hover:bg-slate-50 hover:text-indigo-600 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                >
                    Cerrar Detalle
                </button>
            </div>
        </div>
    </Modal>

    <!-- Modal para Firmar PDF -->
    <Modal :show="isSignModalOpen" @close="closeSignModal" maxWidth="5xl">
        <div
            class="px-3 sm:px-6 py-4 flex flex-col h-[90vh] lg:h-[85vh] max-h-screen"
        >
            <h3
                class="text-xl font-semibold text-slate-800 mb-4 bg-indigo-50 p-4 rounded-xl flex justify-between items-center gap-3 shrink-0"
            >
                <span
                    class="flex items-center gap-2 sm:gap-3 text-sm sm:text-lg"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-5 w-5 sm:h-6 sm:w-6 text-indigo-600"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"
                        />
                    </svg>
                    Firma Interactiva
                </span>
                <button
                    @click="closeSignModal"
                    class="text-indigo-400 hover:text-indigo-800 transition p-1"
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
                            d="M6 18L18 6M6 6l12 12"
                        />
                    </svg>
                </button>
            </h3>

            <div class="flex flex-col lg:flex-row flex-1 overflow-hidden gap-6">
                <!-- Visor PDF (Izquierda) -->
                <div
                    class="flex-1 lg:flex-auto bg-slate-200 rounded-xl overflow-auto relative border border-slate-300 flex justify-center items-start p-2 sm:p-4 custom-scrollbar min-h-[40vh] lg:min-h-0"
                >
                    <div
                        v-if="isLoadingPdf"
                        class="flex flex-col items-center justify-center h-full text-slate-500"
                    >
                        <svg
                            class="animate-spin h-8 w-8 mb-4 text-indigo-500"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                        >
                            <circle
                                class="opacity-25"
                                cx="12"
                                cy="12"
                                r="10"
                                stroke="currentColor"
                                stroke-width="4"
                            ></circle>
                            <path
                                class="opacity-75"
                                fill="currentColor"
                                d="M4 12a8 8 0 018-8V0l-3.293-3.293A1 1 0 008 8v4z"
                            ></path>
                        </svg>
                        Renderizando documento para su firma...
                    </div>

                    <div
                        class="relative shadow-lg inline-block"
                        v-show="!isLoadingPdf"
                    >
                        <!-- Añadimos padding inferior dummy para que no se corte si el click es al final -->
                        <canvas
                            ref="pdfCanvas"
                            @click="handleCanvasClick"
                            class="cursor-crosshair bg-white max-w-full lg:max-w-none transition-all"
                        ></canvas>

                        <!-- Marcador Visual de la Firma -->
                        <div
                            v-if="sigX !== null && sigPage === currentPageNum"
                            class="absolute pointer-events-none border-2 border-red-500 bg-red-500/10 text-red-700 font-bold text-xs flex shadow-sm backdrop-blur-[1px] transform origin-bottom-left"
                            :style="{
                                left: sigX * pdfRenderScale + 'px',
                                bottom: sigY * pdfRenderScale + 'px',
                                width: 180 * pdfRenderScale + 'px',
                                height: 80 * pdfRenderScale + 'px',
                                borderStyle: 'dashed',
                            }"
                        >
                            <span
                                class="absolute top-1 left-1 bg-red-500 text-white px-1.5 py-0.5 rounded text-[10px] opacity-80 uppercase tracking-widest label-firma"
                                >Tu Firma Aquí</span
                            >
                        </div>
                    </div>

                    <!-- Paginación flotante -->
                    <div
                        class="fixed bottom-10 left-1/2 transform -translate-x-1/2 bg-slate-800/80 rounded-full px-5 py-2.5 flex items-center gap-6 text-white shadow-xl backdrop-blur-md z-10"
                        v-if="totalPages > 1 && !isLoadingPdf"
                    >
                        <button
                            @click="prevPage"
                            :disabled="currentPageNum <= 1"
                            class="hover:text-indigo-300 disabled:opacity-50 transition p-1"
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
                                    d="M15 19l-7-7 7-7"
                                />
                            </svg>
                        </button>
                        <span class="text-sm font-bold tracking-wider"
                            >Pág. {{ currentPageNum }} de {{ totalPages }}</span
                        >
                        <button
                            @click="nextPage"
                            :disabled="currentPageNum >= totalPages"
                            class="hover:text-indigo-300 disabled:opacity-50 transition p-1"
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
                                    d="M9 5l7 7-7 7"
                                />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Panel Lateral (Derecha) -->
                <div
                    class="w-full lg:w-80 flex flex-col justify-start shrink-0 overflow-y-auto pb-4 gap-6 custom-scrollbar pr-1"
                >
                    <div class="space-y-6 flex-1">
                        <div
                            class="bg-indigo-50 border border-indigo-100 rounded-xl p-5 mb-6 shadow-sm relative overflow-hidden"
                        >
                            <div
                                class="absolute top-0 left-0 w-1 h-full"
                                :class="
                                    sigX !== null
                                        ? 'bg-emerald-500'
                                        : 'bg-indigo-500'
                                "
                            ></div>
                            <h4
                                class="font-bold text-indigo-900 mb-2 flex items-center gap-2"
                            >
                                <span
                                    class="bg-indigo-200 text-indigo-800 w-5 h-5 flex items-center justify-center rounded-full text-xs"
                                    >1</span
                                >
                                Ubicación de Firma
                            </h4>
                            <p class="text-sm text-indigo-700 leading-relaxed">
                                Da clic sobre el documento (a la izquierda) para
                                indicar
                                <strong class="font-bold"
                                    >exactamente dónde</strong
                                >
                                quieres que se estampe tu sello de firma
                                electrónica.
                            </p>

                            <div
                                v-if="sigX !== null"
                                class="mt-4 p-3 bg-emerald-50 rounded-lg border border-emerald-200 text-sm font-bold text-emerald-700 flex items-center justify-center gap-2 shadow-sm animate-pulse"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-5 w-5"
                                    viewBox="0 0 20 20"
                                    fill="currentColor"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                                ¡Posición Registrada!
                            </div>
                        </div>

                        <div class="space-y-4 relative">
                            <div
                                v-if="sigX === null"
                                class="absolute inset-0 bg-white/60 backdrop-blur-[2px] z-10 flex flex-col items-center justify-center text-center p-4"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-8 w-8 text-indigo-300 mb-2"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"
                                    />
                                </svg>
                                <span class="text-sm font-bold text-slate-500"
                                    >Selecciona la posición primero</span
                                >
                            </div>

                            <h4
                                class="font-bold text-indigo-900 flex items-center gap-2 mb-4"
                            >
                                <span
                                    class="bg-indigo-200 text-indigo-800 w-5 h-5 flex items-center justify-center rounded-full text-xs"
                                    >2</span
                                >
                                Confirmación
                            </h4>
                            <div>
                                <InputLabel
                                    for="password_certificado"
                                    value="Contraseña del Certificado .p12"
                                    class="text-slate-700 font-bold"
                                />
                                <TextInput
                                    id="password_certificado"
                                    ref="passwordInput"
                                    v-model="signForm.password_certificado"
                                    type="password"
                                    class="mt-1 block w-full bg-slate-50 border-slate-200 text-slate-700 focus:border-indigo-500 rounded-xl"
                                    placeholder="••••••••"
                                    @keyup.enter="submitSignature"
                                />
                                <InputError
                                    :message="passwordError"
                                    class="mt-2"
                                />
                            </div>

                            <p
                                class="mt-4 text-xs text-slate-500 bg-slate-50 p-3 rounded-lg border border-slate-100"
                            >
                                <span class="font-bold"
                                    >¿No tiene un certificado configurado?</span
                                ><br />
                                <Link
                                    :href="route('profile.edit')"
                                    class="text-indigo-600 font-semibold hover:underline cursor-pointer flex items-center gap-1 mt-1"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-3 w-3"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"
                                        />
                                    </svg>
                                    Subir Certificado en el Perfil
                                </Link>
                            </p>
                        </div>
                    </div>

                    <div
                        class="mt-auto pt-4 border-t border-slate-100 shrink-0 sticky bottom-0 bg-white"
                    >
                        <button
                            @click="submitSignature"
                            :disabled="
                                isSigning ||
                                !signForm.password_certificado ||
                                sigX === null
                            "
                            class="w-full px-5 py-3 sm:py-4 bg-indigo-600 border border-transparent rounded-xl shadow-lg shadow-indigo-200 text-sm font-bold text-white hover:bg-indigo-700 disabled:opacity-50 disabled:shadow-none transition-all focus:outline-none flex items-center justify-center"
                        >
                            <svg
                                v-if="isSigning"
                                class="animate-spin -ml-1 mr-2 h-5 w-5 text-white"
                                fill="none"
                                viewBox="0 0 24 24"
                            >
                                <circle
                                    class="opacity-25"
                                    cx="12"
                                    cy="12"
                                    r="10"
                                    stroke="currentColor"
                                    stroke-width="4"
                                ></circle>
                                <path
                                    class="opacity-75"
                                    fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                ></path>
                            </svg>
                            <span
                                v-if="!isSigning"
                                class="mr-2 border-2 border-white rounded font-serif px-1 size-5 flex items-center justify-center text-[10px]"
                                >F</span
                            >
                            {{
                                isSigning
                                    ? "Estampando y Descargando..."
                                    : "Firmar Documento Ahora"
                            }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </Modal>

    <!-- Modal para Registrar Renovación -->
    <Modal :show="isRenewModalOpen" @close="closeRenewModal">
        <div class="px-6 py-4">
            <h3
                class="text-xl font-semibold text-slate-800 mb-4 bg-emerald-50 p-4 rounded-xl flex items-center gap-3"
            >
                🔄 Registrar Renovación de Póliza
            </h3>

            <form @submit.prevent="submitRenewal" class="space-y-4">
                <div>
                    <InputLabel
                        for="valor_asegurado"
                        value="Nuevo Valor Asegurado"
                        class="font-bold text-slate-700"
                    />
                    <TextInput
                        id="valor_asegurado"
                        v-model="renewForm.valor_asegurado"
                        type="number"
                        step="0.01"
                        class="mt-1 block w-full rounded-xl bg-slate-50 border-slate-200"
                        required
                    />
                    <InputError
                        :message="renewForm.errors.valor_asegurado"
                        class="mt-2"
                    />
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <InputLabel
                            for="fecha_inicio"
                            value="Nueva Fecha de Inicio"
                            class="font-bold text-slate-700"
                        />
                        <TextInput
                            id="fecha_inicio"
                            v-model="renewForm.fecha_inicio"
                            type="date"
                            class="mt-1 block w-full rounded-xl bg-slate-50 border-slate-200"
                            required
                        />
                        <InputError
                            :message="renewForm.errors.fecha_inicio"
                            class="mt-2"
                        />
                    </div>
                    <div>
                        <InputLabel
                            for="fecha_vencimiento"
                            value="Nueva Fecha de Vencimiento"
                            class="font-bold text-slate-700"
                        />
                        <TextInput
                            id="fecha_vencimiento"
                            v-model="renewForm.fecha_vencimiento"
                            type="date"
                            class="mt-1 block w-full rounded-xl bg-slate-50 border-slate-200"
                            required
                        />
                        <InputError
                            :message="renewForm.errors.fecha_vencimiento"
                            class="mt-2"
                        />
                    </div>
                </div>
                <div>
                    <InputLabel
                        for="observaciones"
                        value="Observaciones (Opcional)"
                        class="font-bold text-slate-700"
                    />
                    <textarea
                        id="observaciones"
                        v-model="renewForm.observaciones"
                        class="mt-1 block w-full border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 rounded-xl shadow-sm bg-slate-50"
                        rows="3"
                    ></textarea>
                    <InputError
                        :message="renewForm.errors.observaciones"
                        class="mt-2"
                    />
                </div>

                <div class="mt-4">
                    <InputLabel
                        for="archivo_renovacion"
                        value="Documento PDF de Renovación (Opcional)"
                        class="font-bold text-slate-700"
                    />
                    <input
                        type="file"
                        id="archivo_renovacion"
                        accept="application/pdf"
                        @change="handleRenewalFileUpload"
                        class="mt-1 block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100"
                    />
                    <InputError
                        :message="renewForm.errors.archivo_renovacion"
                        class="mt-2 text-rose-500 text-sm"
                    />
                </div>
            </form>
        </div>
        <div
            class="bg-emerald-50 px-6 py-4 flex justify-end gap-3 border-t border-emerald-100/50"
        >
            <button
                @click="closeRenewModal"
                class="px-5 py-2.5 bg-white border border-slate-200 rounded-xl shadow-sm text-sm font-semibold text-slate-700 hover:bg-slate-50 transition"
            >
                Cancelar
            </button>
            <button
                @click="submitRenewal"
                :disabled="renewForm.processing"
                class="px-5 py-2.5 bg-emerald-600 border border-transparent rounded-xl shadow-sm text-sm font-semibold text-white hover:bg-emerald-700 disabled:opacity-50 transition-colors flex items-center"
            >
                Registrar Renovación
            </button>
        </div>
    </Modal>

    <!-- Modal de Envío de Oficio por Email -->
    <Modal :show="isEmailModalOpen" @close="closeEmailModal" :max-width="'2xl'">
        <div class="p-6">
            <h2 class="text-lg font-bold text-slate-800 mb-4">📧 Enviar Oficio por Email</h2>

            <!-- Mensaje de éxito -->
            <div v-if="emailSuccess" class="mb-4 p-3 bg-emerald-50 border border-emerald-200 rounded-lg text-emerald-700 text-sm font-semibold">
                {{ emailSuccess }}
            </div>
            <!-- Mensaje de error -->
            <div v-if="emailError" class="mb-4 p-3 bg-rose-50 border border-rose-200 rounded-lg text-rose-700 text-sm">
                {{ emailError }}
            </div>

            <!-- Destinatarios Principales (Para:) -->
            <div class="mb-4">
                <label class="block text-sm font-bold text-slate-700 mb-1">Para: (Principal)</label>
                <div class="flex flex-wrap gap-2">
                    <label
                        v-for="correo in getCorreosAseguradora()"
                        :key="correo"
                        class="flex items-center gap-2 cursor-pointer"
                    >
                        <input
                            type="checkbox"
                            :value="correo"
                            v-model="emailForm.to"
                            class="rounded border-slate-300 text-blue-600"
                        />
                        <span class="text-sm text-slate-700">{{ correo }}</span>
                    </label>
                </div>
                <p v-if="getCorreosAseguradora().length === 0" class="text-xs text-slate-400 mt-1">No hay correos registrados para esta aseguradora.</p>
            </div>

            <!-- CC -->
            <div class="mb-4">
                <label class="block text-sm font-bold text-slate-700 mb-1">CC: (Copia)</label>
                <div class="flex flex-wrap gap-2">
                    <label
                        v-for="correo in getCorreosAseguradora()"
                        :key="'cc-' + correo"
                        class="flex items-center gap-2 cursor-pointer"
                    >
                        <input
                            type="checkbox"
                            :value="correo"
                            v-model="emailForm.cc"
                            class="rounded border-slate-300 text-blue-600"
                        />
                        <span class="text-sm text-slate-700">{{ correo }}</span>
                    </label>
                </div>
            </div>

            <!-- Asunto -->
            <div class="mb-4">
                <label class="block text-sm font-bold text-slate-700 mb-1">Asunto</label>
                <input
                    type="text"
                    v-model="emailForm.asunto"
                    class="w-full border-slate-200 rounded-lg shadow-sm text-sm p-2 focus:border-blue-500 focus:ring-blue-500"
                />
            </div>

            <!-- Cuerpo -->
            <div class="mb-4">
                <label class="block text-sm font-bold text-slate-700 mb-1">Cuerpo del Mensaje</label>
                <textarea
                    v-model="emailForm.cuerpo"
                    rows="7"
                    class="w-full border-slate-200 rounded-lg shadow-sm text-sm p-2 focus:border-blue-500 focus:ring-blue-500"
                ></textarea>
            </div>

            <!-- Adjunto info -->
            <div class="mb-4 p-3 bg-slate-50 rounded-lg border border-slate-200 text-xs text-slate-500">
                📄 Se adjuntará automáticamente: <strong>Oficio_{{ poliza.numero_poliza }}.pdf</strong> (firmado por Gestora y Tesorero)
            </div>

            <!-- Botones -->
            <div class="flex justify-end gap-3">
                <button
                    @click="closeEmailModal"
                    class="px-5 py-2.5 bg-white border border-slate-200 rounded-xl shadow-sm text-sm font-semibold text-slate-700 hover:bg-slate-50 transition"
                >Cancelar</button>
                <button
                    @click="sendEmail"
                    :disabled="isSendingEmail"
                    class="px-5 py-2.5 bg-blue-600 border border-transparent rounded-xl shadow-sm text-sm font-semibold text-white hover:bg-blue-700 disabled:opacity-50 transition flex items-center gap-2"
                >
                    <span v-if="isSendingEmail">⏳ Enviando...</span>
                    <span v-else">📤 Enviar Email</span>
                </button>
            </div>
        </div>
    </Modal>
</template>
