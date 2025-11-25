<template>
    <div
        v-if="show"
        :class="[
            'rounded-md p-4 mb-4',
            type === 'success' ? 'bg-green-50 border border-green-200' : '',
            type === 'error' ? 'bg-red-50 border border-red-200' : '',
            type === 'warning' ? 'bg-yellow-50 border border-yellow-200' : '',
            type === 'info' ? 'bg-blue-50 border border-blue-200' : '',
        ]"
    >
        <div class="flex">
            <div class="flex-shrink-0">
                <span v-if="type === 'success'" class="text-green-400">✓</span>
                <span v-else-if="type === 'error'" class="text-red-400">✕</span>
                <span v-else-if="type === 'warning'" class="text-yellow-400">⚠</span>
                <span v-else class="text-blue-400">ℹ</span>
            </div>
            <div class="ml-3 flex-1">
                <p
                    :class="[
                        'text-sm font-medium',
                        type === 'success' ? 'text-green-800' : '',
                        type === 'error' ? 'text-red-800' : '',
                        type === 'warning' ? 'text-yellow-800' : '',
                        type === 'info' ? 'text-blue-800' : '',
                    ]"
                >
                    {{ message }}
                </p>
            </div>
            <div class="ml-auto pl-3">
                <button
                    @click="close"
                    class="inline-flex rounded-md p-1.5 focus:outline-none focus:ring-2 focus:ring-offset-2"
                    :class="[
                        type === 'success' ? 'text-green-500 hover:bg-green-100 focus:ring-green-600' : '',
                        type === 'error' ? 'text-red-500 hover:bg-red-100 focus:ring-red-600' : '',
                        type === 'warning' ? 'text-yellow-500 hover:bg-yellow-100 focus:ring-yellow-600' : '',
                        type === 'info' ? 'text-blue-500 hover:bg-blue-100 focus:ring-blue-600' : '',
                    ]"
                >
                    <span class="sr-only">Cerrar</span>
                    <span>×</span>
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
    type: {
        type: String,
        default: 'info',
        validator: (value) => ['success', 'error', 'warning', 'info'].includes(value),
    },
    message: {
        type: String,
        required: true,
    },
    autoClose: {
        type: Boolean,
        default: false,
    },
    duration: {
        type: Number,
        default: 5000,
    },
});

const show = ref(true);

const close = () => {
    show.value = false;
};

watch(() => props.message, () => {
    show.value = true;
    if (props.autoClose) {
        setTimeout(() => {
            show.value = false;
        }, props.duration);
    }
}, { immediate: true });
</script>

