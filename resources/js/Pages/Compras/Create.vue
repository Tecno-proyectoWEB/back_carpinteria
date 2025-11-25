<template>
    <AppLayout :menu-items="menuItems" :page-visits="pageVisits">
        <div class="py-12">
            <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white shadow-sm rounded-lg p-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">Crear Nueva Compra</h2>

                    <form @submit.prevent="submit">
                        <!-- Proveedor -->
                        <Select
                            v-model="form.proveedor_id"
                            label="Proveedor"
                            :options="proveedores"
                            option-value="id"
                            option-label="nombre"
                            required
                            :error="form.errors.proveedor_id"
                        />

                        <!-- Estado -->
                        <Select
                            v-model="form.estado"
                            label="Estado"
                            :options="estados"
                            option-value="value"
                            option-label="label"
                            required
                            :error="form.errors.estado"
                        />

                        <!-- Fecha -->
                        <DatePicker
                            v-model="form.fecha"
                            label="Fecha"
                            :error="form.errors.fecha"
                        />

                        <!-- Selector de Materiales -->
                        <MaterialSelector
                            v-model="form.detalles"
                            :materiales="materiales"
                        />
                        <div v-if="form.errors.detalles" class="text-red-600 text-sm mt-1">
                            {{ form.errors.detalles }}
                        </div>

                        <!-- Descuento -->
                        <Input
                            v-model.number="form.importe_descuento"
                            label="Descuento (opcional)"
                            type="number"
                            step="0.01"
                            :error="form.errors.importe_descuento"
                        />

                        <!-- Resumen -->
                        <div v-if="form.detalles.length > 0" class="mt-6 p-4 bg-gray-50 rounded-lg">
                            <h3 class="text-lg font-semibold mb-2">Resumen de la Compra</h3>
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-gray-700">Subtotal:</span>
                                <span class="font-semibold">${{ subtotal.toFixed(2) }}</span>
                            </div>
                            <div v-if="form.importe_descuento > 0" class="flex justify-between items-center mb-2">
                                <span class="text-gray-700">Descuento:</span>
                                <span class="font-semibold text-red-600">-${{ form.importe_descuento.toFixed(2) }}</span>
                            </div>
                            <div class="flex justify-between items-center pt-2 border-t">
                                <span class="text-gray-700 font-semibold">Total:</span>
                                <span class="text-2xl font-bold text-green-600">
                                    ${{ total.toFixed(2) }}
                                </span>
                            </div>
                        </div>

                        <div class="flex justify-end space-x-4 mt-6">
                            <Link
                                :href="route('compras.index')"
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
                                <span v-else>Registrar Compra</span>
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
import Select from '@/Components/Form/Select.vue';
import DatePicker from '@/Components/Form/DatePicker.vue';
import MaterialSelector from '@/Components/MaterialSelector.vue';

const props = defineProps({
    materiales: Array,
    proveedores: Array,
    menuItems: Array,
    pageVisits: Number,
});

const estados = [
    { value: 'PENDIENTE', label: 'Pendiente' },
    { value: 'COMPLETADA', label: 'Completada' },
    { value: 'CANCELADA', label: 'Cancelada' },
];

const today = new Date().toISOString().split('T')[0];

const form = useForm({
    fecha: today,
    estado: 'PENDIENTE',
    proveedor_id: '',
    importe_descuento: 0,
    detalles: [],
});

const subtotal = computed(() => {
    return form.detalles.reduce((sum, item) => sum + item.importe, 0);
});

const total = computed(() => {
    return subtotal.value - (form.importe_descuento || 0);
});

const submit = () => {
    form.post(route('compras.store'));
};
</script>

