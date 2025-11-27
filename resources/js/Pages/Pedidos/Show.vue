<template>
    <Layout :auth="auth">
        <div class="max-w-4xl">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-3xl font-bold text-gray-900">Detalle del Pedido #{{ pedido.id }}</h1>
                <Link :href="route('pedidos.index')" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400">
                    Volver
                </Link>
            </div>

            <div class="bg-white rounded-lg shadow p-6 space-y-6">
                <!-- Información del pedido -->
                <div>
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Información del Pedido</h2>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-500">Cliente</p>
                            <p class="font-medium">{{ pedido.usuario?.nombre }} {{ pedido.usuario?.apellido }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Fecha</p>
                            <p class="font-medium">{{ new Date(pedido.fecha).toLocaleDateString() }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Método de Pago</p>
                            <p class="font-medium">{{ pedido.metodo_pago?.nombre }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Estado</p>
                            <span :class="pedido.estado ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'" class="px-2 py-1 rounded-full text-xs font-medium">
                                {{ pedido.estado ? 'Completado' : 'Pendiente' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Detalles -->
                <div>
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Detalles</h2>
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
                            <tr v-for="detalle in pedido.detalles" :key="detalle.id">
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
                <div class="border-t pt-4">
                    <div class="flex justify-between items-center">
                        <span class="text-lg font-semibold text-gray-900">Total:</span>
                        <span class="text-2xl font-bold text-blue-600">${{ pedido.importe_total?.toFixed(2) }}</span>
                    </div>
                </div>

                <!-- Pagos -->
                <div v-if="pedido.pagos && pedido.pagos.length > 0">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Pagos</h2>
                    <div class="space-y-2">
                        <div v-for="pago in pedido.pagos" :key="pago.id" class="flex justify-between items-center p-3 bg-gray-50 rounded">
                            <div>
                                <p class="font-medium">{{ pago.tipo === 'CONTADO' ? 'Pago al Contado' : `Cuota ${pago.numero_cuota}` }}</p>
                                <p class="text-sm text-gray-500">{{ pago.observaciones }}</p>
                            </div>
                            <div class="text-right">
                                <p class="font-medium">${{ pago.monto?.toFixed(2) }}</p>
                                <span :class="pago.estado === 'PAGADO' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'" class="px-2 py-1 rounded-full text-xs font-medium">
                                    {{ pago.estado }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Layout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import { route } from '../../ziggy.js'
import Layout from '../Layout.vue'

defineProps({
    auth: Object,
    pedido: Object,
})
</script>

