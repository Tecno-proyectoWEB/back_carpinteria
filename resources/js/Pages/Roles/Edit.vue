<template>
    <Layout :auth="auth">
        <div>
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-gray-900">Editar Rol</h1>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <form @submit.prevent="submit">
                    <div class="grid grid-cols-1 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Nombre del Rol *</label>
                            <input v-model="form.nombre" type="text" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <div v-if="errors.nombre" class="mt-1 text-sm text-red-600">{{ errors.nombre }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-3">Permisos</label>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-3 max-h-96 overflow-y-auto border border-gray-200 rounded-md p-4">
                                <label v-for="permiso in permisos" :key="permiso.id" class="flex items-center">
                                    <input
                                        type="checkbox"
                                        :value="permiso.id"
                                        v-model="form.permisos"
                                        class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <span class="ml-2 text-sm text-gray-700">{{ permiso.nombre }}</span>
                                </label>
                            </div>
                            <p class="mt-2 text-sm text-gray-500">Seleccione los permisos que tendrá este rol</p>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <Link :href="route('roles.index')" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
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
    rol: Object,
    permisos: Array,
    errors: Object,
})

const form = useForm({
    nombre: props.rol.nombre,
    permisos: props.rol.permisos?.map(p => p.id) || [],
})

const submit = () => {
    form.put(route('roles.update', props.rol.id))
}
</script>

