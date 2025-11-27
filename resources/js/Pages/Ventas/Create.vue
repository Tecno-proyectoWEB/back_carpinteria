<template>
    <Layout :auth="auth">
        <div class="max-w-4xl">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-3xl font-bold text-primary">Nueva Venta</h1>
                <div class="flex space-x-2">
                    <button
                        @click="tipoVenta = 'contado'"
                        :class="tipoVenta === 'contado' ? 'bg-primary text-white' : 'bg-gray-200 text-gray-700'"
                        class="px-4 py-2 rounded-lg font-medium"
                    >
                        Venta al Contado
                    </button>
                    <button
                        @click="tipoVenta = 'credito'"
                        :class="tipoVenta === 'credito' ? 'bg-primary text-white' : 'bg-gray-200 text-gray-700'"
                        class="px-4 py-2 rounded-lg font-medium"
                    >
                        Venta a Crédito
                    </button>
                </div>
            </div>

            <form @submit.prevent="submit" class="bg-secondary rounded-lg shadow p-6">
                <div class="space-y-6">
                    <!-- Información básica -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-primary">Cliente *</label>
                            <select v-model="form.usuario_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                                <option value="">Seleccione un cliente</option>
                                <option v-for="cliente in clientes" :key="cliente.id" :value="cliente.id">
                                    {{ cliente.nombre }} {{ cliente.apellido }}
                                </option>
                            </select>
                            <div v-if="errors.usuario_id" class="text-red-600 text-sm mt-1">{{ errors.usuario_id }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-primary">Método de Pago *</label>
                            <select v-model="form.metodo_pago_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                                <option value="">Seleccione un método</option>
                                <option v-for="metodo in metodosPago" :key="metodo.id" :value="metodo.id">
                                    {{ metodo.nombre }}
                                </option>
                            </select>
                            <div v-if="errors.metodo_pago_id" class="text-red-600 text-sm mt-1">{{ errors.metodo_pago_id }}</div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-primary">Descripción</label>
                        <textarea v-model="form.descripcion" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></textarea>
                    </div>

                    <!-- Detalles de la venta -->
                    <div>
                        <div class="flex justify-between items-center mb-4">
                            <h2 class="text-lg font-semibold text-primary">Detalles de la Venta</h2>
                            <button type="button" @click="agregarDetalle" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
                                Agregar Item
                            </button>
                        </div>

                        <div class="space-y-4">
                            <div v-for="(detalle, index) in form.detalles" :key="index" class="border rounded-lg p-4">
                                <div class="grid grid-cols-5 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-primary">Tipo</label>
                                        <select v-model="detalle.tipo" @change="cambiarTipoDetalle(index)" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                            <option value="producto">Producto</option>
                                            <option value="servicio">Servicio</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-primary">
                                            {{ detalle.tipo === 'producto' ? 'Producto' : 'Servicio' }} *
                                        </label>
                                        <select
                                            v-model="detalle[detalle.tipo + '_id']"
                                            @change="actualizarPrecio(index)"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                            required
                                        >
                                            <option value="">Seleccione...</option>
                                            <option
                                                v-if="detalle.tipo === 'producto'"
                                                v-for="producto in productos"
                                                :key="producto.id"
                                                :value="producto.id"
                                            >
                                                {{ producto.nombre }} (Stock: {{ producto.stock }})
                                            </option>
                                            <option
                                                v-if="detalle.tipo === 'servicio'"
                                                v-for="servicio in servicios"
                                                :key="servicio.id"
                                                :value="servicio.id"
                                            >
                                                {{ servicio.nombre }}
                                            </option>
                                        </select>
                                        <div v-if="errors[`detalles.${index}.producto_id`]" class="text-red-600 text-sm mt-1">
                                            {{ errors[`detalles.${index}.producto_id`] }}
                                        </div>
                                        <div v-if="errors[`detalles.${index}.servicio_id`]" class="text-red-600 text-sm mt-1">
                                            {{ errors[`detalles.${index}.servicio_id`] }}
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-primary">Cantidad *</label>
                                        <input
                                            v-model.number="detalle.cantidad"
                                            @input="calcularTotal(index)"
                                            type="number"
                                            min="1"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                            required
                                        />
                                        <div v-if="errors[`detalles.${index}.cantidad`]" class="text-red-600 text-sm mt-1">
                                            {{ errors[`detalles.${index}.cantidad`] }}
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-primary">Precio Unitario *</label>
                                        <input
                                            v-model.number="detalle.precio_unitario"
                                            @input="calcularTotal(index)"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                            required
                                        />
                                        <div v-if="errors[`detalles.${index}.precio_unitario`]" class="text-red-600 text-sm mt-1">
                                            {{ errors[`detalles.${index}.precio_unitario`] }}
                                        </div>
                                    </div>

                                    <div class="flex items-end">
                                        <button
                                            type="button"
                                            @click="eliminarDetalle(index)"
                                            class="text-red-600 hover:text-red-900"
                                        >
                                            Eliminar
                                        </button>
                                    </div>
                                </div>

                                <div class="mt-2 text-sm text-secondary">
                                    Total: ${{ (detalle.cantidad * detalle.precio_unitario).toFixed(2) }}
                                </div>
                            </div>
                        </div>

                        <div v-if="form.detalles.length === 0" class="text-center py-8 text-secondary">
                            No hay items agregados. Haga clic en "Agregar Item" para comenzar.
                        </div>
                    </div>

                    <!-- Información de crédito (solo si es crédito) -->
                    <div v-if="tipoVenta === 'credito'" class="border-t pt-4">
                        <h2 class="text-lg font-semibold text-primary mb-4">Información de Crédito</h2>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-primary">Número de Cuotas *</label>
                                <input
                                    v-model.number="form.numero_cuotas"
                                    type="number"
                                    min="1"
                                    max="12"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                    required
                                />
                                <div v-if="errors.numero_cuotas" class="text-red-600 text-sm mt-1">{{ errors.numero_cuotas }}</div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-primary">Fecha Primera Cuota *</label>
                                <input
                                    v-model="form.fecha_primera_cuota"
                                    type="date"
                                    :min="new Date().toISOString().split('T')[0]"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                    required
                                />
                                <div v-if="errors.fecha_primera_cuota" class="text-red-600 text-sm mt-1">{{ errors.fecha_primera_cuota }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Resumen -->
                    <div class="border-t pt-4">
                        <div class="flex justify-between items-center">
                            <span class="text-lg font-semibold text-primary">Total:</span>
                            <span class="text-2xl font-bold text-primary">${{ total.toFixed(2) }}</span>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex space-x-4">
                    <button
                        type="submit"
                        :disabled="form.processing || form.detalles.length === 0"
                        class="bg-primary text-white px-6 py-2 rounded-lg hover:bg-secondary disabled:bg-gray-400"
                    >
                        {{ form.processing ? 'Procesando...' : 'Guardar Venta' }}
                    </button>
                    <Link :href="route('ventas.index')" class="bg-gray-300 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-400">
                        Cancelar
                    </Link>
                </div>
            </form>
        </div>
    </Layout>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import { route } from '../../ziggy.js'
import Layout from '../Layout.vue'

const props = defineProps({
    auth: Object,
    clientes: Array,
    productos: Array,
    servicios: Array,
    metodosPago: Array,
    errors: Object,
})

const tipoVenta = ref('contado')

const form = useForm({
    usuario_id: '',
    metodo_pago_id: '',
    descripcion: '',
    detalles: [],
    numero_cuotas: 1,
    fecha_primera_cuota: new Date().toISOString().split('T')[0],
})

const agregarDetalle = () => {
    form.detalles.push({
        tipo: 'producto',
        producto_id: null,
        servicio_id: null,
        cantidad: 1,
        precio_unitario: 0,
    })
}

const eliminarDetalle = (index) => {
    form.detalles.splice(index, 1)
}

const cambiarTipoDetalle = (index) => {
    const detalle = form.detalles[index]
    detalle.producto_id = null
    detalle.servicio_id = null
    detalle.precio_unitario = 0
}

const actualizarPrecio = (index) => {
    const detalle = form.detalles[index]
    if (detalle.tipo === 'producto' && detalle.producto_id) {
        const producto = props.productos.find(p => p.id === detalle.producto_id)
        if (producto) {
            detalle.precio_unitario = producto.precio_unitario
        }
    } else if (detalle.tipo === 'servicio' && detalle.servicio_id) {
        const servicio = props.servicios.find(s => s.id === detalle.servicio_id)
        if (servicio) {
            detalle.precio_unitario = servicio.precio_base
        }
    }
}

const calcularTotal = (index) => {
    const detalle = form.detalles[index]
    // El total se calcula automáticamente en el template
}

const total = computed(() => {
    return form.detalles.reduce((sum, detalle) => {
        return sum + (detalle.cantidad * detalle.precio_unitario)
    }, 0)
})

const submit = () => {
    // Corrección 5.2: Solo enviar producto_id o servicio_id si tienen valor
    const detallesFormateados = form.detalles.map(detalle => {
        const detalleFormateado = {
            cantidad: detalle.cantidad,
            precio_unitario: detalle.precio_unitario,
        }
        
        // Solo incluir producto_id o servicio_id según el tipo, no ambos
        if (detalle.tipo === 'producto' && detalle.producto_id) {
            detalleFormateado.producto_id = detalle.producto_id
        } else if (detalle.tipo === 'servicio' && detalle.servicio_id) {
            detalleFormateado.servicio_id = detalle.servicio_id
        }
        
        return detalleFormateado
    })

    if (tipoVenta.value === 'contado') {
        form.transform((data) => ({
            ...data,
            detalles: detallesFormateados,
        })).post(route('ventas.storeContado'))
    } else {
        form.transform((data) => ({
            ...data,
            detalles: detallesFormateados,
            numero_cuotas: form.numero_cuotas,
            fecha_primera_cuota: form.fecha_primera_cuota,
        })).post(route('ventas.storeCredito'))
    }
}
</script>

