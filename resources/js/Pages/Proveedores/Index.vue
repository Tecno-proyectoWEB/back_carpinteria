<template>
    <AppLayout>
        <div class="max-w-7xl mx-auto w-full">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-3xl font-bold text-gray-900">Proveedores</h2>
                    <Link
                        v-if="canCreate"
                        :href="getRoute('proveedores.create')"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                    >
                        + Nuevo Proveedor
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
                                placeholder="Nombre, RUC, email, teléfono..."
                                class="w-full px-3 py-2 border border-gray-300 rounded-md"
                                @input="applyFilters"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                            <select
                                v-model="filters.activo"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md"
                                @change="applyFilters"
                            >
                                <option value="">Todos</option>
                                <option value="activo">Activo</option>
                                <option value="inactivo">Inactivo</option>
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
                    :data="proveedoresData"
                    :columns="columns"
                    :loading="false"
                    :show-search="false"
                    :paginated="false"
                >
                    <template #cell-activo="{ row }">
                        <Badge :variant="row?.activo ? 'success' : 'error'">
                            {{ row?.activo ? 'Activo' : 'Inactivo' }}
                        </Badge>
                    </template>
                    <template #actions="{ row }">
                        <Link
                            v-if="row?.id"
                            :href="getRoute('proveedores.show', row.id)"
                            class="text-blue-600 hover:text-blue-900 mr-3"
                        >
                            Ver
                        </Link>
                        <Link
                            v-if="canEdit && row?.id"
                            :href="getRoute('proveedores.edit', row.id)"
                            class="text-indigo-600 hover:text-indigo-900 mr-3"
                        >
                            Editar
                        </Link>
                        <button
                            v-if="canDelete && row?.id"
                            @click="deleteProveedor(row)"
                            class="text-red-600 hover:text-red-900"
                        >
                            Eliminar
                        </button>
                    </template>
                </DataTable>

                <!-- Paginación -->
                <div v-if="proveedores.links" class="mt-4">
                    <div class="flex justify-center">
                        <div v-for="link in proveedores.links" :key="link.label">
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
    proveedores: {
        type: Object,
        default: () => ({ data: [] }),
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

// Asegurar que proveedores.data sea un array válido
const proveedoresData = computed(() => {
    if (!props.proveedores || !props.proveedores.data) {
        return [];
    }
    return Array.isArray(props.proveedores.data) ? props.proveedores.data : [];
});

const columns = [
    { key: 'id', label: 'ID', sortable: true },
    { key: 'nombre', label: 'Nombre', sortable: true },
    { key: 'ruc', label: 'RUC', sortable: true },
    { key: 'telefono', label: 'Teléfono', sortable: false },
    { key: 'email', label: 'Email', sortable: false },
    { key: 'persona_contacto', label: 'Contacto', sortable: false },
    { key: 'activo', label: 'Estado', sortable: true },
];

const filters = ref({
    search: props.filters?.search || '',
    activo: props.filters?.activo || '',
});

const canCreate = computed(() => {
    const rol = page.props.auth?.user?.rol?.nombre;
    return rol && ['PROPIETARIO', 'SECRETARIA'].includes(rol);
});

const canEdit = computed(() => canCreate.value);
const canDelete = computed(() => canCreate.value);

const applyFilters = () => {
    router.get(getRoute('proveedores.index'), filters.value, {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    filters.value = { search: '', activo: '' };
    applyFilters();
};

const deleteProveedor = (proveedor) => {
    if (!proveedor?.id) return;
    if (confirm(`¿Está seguro de eliminar al proveedor "${proveedor?.nombre || 'este proveedor'}"?`)) {
        router.delete(getRoute('proveedores.destroy', proveedor.id), {
            preserveScroll: true,
        });
    }
};
</script>

