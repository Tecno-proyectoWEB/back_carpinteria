<template>
    <Layout :auth="auth">
        <div>
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-gray-900">Detalle de Movimiento de Inventario</h1>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tipo</label>
                        <p class="mt-1 text-sm text-gray-900">
                            <span :class="movimiento.tipo === 'INGRESO' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" class="px-2 py-1 text-xs font-semibold rounded-full">
                                {{ movimiento.tipo }}
                            </span>
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Fecha</label>
                        <p class="mt-1 text-sm text-gray-900">{{ new Date(movimiento.fecha).toLocaleString() }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Item</label>
                        <p class="mt-1 text-sm text-gray-900">{{ movimiento.material?.nombre || movimiento.producto?.nombre || 'N/A' }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Cantidad</label>
                        <p class="mt-1 text-sm text-gray-900">{{ movimiento.cantidad }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Motivo</label>
                        <p class="mt-1 text-sm text-gray-900">{{ movimiento.motivo || 'N/A' }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Usuario</label>
                        <p class="mt-1 text-sm text-gray-900">{{ movimiento.usuario?.nombre }} {{ movimiento.usuario?.apellido }}</p>
                    </div>

                    <div v-if="movimiento.observaciones" class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Observaciones</label>
                        <p class="mt-1 text-sm text-gray-900">{{ movimiento.observaciones }}</p>
                    </div>

                    <div v-if="movimiento.venta" class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Venta Relacionada</label>
                        <Link :href="route('ventas.show', movimiento.venta.id)" class="mt-1 text-sm text-blue-600 hover:text-blue-900">
                            Venta #{{ movimiento.venta.id }}
                        </Link>
                    </div>
                </div>

                <div class="mt-6">
                    <Link :href="route('inventarios.index')" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                        Volver
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
    movimiento: Object,
})
</script>

