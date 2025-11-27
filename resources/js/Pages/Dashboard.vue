<template>
    <AppLayout>
        <div class="max-w-7xl mx-auto w-full">
            <h2 class="text-3xl font-bold text-gray-900 mb-6">Dashboard</h2>

            <!-- Resumen de estadísticas -->
            <div v-if="estadisticas.resumen" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    <div class="bg-white p-6 rounded-lg shadow">
                        <h3 class="text-sm font-medium text-gray-500">Productos</h3>
                        <p class="text-2xl font-bold text-gray-900">{{ estadisticas.resumen.total_productos }}</p>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow">
                        <h3 class="text-sm font-medium text-gray-500">Servicios</h3>
                        <p class="text-2xl font-bold text-gray-900">{{ estadisticas.resumen.total_servicios }}</p>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow">
                        <h3 class="text-sm font-medium text-gray-500">Usuarios</h3>
                        <p class="text-2xl font-bold text-gray-900">{{ estadisticas.resumen.total_usuarios }}</p>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow">
                        <h3 class="text-sm font-medium text-gray-500">Ventas del Mes</h3>
                        <p class="text-2xl font-bold text-green-600">${{ estadisticas.resumen.ventas_mes?.toFixed(2) }}</p>
                </div>
            </div>

            <!-- Ventas recientes -->
            <div v-if="estadisticas.ventas_recientes?.length > 0" class="bg-white rounded-lg shadow mb-6">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4">Ventas Recientes</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cliente</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Método Pago</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="venta in estadisticas.ventas_recientes" :key="venta.id">
                                        <td class="px-4 py-3 text-sm">{{ venta.id }}</td>
                                        <td class="px-4 py-3 text-sm">{{ new Date(venta.fecha).toLocaleDateString() }}</td>
                                        <td class="px-4 py-3 text-sm">{{ venta.cliente }}</td>
                                        <td class="px-4 py-3 text-sm font-semibold">${{ venta.total?.toFixed(2) }}</td>
                                        <td class="px-4 py-3 text-sm">{{ venta.metodo_pago }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                </div>
            </div>

            <!-- Alertas de stock -->
            <div v-if="estadisticas.alertas_stock?.total > 0" class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-semibold text-yellow-800 mb-2">⚠️ Alertas de Stock Bajo</h3>
                    <p class="text-yellow-700">Hay {{ estadisticas.alertas_stock.total }} items con stock bajo</p>
            </div>

            <!-- Productos más vendidos -->
            <div v-if="estadisticas.productos_mas_vendidos?.length > 0" class="bg-white rounded-lg shadow">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4">Productos Más Vendidos</h3>
                        <ul class="space-y-2">
                            <li v-for="producto in estadisticas.productos_mas_vendidos" :key="producto.id"
                                class="flex justify-between items-center p-3 bg-gray-50 rounded">
                                <span class="font-medium">{{ producto.nombre }}</span>
                                <span class="text-blue-600 font-semibold">{{ producto.total_vendido }} unidades</span>
                            </li>
                        </ul>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({
    estadisticas: {
        type: Object,
        default: () => ({}),
    },
});
</script>

