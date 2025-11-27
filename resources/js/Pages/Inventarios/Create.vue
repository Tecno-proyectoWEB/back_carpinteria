<template>
    <Layout :auth="auth">
        <div>
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-gray-900">Nuevo Movimiento de Inventario</h1>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <form @submit.prevent="submit">
                    <div class="grid grid-cols-1 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Tipo *</label>
                            <select v-model="form.tipo" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Seleccione...</option>
                                <option value="INGRESO">Ingreso</option>
                                <option value="SALIDA">Salida</option>
                            </select>
                            <div v-if="errors.tipo" class="mt-1 text-sm text-red-600">{{ errors.tipo }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Seleccione Material o Producto *</label>
                            <div class="mt-2 space-y-4">
                                <div>
                                    <label class="block text-sm text-gray-600 mb-2">Material</label>
                                    <select v-model="form.material_id" @change="form.producto_id = null" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        <option :value="null">Ninguno</option>
                                        <option v-for="material in materiales" :key="material.id" :value="material.id">{{ material.nombre }} (Stock: {{ material.stock_actual }})</option>
                                    </select>
                                </div>
                                <div class="text-center text-gray-500">o</div>
                                <div>
                                    <label class="block text-sm text-gray-600 mb-2">Producto</label>
                                    <select v-model="form.producto_id" @change="form.material_id = null" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        <option :value="null">Ninguno</option>
                                        <option v-for="producto in productos" :key="producto.id" :value="producto.id">{{ producto.nombre }} (Stock: {{ producto.stock }})</option>
                                    </select>
                                </div>
                            </div>
                            <div v-if="errors.material_id || errors.producto_id" class="mt-1 text-sm text-red-600">
                                {{ errors.material_id || errors.producto_id }}
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Cantidad *</label>
                            <input v-model="form.cantidad" type="number" min="1" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <div v-if="errors.cantidad" class="mt-1 text-sm text-red-600">{{ errors.cantidad }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Motivo</label>
                            <input v-model="form.motivo" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <div v-if="errors.motivo" class="mt-1 text-sm text-red-600">{{ errors.motivo }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Observaciones</label>
                            <textarea v-model="form.observaciones" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                            <div v-if="errors.observaciones" class="mt-1 text-sm text-red-600">{{ errors.observaciones }}</div>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <Link :href="route('inventarios.index')" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                            Cancelar
                        </Link>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                            Guardar
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
    materiales: Array,
    productos: Array,
    errors: Object,
})

const form = useForm({
    tipo: '',
    cantidad: 1,
    motivo: '',
    observaciones: '',
    material_id: null,
    producto_id: null,
    venta_id: null,
})

const submit = () => {
    // Corrección 5.3: Solo enviar material_id o producto_id si tienen valor
    const data = {
        tipo: form.tipo,
        cantidad: form.cantidad,
        motivo: form.motivo,
        observaciones: form.observaciones,
        venta_id: form.venta_id,
    }
    
    // Solo incluir material_id o producto_id si tienen valor
    if (form.material_id) {
        data.material_id = form.material_id
    }
    
    if (form.producto_id) {
        data.producto_id = form.producto_id
    }
    
    form.transform(() => data).post(route('inventarios.store'))
}
</script>

