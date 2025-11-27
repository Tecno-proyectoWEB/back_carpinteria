<template>
    <Layout :auth="auth">
        <div>
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-3xl font-bold text-gray-900">Pagos</h1>
                <Link :href="route('pagos.create')" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                    Nuevo Pago
                </Link>
            </div>

            <!-- Filtros -->
            <div class="bg-white rounded-lg shadow p-4 mb-6">
                <form @submit.prevent="filtrar" class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Estado</label>
                        <select v-model="filters.estado" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            <option value="">Todos</option>
                            <option value="PENDIENTE">Pendiente</option>
                            <option value="PAGADO">Pagado</option>
                            <option value="VENCIDO">Vencido</option>
                            <option value="CANCELADO">Cancelado</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tipo</label>
                        <select v-model="filters.tipo" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            <option value="">Todos</option>
                            <option value="CONTADO">Contado</option>
                            <option value="CREDITO">Crédito</option>
                            <option value="CUOTA">Cuota</option>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="w-full px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700">
                            Filtrar
                        </button>
                    </div>
                </form>
            </div>

            <div class="bg-white rounded-lg shadow overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Venta</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Monto</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha Pago</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Método Pago</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="pago in pagos.data" :key="pago.id">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <Link :href="route('ventas.show', pago.venta_id)" class="text-blue-600 hover:text-blue-900">
                                    Venta #{{ pago.venta_id }}
                                </Link>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                ${{ pago.monto?.toFixed(2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ pago.tipo }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span :class="{
                                    'bg-yellow-100 text-yellow-800': pago.estado === 'PENDIENTE',
                                    'bg-green-100 text-green-800': pago.estado === 'PAGADO',
                                    'bg-red-100 text-red-800': pago.estado === 'VENCIDO',
                                    'bg-gray-100 text-gray-800': pago.estado === 'CANCELADO'
                                }" class="px-2 py-1 text-xs font-semibold rounded-full">
                                    {{ pago.estado }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ pago.fecha_pago ? new Date(pago.fecha_pago).toLocaleDateString() : 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ pago.metodo_pago?.nombre || 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                <Link :href="route('pagos.show', pago.id)" class="text-blue-600 hover:text-blue-900">Ver</Link>
                                <Link :href="route('pagos.edit', pago.id)" class="text-green-600 hover:text-green-900">Editar</Link>
                                <button v-if="pago.estado === 'PENDIENTE'" @click="registrarPago(pago.id)" class="text-purple-600 hover:text-purple-900">Registrar</button>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Paginación -->
                <div class="px-6 py-4 border-t border-gray-200" v-if="pagos.links">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-700">
                            Mostrando {{ pagos.from }} a {{ pagos.to }} de {{ pagos.total }} resultados
                        </div>
                        <div class="flex space-x-2">
                            <Link v-for="link in pagos.links" :key="link.label" :href="link.url || '#'" v-html="link.label"
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
    pagos: Object,
    filters: Object,
})

const filters = ref({
    estado: props.filters?.estado || '',
    tipo: props.filters?.tipo || '',
})

const filtrar = () => {
    router.get(route('pagos.index'), filters.value, { preserveState: true })
}

const registrarPago = (id) => {
    if (confirm('¿Está seguro de registrar este pago?')) {
        router.post(route('pagos.registrar', id))
    }
}
</script>

