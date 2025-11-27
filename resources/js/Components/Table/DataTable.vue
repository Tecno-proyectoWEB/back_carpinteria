<template>
    <div class="bg-white shadow-sm rounded-lg overflow-hidden">
        <!-- Header con búsqueda y acciones -->
        <div v-if="showSearch || $slots.actions" class="p-4 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <div v-if="showSearch" class="flex-1 max-w-md">
                    <input
                        v-model="searchTerm"
                        type="text"
                        placeholder="Buscar..."
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                </div>
                <div v-if="$slots.actions" class="ml-4">
                    <slot name="actions"></slot>
                </div>
            </div>
        </div>

        <!-- Tabla -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th
                            v-for="column in columns"
                            :key="column.key"
                            :class="[
                                'px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider',
                                column.sortable ? 'cursor-pointer hover:bg-gray-100' : ''
                            ]"
                            @click="column.sortable && handleSort(column.key)"
                        >
                            <div class="flex items-center space-x-1">
                                <span>{{ column.label }}</span>
                                <span v-if="column.sortable && sortColumn === column.key">
                                    {{ sortDirection === 'asc' ? '↑' : '↓' }}
                                </span>
                            </div>
                        </th>
                        <th v-if="showActions" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Acciones
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-if="loading">
                        <td :colspan="columns.length + (showActions ? 1 : 0)" class="px-6 py-4 text-center">
                            <div class="flex justify-center">
                                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
                            </div>
                        </td>
                    </tr>
                    <tr v-else-if="filteredData.length === 0">
                        <td :colspan="columns.length + (showActions ? 1 : 0)" class="px-6 py-4 text-center text-gray-500">
                            No hay datos disponibles
                        </td>
                    </tr>
                    <tr
                        v-else
                        v-for="(row, index) in paginatedData"
                        :key="row?.id || index"
                        class="hover:bg-gray-50"
                    >
                        <td
                            v-for="column in columns"
                            :key="column.key"
                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"
                        >
                            <slot :name="`cell-${column.key}`" :row="row" :value="getValue(row, column.key)">
                                {{ formatValue(getValue(row, column.key), column) }}
                            </slot>
                        </td>
                        <td v-if="showActions" class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <slot name="actions" :row="row">
                                <button
                                    v-if="onEdit"
                                    @click="onEdit(row)"
                                    class="text-blue-600 hover:text-blue-900 mr-3"
                                >
                                    Editar
                                </button>
                                <button
                                    v-if="onDelete"
                                    @click="handleDelete(row)"
                                    class="text-red-600 hover:text-red-900"
                                >
                                    Eliminar
                                </button>
                            </slot>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        <div v-if="paginated && totalPages > 1" class="px-4 py-3 border-t border-gray-200 sm:px-6">
            <div class="flex items-center justify-between">
                <div class="text-sm text-gray-700">
                    Mostrando {{ startItem }} a {{ endItem }} de {{ totalItems }} resultados
                </div>
                <div class="flex space-x-2">
                    <button
                        @click="currentPage = 1"
                        :disabled="currentPage === 1"
                        class="px-3 py-1 border rounded-md disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        Primera
                    </button>
                    <button
                        @click="currentPage--"
                        :disabled="currentPage === 1"
                        class="px-3 py-1 border rounded-md disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        Anterior
                    </button>
                    <span class="px-3 py-1">
                        Página {{ currentPage }} de {{ totalPages }}
                    </span>
                    <button
                        @click="currentPage++"
                        :disabled="currentPage === totalPages"
                        class="px-3 py-1 border rounded-md disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        Siguiente
                    </button>
                    <button
                        @click="currentPage = totalPages"
                        :disabled="currentPage === totalPages"
                        class="px-3 py-1 border rounded-md disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        Última
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
    data: {
        type: Array,
        required: true,
    },
    columns: {
        type: Array,
        required: true,
    },
    showActions: {
        type: Boolean,
        default: true,
    },
    showSearch: {
        type: Boolean,
        default: true,
    },
    paginated: {
        type: Boolean,
        default: true,
    },
    perPage: {
        type: Number,
        default: 10,
    },
    loading: {
        type: Boolean,
        default: false,
    },
    onEdit: Function,
    onDelete: Function,
});

const searchTerm = ref('');
const currentPage = ref(1);
const sortColumn = ref(null);
const sortDirection = ref('asc');

const filteredData = computed(() => {
    let result = [...props.data];

    // Búsqueda
    if (searchTerm.value) {
        const term = searchTerm.value.toLowerCase();
        result = result.filter(row => {
            return props.columns.some(column => {
                const value = getValue(row, column.key);
                return String(value).toLowerCase().includes(term);
            });
        });
    }

    // Ordenamiento
    if (sortColumn.value) {
        result.sort((a, b) => {
            const aVal = getValue(a, sortColumn.value);
            const bVal = getValue(b, sortColumn.value);
            const comparison = aVal > bVal ? 1 : aVal < bVal ? -1 : 0;
            return sortDirection.value === 'asc' ? comparison : -comparison;
        });
    }

    return result;
});

const totalItems = computed(() => filteredData.value.length);
const totalPages = computed(() => Math.ceil(totalItems.value / props.perPage));
const startItem = computed(() => (currentPage.value - 1) * props.perPage + 1);
const endItem = computed(() => Math.min(currentPage.value * props.perPage, totalItems.value));

const paginatedData = computed(() => {
    if (!props.paginated) return filteredData.value;
    const start = (currentPage.value - 1) * props.perPage;
    const end = start + props.perPage;
    return filteredData.value.slice(start, end);
});

const handleSort = (column) => {
    if (sortColumn.value === column) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortColumn.value = column;
        sortDirection.value = 'asc';
    }
    currentPage.value = 1;
};

const handleDelete = (row) => {
    if (confirm('¿Está seguro de eliminar este registro?')) {
        props.onDelete?.(row);
    }
};

const getValue = (obj, path) => {
    return path.split('.').reduce((o, p) => o?.[p], obj);
};

const formatValue = (value, column) => {
    if (value === null || value === undefined) return '-';
    if (column.format === 'currency') {
        return new Intl.NumberFormat('es-AR', { style: 'currency', currency: 'ARS' }).format(value);
    }
    if (column.format === 'date') {
        return new Date(value).toLocaleDateString('es-AR');
    }
    if (column.format === 'boolean') {
        return value ? 'Sí' : 'No';
    }
    return value;
};
</script>

