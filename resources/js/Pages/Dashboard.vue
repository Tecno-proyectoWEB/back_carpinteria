<template>
    <Layout :auth="auth" :visitasPagina="visitasPagina">
        <div>
            <!-- Header con gradiente -->
            <div class="mb-8">
                <h1 class="text-4xl font-bold text-legible-heading dark:text-white mb-2">
                    Dashboard
                </h1>
                <p class="text-legible dark:text-gray-300 text-lg">Bienvenido al panel de administración</p>
            </div>

            <!-- Estadísticas del Negocio -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg p-6 text-white transform hover:scale-105 transition-all duration-300">
                    <div class="flex items-center justify-between mb-4">
                        <div class="bg-white/20 rounded-lg p-3">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                        </div>
                    </div>
                    <h3 class="text-sm font-medium text-white/90 mb-1">Total Productos</h3>
                    <p class="text-4xl font-bold text-white">{{ stats?.totalProductos || 0 }}</p>
                </div>

                <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl shadow-lg p-6 text-white transform hover:scale-105 transition-all duration-300">
                    <div class="flex items-center justify-between mb-4">
                        <div class="bg-white/20 rounded-lg p-3">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                        </div>
                    </div>
                    <h3 class="text-sm font-medium text-white/90 mb-1">Total Materiales</h3>
                    <p class="text-4xl font-bold text-white">{{ stats?.totalMateriales || 0 }}</p>
                </div>

                <div class="bg-gradient-to-br from-yellow-500 to-orange-500 rounded-xl shadow-lg p-6 text-white transform hover:scale-105 transition-all duration-300">
                    <div class="flex items-center justify-between mb-4">
                        <div class="bg-white/20 rounded-lg p-3">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                    </div>
                    <h3 class="text-sm font-medium text-white/90 mb-1">Ventas Pendientes</h3>
                    <p class="text-4xl font-bold text-white">{{ stats?.ventasPendientes || 0 }}</p>
                </div>

                <div class="bg-gradient-to-br from-red-500 to-pink-500 rounded-xl shadow-lg p-6 text-white transform hover:scale-105 transition-all duration-300">
                    <div class="flex items-center justify-between mb-4">
                        <div class="bg-white/20 rounded-lg p-3">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                    </div>
                    <h3 class="text-sm font-medium text-white/90 mb-1">Pagos Pendientes</h3>
                    <p class="text-4xl font-bold text-white">{{ stats?.pagosPendientes || 0 }}</p>
                </div>
            </div>

            <!-- Estadísticas de Ventas -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border-l-4 border-emerald-500 p-6 transform hover:shadow-xl transition-all duration-300">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-sm font-semibold text-legible dark:text-gray-200 uppercase tracking-wide">Ventas Hoy</h3>
                        <div class="bg-emerald-100 dark:bg-emerald-900 rounded-full p-2">
                            <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <p class="text-3xl font-bold text-legible-heading dark:text-white">${{ formatNumber(stats?.ventasHoy) }}</p>
                    <p class="text-xs text-legible dark:text-gray-300 mt-1">{{ stats?.ventasHoyCount || 0 }} transacciones</p>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border-l-4 border-indigo-500 p-6 transform hover:shadow-xl transition-all duration-300">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-sm font-semibold text-legible dark:text-gray-200 uppercase tracking-wide">Ventas del Mes</h3>
                        <div class="bg-indigo-100 dark:bg-indigo-900 rounded-full p-2">
                            <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                    </div>
                    <p class="text-3xl font-bold text-legible-heading dark:text-white">${{ formatNumber(stats?.ventasMes) }}</p>
                    <p class="text-xs text-legible dark:text-gray-300 mt-1">{{ stats?.ventasMesCount || 0 }} transacciones</p>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border-l-4 border-purple-500 p-6 transform hover:shadow-xl transition-all duration-300">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-sm font-semibold text-legible dark:text-gray-200 uppercase tracking-wide">Visitas Hoy</h3>
                        <div class="bg-purple-100 dark:bg-purple-900 rounded-full p-2">
                            <svg class="w-5 h-5 text-purple-600 dark:text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </div>
                    </div>
                    <p class="text-3xl font-bold text-legible-heading dark:text-white">{{ stats?.visitasHoy || 0 }}</p>
                    <p class="text-xs text-legible dark:text-gray-300 mt-1">Accesos de hoy</p>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border-l-4 border-cyan-500 p-6 transform hover:shadow-xl transition-all duration-300">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-sm font-semibold text-legible dark:text-gray-200 uppercase tracking-wide">Usuarios Activos</h3>
                        <div class="bg-cyan-100 dark:bg-cyan-900 rounded-full p-2">
                            <svg class="w-5 h-5 text-cyan-600 dark:text-cyan-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </div>
                    </div>
                    <p class="text-3xl font-bold text-legible-heading dark:text-white">{{ stats?.usuariosActivos || 0 }}</p>
                    <p class="text-xs text-legible dark:text-gray-300 mt-1">En el sistema</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Productos con Stock Bajo -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 border border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-bold text-legible-heading dark:text-white flex items-center gap-2">
                            <div class="bg-red-100 dark:bg-red-900 rounded-lg p-2">
                                <svg class="w-5 h-5 text-red-600 dark:text-red-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            Productos con Stock Bajo
                        </h2>
                        <span class="bg-red-500 text-white text-xs font-bold px-2 py-1 rounded-full">{{ productosStockBajo?.length || 0 }}</span>
                    </div>
                    <div class="space-y-2 max-h-64 overflow-y-auto">
                        <div v-for="producto in productosStockBajo" :key="producto.id" class="flex justify-between items-center p-3 bg-gradient-to-r from-red-50 to-orange-50 dark:from-red-900/20 dark:to-orange-900/20 rounded-lg border border-red-200 dark:border-red-800 hover:shadow-md transition-all">
                            <span class="font-medium text-legible dark:text-gray-200">{{ producto.nombre }}</span>
                            <span class="bg-red-500 text-white text-xs font-bold px-3 py-1 rounded-full">Stock: {{ producto.stock }}</span>
                        </div>
                        <p v-if="productosStockBajo.length === 0" class="text-center text-gray-500 dark:text-gray-400 py-4">✅ No hay productos con stock bajo</p>
                    </div>
                </div>

                <!-- Materiales con Stock Bajo -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 border border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-bold text-legible-heading dark:text-white flex items-center gap-2">
                            <div class="bg-orange-100 dark:bg-orange-900 rounded-lg p-2">
                                <svg class="w-5 h-5 text-orange-600 dark:text-orange-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                            </div>
                            Materiales con Stock Bajo
                        </h2>
                        <span class="bg-orange-500 text-white text-xs font-bold px-2 py-1 rounded-full">{{ materialesStockBajo?.length || 0 }}</span>
                    </div>
                    <div class="space-y-2 max-h-64 overflow-y-auto">
                        <div v-for="material in materialesStockBajo" :key="material.id" class="flex justify-between items-center p-3 bg-gradient-to-r from-orange-50 to-yellow-50 dark:from-orange-900/20 dark:to-yellow-900/20 rounded-lg border border-orange-200 dark:border-orange-800 hover:shadow-md transition-all">
                            <span class="font-medium text-legible dark:text-gray-200">{{ material.nombre }}</span>
                            <span class="bg-orange-500 text-white text-xs font-bold px-3 py-1 rounded-full">Stock: {{ material.stock_actual }}</span>
                        </div>
                        <p v-if="materialesStockBajo.length === 0" class="text-center text-gray-500 dark:text-gray-400 py-4">✅ No hay materiales con stock bajo</p>
                    </div>
                </div>

                <!-- Páginas Más Visitadas -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 border border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-bold text-legible-heading dark:text-white flex items-center gap-2">
                            <div class="bg-blue-100 dark:bg-blue-900 rounded-lg p-2">
                                <svg class="w-5 h-5 text-blue-600 dark:text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </div>
                            Páginas Más Visitadas
                        </h2>
                    </div>
                    <div class="space-y-2 max-h-64 overflow-y-auto">
                        <div v-for="(pagina, index) in paginasMasVisitadas" :key="index" class="flex justify-between items-center p-3 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-lg border border-blue-200 dark:border-blue-800 hover:shadow-md transition-all">
                            <span class="font-medium text-sm text-legible dark:text-gray-200 truncate flex-1">{{ pagina.ruta }}</span>
                            <span class="bg-blue-500 text-white text-xs font-bold px-3 py-1 rounded-full ml-2">{{ pagina.total }} visitas</span>
                        </div>
                        <p v-if="!paginasMasVisitadas || paginasMasVisitadas.length === 0" class="text-center text-gray-500 dark:text-gray-400 py-4">No hay datos disponibles</p>
                    </div>
                </div>
            </div>
        </div>
    </Layout>
</template>

<script setup>
import Layout from './Layout.vue'

defineProps({
    auth: Object,
    stats: Object,
    productosStockBajo: Array,
    materialesStockBajo: Array,
    paginasMasVisitadas: Array,
    visitasPagina: {
        type: Number,
        default: 0,
    },
})

const formatNumber = (value) => {
    const num = Number(value) || 0
    return num.toFixed(2)
}
</script>
