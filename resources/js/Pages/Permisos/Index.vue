<template>
    <AppLayout :menu-items="menuItems" :page-visits="pageVisits">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h2 class="text-3xl font-bold bg-gradient-to-r from-indigo-600 to-blue-600 bg-clip-text text-transparent">Permisos</h2>
                        <p class="text-gray-600 mt-2">Gestione los permisos del sistema</p>
                    </div>
                    <Link
                        v-if="canCreate"
                        :href="route('permisos.create')"
                        class="px-5 py-2.5 bg-gradient-to-r from-indigo-600 to-blue-600 text-white rounded-lg hover:from-indigo-700 hover:to-blue-700 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200 font-medium flex items-center space-x-2"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>Nuevo Permiso</span>
                    </Link>
                </div>

                <!-- Tabla de Permisos -->
                <div class="bg-white/80 backdrop-blur-sm rounded-xl shadow-lg border border-indigo-100 overflow-hidden">
                    <DataTable
                        v-if="permisos && permisos.length > 0"
                        :data="permisos"
                        :columns="columns"
                        :loading="false"
                        :show-search="true"
                        :paginated="false"
                        :on-edit="editPermiso"
                        :on-delete="deletePermiso"
                    >
                        <template #cell-roles="{ value, row }">
                            <template v-if="value && Array.isArray(value) && value.length > 0">
                                <div class="flex flex-wrap gap-1">
                                    <Badge
                                        v-for="rol in value.slice(0, 3)"
                                        :key="rol.id"
                                        variant="info"
                                        class="text-xs"
                                    >
                                        {{ rol.nombre }}
                                    </Badge>
                                    <Badge
                                        v-if="value.length > 3"
                                        variant="warning"
                                        class="text-xs"
                                    >
                                        +{{ value.length - 3 }}
                                    </Badge>
                                </div>
                            </template>
                            <span v-else class="text-gray-400 text-sm">Sin roles</span>
                        </template>
                        <template #actions="{ row }">
                            <template v-if="row && typeof row === 'object' && row.id">
                                <Link
                                    :href="getRoute('permisos.show', row.id)"
                                    class="text-blue-600 hover:text-blue-900 mr-3"
                                >
                                    Ver
                                </Link>
                                <Link
                                    v-if="canEdit"
                                    :href="getRoute('permisos.edit', row.id)"
                                    class="text-indigo-600 hover:text-indigo-900 mr-3"
                                >
                                    Editar
                                </Link>
                                <button
                                    v-if="canDelete"
                                    @click="() => safeDeletePermiso(row)"
                                    class="text-red-600 hover:text-red-900"
                                >
                                    Eliminar
                                </button>
                            </template>
                        </template>
                    </DataTable>
                    <div v-else class="p-8 text-center text-gray-500">
                        No hay permisos registrados
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import DataTable from '@/Components/Table/DataTable.vue';
import Badge from '@/Components/UI/Badge.vue';

const props = defineProps({
    permisos: Array,
    menuItems: Array,
    pageVisits: Number,
});

const page = usePage();

const columns = [
    { key: 'id', label: 'ID', sortable: true },
    { key: 'nombre', label: 'Nombre', sortable: true },
    { key: 'roles', label: 'Roles Asignados', sortable: false },
];

const canCreate = computed(() => {
    const rol = page.props.auth?.user?.rol?.nombre;
    return ['PROPIETARIO', 'ADMINISTRADOR'].includes(rol);
});

const canEdit = computed(() => canCreate.value);
const canDelete = computed(() => canCreate.value);

const editPermiso = (permiso) => {
    if (!permiso || !permiso.id) return;
    router.visit(route('permisos.edit', permiso.id));
};

const deletePermiso = (permiso) => {
    if (!permiso || !permiso.id) return;
    if (confirm(`¿Está seguro de eliminar el permiso "${permiso.nombre}"?`)) {
        router.delete(route('permisos.destroy', permiso.id), {
            preserveScroll: true,
        });
    }
};

const getRoute = (name, id) => {
    try {
        return route(name, id);
    } catch (e) {
        console.warn('Error getting route:', e);
        return '#';
    }
};

const safeDeletePermiso = (permiso) => {
    try {
        deletePermiso(permiso);
    } catch (e) {
        console.error('Error deleting permiso:', e);
    }
};
</script>

