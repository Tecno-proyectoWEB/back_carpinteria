<template>
    <Layout :auth="auth">
        <div>
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-gray-900">Detalle de Material</h1>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nombre</label>
                        <p class="mt-1 text-sm text-gray-900">{{ material.nombre }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Categoría</label>
                        <p class="mt-1 text-sm text-gray-900">{{ material.categoria?.nombre || 'Sin categoría' }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Descripción</label>
                        <p class="mt-1 text-sm text-gray-900">{{ material.descripcion || 'N/A' }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Unidad de Medida</label>
                        <p class="mt-1 text-sm text-gray-900">{{ material.unidad_medida || 'N/A' }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Stock Actual</label>
                        <p class="mt-1 text-sm text-gray-900" :class="material.stock_actual <= material.stock_minimo ? 'text-red-600 font-bold' : ''">
                            {{ material.stock_actual }}
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Stock Mínimo</label>
                        <p class="mt-1 text-sm text-gray-900">{{ material.stock_minimo }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Punto de Reorden</label>
                        <p class="mt-1 text-sm text-gray-900">{{ material.punto_reorden || 'N/A' }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Precio</label>
                        <p class="mt-1 text-sm text-gray-900">${{ material.precio?.toFixed(2) }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Estado</label>
                        <p class="mt-1">
                            <span :class="material.activo ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" class="px-2 py-1 rounded-full text-xs font-medium">
                                {{ material.activo ? 'Activo' : 'Inactivo' }}
                            </span>
                        </p>
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <Link :href="route('materiales.index')" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                        Volver
                    </Link>
                    <Link :href="route('materiales.edit', material.id)" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        Editar
                    </Link>
                </div>
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
    material: Object,
})
</script>

