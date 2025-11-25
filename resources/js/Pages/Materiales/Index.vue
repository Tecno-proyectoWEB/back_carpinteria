<template>
    <AppLayout :menu-items="menuItems" :page-visits="pageVisits">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-3xl font-bold text-gray-900">Materiales</h2>
                    <Link
                        v-if="canCreate"
                        :href="route('materiales.create')"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                    >
                        + Nuevo Material
                    </Link>
                </div>

                <!-- Filtros -->
                <div class="mb-4 bg-white p-4 rounded-lg shadow">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
                            <input
                                v-model="filters.search"
                                type="text"
                                placeholder="Nombre o descripción..."
                                class="w-full px-3 py-2 border border-gray-300 rounded-md"
                                @input="applyFilters"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Categoría</label>
                            <select
                                v-model="filters.categoria_id"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md"
                                @change="applyFilters"
                            >
                                <option value="">Todas</option>
                                <option v-for="cat in categorias" :key="cat.id" :value="cat.id">
                                    {{ cat.nombre }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Sector</label>
                            <select
                                v-model="filters.sector_id"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md"
                                @change="applyFilters"
                            >
                                <option value="">Todos</option>
                                <option v-for="sector in sectores" :key="sector.id" :value="sector.id">
                                    {{ sector.nombre }}
                                </option>
                            </select>
                        </div>
                        <div class="flex items-end space-x-2">
                            <label class="flex items-center">
                                <input
                                    v-model="filters.stock_bajo"
                                    type="checkbox"
                                    class="mr-2"
                                    @change="applyFilters"
                                />
                                <span class="text-sm">Stock bajo</span>
                            </label>
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
                    :data="materiales.data"
                    :columns="columns"
                    :loading="false"
                    :show-search="false"
                    :paginated="false"
                >
                    <template #cell-categoria="{ value }">
                        <Badge variant="info">{{ value?.nombre || 'Sin categoría' }}</Badge>
                    </template>
                    <template #cell-sector="{ value }">
                        {{ value?.nombre || 'Sin sector' }}
                    </template>
                    <template #cell-precio="{ value }">
                        {{ value ? `$${parseFloat(value).toFixed(2)}` : 'N/A' }}
                    </template>
                    <template #cell-stock_actual="{ row }">
                        <span :class="row.stock_actual <= row.stock_minimo ? 'text-red-600 font-bold' : ''">
                            {{ row.stock_actual }} {{ row.unidad_medida || '' }}
                            <span v-if="row.stock_actual <= row.stock_minimo" class="text-xs">⚠️</span>
                        </span>
                    </template>
                    <template #cell-imagen="{ row }">
                        <img
                            v-if="row.imagen"
                            :src="`/storage/${row.imagen}`"
                            :alt="row.nombre"
                            class="h-12 w-12 object-cover rounded"
                        />
                        <span v-else class="text-gray-400">Sin imagen</span>
                    </template>
                    <template #actions="{ row }">
                        <Link
                            :href="route('materiales.show', row.id)"
                            class="text-blue-600 hover:text-blue-900 mr-3"
                        >
                            Ver
                        </Link>
                        <Link
                            v-if="canEdit"
                            :href="route('materiales.edit', row.id)"
                            class="text-indigo-600 hover:text-indigo-900 mr-3"
                        >
                            Editar
                        </Link>
                        <button
                            v-if="canDelete"
                            @click="deleteMaterial(row)"
                            class="text-red-600 hover:text-red-900"
                        >
                            Eliminar
                        </button>
                    </template>
                </DataTable>

                <!-- Paginación -->
                <div v-if="materiales.links" class="mt-4">
                    <div class="flex justify-center">
                        <div v-for="link in materiales.links" :key="link.label">
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
    materiales: Object,
    categorias: Array,
    sectores: Array,
    menuItems: Array,
    pageVisits: Number,
    filters: Object,
});

const columns = [
    { key: 'id', label: 'ID', sortable: true },
    { key: 'imagen', label: 'Imagen', sortable: false },
    { key: 'nombre', label: 'Nombre', sortable: true },
    { key: 'categoria', label: 'Categoría', sortable: false },
    { key: 'sector', label: 'Sector', sortable: false },
    { key: 'stock_actual', label: 'Stock', sortable: true },
    { key: 'precio', label: 'Precio', sortable: true },
];

const filters = ref({
    search: props.filters?.search || '',
    categoria_id: props.filters?.categoria_id || '',
    sector_id: props.filters?.sector_id || '',
    stock_bajo: props.filters?.stock_bajo || false,
});

const canCreate = computed(() => {
    const rol = window.$page?.props?.auth?.user?.rol?.nombre;
    return ['PROPIETARIO', 'CARPINTERO'].includes(rol);
});

const canEdit = computed(() => canCreate.value);
const canDelete = computed(() => canCreate.value);

const applyFilters = () => {
    router.get(route('materiales.index'), filters.value, {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    filters.value = { search: '', categoria_id: '', sector_id: '', stock_bajo: false };
    applyFilters();
};

const deleteMaterial = (material) => {
    if (confirm(`¿Está seguro de eliminar el material "${material.nombre}"?`)) {
        router.delete(route('materiales.destroy', material.id), {
            preserveScroll: true,
        });
    }
};
</script>
