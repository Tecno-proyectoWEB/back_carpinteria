<template>
    <Layout :auth="auth">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-6">Resultados de Búsqueda: "{{ query }}"</h1>

            <!-- Productos -->
            <div v-if="resultados.productos.length > 0" class="mb-8">
                <h2 class="text-xl font-bold mb-4">Productos ({{ resultados.productos.length }})</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div v-for="producto in resultados.productos" :key="producto.id" class="bg-white rounded-lg shadow p-4">
                        <h3 class="font-bold">{{ producto.nombre }}</h3>
                        <p class="text-sm text-gray-600">{{ producto.descripcion }}</p>
                        <Link :href="route('productos.show', producto.id)" class="text-blue-600 text-sm mt-2 inline-block">
                            Ver detalles →
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Servicios -->
            <div v-if="resultados.servicios.length > 0" class="mb-8">
                <h2 class="text-xl font-bold mb-4">Servicios ({{ resultados.servicios.length }})</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div v-for="servicio in resultados.servicios" :key="servicio.id" class="bg-white rounded-lg shadow p-4">
                        <h3 class="font-bold">{{ servicio.nombre }}</h3>
                        <p class="text-sm text-gray-600">{{ servicio.descripcion }}</p>
                        <Link :href="route('servicios.edit', servicio.id)" class="text-blue-600 text-sm mt-2 inline-block">
                            Ver detalles →
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Materiales -->
            <div v-if="resultados.materiales.length > 0" class="mb-8">
                <h2 class="text-xl font-bold mb-4">Materiales ({{ resultados.materiales.length }})</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div v-for="material in resultados.materiales" :key="material.id" class="bg-white rounded-lg shadow p-4">
                        <h3 class="font-bold">{{ material.nombre }}</h3>
                        <p class="text-sm text-gray-600">{{ material.descripcion }}</p>
                        <Link :href="route('materiales.show', material.id)" class="text-blue-600 text-sm mt-2 inline-block">
                            Ver detalles →
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Ventas -->
            <div v-if="resultados.ventas.length > 0" class="mb-8">
                <h2 class="text-xl font-bold mb-4">Ventas ({{ resultados.ventas.length }})</h2>
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cliente</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="venta in resultados.ventas" :key="venta.id">
                                <td class="px-6 py-4">{{ venta.id }}</td>
                                <td class="px-6 py-4">{{ venta.usuario?.nombre }} {{ venta.usuario?.apellido }}</td>
                                <td class="px-6 py-4">${{ venta.importe_total?.toFixed(2) }}</td>
                                <td class="px-6 py-4">
                                    <Link :href="route('ventas.show', venta.id)" class="text-blue-600">Ver</Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Usuarios -->
            <div v-if="resultados.usuarios.length > 0" class="mb-8">
                <h2 class="text-xl font-bold mb-4">Usuarios ({{ resultados.usuarios.length }})</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div v-for="usuario in resultados.usuarios" :key="usuario.id" class="bg-white rounded-lg shadow p-4">
                        <h3 class="font-bold">{{ usuario.nombre }} {{ usuario.apellido }}</h3>
                        <p class="text-sm text-gray-600">{{ usuario.email }}</p>
                        <p class="text-sm text-gray-500">{{ usuario.rol?.nombre }}</p>
                        <Link :href="route('usuarios.show', usuario.id)" class="text-blue-600 text-sm mt-2 inline-block">
                            Ver perfil →
                        </Link>
                    </div>
                </div>
            </div>

            <div v-if="Object.values(resultados).every(r => r.length === 0)" class="text-center py-12">
                <p class="text-gray-500 text-lg">No se encontraron resultados para "{{ query }}"</p>
            </div>
        </div>
    </Layout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import { route } from '../../ziggy.js'
import Layout from '../Layout.vue'

defineProps({
    auth: Object,
    query: String,
    resultados: Object,
})
</script>

