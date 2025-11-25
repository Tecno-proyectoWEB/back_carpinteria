<template>
    <AppLayout :menu-items="menuItems" :page-visits="pageVisits">
        <div class="py-12">
            <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                    <!-- Header -->
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex justify-between items-center">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900">Pedido #{{ pedido.id }}</h2>
                            <p class="text-sm text-gray-500 mt-1">
                                {{ new Date(pedido.fecha).toLocaleDateString('es-AR') }}
                            </p>
                        </div>
                        <div class="flex items-center space-x-2">
                            <Badge :variant="pedido.estado ? 'success' : 'warning'">
                                {{ pedido.estado ? 'Completado' : 'Pendiente' }}
                            </Badge>
                            <Link
                                :href="route('pedidos.index')"
                                class="px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50"
                            >
                                Volver
                            </Link>
                        </div>
                    </div>

                    <div class="px-6 py-4">
                        <!-- Información del Cliente -->
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold mb-4">Información del Cliente</h3>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="text-sm font-medium text-gray-500">Cliente</label>
                                    <p class="text-gray-900">{{ pedido.usuario?.nombre }} {{ pedido.usuario?.apellido }}</p>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-gray-500">Método de Pago</label>
                                    <p class="text-gray-900">{{ pedido.metodoPago?.nombre }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Detalles del Pedido -->
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold mb-4">Detalles del Pedido</h3>
                            <div class="border rounded-lg overflow-hidden">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500">Tipo</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500">Nombre</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500">Cantidad</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500">Precio Unit.</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr v-for="detalle in pedido.detalles" :key="detalle.id">
                                            <td class="px-4 py-3 text-sm">
                                                <Badge :variant="detalle.producto_id ? 'info' : 'success'">
                                                    {{ detalle.producto_id ? 'Producto' : 'Servicio' }}
                                                </Badge>
                                            </td>
                                            <td class="px-4 py-3 text-sm">
                                                {{ detalle.producto?.nombre || detalle.servicio?.nombre }}
                                            </td>
                                            <td class="px-4 py-3 text-sm">{{ detalle.cantidad }}</td>
                                            <td class="px-4 py-3 text-sm">${{ parseFloat(detalle.precio_unitario).toFixed(2) }}</td>
                                            <td class="px-4 py-3 text-sm font-semibold">
                                                ${{ parseFloat(detalle.importe_total_desc).toFixed(2) }}
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tfoot class="bg-gray-50">
                                        <tr>
                                            <td colspan="4" class="px-4 py-3 text-right font-semibold">Total:</td>
                                            <td class="px-4 py-3 text-xl font-bold text-green-600">
                                                ${{ parseFloat(pedido.importe_total_desc).toFixed(2) }}
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        <!-- Pagos -->
                        <div v-if="pedido.pagos && pedido.pagos.length > 0">
                            <h3 class="text-lg font-semibold mb-4">Pagos</h3>
                            <div class="border rounded-lg overflow-hidden">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500">Monto</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500">Tipo</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500">Estado</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500">Fecha Pago</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500">Vencimiento</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr v-for="pago in pedido.pagos" :key="pago.id">
                                            <td class="px-4 py-3 text-sm font-semibold">
                                                ${{ parseFloat(pago.monto).toFixed(2) }}
                                            </td>
                                            <td class="px-4 py-3 text-sm">{{ pago.tipo }}</td>
                                            <td class="px-4 py-3 text-sm">
                                                <Badge
                                                    :variant="pago.estado === 'PAGADO' ? 'success' : pago.estado === 'VENCIDO' ? 'error' : 'warning'"
                                                >
                                                    {{ pago.estado }}
                                                </Badge>
                                            </td>
                                            <td class="px-4 py-3 text-sm">
                                                {{ pago.fecha_pago ? new Date(pago.fecha_pago).toLocaleDateString('es-AR') : '-' }}
                                            </td>
                                            <td class="px-4 py-3 text-sm">
                                                {{ pago.fecha_vencimiento ? new Date(pago.fecha_vencimiento).toLocaleDateString('es-AR') : '-' }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Badge from '@/Components/UI/Badge.vue';

defineProps({
    pedido: Object,
    menuItems: Array,
    pageVisits: Number,
});
</script>

