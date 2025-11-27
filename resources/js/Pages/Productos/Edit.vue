<template>
    <Layout :auth="auth">
        <div class="max-w-2xl">
            <h1 class="text-3xl font-bold text-gray-900 mb-6">Editar Producto</h1>

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
                        <select v-model="form.categoria_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                            <option value="">Seleccione una categoría</option>
                            <option v-for="categoria in categorias" :key="categoria.id" :value="categoria.id">
                                {{ categoria.nombre }}
                            </option>
                        </select>
                        <div v-if="errors.categoria_id" class="text-red-600 text-sm mt-1">{{ errors.categoria_id }}</div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Stock</label>
                            <input v-model.number="form.stock" type="number" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required />
                            <div v-if="errors.stock" class="text-red-600 text-sm mt-1">{{ errors.stock }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Stock Mínimo</label>
                            <input v-model.number="form.stock_minimo" type="number" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required />
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Precio Unitario</label>
                        <input v-model.number="form.precio_unitario" type="number" step="0.01" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required />
                        <div v-if="errors.precio_unitario" class="text-red-600 text-sm mt-1">{{ errors.precio_unitario }}</div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tiempo de Fabricación</label>
                        <input v-model="form.tiempo" type="text" placeholder="Ej: 5 días" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" />
                    </div>
                </div>

                <div class="mt-6 flex space-x-4">
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                        Actualizar
                    </button>
                    <Link :href="route('productos.index')" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400">
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
    producto: Object,
    categorias: Array,
    errors: Object,
})

const form = useForm({
    nombre: props.producto.nombre,
    descripcion: props.producto.descripcion || '',
    categoria_id: props.producto.categoria_id,
    stock: props.producto.stock,
    stock_minimo: props.producto.stock_minimo,
    precio_unitario: props.producto.precio_unitario,
    tiempo: props.producto.tiempo || '',
})

const submit = () => {
    form.put(route('productos.update', props.producto.id))
}
</script>

