<template>
    <Layout :auth="auth">
        <div>
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-gray-900">Editar Servicio</h1>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <form @submit.prevent="submit">
                    <div class="grid grid-cols-1 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Nombre *</label>
                            <input v-model="form.nombre" type="text" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <div v-if="errors.nombre" class="mt-1 text-sm text-red-600">{{ errors.nombre }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Descripción</label>
                            <textarea v-model="form.descripcion" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                        </div>

                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Precio Base *</label>
                                <input v-model="form.precio_base" type="number" step="0.01" min="0" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <div v-if="errors.precio_base" class="mt-1 text-sm text-red-600">{{ errors.precio_base }}</div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Tiempo Estimado (minutos)</label>
                                <input v-model="form.tiempo_estimado" type="number" min="1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Categoría</label>
                            <select v-model="form.categoria_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option :value="null">Sin categoría</option>
                                <option v-for="categoria in categorias" :key="categoria.id" :value="categoria.id">{{ categoria.nombre }}</option>
                            </select>
                        </div>

                        <div>
                            <label class="flex items-center">
                                <input v-model="form.activo" type="checkbox" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <span class="ml-2 text-sm text-gray-700">Activo</span>
                            </label>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <Link :href="route('servicios.index')" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
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
    servicio: Object,
    categorias: Array,
    errors: Object,
})

const form = useForm({
    nombre: props.servicio.nombre,
    descripcion: props.servicio.descripcion || '',
    precio_base: props.servicio.precio_base,
    tiempo_estimado: props.servicio.tiempo_estimado,
    activo: props.servicio.activo,
    categoria_id: props.servicio.categoria_id,
})

const submit = () => {
    form.put(route('servicios.update', props.servicio.id))
}
</script>

