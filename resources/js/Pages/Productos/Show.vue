<template>
    <AppLayout :menu-items="menuItems" :page-visits="pageVisits">
        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white shadow-lg rounded-xl overflow-hidden border border-gray-200">
                    <!-- Header -->
                    <div class="px-6 py-5 bg-gradient-to-r from-indigo-600 to-blue-600 text-white flex justify-between items-center">
                        <div>
                            <h2 class="text-2xl font-bold">{{ producto?.nombre || '' }}</h2>
                            <p class="text-sm text-indigo-100 mt-1">{{ producto?.categoria?.nombre || 'Sin categoría' }}</p>
                        </div>
                        <div class="flex space-x-2">
                            <button
                                v-if="canEdit"
                                @click="editProducto"
                                class="px-4 py-2 bg-white text-indigo-600 rounded-md hover:bg-indigo-50 font-medium transition-colors"
                            >
                                Editar
                            </button>
                            <button
                                @click="goBack"
                                class="px-4 py-2 bg-indigo-700 text-white rounded-md hover:bg-indigo-800 font-medium transition-colors"
                            >
                                Volver
                            </button>
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="px-6 py-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Imagen -->
                            <div class="bg-gray-50 rounded-lg p-5 border border-gray-200">
                                <h3 class="text-lg font-semibold mb-4 text-gray-800 flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    Imagen del Producto
                                </h3>
                                <div v-if="producto?.imagen" class="w-full">
                                    <img
                                        :src="`/storage/${producto.imagen}`"
                                        :alt="producto?.nombre || 'Imagen'"
                                        class="w-full h-64 object-cover rounded-lg"
                                    />
                                </div>
                                <div v-else class="w-full h-64 bg-gray-200 rounded-lg flex items-center justify-center">
                                    <span class="text-gray-400">Sin imagen</span>
                                </div>
                            </div>

                            <!-- Información -->
                            <div class="space-y-6">
                                <div class="bg-gray-50 rounded-lg p-5 border border-gray-200">
                                    <h3 class="text-lg font-semibold mb-4 text-gray-800 flex items-center">
                                        <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Información General
                                    </h3>
                                    <div class="space-y-4">
                                        <div>
                                            <label class="text-sm font-medium text-gray-500 block mb-1">Descripción</label>
                                            <p class="text-gray-900">{{ producto?.descripcion || 'Sin descripción' }}</p>
                                        </div>
                                        <div>
                                            <label class="text-sm font-medium text-gray-500 block mb-1">Categoría</label>
                                            <p class="mt-1">
                                                <Badge variant="info" class="text-sm px-3 py-1">{{ producto?.categoria?.nombre || 'Sin categoría' }}</Badge>
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-gray-50 rounded-lg p-5 border border-gray-200">
                                    <h3 class="text-lg font-semibold mb-4 text-gray-800 flex items-center">
                                        <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                        </svg>
                                        Stock y Precio
                                    </h3>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="text-sm font-medium text-gray-500 block mb-1">Stock</label>
                                            <p
                                                class="mt-1 text-lg font-semibold"
                                                :class="(producto?.stock || 0) <= (producto?.stock_minimo || 0) ? 'text-red-600' : 'text-gray-900'"
                                            >
                                                {{ producto?.stock || 0 }}
                                                <span v-if="(producto?.stock || 0) <= (producto?.stock_minimo || 0)" class="text-xs">⚠️ Stock bajo</span>
                                            </p>
                                        </div>
                                        <div>
                                            <label class="text-sm font-medium text-gray-500 block mb-1">Stock Mínimo</label>
                                            <p class="mt-1 text-lg font-semibold text-gray-900">{{ producto?.stock_minimo || 0 }}</p>
                                        </div>
                                    </div>
                                    <div class="mt-4">
                                        <label class="text-sm font-medium text-gray-500 block mb-1">Precio Unitario</label>
                                        <p class="mt-1 text-2xl font-bold text-green-600">
                                            ${{ parseFloat(producto?.precio_unitario || 0).toFixed(2) }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Badge from '@/Components/UI/Badge.vue';

const props = defineProps({
    producto: Object,
    menuItems: Array,
    pageVisits: Number,
});

const page = usePage();

const canEdit = computed(() => {
    try {
        const rol = page.props.auth?.user?.rol?.nombre;
        return ['PROPIETARIO', 'CARPINTERO', 'ADMINISTRADOR'].includes(rol);
    } catch (e) {
        return false;
    }
});

const editProducto = () => {
    if (!props.producto?.id) return;
    try {
        router.visit(`/productos/${props.producto.id}/edit`);
    } catch (e) {
        console.error('Error al editar producto:', e);
    }
};

const goBack = () => {
    router.visit('/productos');
};
</script>
