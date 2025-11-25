<template>
    <Teleport to="body">
        <Transition name="modal">
            <div
                v-if="show"
                class="fixed inset-0 z-50 overflow-y-auto"
                @click.self="close"
            >
                <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                    <!-- Overlay -->
                    <div
                        class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"
                        @click="close"
                    ></div>

                    <!-- Modal -->
                    <div
                        class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full"
                        :class="maxWidth"
                    >
                        <!-- Header -->
                        <div v-if="title" class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-medium text-gray-900">{{ title }}</h3>
                                <button
                                    @click="close"
                                    class="text-gray-400 hover:text-gray-500 focus:outline-none"
                                >
                                    <span class="sr-only">Cerrar</span>
                                    ✕
                                </button>
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6">
                            <slot></slot>
                        </div>

                        <!-- Footer -->
                        <div v-if="showFooter" class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <slot name="footer">
                                <button
                                    type="button"
                                    @click="close"
                                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                                >
                                    Cerrar
                                </button>
                            </slot>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<script setup>
import { watch } from 'vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    title: String,
    showFooter: {
        type: Boolean,
        default: true,
    },
    maxWidth: {
        type: String,
        default: 'sm:max-w-lg',
    },
});

const emit = defineEmits(['update:show', 'close']);

const close = () => {
    emit('update:show', false);
    emit('close');
};

// Cerrar con ESC
watch(() => props.show, (newVal) => {
    if (newVal) {
        document.body.style.overflow = 'hidden';
        const handleEsc = (e) => {
            if (e.key === 'Escape') close();
        };
        document.addEventListener('keydown', handleEsc);
        return () => {
            document.body.style.overflow = '';
            document.removeEventListener('keydown', handleEsc);
        };
    } else {
        document.body.style.overflow = '';
    }
});
</script>

<style scoped>
.modal-enter-active,
.modal-leave-active {
    transition: opacity 0.3s;
}

.modal-enter-from,
.modal-leave-to {
    opacity: 0;
}
</style>

