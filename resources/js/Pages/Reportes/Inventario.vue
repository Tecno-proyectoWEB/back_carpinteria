<template>
    <Layout :auth="auth">
        <div>
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-3xl font-bold text-gray-900">Reporte de Inventario</h1>
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

            <!-- Resumen -->
            <div class="grid grid-cols-2 gap-6 mb-6">
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-sm font-medium text-gray-500">Ingresos</h3>
                    <p class="mt-2 text-3xl font-bold text-green-600">{{ ingresos.length }}</p>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-sm font-medium text-gray-500">Salidas</h3>
                    <p class="mt-2 text-3xl font-bold text-red-600">{{ salidas.length }}</p>
                </div>
            </div>

            <!-- Tabla de Movimientos -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cantidad</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Motivo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuario</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="movimiento in movimientos" :key="movimiento.id">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ new Date(movimiento.fecha).toLocaleDateString() }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span :class="movimiento.tipo === 'INGRESO' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" class="px-2 py-1 text-xs font-semibold rounded-full">
                                    {{ movimiento.tipo }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ movimiento.material?.nombre || movimiento.producto?.nombre || 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ movimiento.cantidad }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ movimiento.motivo || 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ movimiento.usuario?.nombre }} {{ movimiento.usuario?.apellido }}
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
    movimientos: Array,
    ingresos: Array,
    salidas: Array,
    filters: Object,
})

const filters = ref({
    fecha_inicio: props.filters?.fecha_inicio || new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().slice(0, 10),
    fecha_fin: props.filters?.fecha_fin || new Date().toISOString().slice(0, 10),
})

const filtrar = () => {
    router.get(route('reportes.inventario'), filters.value, { preserveState: true })
}
</script>

