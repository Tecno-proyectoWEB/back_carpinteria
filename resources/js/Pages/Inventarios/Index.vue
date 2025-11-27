<template>
    <Layout :auth="auth">
        <div>
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-3xl font-bold text-gray-900">Movimientos de Inventario</h1>
                <Link :href="route('inventarios.create')" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                    Nuevo Movimiento
                </Link>
            </div>

            <!-- Filtros -->
            <div class="bg-white rounded-lg shadow p-4 mb-6">
                <form @submit.prevent="filtrar" class="grid grid-cols-5 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tipo</label>
                        <select v-model="filters.tipo" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            <option value="">Todos</option>
                            <option value="INGRESO">Ingreso</option>
                            <option value="SALIDA">Salida</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Material</label>
                        <select v-model="filters.material_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            <option value="">Todos</option>
                            <option v-for="material in materiales" :key="material.id" :value="material.id">{{ material.nombre }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Producto</label>
                        <select v-model="filters.producto_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            <option value="">Todos</option>
                            <option v-for="producto in productos" :key="producto.id" :value="producto.id">{{ producto.nombre }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Fecha Desde</label>
                        <input v-model="filters.fecha_desde" type="date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Fecha Hasta</label>
                        <input v-model="filters.fecha_hasta" type="date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>
                </form>
            </div>

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
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="movimiento in movimientos.data" :key="movimiento.id">
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
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <Link :href="route('inventarios.show', movimiento.id)" class="text-blue-600 hover:text-blue-900">Ver</Link>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Paginación -->
                <div class="px-6 py-4 border-t border-gray-200" v-if="movimientos.links">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-700">
                            Mostrando {{ movimientos.from }} a {{ movimientos.to }} de {{ movimientos.total }} resultados
                        </div>
                        <div class="flex space-x-2">
                            <Link v-for="link in movimientos.links" :key="link.label" :href="link.url || '#'" v-html="link.label"
                                :class="link.active ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50'"
                                class="px-3 py-2 border border-gray-300 rounded-md text-sm font-medium"></Link>
                        </div>
                    </div>
                </div>
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
    movimientos: Object,
    materiales: Array,
    productos: Array,
    filters: Object,
})

const filters = ref({
    tipo: props.filters?.tipo || '',
    material_id: props.filters?.material_id || '',
    producto_id: props.filters?.producto_id || '',
    fecha_desde: props.filters?.fecha_desde || '',
    fecha_hasta: props.filters?.fecha_hasta || '',
})

const filtrar = () => {
    router.get(route('inventarios.index'), filters.value, { preserveState: true })
}
</script>

