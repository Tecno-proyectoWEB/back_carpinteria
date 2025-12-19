<template>
    <AppLayout :auth="auth" :menu-items="menuItems" :page-visits="pageVisits" :visitas-pagina="visitasPagina">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-3xl font-bold bg-gradient-to-r from-indigo-600 to-blue-600 bg-clip-text text-transparent">Materiales</h2>
                    <button
                        v-if="canCreate"
                        @click="showCreateModal = true"
                        class="px-5 py-2.5 bg-gradient-to-r from-indigo-600 to-blue-600 text-white rounded-lg hover:from-indigo-700 hover:to-blue-700 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200 font-medium flex items-center space-x-2"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>Nuevo Material</span>
                    </button>
                </div>

                <!-- Modal para crear material -->
                <Modal :show="showCreateModal" @close="showCreateModal = false" max-width="2xl">
                    <template #title>
                        <h3 class="text-xl font-semibold text-gray-900">Crear Nuevo Material</h3>
                    </template>

                    <form @submit.prevent="submitCreate">
                        <div class="space-y-4">
                            <Input
                                v-model="createForm.nombre"
                                label="Nombre"
                                required
                                :error="createForm.errors.nombre"
                            />

                            <Textarea
                                v-model="createForm.descripcion"
                                label="Descripci├│n"
                                :error="createForm.errors.descripcion"
                                :rows="3"
                            />

                            <div class="grid grid-cols-2 gap-4">
                                <Select
                                    v-model="createForm.categoria_id"
                                    label="Categor├¡a"
                                    :options="categorias"
                                    option-value="id"
                                    option-label="nombre"
                                    required
                                    :error="createForm.errors.categoria_id"
                                />

                                <Select
                                    v-model="createForm.sector_id"
                                    label="Sector/Almac├®n"
                                    :options="sectores"
                                    option-value="id"
                                    option-label="nombre"
                                    required
                                    :error="createForm.errors.sector_id"
                                />
                            </div>

                            <div class="grid grid-cols-3 gap-4">
                                <Input
                                    v-model.number="createForm.stock_actual"
                                    label="Stock Actual"
                                    type="number"
                                    min="0"
                                    :error="createForm.errors.stock_actual"
                                />

                                <Input
                                    v-model.number="createForm.stock_minimo"
                                    label="Stock M├¡nimo"
                                    type="number"
                                    min="0"
                                    :error="createForm.errors.stock_minimo"
                                />

                                <Input
                                    v-model.number="createForm.punto_reorden"
                                    label="Punto de Reorden"
                                    type="number"
                                    min="0"
                                    :error="createForm.errors.punto_reorden"
                                />
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <Input
                                    v-model.number="createForm.precio"
                                    label="Precio"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    :error="createForm.errors.precio"
                                />

                                <Input
                                    v-model="createForm.unidad_medida"
                                    label="Unidad de Medida"
                                    placeholder="Ej: kg, m┬▓, unidades"
                                    :error="createForm.errors.unidad_medida"
                                />
                            </div>

                            <div class="mb-4">
                                <label class="flex items-center">
                                    <input
                                        v-model="createForm.activo"
                                        type="checkbox"
                                        class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                    />
                                    <span class="ml-2 text-sm text-gray-700">Material activo</span>
                                </label>
                            </div>
                        </div>
                    </form>

                    <template #footer>
                        <button
                            type="button"
                            @click="showCreateModal = false"
                            class="px-5 py-2.5 border border-indigo-200 rounded-lg hover:bg-indigo-50 text-indigo-700 transition-colors"
                        >
                            Cancelar
                        </button>
                        <button
                            type="button"
                            @click="submitCreate"
                            :disabled="createForm.processing"
                            class="px-5 py-2.5 bg-gradient-to-r from-indigo-600 to-blue-600 text-white rounded-lg hover:from-indigo-700 hover:to-blue-700 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200 disabled:opacity-50 disabled:transform-none"
                        >
                            <span v-if="createForm.processing">Guardando...</span>
                            <span v-else>Guardar Material</span>
                        </button>
                    </template>
                </Modal>

                <!-- Filtros -->
                <div class="mb-4 bg-white/80 backdrop-blur-sm p-4 rounded-xl shadow-lg border border-indigo-100">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
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
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Sector</label>
                            <select
                                v-model="filters.sector_id"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
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
                    v-if="materiales && materiales.data"
                    :data="materiales.data || []"
                    :columns="columns"
                    :loading="false"
                    :show-search="false"
                    :paginated="false"
                    :on-edit="editMaterial"
                    :on-delete="deleteMaterial"
                >
                    <template #cell-categoria="{ value, row }">
                        <template v-if="value && typeof value === 'object' && value.nombre">
                            <Badge variant="info">{{ value.nombre }}</Badge>
                        </template>
                        <template v-else-if="row && row.categoria && typeof row.categoria === 'object' && row.categoria.nombre">
                            <Badge variant="info">{{ row.categoria.nombre }}</Badge>
                        </template>
                        <span v-else class="text-gray-400">Sin categor├¡a</span>
                    </template>
                    <template #cell-sector="{ value, row }">
                        <template v-if="value && typeof value === 'object' && value.nombre">
                            {{ value.nombre }}
                        </template>
                        <template v-else-if="row && row.sector && typeof row.sector === 'object' && row.sector.nombre">
                            {{ row.sector.nombre }}
                        </template>
                        <span v-else class="text-gray-400">Sin sector</span>
                    </template>
                    <template #cell-precio="{ value }">
                        <span v-if="value !== null && value !== undefined">
                            ${{ formatPrecio(value) }}
                        </span>
                        <span v-else class="text-gray-400">$0.00</span>
                    </template>
                    <template #cell-stock_actual="{ row }">
                        <template v-if="row && typeof row === 'object'">
                            <span :class="getStockClass(row)">
                                {{ row.stock_actual || 0 }} {{ row.unidad_medida || '' }}
                                <span v-if="isStockBajo(row)" class="text-xs">ÔÜá´©Å</span>
                            </span>
                        </template>
                        <span v-else class="text-gray-400">-</span>
                    </template>
                    <template #actions="{ row }">
                        <template v-if="row && typeof row === 'object' && row.id">
                            <div class="flex items-center space-x-2">
                                <button
                                    @click="() => viewMaterial(row)"
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
                                    @click="() => editMaterial(row)"
                                    class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-indigo-700 bg-indigo-50 rounded-md hover:bg-indigo-100 transition-colors"
                                    title="Editar"
                                >
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                    Editar
                                </button>
                                <button
                                    @click="() => deleteMaterial(row)"
                                    class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-red-700 bg-red-50 rounded-md hover:bg-red-100 transition-colors"
                                    title="Eliminar"
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
                <div v-if="materiales.links" class="mt-4">
                    <div class="flex justify-center">
                        <div v-for="link in materiales.links" :key="link.label">
                            <Link
                                v-if="link.url"
                                :href="link.url"
                                v-html="link.label"
                                :class="[
                                    'px-3 py-2 border rounded-md mx-1',
                                    link.active ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50'
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
import { Link, router, useForm, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Pages/Layout.vue';
import DataTable from '@/Components/Table/DataTable.vue';
import Badge from '@/Components/UI/Badge.vue';
import Modal from '@/Components/UI/Modal.vue';
import Input from '@/Components/Form/Input.vue';
import Textarea from '@/Components/Form/Textarea.vue';
import Select from '@/Components/Form/Select.vue';

const props = defineProps({
        auth: { type: Object, required: true },
    visitasPagina: { type: Number, default: 0 },
materiales: Object,
    categorias: Array,
    sectores: Array,
    menuItems: Array,
    pageVisits: Number,
    filters: Object,
});

const showCreateModal = ref(false);

const columns = [
    { key: 'id', label: 'ID', sortable: true },
    { key: 'nombre', label: 'Nombre', sortable: true },
    { key: 'categoria', label: 'Categor├¡a', sortable: false },
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

const createForm = useForm({
    nombre: '',
    descripcion: '',
    categoria_id: '',
    sector_id: '',
    stock_actual: 0,
    stock_minimo: 0,
    punto_reorden: 0,
    precio: 0,
    unidad_medida: '',
    activo: true,
});

const page = usePage();

const canCreate = computed(() => {
    try {
        const user = page.props.auth?.user;
        const rol = user?.rol?.nombre;
        return rol === 'PROPIETARIO' || rol === 'CARPINTERO';
    } catch (e) {
        console.error('Error checking canCreate:', e);
        return true; // Temporalmente true para debug
    }
});

const canEdit = computed(() => {
    try {
        const rolNombre = page.props.auth?.user?.rol?.nombre;
        return rolNombre && ['PROPIETARIO', 'CARPINTERO'].includes(rolNombre);
    } catch (e) {
        console.error('Error checking canEdit:', e);
        return true; // Temporalmente true para debug
    }
});

const canDelete = computed(() => {
    try {
        const rolNombre = page.props.auth?.user?.rol?.nombre;
        return rolNombre && ['PROPIETARIO', 'CARPINTERO'].includes(rolNombre);
    } catch (e) {
        console.error('Error checking canDelete:', e);
        return true; // Temporalmente true para debug
    }
});

const getRoute = (name, params = null) => {
    try {
        if (window.route && typeof window.route === 'function') {
            return params ? window.route(name, params) : window.route(name);
        }
    } catch (e) {
        console.warn('route function not available:', e);
    }
    // Fallback a URLs directas
    if (name === 'materiales.index') return '/materiales';
    if (name === 'materiales.edit' && params) return `/materiales/${params}/edit`;
    if (name === 'materiales.show' && params) return `/materiales/${params}`;
    if (name === 'materiales.destroy' && params) return `/materiales/${params}`;
    return '#';
};

const formatPrecio = (precio) => {
    if (!precio) return '0.00';
    return parseFloat(precio).toFixed(2);
};

const getStockClass = (row) => {
    if (!row || typeof row !== 'object') return '';
    if (row.stock_actual <= row.stock_minimo) {
        return 'text-red-600 font-bold';
    }
    return 'text-gray-700';
};

const isStockBajo = (row) => {
    if (!row || typeof row !== 'object') return false;
    return row.stock_actual <= row.stock_minimo;
};

const applyFilters = () => {
    const cleanFilters = {};
    if (filters.value.search && filters.value.search.trim() !== '') {
        cleanFilters.search = filters.value.search.trim();
    }
    if (filters.value.categoria_id && filters.value.categoria_id !== '') {
        cleanFilters.categoria_id = filters.value.categoria_id;
    }
    if (filters.value.sector_id && filters.value.sector_id !== '') {
        cleanFilters.sector_id = filters.value.sector_id;
    }
    if (filters.value.stock_bajo) {
        cleanFilters.stock_bajo = filters.value.stock_bajo;
    }
    
    try {
        const routeUrl = getRoute('materiales.index');
        if (routeUrl && routeUrl !== '#') {
            router.get(routeUrl, cleanFilters, {
                preserveState: true,
                preserveScroll: true,
            });
        } else {
            router.get('/materiales', cleanFilters, {
                preserveState: true,
                preserveScroll: true,
            });
        }
    } catch (e) {
        console.error('Error aplicando filtros:', e);
        router.get('/materiales', cleanFilters, {
            preserveState: true,
            preserveScroll: true,
        });
    }
};

const clearFilters = () => {
    filters.value = { search: '', categoria_id: '', sector_id: '', stock_bajo: false };
    router.get('/materiales', {}, {
        preserveState: true,
        preserveScroll: true,
    });
};

const submitCreate = () => {
    createForm.post('/materiales', {
        preserveScroll: true,
        onSuccess: () => {
            showCreateModal.value = false;
            createForm.reset();
        },
    });
};

const viewMaterial = (material) => {
    if (!material || !material.id) {
        console.error('Material inv├ílido para ver:', material);
        return;
    }
    try {
        const routeUrl = getRoute('materiales.show', material.id);
        if (routeUrl && routeUrl !== '#') {
            router.visit(routeUrl);
        } else {
            router.visit(`/materiales/${material.id}`);
        }
    } catch (e) {
        console.error('Error al ver material:', e);
        router.visit(`/materiales/${material.id}`);
    }
};

const editMaterial = (material) => {
    if (!material || !material.id) {
        console.error('Material inv├ílido para editar:', material);
        return;
    }
    console.log('Editando material:', material.id);
    try {
        const routeUrl = getRoute('materiales.edit', material.id);
        console.log('Route URL:', routeUrl);
        if (routeUrl && routeUrl !== '#') {
            router.visit(routeUrl);
        } else {
            const directUrl = `/materiales/${material.id}/edit`;
            console.log('Usando URL directa:', directUrl);
            router.visit(directUrl);
        }
    } catch (e) {
        console.error('Error al editar material:', e);
        router.visit(`/materiales/${material.id}/edit`);
    }
};

const deleteMaterial = (material) => {
    if (!material || !material.id) {
        console.error('Material inv├ílido para eliminar:', material);
        return;
    }
    if (confirm(`┬┐Est├í seguro de eliminar el material "${material.nombre || 'este material'}"?`)) {
        try {
            const routeUrl = getRoute('materiales.destroy', material.id);
            if (routeUrl && routeUrl !== '#') {
                router.delete(routeUrl, {
                    preserveScroll: true,
                });
            } else {
                router.delete(`/materiales/${material.id}`, {
                    preserveScroll: true,
                });
            }
        } catch (e) {
            console.error('Error al eliminar material:', e);
            router.delete(`/materiales/${material.id}`, {
                preserveScroll: true,
            });
        }
    }
};
</script>
