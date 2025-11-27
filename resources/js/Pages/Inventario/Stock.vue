<template>
    <AppLayout>
        <div class="max-w-7xl mx-auto">
            <div class="max-w-7xl mx-auto">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h2 class="text-3xl font-bold text-gray-900">Stock Actual</h2>
                        <p class="text-gray-600 mt-1">Inventario actual de materiales y productos</p>
                    </div>
                    <Link
                        :href="getRoute('inventario.index')"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                    >
                        Ver Movimientos
                    </Link>
                </div>

                <!-- Estadísticas -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <div class="bg-white p-4 rounded-lg shadow">
                        <p class="text-sm text-gray-500">Total Materiales</p>
                        <p class="text-2xl font-bold text-gray-900">{{ estadisticas.total_materiales }}</p>
                    </div>
                    <div class="bg-white p-4 rounded-lg shadow">
                        <p class="text-sm text-gray-500">Materiales Bajo Stock</p>
                        <p class="text-2xl font-bold text-red-600">{{ estadisticas.materiales_bajo_stock }}</p>
                    </div>
                    <div class="bg-white p-4 rounded-lg shadow">
                        <p class="text-sm text-gray-500">Total Productos</p>
                        <p class="text-2xl font-bold text-gray-900">{{ estadisticas.total_productos }}</p>
                    </div>
                    <div class="bg-white p-4 rounded-lg shadow">
                        <p class="text-sm text-gray-500">Productos Bajo Stock</p>
                        <p class="text-2xl font-bold text-red-600">{{ estadisticas.productos_bajo_stock }}</p>
                    </div>
                </div>

                <!-- Filtros -->
                <div class="mb-4 bg-white p-4 rounded-lg shadow">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Buscar Material</label>
                            <input
                                v-model="filters.material_search"
                                type="text"
                                placeholder="Nombre del material..."
                                class="w-full px-3 py-2 border border-gray-300 rounded-md"
                                @input="applyFilters"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Buscar Producto</label>
                            <input
                                v-model="filters.producto_search"
                                type="text"
                                placeholder="Nombre del producto..."
                                class="w-full px-3 py-2 border border-gray-300 rounded-md"
                                @input="applyFilters"
                            />
                        </div>
                        <div>
                            <label class="flex items-center">
                                <input
                                    v-model="filters.material_bajo_stock"
                                    type="checkbox"
                                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                    @change="applyFilters"
                                />
                                <span class="ml-2 text-sm text-gray-700">Solo materiales bajo stock</span>
                            </label>
                        </div>
                        <div>
                            <label class="flex items-center">
                                <input
                                    v-model="filters.producto_bajo_stock"
                                    type="checkbox"
                                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                    @change="applyFilters"
                                />
                                <span class="ml-2 text-sm text-gray-700">Solo productos bajo stock</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Materiales -->
                <div class="bg-white shadow-sm rounded-lg mb-6">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Materiales</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Unidad</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Stock Actual</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Stock Mínimo</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Punto Reorden</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr
                                    v-for="material in materiales"
                                    :key="material.id"
                                    :class="{
                                        'bg-red-50': material.stock_actual <= material.stock_minimo
                                    }"
                                    class="hover:bg-gray-50"
                                >
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ material.nombre }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ material.unidad_medida }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <span class="font-semibold">{{ material.stock_actual }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ material.stock_minimo }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ material.punto_reorden }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <Badge
                                            :variant="material.stock_actual <= material.stock_minimo ? 'error' : 'success'"
                                        >
                                            {{ material.stock_actual <= material.stock_minimo ? 'Bajo Stock' : 'Normal' }}
                                        </Badge>
                                    </td>
                                </tr>
                                <tr v-if="materiales.length === 0">
                                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                        No se encontraron materiales
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Productos -->
                <div class="bg-white shadow-sm rounded-lg">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Productos</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Stock Actual</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Stock Mínimo</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Precio Unitario</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr
                                    v-for="producto in productos"
                                    :key="producto.id"
                                    :class="{
                                        'bg-red-50': producto.stock <= producto.stock_minimo
                                    }"
                                    class="hover:bg-gray-50"
                                >
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ producto.nombre }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <span class="font-semibold">{{ producto.stock }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ producto.stock_minimo }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        ${{ producto.precio_unitario?.toFixed(2) || '0.00' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <Badge
                                            :variant="producto.stock <= producto.stock_minimo ? 'error' : 'success'"
                                        >
                                            {{ producto.stock <= producto.stock_minimo ? 'Bajo Stock' : 'Normal' }}
                                        </Badge>
                                    </td>
                                </tr>
                                <tr v-if="productos.length === 0">
                                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                        No se encontraron productos
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { getRoute } from '@/utils/routeHelper';
import AppLayout from '@/Layouts/AppLayout.vue';
import Badge from '@/Components/UI/Badge.vue';

const props = defineProps({
    materiales: {
        type: Array,
        default: () => [],
    },
    productos: {
        type: Array,
        default: () => [],
    },
    estadisticas: {
        type: Object,
        default: () => ({}),
    },
    },
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

const filters = ref({
    material_search: props.filters?.material_search || '',
    material_bajo_stock: props.filters?.material_bajo_stock || false,
    producto_search: props.filters?.producto_search || '',
    producto_bajo_stock: props.filters?.producto_bajo_stock || false,
});

const applyFilters = () => {
    router.get(getRoute('inventario.stock'), filters.value, {
        preserveState: true,
        preserveScroll: true,
    });
};
</script>

