<template>
    <AppLayout :auth="auth" :menu-items="menuItems" :page-visits="pageVisits" :visitas-pagina="visitasPagina">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-3xl font-bold bg-gradient-to-r from-indigo-600 to-blue-600 bg-clip-text text-transparent">Productos</h2>
                    <Link
                        v-if="$page.props.auth?.user?.rol?.nombre === 'PROPIETARIO' || $page.props.auth?.user?.rol?.nombre === 'CARPINTERO'"
                        :href="route('productos.create')"
                        class="px-5 py-2.5 bg-gradient-to-r from-indigo-600 to-blue-600 text-white rounded-lg hover:from-indigo-700 hover:to-blue-700 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200 font-medium flex items-center space-x-2"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>Nuevo Producto</span>
                    </Link>
                </div>

                <!-- Filtros -->
                <div class="mb-4 bg-white/80 backdrop-blur-sm p-4 rounded-xl shadow-lg border border-indigo-100">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
                            <input
                                v-model="filters.search"
                                type="text"
                                placeholder="Nombre o descripci├│n..."
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                @keyup.enter="applyFilters"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Categor├¡a</label>
                            <select
                                v-model="filters.categoria_id"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                @change="applyFilters"
                            >
                                <option value="">Todas</option>
                                <option v-for="cat in categorias" :key="cat.id" :value="cat.id">
                                    {{ cat.nombre }}
                                </option>
                            </select>
                        </div>
                        <div class="flex items-end space-x-2">
                            <button
                                @click="applyFilters"
                                class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 font-medium"
                            >
                                Filtrar
                            </button>
                            <button
                                @click="clearFilters"
                                class="px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50 font-medium"
                            >
                                Limpiar
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Tabla -->
                <DataTable
                    v-if="productos && productos.data"
                    :data="productos.data || []"
                    :columns="columns"
                    :loading="false"
                    :show-search="false"
                    :paginated="false"
                    :on-edit="editProducto"
                    :on-delete="deleteProducto"
                >
                    <template #cell-categoria="{ value, row }">
                        <template v-if="value && typeof value === 'object' && value.nombre">
                            <Badge variant="info">
                                {{ value.nombre }}
                            </Badge>
                        </template>
                        <template v-else-if="row && row.categoria && typeof row.categoria === 'object' && row.categoria.nombre">
                            <Badge variant="info">
                                {{ row.categoria.nombre }}
                            </Badge>
                        </template>
                        <span v-else class="text-gray-400">Sin categor├¡a</span>
                    </template>
                    <template #cell-precio_unitario="{ value }">
                        <span v-if="value !== null && value !== undefined">
                            ${{ formatPrecio(value) }}
                        </span>
                        <span v-else class="text-gray-400">$0.00</span>
                    </template>
                    <template #cell-stock="{ row }">
                        <template v-if="row && typeof row === 'object'">
                            <span :class="getStockClass(row)">
                                {{ row.stock || 0 }}
                                <span v-if="isStockBajo(row)" class="text-xs">ÔÜá´©Å</span>
                            </span>
                        </template>
                        <span v-else class="text-gray-400">-</span>
                    </template>
                    <template #cell-imagen="{ row }">
                        <template v-if="row && typeof row === 'object'">
                            <img
                                v-if="row.imagen && typeof row.imagen === 'string'"
                                :src="`/storage/${row.imagen}`"
                                :alt="row.nombre || 'Imagen'"
                                class="h-12 w-12 object-cover rounded"
                                @error="handleImageError"
                            />
                            <span v-else class="text-gray-400">Sin imagen</span>
                        </template>
                        <span v-else class="text-gray-400">-</span>
                    </template>
                    <template #actions="{ row }">
                        <template v-if="row && typeof row === 'object' && row.id">
                            <div class="flex items-center space-x-2">
                                <button
                                    @click="() => viewProducto(row)"
                                    class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-blue-700 bg-blue-50 rounded-md hover:bg-blue-100 transition-colors"
                                    title="Ver detalles"
                                >
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    Ver
                                </button>
                                <button
                                    @click="() => editProducto(row)"
                                    class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-indigo-700 bg-indigo-50 rounded-md hover:bg-indigo-100 transition-colors"
                                    title="Editar producto"
                                >
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                    Editar
                                </button>
                                <button
                                    @click="() => safeDeleteProducto(row)"
                                    class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-red-700 bg-red-50 rounded-md hover:bg-red-100 transition-colors"
                                    title="Eliminar producto"
                                >
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    Eliminar
                                </button>
                            </div>
                        </template>
                    </template>
                </DataTable>

                <!-- Paginaci├│n -->
                <div v-if="productos.links" class="mt-4">
                    <div class="flex justify-center">
                        <div v-for="link in productos.links" :key="link.label">
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
import { Link, router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Pages/Layout.vue';
import DataTable from '@/Components/Table/DataTable.vue';
import Badge from '@/Components/UI/Badge.vue';

const props = defineProps({
        auth: { type: Object, required: true },
    visitasPagina: { type: Number, default: 0 },
productos: Object,
    categorias: Array,
    menuItems: {
        type: Array,
        default: () => [],
    },
    pageVisits: {
        type: Number,
        default: null,
    },
    filters: Object,
});

const page = usePage();

const columns = [
    { key: 'id', label: 'ID', sortable: true },
    { key: 'imagen', label: 'Imagen', sortable: false },
    { key: 'nombre', label: 'Nombre', sortable: true },
    { key: 'categoria', label: 'Categor├¡a', sortable: false },
    { key: 'stock', label: 'Stock', sortable: true },
    { key: 'precio_unitario', label: 'Precio', sortable: true },
];

const filters = ref({
    search: props.filters?.search || '',
    categoria_id: props.filters?.categoria_id || '',
});

const canEdit = computed(() => {
    try {
        const rolNombre = page.props.auth?.user?.rol?.nombre;
        const can = rolNombre && ['PROPIETARIO', 'CARPINTERO', 'ADMINISTRADOR'].includes(rolNombre);
        console.log('canEdit check productos:', { rolNombre, can });
        return can || true; // Temporalmente true para debug
    } catch (e) {
        console.error('Error checking canEdit:', e);
        return true; // Temporalmente true para debug
    }
});

const canDelete = computed(() => {
    try {
        const rolNombre = page.props.auth?.user?.rol?.nombre;
        const can = rolNombre && ['PROPIETARIO', 'CARPINTERO', 'ADMINISTRADOR'].includes(rolNombre);
        console.log('canDelete check productos:', { rolNombre, can });
        return can || true; // Temporalmente true para debug
    } catch (e) {
        console.error('Error checking canDelete:', e);
        return true; // Temporalmente true para debug
    }
});

const applyFilters = () => {
    // Limpiar valores vac├¡os
    const cleanFilters = {};
    if (filters.value.search && filters.value.search.trim() !== '') {
        cleanFilters.search = filters.value.search.trim();
    }
    if (filters.value.categoria_id && filters.value.categoria_id !== '') {
        cleanFilters.categoria_id = filters.value.categoria_id;
    }

    console.log('Aplicando filtros:', cleanFilters);

    try {
        const routeUrl = route('productos.index');
        if (routeUrl && routeUrl !== '#') {
            router.get(routeUrl, cleanFilters, {
                preserveState: true,
                preserveScroll: true,
            });
        } else {
            router.get('/productos', cleanFilters, {
                preserveState: true,
                preserveScroll: true,
            });
        }
    } catch (e) {
        console.error('Error aplicando filtros:', e);
        router.get('/productos', cleanFilters, {
            preserveState: true,
            preserveScroll: true,
        });
    }
};

const clearFilters = () => {
    filters.value = { search: '', categoria_id: '' };
    console.log('Limpiando filtros');
    router.get('/productos', {}, {
        preserveState: true,
        preserveScroll: true,
    });
};

const viewProducto = (producto) => {
    if (!producto || !producto.id) {
        console.error('Producto inv├ílido para ver:', producto);
        return;
    }
    try {
        const routeUrl = route('productos.show', producto.id);
        if (routeUrl && routeUrl !== '#') {
            router.visit(routeUrl);
        } else {
            router.visit(`/productos/${producto.id}`);
        }
    } catch (e) {
        console.error('Error al ver producto:', e);
        router.visit(`/productos/${producto.id}`);
    }
};

const editProducto = (producto) => {
    if (!producto || !producto.id) {
        console.error('Producto inv├ílido para editar:', producto);
        return;
    }
    try {
        const routeUrl = route('productos.edit', producto.id);
        if (routeUrl && routeUrl !== '#') {
            router.visit(routeUrl);
        } else {
            router.visit(`/productos/${producto.id}/edit`);
        }
    } catch (e) {
        console.error('Error al editar producto:', e);
        router.visit(`/productos/${producto.id}/edit`);
    }
};

const deleteProducto = (producto) => {
    if (!producto || !producto.id) {
        console.error('Producto inv├ílido para eliminar:', producto);
        return;
    }
    const nombre = producto.nombre || 'este producto';
    if (confirm(`┬┐Est├í seguro de eliminar el producto "${nombre}"?`)) {
        try {
            const routeUrl = route('productos.destroy', producto.id);
            if (routeUrl && routeUrl !== '#') {
                router.delete(routeUrl, {
                    preserveScroll: true,
                    onSuccess: () => {
                        router.reload({ only: ['productos'] });
                    },
                });
            } else {
                router.delete(`/productos/${producto.id}`, {
                    preserveScroll: true,
                    onSuccess: () => {
                        router.reload({ only: ['productos'] });
                    },
                });
            }
        } catch (e) {
            console.error('Error al eliminar producto:', e);
            router.delete(`/productos/${producto.id}`, {
                preserveScroll: true,
                onSuccess: () => {
                    router.reload({ only: ['productos'] });
                },
            });
        }
    }
};

const formatPrecio = (value) => {
    try {
        const num = parseFloat(value);
        return isNaN(num) ? '0.00' : num.toFixed(2);
    } catch (e) {
        return '0.00';
    }
};

const getStockClass = (row) => {
    try {
        const stock = row.stock || 0;
        const stockMinimo = row.stock_minimo || 0;
        return stock <= stockMinimo ? 'text-red-600 font-bold' : '';
    } catch (e) {
        return '';
    }
};

const isStockBajo = (row) => {
    try {
        const stock = row.stock || 0;
        const stockMinimo = row.stock_minimo || 0;
        return stock <= stockMinimo;
    } catch (e) {
        return false;
    }
};

const handleImageError = (e) => {
    if (e && e.target) {
        e.target.style.display = 'none';
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

const safeDeleteProducto = (producto) => {
    try {
        deleteProducto(producto);
    } catch (e) {
        console.error('Error deleting producto:', e);
    }
};
</script>

