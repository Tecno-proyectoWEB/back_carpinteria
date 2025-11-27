<template>
    <AppLayout>
        <div class="max-w-7xl mx-auto w-full">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-3xl font-bold text-gray-900">Servicios</h2>
                    <Link
                        v-if="canCreate"
                        :href="getRoute('servicios.create')"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                    >
                        + Nuevo Servicio
                    </Link>
                </div>

                <!-- Filtros -->
                <div class="mb-4 bg-white p-4 rounded-lg shadow">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
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
                    :data="serviciosData"
                    :columns="columns"
                    :loading="false"
                    :show-search="false"
                    :paginated="false"
                >
                    <template #cell-categoria="{ value }">
                        <Badge variant="info">{{ value?.nombre || 'Sin categoría' }}</Badge>
                    </template>
                    <template #cell-precio_base="{ value }">
                        ${{ parseFloat(value || 0).toFixed(2) }}
                    </template>
                    <template #cell-tiempo_estimado="{ value }">
                        {{ value ? `${value} horas` : 'N/A' }}
                    </template>
                    <template #actions="{ row }">
                        <Link
                            v-if="row?.id"
                            :href="getRoute('servicios.show', row.id)"
                            class="text-blue-600 hover:text-blue-900 mr-3"
                        >
                            Ver
                        </Link>
                        <Link
                            v-if="canEdit && row?.id"
                            :href="getRoute('servicios.edit', row.id)"
                            class="text-indigo-600 hover:text-indigo-900 mr-3"
                        >
                            Editar
                        </Link>
                        <button
                            v-if="canDelete && row?.id"
                            @click="deleteServicio(row)"
                            class="text-red-600 hover:text-red-900"
                        >
                            Desactivar
                        </button>
                    </template>
                </DataTable>

                <!-- Paginación -->
                <div v-if="servicios.links" class="mt-4">
                    <div class="flex justify-center">
                        <div v-for="link in servicios.links" :key="link.label">
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
    servicios: {
        type: Object,
        default: () => ({ data: [] }),
    },
    categorias: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

// Asegurar que servicios.data sea un array válido
const serviciosData = computed(() => {
    if (!props.servicios || !props.servicios.data) {
        return [];
    }
    return Array.isArray(props.servicios.data) ? props.servicios.data : [];
});

const columns = [
    { key: 'id', label: 'ID', sortable: true },
    { key: 'nombre', label: 'Nombre', sortable: true },
    { key: 'categoria', label: 'Categoría', sortable: false },
    { key: 'precio_base', label: 'Precio Base', sortable: true },
    { key: 'tiempo_estimado', label: 'Tiempo Estimado', sortable: true },
];

const filters = ref({
    search: props.filters?.search || '',
    categoria_id: props.filters?.categoria_id || '',
});

const canCreate = computed(() => {
    const rol = page.props.auth?.user?.rol?.nombre;
    return rol && ['PROPIETARIO', 'CARPINTERO'].includes(rol);
});

const canEdit = computed(() => canCreate.value);
const canDelete = computed(() => canCreate.value);


const applyFilters = () => {
    router.get(getRoute('servicios.index'), filters.value, {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    filters.value = { search: '', categoria_id: '' };
    applyFilters();
};

const deleteServicio = (servicio) => {
    if (!servicio?.id) return;
    if (confirm(`¿Está seguro de desactivar el servicio "${servicio?.nombre || 'este servicio'}"?`)) {
        router.delete(getRoute('servicios.destroy', servicio.id), {
            preserveScroll: true,
        });
    }
};
</script>

