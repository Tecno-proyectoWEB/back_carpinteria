<template>
    <Layout :auth="auth">
        <div>
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-3xl font-bold text-gray-900">Estadísticas</h1>
                <Link :href="route('reportes.index')" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                    Volver
                </Link>
            </div>

            <!-- Filtros -->
            <div class="bg-white rounded-lg shadow p-4 mb-6">
                <form @submit.prevent="filtrar" class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Fecha Inicio</label>
                        <input v-model="filters.fecha_inicio" type="date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Fecha Fin</label>
                        <input v-model="filters.fecha_fin" type="date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                            Filtrar
                        </button>
                    </div>
                </form>
            </div>

            <!-- Resumen General -->
            <div class="grid grid-cols-4 gap-6 mb-6">
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-sm font-medium text-gray-500">Total Ventas</h3>
                    <p class="mt-2 text-2xl font-bold text-gray-900">${{ totalVentas?.toFixed(2) }}</p>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-sm font-medium text-gray-500">Pagos Pendientes</h3>
                    <p class="mt-2 text-2xl font-bold text-yellow-600">{{ pagosPendientes }}</p>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-sm font-medium text-gray-500">Productos Bajo Stock</h3>
                    <p class="mt-2 text-2xl font-bold text-red-600">{{ productosBajoStock }}</p>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-sm font-medium text-gray-500">Materiales Bajo Stock</h3>
                    <p class="mt-2 text-2xl font-bold text-red-600">{{ materialesBajoStock }}</p>
                </div>
            </div>

            <!-- Ventas por Vendedor -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Ventas por Vendedor</h2>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Vendedor</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="vendedor in ventasPorVendedor" :key="vendedor.usuario_id">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ vendedor.usuario?.nombre }} {{ vendedor.usuario?.apellido }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                ${{ vendedor.total?.toFixed(2) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Productos Más Vendidos -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Productos Más Vendidos</h2>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Producto</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cantidad</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Ventas</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="producto in productosMasVendidos" :key="producto.id">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ producto.nombre }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ producto.total_cantidad }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${{ producto.total_ventas?.toFixed(2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Servicios Más Vendidos -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Servicios Más Vendidos</h2>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Servicio</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cantidad</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Ingresos</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="servicio in serviciosMasVendidos" :key="servicio.id">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ servicio.nombre }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ servicio.total_ventas }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${{ servicio.total_ingresos?.toFixed(2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </Layout>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3'
import { route } from '../../ziggy.js'
import Layout from '../Layout.vue'
import { ref } from 'vue'

const props = defineProps({
    auth: Object,
    totalVentas: Number,
    ventasPorVendedor: Array,
    productosMasVendidos: Array,
    serviciosMasVendidos: Array,
    pagosPendientes: Number,
    pagosPagados: Number,
    productosBajoStock: Number,
    materialesBajoStock: Number,
    ingresosInventario: Number,
    salidasInventario: Number,
    filters: Object,
})

const filters = ref({
    fecha_inicio: props.filters?.fecha_inicio || new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().slice(0, 10),
    fecha_fin: props.filters?.fecha_fin || new Date().toISOString().slice(0, 10),
})

const filtrar = () => {
    router.get(route('reportes.estadisticas'), filters.value, { preserveState: true })
}
</script>

