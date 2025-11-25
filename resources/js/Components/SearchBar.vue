<template>
    <div class="relative">
        <div class="flex items-center">
            <input
                v-model="searchTerm"
                @input="handleSearch"
                @focus="showResults = true"
                @blur="hideResults"
                type="text"
                placeholder="Buscar productos, servicios..."
                class="w-64 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
            <button
                @click="performSearch"
                class="ml-2 px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600"
            >
                🔍
            </button>
        </div>

        <!-- Resultados de búsqueda -->
        <div
            v-if="showResults && resultados.length > 0"
            class="absolute top-full left-0 mt-2 w-96 bg-white border border-gray-300 rounded-lg shadow-lg z-50 max-h-96 overflow-y-auto"
        >
            <div
                v-for="resultado in resultados"
                :key="`${resultado.tipo}-${resultado.id}`"
                @click="goToResult(resultado)"
                class="p-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-b-0"
            >
                <div class="flex justify-between items-start">
                    <div>
                        <p class="font-semibold text-sm">{{ resultado.nombre || resultado.descripcion }}</p>
                        <p class="text-xs text-gray-500">{{ getTipoLabel(resultado.tipo) }}</p>
                        <p v-if="resultado.precio" class="text-xs text-gray-700 mt-1">
                            Precio: ${{ resultado.precio?.toFixed(2) }}
                        </p>
                    </div>
                    <span class="text-xs px-2 py-1 bg-blue-100 text-blue-800 rounded">
                        {{ resultado.tipo }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Mensaje cuando no hay resultados -->
        <div
            v-if="showResults && searchTerm && resultados.length === 0 && !loading"
            class="absolute top-full left-0 mt-2 w-96 bg-white border border-gray-300 rounded-lg shadow-lg z-50 p-4"
        >
            <p class="text-sm text-gray-500">No se encontraron resultados para "{{ searchTerm }}"</p>
        </div>
    </div>
</template>

<script setup>
import { ref, debounce } from 'vue';
import axios from 'axios';
import { router } from '@inertiajs/vue3';

const searchTerm = ref('');
const resultados = ref([]);
const showResults = ref(false);
const loading = ref(false);

// Debounce para búsqueda mientras escribe
const handleSearch = debounce(async () => {
    if (searchTerm.value.length < 2) {
        resultados.value = [];
        return;
    }

    loading.value = true;
    try {
        const response = await axios.get('/api/buscar', {
            params: { q: searchTerm.value },
        });

        // Combinar todos los resultados
        resultados.value = [
            ...response.data.productos,
            ...response.data.servicios,
            ...response.data.materiales,
            ...response.data.pedidos,
        ];
    } catch (error) {
        console.error('Error en búsqueda:', error);
        resultados.value = [];
    } finally {
        loading.value = false;
    }
}, 300);

const performSearch = () => {
    if (searchTerm.value.trim()) {
        router.visit(`/buscar?q=${encodeURIComponent(searchTerm.value)}`);
        showResults.value = false;
    }
};

const goToResult = (resultado) => {
    router.visit(resultado.ruta);
    showResults.value = false;
    searchTerm.value = '';
};

const hideResults = () => {
    // Delay para permitir click en resultados
    setTimeout(() => {
        showResults.value = false;
    }, 200);
};

const getTipoLabel = (tipo) => {
    const labels = {
        producto: 'Producto',
        servicio: 'Servicio',
        material: 'Material',
        pedido: 'Pedido',
    };
    return labels[tipo] || tipo;
};
</script>

