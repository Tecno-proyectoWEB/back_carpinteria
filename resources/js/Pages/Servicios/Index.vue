<template>
    <Layout :auth="auth">
        <div>
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-3xl font-bold text-gray-900">Servicios</h1>
                <Link :href="route('servicios.create')" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                    Nuevo Servicio
                </Link>
            </div>

            <div class="bg-white rounded-lg shadow overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Categoría</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Precio Base</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tiempo Estimado</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="servicio in servicios" :key="servicio.id">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ servicio.nombre }}</div>
                                <div class="text-sm text-gray-500">{{ servicio.descripcion }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ servicio.categoria?.nombre || 'Sin categoría' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                ${{ servicio.precio_base?.toFixed(2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ servicio.tiempo_estimado ? `${servicio.tiempo_estimado} min` : 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span :class="servicio.activo ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" class="px-2 py-1 text-xs font-semibold rounded-full">
                                    {{ servicio.activo ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                <Link :href="route('servicios.edit', servicio.id)" class="text-blue-600 hover:text-blue-900">Editar</Link>
                                <button @click="eliminar(servicio.id)" class="text-red-600 hover:text-red-900">Eliminar</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </Layout>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3'
import { route } from '../../ziggy.js'
import Layout from '../Layout.vue'

defineProps({
    auth: Object,
    servicios: Array,
})

const eliminar = (id) => {
    if (confirm('¿Está seguro de eliminar este servicio?')) {
        router.delete(route('servicios.destroy', id))
    }
}
</script>

