<template>
    <Layout :auth="auth">
        <div>
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-3xl font-bold text-gray-900">Materiales</h1>
                <Link :href="route('materiales.create')" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                    Nuevo Material
                </Link>
            </div>

            <div class="bg-white rounded-lg shadow overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Categoría</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Precio</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="material in materiales" :key="material.id">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ material.nombre }}</div>
                                <div class="text-sm text-gray-500">{{ material.descripcion }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ material.categoria?.nombre || 'Sin categoría' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span :class="material.stock_actual <= material.stock_minimo ? 'text-red-600 font-bold' : 'text-gray-900'">
                                    {{ material.stock_actual }} {{ material.unidad_medida }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                ${{ material.precio?.toFixed(2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                <Link :href="route('materiales.edit', material.id)" class="text-blue-600 hover:text-blue-900">Editar</Link>
                                <button @click="eliminar(material.id)" class="text-red-600 hover:text-red-900">Eliminar</button>
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
    materiales: Array,
})

const eliminar = (id) => {
    if (confirm('¿Está seguro de eliminar este material?')) {
        router.delete(route('materiales.destroy', id))
    }
}
</script>

