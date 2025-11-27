<template>
    <div v-if="show" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" @click.self="close">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold text-gray-900">Estado del Pago</h3>
                    <button @click="close" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="space-y-4">
                    <!-- Estado del pago -->
                    <div class="text-center">
                        <div v-if="pago.estado === 'PAGADO'" class="mb-4">
                            <div class="mx-auto w-16 h-16 bg-green-100 rounded-full flex items-center justify-center">
                                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <p class="mt-2 text-lg font-semibold text-green-600">Pago Completado</p>
                        </div>
                        <div v-else-if="pago.estado === 'PENDIENTE'" class="mb-4">
                            <div class="mx-auto w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center">
                                <svg class="w-8 h-8 text-yellow-600 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                            </div>
                            <p class="mt-2 text-lg font-semibold text-yellow-600">Pago Pendiente</p>
                        </div>
                        <div v-else class="mb-4">
                            <div class="mx-auto w-16 h-16 bg-red-100 rounded-full flex items-center justify-center">
                                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </div>
                            <p class="mt-2 text-lg font-semibold text-red-600">{{ pago.estado }}</p>
                        </div>
                    </div>

                    <!-- Información del pago -->
                    <div class="bg-gray-50 rounded-lg p-4 space-y-2">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Monto:</span>
                            <span class="font-semibold">${{ pago.monto?.toFixed(2) }}</span>
                        </div>
                        <div v-if="pago.fecha_confirmacion" class="flex justify-between">
                            <span class="text-gray-600">Fecha de Pago:</span>
                            <span class="font-semibold">{{ formatDate(pago.fecha_confirmacion) }}</span>
                        </div>
                        <div v-if="pago.metodo_pago_facil" class="flex justify-between">
                            <span class="text-gray-600">Método:</span>
                            <span class="font-semibold">{{ pago.metodo_pago_facil }}</span>
                        </div>
                        <div v-if="pago.nro_transaccion" class="flex justify-between">
                            <span class="text-gray-600">Nro. Transacción:</span>
                            <span class="font-semibold text-sm">{{ pago.nro_transaccion }}</span>
                        </div>
                    </div>

                    <!-- Información adicional de PagoFácil -->
                    <div v-if="paymentInfo" class="bg-blue-50 rounded-lg p-4 space-y-2">
                        <h4 class="font-semibold text-blue-900 mb-2">Información de PagoFácil</h4>
                        <div v-if="paymentInfo.paymentStatusDescription" class="text-sm">
                            <span class="text-blue-700 font-medium">Estado: </span>
                            <span>{{ paymentInfo.paymentStatusDescription }}</span>
                        </div>
                        <div v-if="paymentInfo.paymentDate && paymentInfo.paymentTime" class="text-sm">
                            <span class="text-blue-700 font-medium">Fecha y Hora: </span>
                            <span>{{ paymentInfo.paymentDate }} {{ paymentInfo.paymentTime }}</span>
                        </div>
                    </div>

                    <!-- Botón para consultar estado -->
                    <div v-if="pago.estado === 'PENDIENTE'" class="flex justify-center">
                        <button
                            @click="consultarEstado"
                            :disabled="consultando"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <span v-if="consultando">Consultando...</span>
                            <span v-else>Consultar Estado</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import { route } from '../ziggy.js'

const props = defineProps({
    show: Boolean,
    pago: Object,
    paymentInfo: Object,
})

const emit = defineEmits(['close'])

const consultando = ref(false)

const close = () => {
    emit('close')
}

const formatDate = (date) => {
    if (!date) return ''
    return new Date(date).toLocaleString('es-ES')
}

const consultarEstado = () => {
    consultando.value = true
    router.get(route('payment.status', props.pago.id), {}, {
        preserveState: true,
        preserveScroll: true,
        onFinish: () => {
            consultando.value = false
        }
    })
}
</script>

