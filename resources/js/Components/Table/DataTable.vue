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
                            v-for="(column, colIdx) in (columns || [])"
                            :key="column?.key || `col-${colIdx}`"
                            :class="[
                                'px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider',
                                column?.sortable ? 'cursor-pointer hover:bg-gray-100' : ''
                            ]"
                            @click="column?.sortable && column?.key && handleSort(column.key)"
                        >
                            <div class="flex items-center space-x-1">
                                <span>{{ column?.label || '' }}</span>
                                <span v-if="column?.sortable && sortColumn === column?.key">
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
                        <td :colspan="(columns?.length || 0) + (showActions ? 1 : 0)" class="px-6 py-4 text-center">
                            <div class="flex justify-center">
                                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
                            </div>
                        </td>
                    </tr>
                    <tr v-else-if="filteredData.length === 0">
                        <td :colspan="(columns?.length || 0) + (showActions ? 1 : 0)" class="px-6 py-4 text-center text-gray-500">
                            No hay datos disponibles
                        </td>
                    </tr>
                    <tr
                        v-else
                        v-for="(row, index) in paginatedData"
                        :key="row?.id || index"
                        class="hover:bg-gray-50"
                    >
                        <template v-if="row && typeof row === 'object'">
                            <td
                                v-for="(column, colIndex) in (columns || [])"
                                :key="column?.key || `col-${colIndex}`"
                                class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"
                            >
                                <template v-if="column && column.key">
                                    <slot 
                                        :name="`cell-${column.key}`" 
                                        :row="row" 
                                        :value="safeGetValue(row, column.key)"
                                    >
                                        {{ safeFormatValue(safeGetValue(row, column.key), column) }}
                                    </slot>
                                </template>
                                <span v-else>-</span>
                            </td>
                            <td v-if="showActions" class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <slot name="actions" :row="row">
                                    <button
                                        v-if="props.onEdit && typeof props.onEdit === 'function' && row && row.id"
                                        @click="() => safeEdit(row)"
                                        class="text-blue-600 hover:text-blue-900 mr-3"
                                    >
                                        Editar
                                    </button>
                                    <button
                                        v-if="props.onDelete && typeof props.onDelete === 'function' && row && row.id"
                                        @click="() => safeDelete(row)"
                                        class="text-red-600 hover:text-red-900"
                                    >
                                        Eliminar
                                    </button>
                                </slot>
                            </td>
                        </template>
                        <template v-else>
                            <td :colspan="(columns?.length || 0) + (showActions ? 1 : 0)" class="px-6 py-4 text-center text-gray-400 text-xs">
                                Fila inválida
                            </td>
                        </template>
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
        type: [Array, Object],
        required: true,
        default: () => [],
    },
    columns: {
        type: Array,
        required: true,
        default: () => [],
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
    if (!props.data) {
        return [];
    }
    
    // Asegurar que data sea un array
    const dataArray = Array.isArray(props.data) ? props.data : [];
    let result = [...dataArray];

    // Búsqueda
    if (searchTerm.value && props.columns && Array.isArray(props.columns)) {
        const term = searchTerm.value.toLowerCase();
        result = result.filter(row => {
            if (!row) return false;
            return props.columns.some(column => {
                if (!column || typeof column !== 'object' || !column.key) return false;
                const value = safeGetValue(row, column.key);
                return String(value || '').toLowerCase().includes(term);
            });
        });
    }

    // Ordenamiento
    if (sortColumn.value) {
        result.sort((a, b) => {
            if (!a || typeof a !== 'object' || !b || typeof b !== 'object') return 0;
            const aVal = safeGetValue(a, sortColumn.value);
            const bVal = safeGetValue(b, sortColumn.value);
            // Manejar valores undefined/null
            if (aVal === undefined || aVal === null) return 1;
            if (bVal === undefined || bVal === null) return -1;
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
    if (!row) {
        console.warn('Cannot delete: row is undefined or null');
        return;
    }
    if (confirm('¿Está seguro de eliminar este registro?')) {
        if (props.onDelete && typeof props.onDelete === 'function') {
            props.onDelete(row);
        }
    }
};

const safeGetValue = (obj, path) => {
    if (!obj || typeof obj !== 'object' || !path || typeof path !== 'string') {
        return undefined;
    }
    try {
        const keys = path.split('.');
        let result = obj;
        for (const key of keys) {
            if (result === null || result === undefined) {
                return undefined;
            }
            result = result[key];
        }
        return result;
    } catch (e) {
        console.warn('Error getting value:', e, { obj, path });
        return undefined;
    }
};

const safeFormatValue = (value, column) => {
    if (value === null || value === undefined) return '-';
    if (!column || typeof column !== 'object') {
        return String(value || '');
    }
    
    try {
        if (column.format === 'currency') {
            const numValue = parseFloat(value);
            if (isNaN(numValue)) return '-';
            return new Intl.NumberFormat('es-AR', { style: 'currency', currency: 'ARS' }).format(numValue);
        }
        if (column.format === 'date') {
            const dateValue = new Date(value);
            if (isNaN(dateValue.getTime())) return '-';
            return dateValue.toLocaleDateString('es-AR');
        }
        if (column.format === 'boolean') {
            return value ? 'Sí' : 'No';
        }
        return String(value || '');
    } catch (error) {
        console.warn('Error formatting value:', error, value, column);
        return String(value || '');
    }
};

const safeEdit = (row) => {
    if (!row || !row.id || !props.onEdit || typeof props.onEdit !== 'function') {
        console.warn('Cannot edit: invalid row or handler', row);
        return;
    }
    try {
        props.onEdit(row);
    } catch (e) {
        console.error('Error in edit handler:', e);
    }
};

const safeDelete = (row) => {
    if (!row || !row.id || !props.onDelete || typeof props.onDelete !== 'function') {
        console.warn('Cannot delete: invalid row or handler', row);
        return;
    }
    try {
        handleDelete(row);
    } catch (e) {
        console.error('Error in delete handler:', e);
    }
};
</script>

