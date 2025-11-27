<template>
    <Layout :auth="auth">
        <div>
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-gray-900">Nuevo Pago</h1>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <form @submit.prevent="submit">
                    <div class="grid grid-cols-1 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Pedido *</label>
                            <select v-model="form.pedido_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Seleccione un pedido</option>
                                <option v-for="pedido in pedidos" :key="pedido.id" :value="pedido.id">
                                    Pedido #{{ pedido.id }} - {{ pedido.usuario?.nombre }} {{ pedido.usuario?.apellido }} - ${{ pedido.importe_total?.toFixed(2) }}
                                </option>
                            </select>
                            <div v-if="errors.pedido_id" class="mt-1 text-sm text-red-600">{{ errors.pedido_id }}</div>
                        </div>

                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Monto *</label>
                                <input v-model="form.monto" type="number" step="0.01" min="0" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <div v-if="errors.monto" class="mt-1 text-sm text-red-600">{{ errors.monto }}</div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Método de Pago *</label>
                                <select v-model="form.metodo_pago_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">Seleccione...</option>
                                    <option v-for="metodo in metodosPago" :key="metodo.id" :value="metodo.id">{{ metodo.nombre }}</option>
                                </select>
                                <div v-if="errors.metodo_pago_id" class="mt-1 text-sm text-red-600">{{ errors.metodo_pago_id }}</div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Tipo *</label>
                                <select v-model="form.tipo" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">Seleccione...</option>
                                    <option value="CONTADO">Contado</option>
                                    <option value="CREDITO">Crédito</option>
                                    <option value="CUOTA">Cuota</option>
                                </select>
                                <div v-if="errors.tipo" class="mt-1 text-sm text-red-600">{{ errors.tipo }}</div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Estado *</label>
                                <select v-model="form.estado" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">Seleccione...</option>
                                    <option value="PENDIENTE">Pendiente</option>
                                    <option value="PAGADO">Pagado</option>
                                    <option value="VENCIDO">Vencido</option>
                                    <option value="CANCELADO">Cancelado</option>
                                </select>
                                <div v-if="errors.estado" class="mt-1 text-sm text-red-600">{{ errors.estado }}</div>
                            </div>
                        </div>

                        <div v-if="form.tipo === 'CUOTA'" class="grid grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Número de Cuota</label>
                                <input v-model="form.numero_cuota" type="number" min="1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Fecha de Vencimiento</label>
                                <input v-model="form.fecha_vencimiento" type="date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Observaciones</label>
                            <textarea v-model="form.observaciones" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <Link :href="route('pagos.index')" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
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
    pedidos: Array,
    metodosPago: Array,
    pedido_id: Number,
    errors: Object,
})

const form = useForm({
    pedido_id: props.pedido_id || null,
    monto: 0,
    metodo_pago_id: null,
    tipo: 'CONTADO',
    estado: 'PENDIENTE',
    numero_cuota: null,
    fecha_pago: null,
    fecha_vencimiento: null,
    observaciones: '',
})

const submit = () => {
    form.post(route('pagos.store'))
}
</script>

