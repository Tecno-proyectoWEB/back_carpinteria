<template>
    <AppLayout :menu-items="menuItems" :page-visits="pageVisits">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <h2 class="text-3xl font-bold text-gray-900 mb-6">Reportes del Negocio</h2>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Reporte de Ventas -->
                    <Link
                        :href="route('reportes.ventas')"
                        class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow"
                    >
                        <div class="flex items-center justify-between mb-4">
                            <div class="p-3 bg-green-100 rounded-lg">
                                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">Reporte de Ventas</h3>
                        <p class="text-gray-600 text-sm">
                            Análisis detallado de ventas, productos más vendidos y rendimiento por vendedor
                        </p>
                    </Link>

                    <!-- Reporte de Compras -->
                    <Link
                        :href="route('reportes.compras')"
                        class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow"
                    >
                        <div class="flex items-center justify-between mb-4">
                            <div class="p-3 bg-blue-100 rounded-lg">
                                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                </svg>
                            </div>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">Reporte de Compras</h3>
                        <p class="text-gray-600 text-sm">
                            Análisis de compras, materiales más comprados y gastos por proveedor
                        </p>
                    </Link>

                    <!-- Reporte de Inventario -->
                    <Link
                        :href="route('reportes.inventario')"
                        class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow"
                    >
                        <div class="flex items-center justify-between mb-4">
                            <div class="p-3 bg-yellow-100 rounded-lg">
                                <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                            </div>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">Reporte de Inventario</h3>
                        <p class="text-gray-600 text-sm">
                            Stock bajo, movimientos de inventario y análisis de existencias
                        </p>
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    menuItems: Array,
    pageVisits: Number,
});
</script>

