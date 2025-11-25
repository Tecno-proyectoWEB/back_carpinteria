<template>
    <AppLayout :menu-items="menuItems" :page-visits="pageVisits">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-3xl font-bold text-gray-900">Usuarios</h2>
                    <Link
                        v-if="canCreate"
                        :href="route('usuarios.create')"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                    >
                        + Nuevo Usuario
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
                                placeholder="Nombre, apellido o email..."
                                class="w-full px-3 py-2 border border-gray-300 rounded-md"
                                @input="applyFilters"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Rol</label>
                            <select
                                v-model="filters.rol_id"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md"
                                @change="applyFilters"
                            >
                                <option value="">Todos</option>
                                <option v-for="rol in roles" :key="rol.id" :value="rol.id">
                                    {{ rol.nombre }}
                                </option>
                            </select>
                        </div>
                        <div class="flex items-end space-x-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                            <select
                                v-model="filters.estado"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md"
                                @change="applyFilters"
                            >
                                <option value="">Todos</option>
                                <option value="activo">Activo</option>
                                <option value="inactivo">Inactivo</option>
                            </select>
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
                    :data="usuarios.data"
                    :columns="columns"
                    :loading="false"
                    :show-search="false"
                    :paginated="false"
                >
                    <template #cell-nombre_completo="{ row }">
                        {{ row.nombre }} {{ row.apellido }}
                    </template>
                    <template #cell-rol="{ value }">
                        <Badge variant="info">{{ value?.nombre || 'Sin rol' }}</Badge>
                    </template>
                    <template #cell-estado="{ row }">
                        <Badge :variant="row.estado ? 'success' : 'error'">
                            {{ row.estado ? 'Activo' : 'Inactivo' }}
                        </Badge>
                    </template>
                    <template #cell-disponibilidad="{ row }">
                        <Badge :variant="row.disponibilidad ? 'success' : 'warning'">
                            {{ row.disponibilidad ? 'Disponible' : 'No disponible' }}
                        </Badge>
                    </template>
                    <template #cell-cuenta="{ row }">
                        <div class="text-xs space-y-1">
                            <div :class="row.cuenta_no_expirada ? 'text-green-600' : 'text-red-600'">
                                {{ row.cuenta_no_expirada ? '✓' : '✗' }} No expirada
                            </div>
                            <div :class="row.cuenta_no_bloqueada ? 'text-green-600' : 'text-red-600'">
                                {{ row.cuenta_no_bloqueada ? '✓' : '✗' }} No bloqueada
                            </div>
                            <div :class="row.credenciales_no_expiradas ? 'text-green-600' : 'text-red-600'">
                                {{ row.credenciales_no_expiradas ? '✓' : '✗' }} Credenciales válidas
                            </div>
                        </div>
                    </template>
                    <template #actions="{ row }">
                        <Link
                            :href="route('usuarios.show', row.id)"
                            class="text-blue-600 hover:text-blue-900 mr-3"
                        >
                            Ver
                        </Link>
                        <Link
                            v-if="canEdit"
                            :href="route('usuarios.edit', row.id)"
                            class="text-indigo-600 hover:text-indigo-900 mr-3"
                        >
                            Editar
                        </Link>
                        <button
                            v-if="canDelete && row.id !== currentUserId"
                            @click="deleteUsuario(row)"
                            class="text-red-600 hover:text-red-900"
                        >
                            Eliminar
                        </button>
                    </template>
                </DataTable>

                <!-- Paginación -->
                <div v-if="usuarios.links" class="mt-4">
                    <div class="flex justify-center">
                        <div v-for="link in usuarios.links" :key="link.label">
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
    usuarios: Object,
    roles: Array,
    menuItems: Array,
    pageVisits: Number,
    filters: Object,
});

const columns = [
    { key: 'id', label: 'ID', sortable: true },
    { key: 'nombre_completo', label: 'Nombre Completo', sortable: false },
    { key: 'email', label: 'Email', sortable: true },
    { key: 'telefono', label: 'Teléfono', sortable: false },
    { key: 'rol', label: 'Rol', sortable: false },
    { key: 'estado', label: 'Estado', sortable: true },
    { key: 'disponibilidad', label: 'Disponibilidad', sortable: false },
    { key: 'cuenta', label: 'Estado Cuenta', sortable: false },
];

const filters = ref({
    search: props.filters?.search || '',
    rol_id: props.filters?.rol_id || '',
    estado: props.filters?.estado || '',
});

const currentUserId = computed(() => {
    return window.$page?.props?.auth?.user?.id;
});

const canCreate = computed(() => {
    const rol = window.$page?.props?.auth?.user?.rol?.nombre;
    return ['PROPIETARIO'].includes(rol);
});

const canEdit = computed(() => canCreate.value);
const canDelete = computed(() => canCreate.value);

const applyFilters = () => {
    router.get(route('usuarios.index'), filters.value, {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    filters.value = { search: '', rol_id: '', estado: '' };
    applyFilters();
};

const deleteUsuario = (usuario) => {
    if (confirm(`¿Está seguro de eliminar al usuario "${usuario.nombre} ${usuario.apellido}"?`)) {
        router.delete(route('usuarios.destroy', usuario.id), {
            preserveScroll: true,
        });
    }
};
</script>


