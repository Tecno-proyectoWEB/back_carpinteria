<template>
    <AppLayout :menu-items="menuItems" :page-visits="pageVisits">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h2 class="text-3xl font-bold bg-gradient-to-r from-indigo-600 to-blue-600 bg-clip-text text-transparent">Bitácora del Sistema</h2>
                        <p class="text-gray-600 mt-2">Registro de todas las acciones realizadas en el sistema</p>
                    </div>
                </div>

                <!-- Filtros -->
                <div class="mb-4 bg-white/80 backdrop-blur-sm p-4 rounded-xl shadow-lg border border-indigo-100">
                    <form @submit.prevent="applyFilters" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
                            <input
                                v-model="filters.search"
                                type="text"
                                placeholder="Acción, módulo, tabla..."
                                class="w-full px-3 py-2 border border-gray-300 rounded-md"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Módulo</label>
                            <select
                                v-model="filters.modulo"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md"
                            >
                                <option value="">Todos</option>
                                <option v-for="modulo in modulos" :key="modulo" :value="modulo">
                                    {{ modulo }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Usuario</label>
                            <select
                                v-model="filters.usuario_id"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md"
                            >
                                <option value="">Todos</option>
                                <option v-for="usuario in usuarios" :key="usuario.id" :value="usuario.id">
                                    {{ usuario.nombre }} {{ usuario.apellido }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tabla Afectada</label>
                            <input
                                v-model="filters.tabla_afectada"
                                type="text"
                                placeholder="Nombre de tabla..."
                                class="w-full px-3 py-2 border border-gray-300 rounded-md"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Desde</label>
                            <input
                                v-model="filters.fecha_desde"
                                type="date"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Hasta</label>
                            <input
                                v-model="filters.fecha_hasta"
                                type="date"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md"
                            />
                        </div>
                        <div class="flex items-end space-x-2">
                            <button
                                type="submit"
                                class="px-5 py-2.5 bg-gradient-to-r from-indigo-600 to-blue-600 text-white rounded-lg hover:from-indigo-700 hover:to-blue-700 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200"
                            >
                                Filtrar
                            </button>
                            <button
                                type="button"
                                @click="clearFilters"
                                class="px-5 py-2.5 border border-indigo-200 rounded-lg hover:bg-indigo-50 text-indigo-700 transition-colors"
                            >
                                Limpiar
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Tabla de Bitácora -->
                <div class="bg-white/80 backdrop-blur-sm shadow-lg rounded-xl border border-indigo-100 overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Usuario</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Módulo</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acción</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tabla</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Registro ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="bitacora in bitacoras.data" :key="bitacora.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ formatDate(bitacora.fecha) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ bitacora.usuario?.nombre }} {{ bitacora.usuario?.apellido }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <Badge variant="info">{{ bitacora.modulo }}</Badge>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    {{ bitacora.accion }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ bitacora.tabla_afectada || 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ bitacora.registro_id || 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <Link
                                        :href="route('bitacora.show', bitacora.id)"
                                        class="text-indigo-600 hover:text-indigo-900"
                                    >
                                        Ver Detalle
                                    </Link>
                                </td>
                            </tr>
                            <tr v-if="bitacoras.data.length === 0">
                                <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                    No se encontraron registros
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <div v-if="bitacoras.links" class="mt-4">
                    <div class="flex justify-center">
                        <div v-for="link in bitacoras.links" :key="link.label">
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
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Badge from '@/Components/UI/Badge.vue';

const props = defineProps({
    bitacoras: Object,
    modulos: Array,
    usuarios: Array,
    menuItems: Array,
    pageVisits: Number,
    filters: Object,
});

const filters = ref({
    search: props.filters?.search || '',
    modulo: props.filters?.modulo || '',
    usuario_id: props.filters?.usuario_id || '',
    tabla_afectada: props.filters?.tabla_afectada || '',
    fecha_desde: props.filters?.fecha_desde || '',
    fecha_hasta: props.filters?.fecha_hasta || '',
});

const applyFilters = () => {
    router.get(route('bitacora.index'), filters.value, {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    filters.value = {
        search: '',
        modulo: '',
        usuario_id: '',
        tabla_afectada: '',
        fecha_desde: '',
        fecha_hasta: '',
    };
    applyFilters();
};

const formatDate = (date) => {
    if (!date) return '';
    return new Date(date).toLocaleString('es-ES', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
    });
};
</script>

