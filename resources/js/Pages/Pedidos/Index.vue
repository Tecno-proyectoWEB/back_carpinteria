<template>
    <AppLayout>
        <div class="max-w-7xl mx-auto w-full">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-3xl font-bold text-gray-900">Pedidos</h2>
                    <Link
                        v-if="canCreate"
                        :href="getRoute('pedidos.create')"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                    >
                        + Nuevo Pedido
                    </Link>
                </div>

                <!-- Filtros -->
                <div class="mb-4 bg-white p-4 rounded-lg shadow">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                            <select
                                v-model="filters.estado"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md"
                                @change="applyFilters"
                            >
                                <option value="">Todos</option>
                                <option value="completado">Completados</option>
                                <option value="pendiente">Pendientes</option>
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

                <!-- Tabla -->
                <DataTable
                    :data="pedidosData"
                    :columns="columns"
                    :loading="false"
                    :show-search="false"
                    :paginated="false"
                >
                    <template #cell-fecha="{ value }">
                        {{ value ? new Date(value).toLocaleDateString('es-AR') : 'N/A' }}
                    </template>
                    <template #cell-usuario="{ value }">
                        {{ value?.nombre || '' }} {{ value?.apellido || '' }}
                    </template>
                    <template #cell-estado="{ row }">
                        <Badge :variant="row?.estado ? 'success' : 'warning'">
                            {{ row?.estado ? 'Completado' : 'Pendiente' }}
                        </Badge>
                    </template>
                    <template #cell-importe_total_desc="{ value }">
                        <span class="font-semibold text-green-600">${{ parseFloat(value || 0).toFixed(2) }}</span>
                    </template>
                    <template #actions="{ row }">
                        <Link
                            v-if="row?.id"
                            :href="getRoute('pedidos.show', row.id)"
                            class="text-blue-600 hover:text-blue-900"
                        >
                            Ver Detalle
                        </Link>
                    </template>
                </DataTable>

                <!-- Paginación -->
                <div v-if="pedidos.links" class="mt-4">
                    <div class="flex justify-center">
                        <div v-for="link in pedidos.links" :key="link.label">
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
    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { getRoute } from '@/utils/routeHelper';
import AppLayout from '@/Layouts/AppLayout.vue';
import DataTable from '@/Components/Table/DataTable.vue';
import Badge from '@/Components/UI/Badge.vue';

const page = usePage();

const props = defineProps({
    pedidos: {
        type: Object,
        default: () => ({ data: [] }),
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

// Asegurar que pedidos.data sea un array válido
const pedidosData = computed(() => {
    if (!props.pedidos || !props.pedidos.data) {
        return [];
    }
    return Array.isArray(props.pedidos.data) ? props.pedidos.data : [];
});

const columns = [
    { key: 'id', label: 'ID', sortable: true },
    { key: 'fecha', label: 'Fecha', sortable: true },
    { key: 'usuario', label: 'Cliente', sortable: false },
    { key: 'estado', label: 'Estado', sortable: true },
    { key: 'importe_total_desc', label: 'Total', sortable: true },
];

const filters = ref({
    estado: props.filters?.estado || '',
    fecha_desde: props.filters?.fecha_desde || '',
    fecha_hasta: props.filters?.fecha_hasta || '',
});

const canCreate = computed(() => {
    const rol = page.props.auth?.user?.rol?.nombre;
    return rol && ['PROPIETARIO', 'SECRETARIA', 'CLIENTE'].includes(rol);
});

const applyFilters = () => {
    router.get(getRoute('pedidos.index'), filters.value, {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    filters.value = { estado: '', fecha_desde: '', fecha_hasta: '' };
    applyFilters();
};
</script>

