<template>
    <Layout :auth="auth">
        <div>
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-gray-900">Detalle de Usuario</h1>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nombre</label>
                        <p class="mt-1 text-sm text-gray-900">{{ usuario.nombre }} {{ usuario.apellido }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Email</label>
                        <p class="mt-1 text-sm text-gray-900">{{ usuario.email }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Teléfono</label>
                        <p class="mt-1 text-sm text-gray-900">{{ usuario.telefono || 'N/A' }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Rol</label>
                        <p class="mt-1 text-sm text-gray-900">{{ usuario.rol?.nombre || 'N/A' }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Estado</label>
                        <p class="mt-1">
                            <span :class="usuario.estado ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" class="px-2 py-1 rounded-full text-xs font-medium">
                                {{ usuario.estado ? 'Activo' : 'Inactivo' }}
                            </span>
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Disponibilidad</label>
                        <p class="mt-1">
                            <span :class="usuario.disponibilidad ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" class="px-2 py-1 rounded-full text-xs font-medium">
                                {{ usuario.disponibilidad ? 'Disponible' : 'No Disponible' }}
                            </span>
                        </p>
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <Link :href="route('usuarios.index')" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                        Volver
                    </Link>
                    <Link :href="route('usuarios.edit', usuario.id)" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
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
    usuario: Object,
})
</script>

