<template>
    <AppLayout :menu-items="menuItems" :page-visits="pageVisits">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-3xl font-bold text-gray-900">Compras</h2>
                    <Link
                        v-if="canCreate"
                        :href="route('compras.create')"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                    >
                        + Nueva Compra
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
                                <option value="PENDIENTE">Pendiente</option>
                                <option value="COMPLETADA">Completada</option>
                                <option value="CANCELADA">Cancelada</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Proveedor</label>
                            <select
                                v-model="filters.proveedor_id"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md"
                                @change="applyFilters"
                            >
                                <option value="">Todos</option>
                                <option v-for="prov in proveedores" :key="prov.id" :value="prov.id">
                                    {{ prov.nombre }}
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
                    :data="compras.data"
                    :columns="columns"
                    :loading="false"
                    :show-search="false"
                    :paginated="false"
                >
                    <template #cell-fecha="{ value }">
                        {{ new Date(value).toLocaleDateString('es-AR') }}
                    </template>
                    <template #cell-proveedor="{ value }">
                        {{ value?.nombre }}
                    </template>
                    <template #cell-estado="{ row }">
                        <Badge
                            :variant="row.estado === 'COMPLETADA' ? 'success' : row.estado === 'CANCELADA' ? 'error' : 'warning'"
                        >
                            {{ row.estado }}
                        </Badge>
                    </template>
                    <template #cell-importe_total="{ value }">
                        <span class="font-semibold text-green-600">${{ parseFloat(value).toFixed(2) }}</span>
                    </template>
                    <template #actions="{ row }">
                        <Link
                            :href="route('compras.show', row.id)"
                            class="text-blue-600 hover:text-blue-900 mr-3"
                        >
                            Ver Detalle
                        </Link>
                        <button
                            v-if="row.estado === 'PENDIENTE' && canConfirm"
                            @click="confirmarCompra(row)"
                            class="text-green-600 hover:text-green-900"
                        >
                            Confirmar
                        </button>
                    </template>
                </DataTable>

                <!-- Paginación -->
                <div v-if="compras.links" class="mt-4">
                    <div class="flex justify-center">
                        <div v-for="link in compras.links" :key="link.label">
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
import AppLayout from '@/Layouts/AppLayout.vue';
import DataTable from '@/Components/Table/DataTable.vue';
import Badge from '@/Components/UI/Badge.vue';

const props = defineProps({
    compras: Object,
    proveedores: Array,
    menuItems: Array,
    pageVisits: Number,
    filters: Object,
});

const columns = [
    { key: 'id', label: 'ID', sortable: true },
    { key: 'fecha', label: 'Fecha', sortable: true },
    { key: 'proveedor', label: 'Proveedor', sortable: false },
    { key: 'estado', label: 'Estado', sortable: true },
    { key: 'importe_total', label: 'Total', sortable: true },
];

const filters = ref({
    estado: props.filters?.estado || '',
    proveedor_id: props.filters?.proveedor_id || '',
    fecha_desde: props.filters?.fecha_desde || '',
    fecha_hasta: props.filters?.fecha_hasta || '',
});

const canCreate = computed(() => {
    const rol = window.$page?.props?.auth?.user?.rol?.nombre;
    return ['PROPIETARIO', 'SECRETARIA'].includes(rol);
});

const canConfirm = computed(() => canCreate.value);

const applyFilters = () => {
    router.get(route('compras.index'), filters.value, {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    filters.value = { estado: '', proveedor_id: '', fecha_desde: '', fecha_hasta: '' };
    applyFilters();
};

const confirmarCompra = (compra) => {
    if (confirm(`¿Está seguro de confirmar la compra #${compra.id}? Esto actualizará el stock de los materiales.`)) {
        router.post(route('compras.confirmar', compra.id), {}, {
            preserveScroll: true,
        });
    }
};
</script>

