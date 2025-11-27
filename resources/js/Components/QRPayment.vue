<template>
    <div v-if="pago && pago.qr_image" class="bg-white rounded-lg shadow-lg p-6 max-w-md mx-auto">
        <h3 class="text-xl font-bold text-gray-900 mb-4 text-center">Escanea el código QR para pagar</h3>

        <div class="text-center mb-4">
            <img :src="qrImageUrl" alt="Código QR de Pago" class="mx-auto border-2 border-gray-200 rounded-lg" />
        </div>

        <div class="bg-gray-50 rounded-lg p-4 mb-4">
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-600">Monto a pagar:</span>
                    <span class="font-bold text-lg">${{ pago.monto?.toFixed(2) }}</span>
                </div>
                <div v-if="pago.qr_expires_at" class="flex justify-between">
                    <span class="text-gray-600">Expira:</span>
                    <span class="font-medium">{{ formatDate(pago.qr_expires_at) }}</span>
                </div>
                <div v-if="pago.nro_pago" class="flex justify-between">
                    <span class="text-gray-600">Nro. de Pago:</span>
                    <span class="font-medium">{{ pago.nro_pago }}</span>
                </div>
            </div>
        </div>

        <div class="flex gap-2">
            <button
                @click="consultarEstado"
                :disabled="consultando"
                class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50"
            >
                <span v-if="consultando">Consultando...</span>
                <span v-else>Verificar Pago</span>
            </button>
            <button
                @click="mostrarModal"
                class="flex-1 px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300"
            >
                Ver Estado
            </button>
        </div>

        <div v-if="pago.estado === 'PENDIENTE'" class="mt-4 text-center">
            <p class="text-sm text-gray-600">
                <svg class="inline-block w-4 h-4 animate-spin mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                Esperando confirmación de pago...
            </p>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { router } from '@inertiajs/vue3'
import { route } from '../ziggy.js'
import { useStorage } from '../composables/useStorage'

const props = defineProps({
    pago: Object,
    autoConsult: {
        type: Boolean,
        default: true, // Por defecto sí consulta automáticamente
    },
})

const emit = defineEmits(['showModal'])

const { storageUrlSafe } = useStorage()
const consultando = ref(false)
let intervalId = null

// URL de la imagen QR usando el composable
const qrImageUrl = computed(() => {
    if (!props.pago?.qr_image) return ''
    return storageUrlSafe(props.pago.qr_image)
})

const formatDate = (date) => {
    if (!date) return ''
    return new Date(date).toLocaleString('es-ES')
}

const consultarEstado = () => {
    if (consultando.value) return

    consultando.value = true
    router.get(route('payment.status', props.pago.id), {}, {
        preserveState: true,
        preserveScroll: true,
        onFinish: () => {
            consultando.value = false
        }
    })
}

const mostrarModal = () => {
    emit('showModal')
}

// Auto-consultar cada 30 segundos si está pendiente (solo si autoConsult es true)
onMounted(() => {
    // Solo auto-consultar si está habilitado y el pago está pendiente
    if (props.autoConsult && props.pago && props.pago.estado === 'PENDIENTE' && props.pago.qr_image) {
        intervalId = setInterval(() => {
            if (!consultando.value && props.pago && props.pago.estado === 'PENDIENTE') {
                consultarEstado()
            } else if (props.pago && props.pago.estado !== 'PENDIENTE') {
                if (intervalId) {
                    clearInterval(intervalId)
                    intervalId = null
                }
            }
        }, 30000) // 30 segundos para reducir carga del servidor
    }
})

onUnmounted(() => {
    if (intervalId) {
        clearInterval(intervalId)
    }
})
</script>
