<template>
    <Layout :auth="auth">
        <div>
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-gray-900">Detalle de Pago</h1>
            </div>

            <!-- QR de Pago (si existe) -->
            <div v-if="pagoConQR && pagoConQR.estado === 'PENDIENTE'" class="mb-6">
                <QRPayment
                    :pago="pagoConQR"
                    @show-modal="showModal = true"
                />
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Venta</label>
                        <Link :href="route('ventas.show', pago.venta_id)" class="mt-1 text-sm text-blue-600 hover:text-blue-900">
                            Venta #{{ pago.venta_id }}
                        </Link>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Monto</label>
                        <p class="mt-1 text-sm text-gray-900">${{ pago.monto?.toFixed(2) }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tipo</label>
                        <p class="mt-1 text-sm text-gray-900">{{ pago.tipo }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Estado</label>
                        <p class="mt-1">
                            <span :class="{
                                'bg-yellow-100 text-yellow-800': pago.estado === 'PENDIENTE',
                                'bg-green-100 text-green-800': pago.estado === 'PAGADO',
                                'bg-red-100 text-red-800': pago.estado === 'VENCIDO',
                                'bg-gray-100 text-gray-800': pago.estado === 'CANCELADO'
                            }" class="px-2 py-1 text-xs font-semibold rounded-full">
                                {{ pago.estado }}
                            </span>
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Método de Pago</label>
                        <p class="mt-1 text-sm text-gray-900">{{ pago.metodo_pago?.nombre || 'N/A' }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Fecha de Pago</label>
                        <p class="mt-1 text-sm text-gray-900">{{ pago.fecha_pago ? new Date(pago.fecha_pago).toLocaleString() : 'N/A' }}</p>
                    </div>

                    <div v-if="pago.fecha_vencimiento">
                        <label class="block text-sm font-medium text-gray-700">Fecha de Vencimiento</label>
                        <p class="mt-1 text-sm text-gray-900">{{ new Date(pago.fecha_vencimiento).toLocaleDateString() }}</p>
                    </div>

                    <div v-if="pago.numero_cuota">
                        <label class="block text-sm font-medium text-gray-700">Número de Cuota</label>
                        <p class="mt-1 text-sm text-gray-900">{{ pago.numero_cuota }}</p>
                    </div>

                    <div v-if="pago.observaciones" class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Observaciones</label>
                        <p class="mt-1 text-sm text-gray-900">{{ pago.observaciones }}</p>
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <Link :href="route('pagos.index')" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                        Volver
                    </Link>
                    <Link :href="route('pagos.edit', pago.id)" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        Editar
                    </Link>
                </div>
            </div>
        </div>

        <!-- Modal de Estado de Pago -->
        <PaymentModal
            v-if="pagoConQR"
            :show="showModal"
            :pago="pagoConQR"
            :payment-info="paymentInfo"
            @close="showModal = false"
        />
    </Layout>
</template>

<script setup>
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { route } from '../../ziggy.js'
import Layout from '../Layout.vue'
import QRPayment from '../../Components/QRPayment.vue'
import PaymentModal from '../../Components/PaymentModal.vue'

const props = defineProps({
    auth: Object,
    pago: Object,
    pagoConQR: Object,
})

const showModal = ref(false)
const paymentInfo = ref(null)

// NO consultar automáticamente al cargar - solo cuando el usuario abra el modal
// Esto evita consultas innecesarias que pueden causar problemas de rendimiento
</script>

