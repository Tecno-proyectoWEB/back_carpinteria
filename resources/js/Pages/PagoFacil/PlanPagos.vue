<template>
    <AppLayout :menu-items="menuItems" :page-visits="pageVisits">
        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white shadow-sm rounded-lg p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-bold text-gray-900">Crear Plan de Pagos</h2>
                        <Link
                            :href="route('pedidos.show', pedido.id)"
                            class="px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50"
                        >
                            Volver
                        </Link>
                    </div>

                    <!-- Información del Pedido -->
                    <div class="mb-6 bg-gray-50 p-4 rounded-lg">
                        <h3 class="font-semibold text-gray-900 mb-2">Pedido #{{ pedido.id }}</h3>
                        <p class="text-sm text-gray-600">
                            Cliente: {{ pedido.usuario?.nombre }} {{ pedido.usuario?.apellido }}
                        </p>
                        <p class="text-sm text-gray-600">
                            Total: ${{ formatCurrency(pedido.importe_total_desc) }}
                        </p>
                    </div>

                    <form @submit.prevent="submit">
                        <div class="grid grid-cols-2 gap-4">
                            <Input
                                v-model.number="form.numero_cuotas"
                                label="Número de Cuotas *"
                                type="number"
                                min="2"
                                max="12"
                                required
                                :error="form.errors.numero_cuotas"
                            />

                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Fecha Primera Cuota *
                                </label>
                                <input
                                    v-model="form.fecha_primera_cuota"
                                    type="date"
                                    required
                                    :min="minDate"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md"
                                    :class="{ 'border-red-500': form.errors.fecha_primera_cuota }"
                                />
                                <p v-if="form.errors.fecha_primera_cuota" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.fecha_primera_cuota }}
                                </p>
                            </div>
                        </div>

                        <!-- Resumen del Plan -->
                        <div v-if="form.numero_cuotas && form.fecha_primera_cuota" class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <h4 class="font-semibold text-blue-900 mb-3">Resumen del Plan de Pagos</h4>
                            <div class="space-y-2 text-sm">
                                <p><strong>Total a pagar:</strong> ${{ formatCurrency(pedido.importe_total_desc) }}</p>
                                <p><strong>Número de cuotas:</strong> {{ form.numero_cuotas }}</p>
                                <p><strong>Monto por cuota:</strong> ${{ formatCurrency(pedido.importe_total_desc / form.numero_cuotas) }}</p>
                                <p><strong>Primera cuota:</strong> {{ formatDate(form.fecha_primera_cuota) }}</p>
                            </div>
                        </div>

                        <div class="flex justify-end space-x-4 mt-6">
                            <Link
                                :href="route('pedidos.show', pedido.id)"
                                class="px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50"
                            >
                                Cancelar
                            </Link>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50"
                            >
                                <span v-if="form.processing">Creando Plan...</span>
                                <span v-else>Crear Plan de Pagos</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { computed } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Input from '@/Components/Form/Input.vue';

const props = defineProps({
    pedido: Object,
    menuItems: Array,
    pageVisits: Number,
});

const minDate = computed(() => {
    const tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    return tomorrow.toISOString().split('T')[0];
});

const form = useForm({
    pedido_id: props.pedido.id,
    numero_cuotas: 3,
    fecha_primera_cuota: minDate.value,
});

const submit = () => {
    form.post(route('pagofacil.crear-plan-pagos'));
};

const formatCurrency = (value) => {
    return Number(value || 0).toFixed(2);
};

const formatDate = (date) => {
    if (!date) return '';
    return new Date(date).toLocaleDateString('es-ES');
};
</script>

