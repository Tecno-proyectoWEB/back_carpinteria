<template>
    <Layout :auth="auth">
        <div>
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-3xl font-bold text-gray-900">Reporte de Ventas</h1>
                <Link :href="route('reportes.index')" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                    Volver
                </Link>
            </div>

            <!-- Filtros -->
            <div class="bg-white rounded-lg shadow p-4 mb-6">
                <form @submit.prevent="filtrar" class="grid grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Fecha Inicio *</label>
                        <input v-model="filters.fecha_inicio" type="date" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Fecha Fin *</label>
                        <input v-model="filters.fecha_fin" type="date" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Vendedor</label>
                        <select v-model="filters.usuario_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            <option value="">Todos</option>
                            <!-- Aquí se pueden agregar usuarios si es necesario -->
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                            Filtrar
                        </button>
                    </div>
                </form>
            </div>

            <!-- Resumen -->
            <div class="grid grid-cols-3 gap-6 mb-6">
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-sm font-medium text-gray-500">Total Ventas</h3>
                    <p class="mt-2 text-3xl font-bold text-gray-900">${{ formatNumber(totalVentas) }}</p>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-sm font-medium text-gray-500">Ventas al Contado</h3>
                    <p class="mt-2 text-3xl font-bold text-green-600">${{ formatNumber(totalVentasContado) }}</p>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-sm font-medium text-gray-500">Ventas a Crédito</h3>
                    <p class="mt-2 text-3xl font-bold text-blue-600">${{ formatNumber(totalVentasCredito) }}</p>
                </div>
            </div>

            <!-- Tabla de Ventas -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Método Pago</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="venta in ventas" :key="venta.id">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ new Date(venta.fecha).toLocaleDateString() }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ venta.usuario?.nombre }} {{ venta.usuario?.apellido }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                ${{ formatNumber(venta.importe_total) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ venta.metodo_pago?.nombre || 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <Link :href="route('ventas.show', venta.id)" class="text-blue-600 hover:text-blue-900">Ver</Link>
                            </td>
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
    ventas: Array,
    totalVentas: Number,
    totalVentasContado: Number,
    totalVentasCredito: Number,
    filters: Object,
})

const filters = ref({
    fecha_inicio: props.filters?.fecha_inicio || new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().slice(0, 10),
    fecha_fin: props.filters?.fecha_fin || new Date().toISOString().slice(0, 10),
    usuario_id: props.filters?.usuario_id || '',
})

const filtrar = () => {
    router.get(route('reportes.ventas'), filters.value, { preserveState: true })
}

// Función helper para formatear números de forma segura
const formatNumber = (value) => {
    const num = Number(value) || 0
    return num.toFixed(2)
}
</script>

