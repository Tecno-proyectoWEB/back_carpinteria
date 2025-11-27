<template>
    <Layout :auth="auth">
        <div class="max-w-4xl">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-3xl font-bold text-primary">Detalle de la Venta #{{ venta.id }}</h1>
                <Link :href="route('ventas.index')" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400">
                    Volver
                </Link>
            </div>

            <!-- QRs de Pagos (si existen) -->
            <div v-if="pagosConQR && pagosConQR.length > 0" class="mb-6 space-y-4">
                <h2 class="text-xl font-semibold text-primary mb-4">Códigos QR para Pagar</h2>
                <div v-for="pagoQR in pagosConQR" :key="pagoQR.id" class="mb-4">
                    <div v-if="pagoQR && pagoQR.estado === 'PENDIENTE' && pagoQR.qr_image" class="bg-white rounded-lg shadow p-4 border-2 border-blue-200">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="text-lg font-semibold text-primary">
                                {{ pagoQR.tipo === 'CUOTA' ? `Cuota ${pagoQR.numero_cuota}` : 'Pago al Contado' }}
                            </h3>
                            <span class="text-sm font-medium text-gray-600">${{ pagoQR.monto?.toFixed(2) }}</span>
                        </div>
                        <QRPayment
                            :pago="pagoQR"
                            :auto-consult="false"
                            @show-modal="showModal = true; pagoSeleccionado = pagoQR"
                        />
                    </div>
                </div>
            </div>
            
            <!-- QR único (compatibilidad con versión anterior) -->
            <div v-else-if="pagoConQR && pagoConQR.estado === 'PENDIENTE' && pagoConQR.qr_image" class="mb-6">
                <QRPayment
                    :pago="pagoConQR"
                    :auto-consult="true"
                    @show-modal="showModal = true; pagoSeleccionado = pagoConQR"
                />
            </div>

            <div class="bg-secondary rounded-lg shadow p-6 space-y-6">
                <!-- Información de la venta -->
                <div>
                    <h2 class="text-xl font-semibold text-primary mb-4">Información de la Venta</h2>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-secondary">Cliente</p>
                            <p class="font-medium text-primary">{{ venta.usuario?.nombre }} {{ venta.usuario?.apellido }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-secondary">Fecha</p>
                            <p class="font-medium text-primary">{{ formatDate(venta.fecha) }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-secondary">Método de Pago</p>
                            <p class="font-medium text-primary">{{ venta.metodo_pago?.nombre }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-secondary">Estado</p>
                            <span :class="venta.estado ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'" class="px-2 py-1 rounded-full text-xs font-medium">
                                {{ venta.estado ? 'Completado' : 'Pendiente' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Detalles -->
                <div>
                    <h2 class="text-xl font-semibold text-primary mb-4">Detalles</h2>
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cantidad</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Precio Unitario</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="detalle in venta.detalles" :key="detalle.id">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    {{ detalle.producto?.nombre || detalle.servicio?.nombre }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ detalle.cantidad }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">${{ detalle.precio_unitario?.toFixed(2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap font-medium">${{ detalle.importe_total?.toFixed(2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Total -->
                <div class="border-t border-theme pt-4">
                    <div class="flex justify-between items-center">
                        <span class="text-lg font-semibold text-primary">Total:</span>
                        <span class="text-2xl font-bold text-primary">${{ venta.importe_total?.toFixed(2) }}</span>
                    </div>
                </div>

                <!-- Pagos -->
                <div v-if="venta.pagos && venta.pagos.length > 0">
                    <h2 class="text-xl font-semibold text-primary mb-4">Pagos</h2>
                    <div class="space-y-2">
                        <div
                            v-for="pago in venta.pagos"
                            :key="pago.id"
                            class="flex justify-between items-center p-3 bg-white rounded cursor-pointer hover:bg-gray-50 border-l-4"
                            :class="pago.qr_image && pago.estado === 'PENDIENTE' ? 'border-blue-500' : 'border-gray-200'"
                            @click="abrirModalPago(pago)"
                        >
                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    <p class="font-medium text-primary">
                                        {{ pago.tipo === 'CONTADO' ? 'Pago al Contado' : `Cuota ${pago.numero_cuota}` }}
                                    </p>
                                    <span v-if="pago.qr_image && pago.estado === 'PENDIENTE'" class="bg-blue-100 text-blue-800 text-xs font-semibold px-2 py-1 rounded-full">
                                        QR Disponible
                                    </span>
                                </div>
                                <p class="text-sm text-secondary">{{ pago.observaciones }}</p>
                                <p v-if="pago.nro_pago" class="text-xs text-gray-500">Nro: {{ pago.nro_pago }}</p>
                            </div>
                            <div class="text-right">
                                <p class="font-medium text-primary">${{ pago.monto?.toFixed(2) }}</p>
                                <span :class="pago.estado === 'PAGADO' ? 'bg-green-100 text-green-800' : pago.estado === 'PENDIENTE' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'" class="px-2 py-1 rounded-full text-xs font-medium">
                                    {{ pago.estado }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de Estado de Pago -->
        <PaymentModal
            v-if="pagoSeleccionado"
            :show="showModal"
            :pago="pagoSeleccionado"
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
    venta: Object,
    pagoConQR: Object,
    pagosConQR: {
        type: Array,
        default: () => [],
    },
})

const showModal = ref(false)
const pagoSeleccionado = ref(null)
const paymentInfo = ref(null)

const formatDate = (date) => {
    if (!date) return ''
    return new Date(date).toLocaleDateString('es-ES')
}

const abrirModalPago = (pago) => {
    pagoSeleccionado.value = pago
    showModal.value = true

    if (pago.qr_image && pago.estado === 'PENDIENTE') {
        router.get(route('payment.status', pago.id), {}, {
            preserveState: true,
            preserveScroll: true,
            onSuccess: (page) => {
                paymentInfo.value = page.props.paymentInfo
            }
        })
    }
}
</script>

