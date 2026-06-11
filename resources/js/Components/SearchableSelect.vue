<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount } from "vue";

const props = defineProps({
    modelValue: [String, Number],
    options: {
        type: Array,
        default: () => [],
    },
    labelKey: {
        type: String,
        default: "label",
    },
    valueKey: {
        type: String,
        default: "id",
    },
    placeholder: {
        type: String,
        default: "Seleccione...",
    },
    searchPlaceholder: {
        type: String,
        default: "Buscar...",
    },
    disabled: Boolean,
});

const emit = defineEmits(["update:modelValue"]);

const isOpen = ref(false);
const searchQuery = ref("");
const containerRef = ref(null);

const filteredOptions = computed(() => {
    if (!searchQuery.value) return props.options;
    const q = searchQuery.value.toLowerCase();
    return props.options.filter((opt) =>
        String(opt[props.labelKey] || "")
            .toLowerCase()
            .includes(q),
    );
});

const selectedLabel = computed(() => {
    if (!props.modelValue && props.modelValue !== 0) return "";
    const found = props.options.find(
        (opt) => String(opt[props.valueKey]) === String(props.modelValue),
    );
    return found ? found[props.labelKey] : "";
});

const toggle = () => {
    if (props.disabled) return;
    isOpen.value = !isOpen.value;
    if (isOpen.value) {
        searchQuery.value = "";
    }
};

const select = (opt) => {
    emit("update:modelValue", opt[props.valueKey]);
    isOpen.value = false;
    searchQuery.value = "";
};

const clear = () => {
    emit("update:modelValue", "");
    isOpen.value = false;
    searchQuery.value = "";
};

const handleClickOutside = (e) => {
    if (containerRef.value && !containerRef.value.contains(e.target)) {
        isOpen.value = false;
    }
};

onMounted(() => document.addEventListener("mousedown", handleClickOutside));
onBeforeUnmount(() =>
    document.removeEventListener("mousedown", handleClickOutside),
);
</script>

<template>
    <div ref="containerRef" class="relative">
        <!-- Trigger Button -->
        <button
            type="button"
            @click="toggle"
            :disabled="disabled"
            class="mt-1 flex w-full items-center justify-between rounded-md border border-gray-300 bg-white px-3 py-2 text-left text-sm shadow-sm transition focus:border-[#024283] focus:outline-none focus:ring-1 focus:ring-[#024283]"
            :class="{ 'opacity-50 cursor-not-allowed': disabled }"
        >
            <span :class="selectedLabel ? 'text-gray-900' : 'text-gray-400'">
                {{ selectedLabel || placeholder }}
            </span>
            <svg
                class="h-4 w-4 text-gray-400 transition-transform"
                :class="{ 'rotate-180': isOpen }"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M19 9l-7 7-7-7"
                />
            </svg>
        </button>

        <!-- Dropdown -->
        <Transition
            enter-active-class="transition duration-100 ease-out"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition duration-75 ease-in"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
        >
            <div
                v-if="isOpen"
                class="absolute z-50 mt-1 w-full rounded-lg border border-slate-200 bg-white shadow-xl"
            >
                <!-- Search Input -->
                <div class="border-b border-slate-100 p-2">
                    <div class="relative">
                        <svg
                            class="absolute left-2.5 top-2.5 h-4 w-4 text-gray-400"
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
                        <input
                            v-model="searchQuery"
                            type="text"
                            :placeholder="searchPlaceholder"
                            class="block w-full rounded-md border-slate-200 py-2 pl-8 pr-3 text-sm placeholder-slate-400 focus:border-[#024283] focus:outline-none focus:ring-1 focus:ring-[#024283]"
                            @click.stop
                        />
                    </div>
                </div>

                <!-- Options List -->
                <ul class="max-h-48 overflow-y-auto py-1">
                    <li
                        v-if="modelValue"
                        @click="clear"
                        class="cursor-pointer px-3 py-2 text-sm text-slate-400 hover:bg-slate-50 italic"
                    >
                        — Limpiar selección
                    </li>
                    <li
                        v-for="opt in filteredOptions"
                        :key="opt[valueKey]"
                        @click="select(opt)"
                        class="cursor-pointer px-3 py-2 text-sm transition hover:bg-blue-50 hover:text-[#024283]"
                        :class="{
                            'bg-blue-50 font-semibold text-[#024283]':
                                String(opt[valueKey]) === String(modelValue),
                        }"
                    >
                        {{ opt[labelKey] }}
                    </li>
                    <li
                        v-if="filteredOptions.length === 0"
                        class="px-3 py-4 text-center text-sm text-slate-400"
                    >
                        No se encontraron resultados
                    </li>
                </ul>
            </div>
        </Transition>
    </div>
</template>
