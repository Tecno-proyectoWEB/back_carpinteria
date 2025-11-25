<template>
    <AppLayout :menu-items="menuItems" :page-visits="pageVisits">
        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                    <!-- Header -->
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex justify-between items-center">
                        <h2 class="text-2xl font-bold text-gray-900">{{ producto.nombre }}</h2>
                        <div class="flex space-x-2">
                            <Link
                                v-if="canEdit"
                                :href="route('productos.edit', producto.id)"
                                class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700"
                            >
                                Editar
                            </Link>
                            <Link
                                :href="route('productos.index')"
                                class="px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50"
                            >
                                Volver
                            </Link>
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="px-6 py-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Imagen -->
                            <div>
                                <img
                                    v-if="producto.imagen"
                                    :src="`/storage/${producto.imagen}`"
                                    :alt="producto.nombre"
                                    class="w-full h-64 object-cover rounded-lg"
                                />
                                <div v-else class="w-full h-64 bg-gray-200 rounded-lg flex items-center justify-center">
                                    <span class="text-gray-400">Sin imagen</span>
                                </div>
                            </div>

                            <!-- Información -->
                            <div class="space-y-4">
                                <div>
                                    <label class="text-sm font-medium text-gray-500">Descripción</label>
                                    <p class="mt-1 text-gray-900">{{ producto.descripcion || 'Sin descripción' }}</p>
                                </div>

                                <div>
                                    <label class="text-sm font-medium text-gray-500">Categoría</label>
                                    <p class="mt-1">
                                        <Badge variant="info">{{ producto.categoria?.nombre || 'Sin categoría' }}</Badge>
                                    </p>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Stock</label>
                                        <p
                                            class="mt-1 text-lg font-semibold"
                                            :class="producto.stock <= producto.stock_minimo ? 'text-red-600' : 'text-gray-900'"
                                        >
                                            {{ producto.stock }}
                                            <span v-if="producto.stock <= producto.stock_minimo" class="text-xs">⚠️ Stock bajo</span>
                                        </p>
                                    </div>

                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Stock Mínimo</label>
                                        <p class="mt-1 text-lg font-semibold text-gray-900">{{ producto.stock_minimo || 0 }}</p>
                                    </div>
                                </div>

                                <div>
                                    <label class="text-sm font-medium text-gray-500">Precio Unitario</label>
                                    <p class="mt-1 text-2xl font-bold text-green-600">
                                        ${{ parseFloat(producto.precio_unitario).toFixed(2) }}
                                    </p>
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
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Badge from '@/Components/UI/Badge.vue';

const props = defineProps({
    producto: Object,
    menuItems: Array,
    pageVisits: Number,
});

const canEdit = computed(() => {
    const rol = window.$page?.props?.auth?.user?.rol?.nombre;
    return ['PROPIETARIO', 'CARPINTERO'].includes(rol);
});
</script>

