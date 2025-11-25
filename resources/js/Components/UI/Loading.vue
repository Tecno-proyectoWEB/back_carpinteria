<template>
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-500 bg-opacity-75">
        <div class="text-center">
            <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-white"></div>
            <p v-if="message" class="mt-4 text-white font-medium">{{ message }}</p>
        </div>
    </div>
</template>

<script setup>
defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    message: {
        type: String,
        default: 'Cargando...',
    },
});
</script>

<style scoped>
@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

.animate-spin {
    animation: spin 1s linear infinite;
}
</style>

