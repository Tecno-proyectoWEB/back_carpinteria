<template>
    <Layout :auth="auth">
        <div>
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-gray-900">Editar Usuario</h1>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <form @submit.prevent="submit">
                    <div class="grid grid-cols-1 gap-6">
                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Nombre *</label>
                                <input v-model="form.nombre" type="text" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <div v-if="errors.nombre" class="mt-1 text-sm text-red-600">{{ errors.nombre }}</div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Apellido *</label>
                                <input v-model="form.apellido" type="text" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <div v-if="errors.apellido" class="mt-1 text-sm text-red-600">{{ errors.apellido }}</div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Email *</label>
                                <input v-model="form.email" type="email" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <div v-if="errors.email" class="mt-1 text-sm text-red-600">{{ errors.email }}</div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Teléfono</label>
                                <input v-model="form.telefono" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Nueva Contraseña</label>
                                <input v-model="form.password" type="password" minlength="8" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <p class="mt-1 text-xs text-gray-500">Dejar en blanco para mantener la contraseña actual</p>
                                <div v-if="errors.password" class="mt-1 text-sm text-red-600">{{ errors.password }}</div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Rol *</label>
                                <select v-model="form.rol_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">Seleccione un rol</option>
                                    <option v-for="rol in roles" :key="rol.id" :value="rol.id">{{ rol.nombre }}</option>
                                </select>
                                <div v-if="errors.rol_id" class="mt-1 text-sm text-red-600">{{ errors.rol_id }}</div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <label class="flex items-center">
                                    <input v-model="form.estado" type="checkbox" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <span class="ml-2 text-sm text-gray-700">Activo</span>
                                </label>
                            </div>

                            <div>
                                <label class="flex items-center">
                                    <input v-model="form.disponibilidad" type="checkbox" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <span class="ml-2 text-sm text-gray-700">Disponible</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <Link :href="route('usuarios.index')" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                            Cancelar
                        </Link>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                            Actualizar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </Layout>
</template>

<script setup>
import { useForm, Link } from '@inertiajs/vue3'
import { route } from '../../ziggy.js'
import Layout from '../Layout.vue'

const props = defineProps({
    auth: Object,
    usuario: Object,
    roles: Array,
    errors: Object,
})

const form = useForm({
    nombre: props.usuario.nombre,
    apellido: props.usuario.apellido,
    email: props.usuario.email,
    telefono: props.usuario.telefono || '',
    password: '',
    rol_id: props.usuario.rol_id,
    estado: props.usuario.estado,
    disponibilidad: props.usuario.disponibilidad,
    cuenta_no_expirada: props.usuario.cuenta_no_expirada,
    cuenta_no_bloqueada: props.usuario.cuenta_no_bloqueada,
    credenciales_no_expiradas: props.usuario.credenciales_no_expiradas,
})

const submit = () => {
    // Si no hay password, no enviarlo
    if (!form.password) {
        form.transform((data) => {
            const { password, ...rest } = data
            return rest
        }).put(route('usuarios.update', props.usuario.id))
    } else {
        form.put(route('usuarios.update', props.usuario.id))
    }
}
</script>

