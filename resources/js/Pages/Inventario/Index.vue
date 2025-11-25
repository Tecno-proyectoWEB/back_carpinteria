<template>
    <AppLayout :menu-items="menuItems" :page-visits="pageVisits">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h2 class="text-3xl font-bold text-gray-900">Movimientos de Inventario</h2>
                        <p class="text-gray-600 mt-1">Historial de todos los movimientos de inventario</p>
                    </div>
                    <div class="flex space-x-2">
                        <Link
                            v-if="canCreate"
                            :href="route('inventario.create')"
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                        >
                            + Nuevo Movimiento
                        </Link>
                        <Link
                            :href="route('inventario.stock')"
                            class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700"
                        >
                            Ver Stock
                        </Link>
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
                                placeholder="Material, producto, motivo..."
                                class="w-full px-3 py-2 border border-gray-300 rounded-md"
                                @input="applyFilters"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
                            <select
                                v-model="filters.tipo"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md"
                                @change="applyFilters"
                            >
                                <option value="">Todos</option>
                                <option value="INGRESO">Ingreso</option>
                                <option value="SALIDA">Salida</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Material</label>
                            <select
                                v-model="filters.material_id"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md"
                                @change="applyFilters"
                            >
                                <option value="">Todos</option>
                                <option v-for="material in materiales" :key="material.id" :value="material.id">
                                    {{ material.nombre }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Producto</label>
                            <select
                                v-model="filters.producto_id"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md"
                                @change="applyFilters"
                            >
                                <option value="">Todos</option>
                                <option v-for="producto in productos" :key="producto.id" :value="producto.id">
                                    {{ producto.nombre }}
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
                                Limpiar Filtros
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Tabla de Movimientos -->
                <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Fecha
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Tipo
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Item
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Cantidad
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Motivo
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Usuario
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Origen
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="movimiento in movimientos.data" :key="movimiento.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ formatDate(movimiento.fecha) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <Badge :variant="movimiento.tipo === 'INGRESO' ? 'success' : 'error'">
                                        {{ movimiento.tipo }}
                                    </Badge>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <div v-if="movimiento.material">
                                        <span class="font-medium">{{ movimiento.material.nombre }}</span>
                                        <span class="text-gray-500 text-xs block">Material</span>
                                    </div>
                                    <div v-else-if="movimiento.producto">
                                        <span class="font-medium">{{ movimiento.producto.nombre }}</span>
                                        <span class="text-gray-500 text-xs block">Producto</span>
                                    </div>
                                    <span v-else class="text-gray-400">N/A</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <span class="font-semibold">{{ movimiento.cantidad }}</span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    {{ movimiento.motivo || 'Sin motivo' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ movimiento.usuario?.nombre }} {{ movimiento.usuario?.apellido }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <span v-if="movimiento.compra">Compra #{{ movimiento.compra.id }}</span>
                                    <span v-else-if="movimiento.pedido">Pedido #{{ movimiento.pedido.id }}</span>
                                    <span v-else class="text-gray-400">Manual</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <Link
                                        :href="route('inventario.show', movimiento.id)"
                                        class="text-indigo-600 hover:text-indigo-900"
                                    >
                                        Ver Detalle
                                    </Link>
                                </td>
                            </tr>
                            <tr v-if="movimientos.data.length === 0">
                                <td colspan="8" class="px-6 py-4 text-center text-gray-500">
                                    No se encontraron movimientos
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <div v-if="movimientos.links" class="mt-4">
                    <div class="flex justify-center">
                        <div v-for="link in movimientos.links" :key="link.label">
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
import Badge from '@/Components/UI/Badge.vue';

const props = defineProps({
    movimientos: Object,
    materiales: Array,
    productos: Array,
    menuItems: Array,
    pageVisits: Number,
    filters: Object,
});

const filters = ref({
    search: props.filters?.search || '',
    tipo: props.filters?.tipo || '',
    material_id: props.filters?.material_id || '',
    producto_id: props.filters?.producto_id || '',
    fecha_desde: props.filters?.fecha_desde || '',
    fecha_hasta: props.filters?.fecha_hasta || '',
});

const canCreate = computed(() => {
    const rol = window.$page?.props?.auth?.user?.rol?.nombre;
    return ['PROPIETARIO', 'CARPINTERO'].includes(rol);
});

const applyFilters = () => {
    router.get(route('inventario.index'), filters.value, {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    filters.value = {
        search: '',
        tipo: '',
        material_id: '',
        producto_id: '',
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
    });
};
</script>

