<template>
    <Layout :auth="auth">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-8">Dashboard</h1>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-sm font-medium text-gray-500">Total Productos</h3>
                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ stats?.totalProductos || 0 }}</p>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-sm font-medium text-gray-500">Total Materiales</h3>
                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ stats?.totalMateriales || 0 }}</p>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-sm font-medium text-gray-500">Ventas Pendientes</h3>
                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ stats?.ventasPendientes || 0 }}</p>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-sm font-medium text-gray-500">Pagos Pendientes</h3>
                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ stats?.pagosPendientes || 0 }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Productos con Stock Bajo</h2>
                    <div class="space-y-2">
                        <div v-for="producto in productosStockBajo" :key="producto.id" class="flex justify-between items-center p-3 bg-red-50 rounded">
                            <span class="font-medium">{{ producto.nombre }}</span>
                            <span class="text-red-600 font-bold">Stock: {{ producto.stock }}</span>
                        </div>
                        <p v-if="productosStockBajo.length === 0" class="text-gray-500">No hay productos con stock bajo</p>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Materiales con Stock Bajo</h2>
                    <div class="space-y-2">
                        <div v-for="material in materialesStockBajo" :key="material.id" class="flex justify-between items-center p-3 bg-red-50 rounded">
                            <span class="font-medium">{{ material.nombre }}</span>
                            <span class="text-red-600 font-bold">Stock: {{ material.stock_actual }}</span>
                        </div>
                        <p v-if="materialesStockBajo.length === 0" class="text-gray-500">No hay materiales con stock bajo</p>
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
})
</script>

