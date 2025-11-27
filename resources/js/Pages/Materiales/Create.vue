<template>
    <Layout :auth="auth">
        <div class="max-w-2xl">
            <h1 class="text-3xl font-bold text-gray-900 mb-6">Nuevo Material</h1>

            <form @submit.prevent="submit" class="bg-white rounded-lg shadow p-6">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nombre</label>
                        <input v-model="form.nombre" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required />
                        <div v-if="errors.nombre" class="text-red-600 text-sm mt-1">{{ errors.nombre }}</div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Descripción</label>
                        <textarea v-model="form.descripcion" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Categoría</label>
                        <select v-model="form.categoria_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            <option value="">Seleccione una categoría (opcional)</option>
                            <option v-for="categoria in categorias" :key="categoria.id" :value="categoria.id">
                                {{ categoria.nombre }}
                            </option>
                        </select>
                        <div v-if="errors.categoria_id" class="text-red-600 text-sm mt-1">{{ errors.categoria_id }}</div>
                    </div>

                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Stock Actual</label>
                            <input v-model.number="form.stock_actual" type="number" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Stock Mínimo</label>
                            <input v-model.number="form.stock_minimo" type="number" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Unidad de Medida</label>
                            <input v-model="form.unidad_medida" type="text" placeholder="Ej: m², unidad, litro" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required />
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Precio</label>
                        <input v-model.number="form.precio" type="number" step="0.01" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required />
                        <div v-if="errors.precio" class="text-red-600 text-sm mt-1">{{ errors.precio }}</div>
                    </div>
                </div>

                <div class="mt-6 flex space-x-4">
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                        Guardar
                    </button>
                    <Link :href="route('materiales.index')" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400">
                        Cancelar
                    </Link>
                </div>
            </form>
        </div>
    </Layout>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3'
import { route } from '../../ziggy.js'
import Layout from '../Layout.vue'

const props = defineProps({
    auth: Object,
    categorias: Array,
    errors: Object,
})

const form = useForm({
    nombre: '',
    descripcion: '',
    categoria_id: null,
    stock_actual: 0,
    stock_minimo: 0,
    precio: 0,
    unidad_medida: '',
    activo: true,
})

const submit = () => {
    form.post(route('materiales.store'))
}
</script>

