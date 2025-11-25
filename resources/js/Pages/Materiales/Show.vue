<template>
    <AppLayout :menu-items="menuItems" :page-visits="pageVisits">
        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex justify-between items-center">
                        <h2 class="text-2xl font-bold text-gray-900">{{ material.nombre }}</h2>
                        <div class="flex space-x-2">
                            <Link
                                v-if="canEdit"
                                :href="route('materiales.edit', material.id)"
                                class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700"
                            >
                                Editar
                            </Link>
                            <Link
                                :href="route('materiales.index')"
                                class="px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50"
                            >
                                Volver
                            </Link>
                        </div>
                    </div>

                    <div class="px-6 py-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Imagen -->
                            <div>
                                <img
                                    v-if="material.imagen"
                                    :src="`/storage/${material.imagen}`"
                                    :alt="material.nombre"
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
                                    <p class="mt-1 text-gray-900">{{ material.descripcion || 'Sin descripción' }}</p>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Categoría</label>
                                        <p class="mt-1">
                                            <Badge variant="info">{{ material.categoria?.nombre || 'Sin categoría' }}</Badge>
                                        </p>
                                    </div>

                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Sector</label>
                                        <p class="mt-1 text-gray-900">{{ material.sector?.nombre || 'Sin sector' }}</p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-3 gap-4">
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Stock Actual</label>
                                        <p
                                            class="mt-1 text-lg font-semibold"
                                            :class="material.stock_actual <= material.stock_minimo ? 'text-red-600' : 'text-gray-900'"
                                        >
                                            {{ material.stock_actual }} {{ material.unidad_medida || '' }}
                                            <span v-if="material.stock_actual <= material.stock_minimo" class="text-xs">⚠️</span>
                                        </p>
                                    </div>

                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Stock Mínimo</label>
                                        <p class="mt-1 text-lg font-semibold text-gray-900">
                                            {{ material.stock_minimo || 0 }} {{ material.unidad_medida || '' }}
                                        </p>
                                    </div>

                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Punto Reorden</label>
                                        <p class="mt-1 text-lg font-semibold text-gray-900">
                                            {{ material.punto_reorden || 0 }} {{ material.unidad_medida || '' }}
                                        </p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Precio</label>
                                        <p class="mt-1 text-2xl font-bold text-green-600">
                                            {{ material.precio ? `$${parseFloat(material.precio).toFixed(2)}` : 'N/A' }}
                                        </p>
                                    </div>

                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Estado</label>
                                        <p class="mt-1">
                                            <Badge :variant="material.activo ? 'success' : 'error'">
                                                {{ material.activo ? 'Activo' : 'Inactivo' }}
                                            </Badge>
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
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Badge from '@/Components/UI/Badge.vue';

const props = defineProps({
    material: Object,
    menuItems: Array,
    pageVisits: Number,
});

const canEdit = computed(() => {
    const rol = window.$page?.props?.auth?.user?.rol?.nombre;
    return ['PROPIETARIO', 'CARPINTERO'].includes(rol);
});
</script>
