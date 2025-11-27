<template>
    <div v-if="show" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" @click.self="$emit('close')">
        <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
            <div class="p-6">
                <!-- Header del Modal -->
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-2xl font-bold text-gray-800">Estado del Pago</h2>
                    <button
                        @click="$emit('close')"
                        class="text-gray-400 hover:text-gray-600 transition-colors"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Contenido del Modal -->
                <div v-if="pago" class="space-y-4">
                    <!-- Información básica del pago -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-gray-600">Monto</p>
                                <p class="font-bold text-lg text-gray-900">${{ pago.monto?.toFixed(2) }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Estado</p>
                                <span :class="{
                                    'bg-yellow-100 text-yellow-800': pago.estado === 'PENDIENTE',
                                    'bg-green-100 text-green-800': pago.estado === 'PAGADO',
                                    'bg-red-100 text-red-800': pago.estado === 'VENCIDO',
                                    'bg-gray-100 text-gray-800': pago.estado === 'CANCELADO'
                                }" class="px-3 py-1 rounded-full text-sm font-semibold">
                                    {{ pago.estado }}
                                </span>
                            </div>
                            <div v-if="pago.nro_pago">
                                <p class="text-sm text-gray-600">Nro. de Pago</p>
                                <p class="font-medium text-gray-900">{{ pago.nro_pago }}</p>
                            </div>
                            <div v-if="pago.fecha_vencimiento">
                                <p class="text-sm text-gray-600">Fecha de Vencimiento</p>
                                <p class="font-medium text-gray-900">{{ formatDate(pago.fecha_vencimiento) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Información de PagoFácil si está disponible -->
                    <div v-if="paymentInfo" class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                        <h3 class="font-semibold text-blue-900 mb-3">Información de PagoFácil</h3>
                        <div class="space-y-2 text-sm">
                            <div v-if="paymentInfo.paymentStatus" class="flex justify-between">
                                <span class="text-blue-700">Estado del Pago:</span>
                                <span class="font-medium text-blue-900">{{ getStatusText(paymentInfo.paymentStatus) }}</span>
                            </div>
                            <div v-if="paymentInfo.paymentStatusDescription" class="flex justify-between">
                                <span class="text-blue-700">Descripción:</span>
                                <span class="font-medium text-blue-900">{{ paymentInfo.paymentStatusDescription }}</span>
                            </div>
                            <div v-if="paymentInfo.paymentDate && paymentInfo.paymentTime" class="flex justify-between">
                                <span class="text-blue-700">Fecha y Hora de Pago:</span>
                                <span class="font-medium text-blue-900">{{ formatDateTime(paymentInfo.paymentDate, paymentInfo.paymentTime) }}</span>
                            </div>
                            <div v-if="paymentInfo.amount" class="flex justify-between">
                                <span class="text-blue-700">Monto Pagado:</span>
                                <span class="font-medium text-blue-900">${{ paymentInfo.amount }}</span>
                            </div>
                            <div v-if="paymentInfo.pagofacilTransactionId" class="flex justify-between">
                                <span class="text-blue-700">ID Transacción PagoFácil:</span>
                                <span class="font-medium text-blue-900 text-xs">{{ paymentInfo.pagofacilTransactionId }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Icono de estado -->
                    <div class="text-center py-4">
                        <div v-if="pago.estado === 'PENDIENTE'" class="inline-block">
                            <svg class="w-16 h-16 text-yellow-500 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            <p class="mt-2 text-yellow-600 font-medium">Pago Pendiente</p>
                        </div>
                        <div v-else-if="pago.estado === 'PAGADO'" class="inline-block">
                            <svg class="w-16 h-16 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p class="mt-2 text-green-600 font-medium">Pago Completado</p>
                        </div>
                        <div v-else class="inline-block">
                            <svg class="w-16 h-16 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p class="mt-2 text-red-600 font-medium">{{ pago.estado }}</p>
                        </div>
                    </div>
                </div>

                <!-- Botón Cerrar -->
                <div class="mt-6 flex justify-end">
                    <button
                        @click="$emit('close')"
                        class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors"
                    >
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
defineProps({
    show: Boolean,
    pago: Object,
    paymentInfo: Object,
})

defineEmits(['close'])

const formatDate = (date) => {
    if (!date) return 'N/A'
    return new Date(date).toLocaleDateString('es-ES')
}

const formatDateTime = (date, time) => {
    if (!date) return 'N/A'
    try {
        const dateTime = time ? `${date} ${time}` : date
        return new Date(dateTime).toLocaleString('es-ES')
    } catch (e) {
        return date
    }
}

const getStatusText = (status) => {
    const statusMap = {
        1: 'PAGADO',
        2: 'PENDIENTE',
        3: 'EXPIRADO',
        4: 'CANCELADO'
    }
    return statusMap[status] || 'DESCONOCIDO'
}
</script>

