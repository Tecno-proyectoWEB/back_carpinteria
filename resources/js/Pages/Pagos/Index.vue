<template>
    <AppLayout :auth="auth" :menu-items="menuItems" :page-visits="pageVisits" :visitas-pagina="visitasPagina">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <h2 class="text-3xl font-bold text-gray-900 mb-6">Gesti├│n de Pagos</h2>

                <!-- Estad├¡sticas -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div class="bg-white p-4 rounded-lg shadow">
                        <p class="text-sm text-gray-500">Total Pendientes</p>
                        <p class="text-2xl font-bold text-yellow-600">${{ formatCurrency(estadisticas.total_pendientes) }}</p>
                    </div>
                    <div class="bg-white p-4 rounded-lg shadow">
                        <p class="text-sm text-gray-500">Total Pagados (Mes)</p>
                        <p class="text-2xl font-bold text-green-600">${{ formatCurrency(estadisticas.total_pagados_mes) }}</p>
                    </div>
                    <div class="bg-white p-4 rounded-lg shadow">
                        <p class="text-sm text-gray-500">Pagos Vencidos</p>
                        <p class="text-2xl font-bold text-red-600">{{ estadisticas.total_vencidos }}</p>
                    </div>
                </div>

                <!-- Filtros -->
                <div class="mb-4 bg-white p-4 rounded-lg shadow">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
                            <input
                                v-model="filters.search"
                                type="text"
                                placeholder="ID pedido, observaciones..."
                                class="w-full px-3 py-2 border border-gray-300 rounded-md"
                                @input="applyFilters"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                            <select
                                v-model="filters.estado"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md"
                                @change="applyFilters"
                            >
                                <option value="">Todos</option>
                                <option value="PENDIENTE">Pendiente</option>
                                <option value="PAGADO">Pagado</option>
                                <option value="VENCIDO">Vencido</option>
                                <option value="CANCELADO">Cancelado</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
                            <select
                                v-model="filters.tipo"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md"
                                @change="applyFilters"
                            >
                                <option value="">Todos</option>
                                <option value="CONTADO">Contado</option>
                                <option value="CREDITO">Cr├®dito</option>
                                <option value="CUOTA">Cuota</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Pedido</label>
                            <select
                                v-model="filters.pedido_id"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md"
                                @change="applyFilters"
                            >
                                <option value="">Todos</option>
                                <option v-for="pedido in pedidos" :key="pedido.id" :value="pedido.id">
                                    Pedido #{{ pedido.id }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Desde</label>
                            <input
                                v-model="filters.fecha_desde"
                                type="date"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md"
                                @change="applyFilters"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Hasta</label>
                            <input
                                v-model="filters.fecha_hasta"
                                type="date"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md"
                                @change="applyFilters"
                            />
                        </div>
                        <div class="flex items-end">
                            <button
                                @click="clearFilters"
                                class="px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50"
                            >
                                Limpiar
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Tabla de Pagos -->
                <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pedido</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Monto</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cuota</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Vencimiento</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">M├®todo</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr
                                v-for="pago in pagos.data"
                                :key="pago.id"
                                :class="{
                                    'bg-red-50': pago.estado === 'VENCIDO',
                                    'bg-yellow-50': pago.estado === 'PENDIENTE' && isVencido(pago.fecha_vencimiento),
                                }"
                                class="hover:bg-gray-50"
                            >
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    #{{ pago.id }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <Link
                                        :href="route('pedidos.show', pago.pedido_id)"
                                        class="text-blue-600 hover:text-blue-900"
                                    >
                                        Pedido #{{ pago.pedido_id }}
                                    </Link>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    ${{ formatCurrency(pago.monto) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ pago.tipo }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <span v-if="pago.numero_cuota">{{ pago.numero_cuota }}/{{ totalCuotas(pago.pedido_id) }}</span>
                                    <span v-else>-</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <Badge :variant="getEstadoVariant(pago.estado)">
                                        {{ pago.estado }}
                                    </Badge>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ pago.fecha_vencimiento ? formatDate(pago.fecha_vencimiento) : 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ pago.metodo_pago?.nombre || 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <Link
                                        :href="route('pagos.show', pago.id)"
                                        class="text-indigo-600 hover:text-indigo-900 mr-3"
                                    >
                                        Ver
                                    </Link>
                                </td>
                            </tr>
                            <tr v-if="pagos.data.length === 0">
                                <td colspan="9" class="px-6 py-4 text-center text-gray-500">
                                    No se encontraron pagos
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Paginaci├│n -->
                <div v-if="pagos.links" class="mt-4">
                    <div class="flex justify-center">
                        <div v-for="link in pagos.links" :key="link.label">
                            <Link
                                v-if="link.url"
                                :href="link.url"
                                v-html="link.label"
                                :class="[
                                    'px-3 py-2 border rounded-md mx-1',
                                    link.active ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50'
                                ]"
                            ></Link>
                            <span
                                v-else
                                v-html="link.label"
                                class="px-3 py-2 border rounded-md mx-1 bg-gray-100 text-gray-400"
                            ></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Pages/Layout.vue';
import Badge from '@/Components/UI/Badge.vue';

const props = defineProps({
        auth: { type: Object, required: true },
    visitasPagina: { type: Number, default: 0 },
pagos: Object,
    pedidos: Array,
    metodosPago: Array,
    estadisticas: Object,
    menuItems: Array,
    pageVisits: Number,
    filters: Object,
});

const filters = ref({
    search: props.filters?.search || '',
    estado: props.filters?.estado || '',
    tipo: props.filters?.tipo || '',
    pedido_id: props.filters?.pedido_id || '',
    fecha_desde: props.filters?.fecha_desde || '',
    fecha_hasta: props.filters?.fecha_hasta || '',
});

const applyFilters = () => {
    router.get(route('pagos.index'), filters.value, {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    filters.value = {
        search: '',
        estado: '',
        tipo: '',
        pedido_id: '',
        fecha_desde: '',
        fecha_hasta: '',
    };
    applyFilters();
};

const formatCurrency = (value) => {
    return Number(value || 0).toFixed(2);
};

const formatDate = (date) => {
    if (!date) return '';
    return new Date(date).toLocaleDateString('es-ES');
};

const isVencido = (fechaVencimiento) => {
    if (!fechaVencimiento) return false;
    return new Date(fechaVencimiento) < new Date();
};

const getEstadoVariant = (estado) => {
    const variants = {
        'PAGADO': 'success',
        'PENDIENTE': 'warning',
        'VENCIDO': 'error',
        'CANCELADO': 'error',
    };
    return variants[estado] || 'info';
};

const totalCuotas = (pedidoId) => {
    const pedido = props.pedidos.find(p => p.id === pedidoId);
    if (!pedido) return 0;
    // Esto deber├¡a venir del backend, pero por ahora retornamos 0
    return 0;
};
</script>

