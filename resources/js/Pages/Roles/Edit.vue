<template>
    <Layout :auth="auth">
        <div>
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-gray-900">Editar Rol</h1>
                <div v-if="!rol" class="mt-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                    <p>Error: No se encontraron datos del rol. Por favor, vuelve a la lista de roles.</p>
                    <Link :href="route('roles.index')" class="mt-2 inline-block text-blue-600 hover:underline">
                        Volver a Roles
                    </Link>
                </div>
            </div>

            <div v-if="rol" class="bg-white rounded-lg shadow p-6">
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
            <div v-else class="bg-white rounded-lg shadow p-6">
                <p class="text-gray-600">Cargando datos del rol...</p>
            </div>
        </div>
    </Layout>
</template>

<script setup>
import { useForm, Link } from '@inertiajs/vue3'
import { computed, watchEffect, onMounted } from 'vue'
import { route } from '../../ziggy.js'
import Layout from '../Layout.vue'

const props = defineProps({
    auth: Object,
    rol: Object,
    permisos: Array,
    errors: Object,
})

// Extraer los IDs de los permisos del rol de forma segura
const permisosSeleccionados = computed(() => {
    if (!props.rol || !props.rol.permisos) {
        return []
    }

    // Asegurarse de que permisos es un array
    const permisosArray = Array.isArray(props.rol.permisos)
        ? props.rol.permisos
        : []

    // Extraer los IDs
    return permisosArray.map(p => {
        // Manejar tanto objetos como IDs directos
        return typeof p === 'object' ? p.id : p
    }).filter(id => id !== null && id !== undefined)
})

// Inicializar el formulario con los datos del rol disponibles
const form = useForm({
    nombre: props.rol?.nombre || '',
    permisos: permisosSeleccionados.value,
})

// Actualizar el formulario cuando los props cambien
watchEffect(() => {
    if (props.rol) {
        form.nombre = props.rol.nombre || ''
        form.permisos = permisosSeleccionados.value
    }
})

const submit = () => {
    if (!props.rol || !props.rol.id) {
        console.error('Error: No se puede actualizar el rol porque no se encontró el ID', props.rol)
        alert('Error: No se puede actualizar el rol porque no se encontraron los datos')
        return
    }

    // Usar el ID directamente o como objeto según lo que espere la ruta
    const rolId = props.rol.id
    form.put(route('roles.update', rolId))
}
</script>

