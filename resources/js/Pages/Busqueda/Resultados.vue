<template>
    <Layout :auth="auth">
        <div class="max-w-6xl mx-auto">
            <h1 class="text-3xl font-bold text-primary mb-6">Resultados de Búsqueda</h1>

            <div v-if="query" class="mb-4">
                <p class="text-secondary">Buscando: <strong>{{ query }}</strong></p>
            </div>

            <div class="space-y-6">
                <!-- Productos -->
                <div v-if="resultados.productos && resultados.productos.length > 0">
                    <h2 class="text-xl font-semibold text-primary mb-3">Productos ({{ resultados.productos.length }})</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div v-for="producto in resultados.productos" :key="producto.id" class="bg-secondary rounded-lg shadow p-4">
                            <Link :href="route('productos.show', producto.id)" class="block">
                                <h3 class="font-semibold text-primary">{{ producto.nombre }}</h3>
                                <p class="text-sm text-secondary mt-1">{{ producto.descripcion }}</p>
                                <p class="text-sm text-gray-600 mt-2">Stock: {{ producto.stock }}</p>
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Servicios -->
                <div v-if="resultados.servicios && resultados.servicios.length > 0">
                    <h2 class="text-xl font-semibold text-primary mb-3">Servicios ({{ resultados.servicios.length }})</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div v-for="servicio in resultados.servicios" :key="servicio.id" class="bg-secondary rounded-lg shadow p-4">
                            <Link :href="route('servicios.edit', servicio.id)" class="block">
                                <h3 class="font-semibold text-primary">{{ servicio.nombre }}</h3>
                                <p class="text-sm text-secondary mt-1">{{ servicio.descripcion }}</p>
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Materiales -->
                <div v-if="resultados.materiales && resultados.materiales.length > 0">
                    <h2 class="text-xl font-semibold text-primary mb-3">Materiales ({{ resultados.materiales.length }})</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div v-for="material in resultados.materiales" :key="material.id" class="bg-secondary rounded-lg shadow p-4">
                            <Link :href="route('materiales.show', material.id)" class="block">
                                <h3 class="font-semibold text-primary">{{ material.nombre }}</h3>
                                <p class="text-sm text-secondary mt-1">{{ material.descripcion }}</p>
                                <p class="text-sm text-gray-600 mt-2">Stock: {{ material.stock_actual }}</p>
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Ventas -->
                <div v-if="resultados.ventas && resultados.ventas.length > 0">
                    <h2 class="text-xl font-semibold text-primary mb-3">Ventas ({{ resultados.ventas.length }})</h2>
                    <div class="bg-secondary rounded-lg shadow overflow-hidden">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cliente</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="venta in resultados.ventas" :key="venta.id">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">#{{ venta.id }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ venta.usuario?.nombre }} {{ venta.usuario?.apellido }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        ${{ venta.importe_total?.toFixed(2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <Link :href="route('ventas.show', venta.id)" class="text-blue-600 hover:text-blue-900">Ver</Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pagos -->
                <div v-if="resultados.pagos && resultados.pagos.length > 0">
                    <h2 class="text-xl font-semibold text-primary mb-3">Pagos ({{ resultados.pagos.length }})</h2>
                    <div class="bg-secondary rounded-lg shadow overflow-hidden">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nro. Pago</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Monto</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="pago in resultados.pagos" :key="pago.id">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ pago.nro_pago || 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        ${{ pago.monto?.toFixed(2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="{
                                            'bg-yellow-100 text-yellow-800': pago.estado === 'PENDIENTE',
                                            'bg-green-100 text-green-800': pago.estado === 'PAGADO',
                                        }" class="px-2 py-1 rounded-full text-xs font-medium">
                                            {{ pago.estado }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <Link :href="route('pagos.show', pago.id)" class="text-blue-600 hover:text-blue-900">Ver</Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Sin resultados -->
                <div v-if="query && !hayResultados" class="text-center py-12">
                    <p class="text-secondary text-lg">No se encontraron resultados para "{{ query }}"</p>
                </div>
            </div>
        </div>
    </Layout>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import { route } from '../../ziggy.js'
import Layout from '../Layout.vue'

const props = defineProps({
    auth: Object,
    query: String,
    resultados: Object,
})

const hayResultados = computed(() => {
    return (props.resultados?.productos?.length > 0) ||
           (props.resultados?.servicios?.length > 0) ||
           (props.resultados?.materiales?.length > 0) ||
           (props.resultados?.ventas?.length > 0) ||
           (props.resultados?.pagos?.length > 0)
})
</script>


