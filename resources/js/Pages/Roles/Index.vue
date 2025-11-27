<template>
    <Layout :auth="auth">
        <div>
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-3xl font-bold text-gray-900">Roles y Permisos</h1>
                <Link :href="route('roles.create')" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                    Nuevo Rol
                </Link>
            </div>

            <div class="bg-white rounded-lg shadow overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuarios</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Permisos</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="rol in roles" :key="rol.id">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ rol.nombre }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ rol.usuarios?.length || 0 }} usuario(s)
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                <div class="flex flex-wrap gap-1">
                                    <span v-for="permiso in rol.permisos" :key="permiso.id"
                                        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ permiso.nombre }}
                                    </span>
                                    <span v-if="!rol.permisos || rol.permisos.length === 0" class="text-gray-400">
                                        Sin permisos
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                <Link :href="route('roles.edit', rol.id)" class="text-blue-600 hover:text-blue-900">Editar</Link>
                                <button @click="eliminar(rol.id)" class="text-red-600 hover:text-red-900">Eliminar</button>
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
    roles: Array,
})

const eliminar = (id) => {
    if (confirm('¿Está seguro de eliminar este rol?')) {
        router.delete(route('roles.destroy', id))
    }
}
</script>

