<template>
    <AppLayout :auth="auth" :visitas-pagina="visitasPagina">`n        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-3xl font-bold text-gray-900">Almacenes</h2>
                    <Link
                        :href="route('almacenes.create')"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                    >
                        + Nuevo Almacén
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
                                placeholder="Nombre, ubicación..."
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
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ubicación</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Capacidad</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="almacen in almacenes.data" :key="almacen.id">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ almacen.id }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ almacen.nombre }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ almacen.ubicacion }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ almacen.capacidad }} m²</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span :class="almacen.activo ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                                        {{ almacen.activo ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                    <Link :href="route('almacenes.show', almacen.id)" class="text-blue-600 hover:text-blue-900">Ver</Link>
                                    <Link :href="route('almacenes.edit', almacen.id)" class="text-indigo-600 hover:text-indigo-900">Editar</Link>
                                    <button @click="deleteAlmacen(almacen.id)" class="text-red-600 hover:text-red-900">Eliminar</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <div class="mt-4" v-if="almacenes.links">
                    <nav class="flex justify-center space-x-2">
                        <Link
                            v-for="link in almacenes.links"
                            :key="link.label"
                            :href="link.url"
                            :class="link.active ? 'bg-blue-600 text-white' : 'bg-white text-gray-700'"
                            class="px-3 py-2 border rounded-md hover:bg-gray-50"
                            v-html="link.label"
                        />
                    </nav>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Pages/Layout.vue';

const props = defineProps({
        auth: { type: Object, required: true },
    visitasPagina: { type: Number, default: 0 },
almacenes: Object,
    filters: Object
});

const filters = reactive({
    search: props.filters?.search || '',
    activo: props.filters?.activo || ''
});

const applyFilters = () => {
    router.get(route('almacenes.index'), filters, { preserveState: true });
};

const clearFilters = () => {
    filters.search = '';
    filters.activo = '';
    applyFilters();
};

const deleteAlmacen = (id) => {
    if (confirm('¿Está seguro de eliminar este almacén?')) {
        router.delete(route('almacenes.destroy', id));
    }
};
</script>
