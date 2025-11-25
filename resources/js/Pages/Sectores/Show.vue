<template>
    <AppLayout :menu-items="menuItems" :page-visits="pageVisits">
        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex justify-between items-center">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900">{{ sector.nombre }}</h2>
                            <p class="text-sm text-gray-500 mt-1">ID: {{ sector.id }}</p>
                        </div>
                        <div class="flex space-x-2">
                            <Link
                                v-if="canEdit"
                                :href="route('sectores.edit', sector.id)"
                                class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700"
                            >
                                Editar
                            </Link>
                            <Link
                                :href="route('sectores.index')"
                                class="px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50"
                            >
                                Volver
                            </Link>
                        </div>
                    </div>

                    <div class="px-6 py-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Información General -->
                            <div>
                                <h3 class="text-lg font-semibold mb-4">Información General</h3>
                                <div class="space-y-3">
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Nombre</label>
                                        <p class="text-gray-900">{{ sector.nombre }}</p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Almacén</label>
                                        <p class="mt-1">
                                            <Badge variant="info">{{ sector.almacen?.nombre || 'N/A' }}</Badge>
                                        </p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Tipo</label>
                                        <p class="text-gray-900">{{ sector.tipo || 'No especificado' }}</p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Descripción</label>
                                        <p class="text-gray-900">{{ sector.descripcion || 'Sin descripción' }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Capacidad y Stock -->
                            <div>
                                <h3 class="text-lg font-semibold mb-4">Capacidad y Stock</h3>
                                <div class="space-y-3">
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Stock Actual</label>
                                        <p class="text-gray-900 font-semibold text-xl">{{ sector.stock || 0 }}</p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Capacidad Máxima</label>
                                        <p class="text-gray-900 font-semibold text-xl">{{ sector.capacidad_maxima || 'Sin límite' }}</p>
                                    </div>
                                    <div v-if="sector.capacidad_maxima">
                                        <label class="text-sm font-medium text-gray-500">Porcentaje de Uso</label>
                                        <div class="mt-2">
                                            <div class="w-full bg-gray-200 rounded-full h-2.5">
                                                <div
                                                    class="bg-blue-600 h-2.5 rounded-full"
                                                    :style="{ width: porcentajeUso + '%' }"
                                                ></div>
                                            </div>
                                            <p class="text-sm text-gray-600 mt-1">{{ porcentajeUso.toFixed(1) }}%</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Materiales en el Sector -->
                        <div class="mt-6">
                            <h3 class="text-lg font-semibold mb-4">Materiales en este Sector</h3>
                            <div v-if="sector.materiales && sector.materiales.length > 0" class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Material</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Stock</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr v-for="material in sector.materiales" :key="material.id">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ material.nombre }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ material.stock_actual }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div v-else class="text-center py-8 text-gray-500">
                                No hay materiales asignados a este sector
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
    sector: Object,
    menuItems: Array,
    pageVisits: Number,
});

const canEdit = computed(() => {
    const rol = window.$page?.props?.auth?.user?.rol?.nombre;
    return ['PROPIETARIO', 'CARPINTERO'].includes(rol);
});

const porcentajeUso = computed(() => {
    if (!props.sector.capacidad_maxima || props.sector.capacidad_maxima === 0) return 0;
    return ((props.sector.stock || 0) / props.sector.capacidad_maxima) * 100;
});
</script>

