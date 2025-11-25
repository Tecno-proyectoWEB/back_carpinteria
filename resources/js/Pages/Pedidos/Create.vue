<template>
    <AppLayout :menu-items="menuItems" :page-visits="pageVisits">
        <div class="py-12">
            <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white shadow-sm rounded-lg p-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">Crear Nuevo Pedido</h2>

                    <!-- Selector de tipo de venta -->
                    <div class="mb-6 p-4 bg-blue-50 rounded-lg">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de Venta</label>
                        <div class="flex space-x-4">
                            <label class="flex items-center">
                                <input
                                    v-model="tipoVenta"
                                    type="radio"
                                    value="contado"
                                    class="mr-2"
                                />
                                <span>Al Contado</span>
                            </label>
                            <label class="flex items-center">
                                <input
                                    v-model="tipoVenta"
                                    type="radio"
                                    value="credito"
                                    class="mr-2"
                                />
                                <span>A Crédito</span>
                            </label>
                        </div>
                    </div>

                    <form @submit.prevent="submit">
                        <!-- Cliente -->
                        <Select
                            v-model="form.usuario_id"
                            label="Cliente"
                            :options="clientesConNombre"
                            option-value="id"
                            option-label="nombreCompleto"
                            required
                            :error="form.errors.usuario_id"
                        />

                        <!-- Método de Pago -->
                        <Select
                            v-model="form.metodo_pago_id"
                            label="Método de Pago"
                            :options="metodosPago"
                            option-value="id"
                            option-label="nombre"
                            required
                            :error="form.errors.metodo_pago_id"
                        />

                        <!-- Fecha -->
                        <DatePicker
                            v-model="form.fecha"
                            label="Fecha"
                            :error="form.errors.fecha"
                        />

                        <!-- Descripción -->
                        <Textarea
                            v-model="form.descripcion"
                            label="Descripción (opcional)"
                            :error="form.errors.descripcion"
                            :rows="2"
                        />

                        <!-- Selector de Productos/Servicios -->
                        <ProductoSelector
                            v-model="form.detalles"
                            :productos="productos"
                            :servicios="servicios"
                        />
                        <div v-if="form.errors.detalles" class="text-red-600 text-sm mt-1">
                            {{ form.errors.detalles }}
                        </div>

                        <!-- Campos adicionales para crédito -->
                        <div v-if="tipoVenta === 'credito'" class="grid grid-cols-2 gap-4 mt-4">
                            <Input
                                v-model.number="form.numero_cuotas"
                                label="Número de Cuotas"
                                type="number"
                                :min="1"
                                :max="12"
                                required
                                :error="form.errors.numero_cuotas"
                            />

                            <DatePicker
                                v-model="form.fecha_primera_cuota"
                                label="Fecha Primera Cuota"
                                :min="today"
                                required
                                :error="form.errors.fecha_primera_cuota"
                            />
                        </div>

                        <!-- Resumen -->
                        <div v-if="form.detalles.length > 0" class="mt-6 p-4 bg-gray-50 rounded-lg">
                            <h3 class="text-lg font-semibold mb-2">Resumen del Pedido</h3>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-700">Total de Items:</span>
                                <span class="font-semibold">{{ form.detalles.length }}</span>
                            </div>
                            <div class="flex justify-between items-center mt-2">
                                <span class="text-gray-700">Total a Pagar:</span>
                                <span class="text-2xl font-bold text-green-600">
                                    ${{ total.toFixed(2) }}
                                </span>
                            </div>
                        </div>

                        <div class="flex justify-end space-x-4 mt-6">
                            <Link
                                :href="route('pedidos.index')"
                                class="px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50"
                            >
                                Cancelar
                            </Link>
                            <button
                                type="submit"
                                :disabled="form.processing || form.detalles.length === 0"
                                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50"
                            >
                                <span v-if="form.processing">Procesando...</span>
                                <span v-else>{{ tipoVenta === 'contado' ? 'Registrar Venta al Contado' : 'Registrar Venta a Crédito' }}</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Input from '@/Components/Form/Input.vue';
import Textarea from '@/Components/Form/Textarea.vue';
import Select from '@/Components/Form/Select.vue';
import DatePicker from '@/Components/Form/DatePicker.vue';
import ProductoSelector from '@/Components/ProductoSelector.vue';

const props = defineProps({
    productos: Array,
    servicios: Array,
    clientes: Array,
    metodosPago: Array,
    menuItems: Array,
    pageVisits: Number,
});

const tipoVenta = ref('contado');
const today = new Date().toISOString().split('T')[0];

// Preparar clientes con nombre completo
const clientesConNombre = computed(() => {
    return props.clientes.map(cliente => ({
        ...cliente,
        nombreCompleto: `${cliente.nombre} ${cliente.apellido}`
    }));
});

const form = useForm({
    fecha: today,
    descripcion: '',
    usuario_id: '',
    metodo_pago_id: '',
    detalles: [],
    numero_cuotas: 1,
    fecha_primera_cuota: today,
});

const total = computed(() => {
    return form.detalles.reduce((sum, item) => sum + item.importe_total, 0);
});

const submit = () => {
    if (tipoVenta.value === 'contado') {
        form.post(route('pedidos.store-contado'));
    } else {
        form.post(route('pedidos.store-credito'));
    }
};
</script>

