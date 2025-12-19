<template>
    <AppLayout :auth="auth" :menu-items="menuItems" :page-visits="pageVisits" :visitas-pagina="visitasPagina">
        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex justify-between items-center">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900">Detalle del Pago</h2>
                            <p class="text-sm text-gray-500 mt-1">ID: {{ pago.id }}</p>
                        </div>
                        <Link
                            :href="route('pagos.index')"
                            class="px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50"
                        >
                            Volver
                        </Link>
                    </div>

                    <div class="px-6 py-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Informaci├│n del Pago -->
                            <div>
                                <h3 class="text-lg font-semibold mb-4">Informaci├│n del Pago</h3>
                                <div class="space-y-3">
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Monto</label>
                                        <p class="text-gray-900 font-semibold text-xl">${{ formatCurrency(pago.monto) }}</p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Estado</label>
                                        <p class="mt-1">
                                            <Badge :variant="getEstadoVariant(pago.estado)">
                                                {{ pago.estado }}
                                            </Badge>
                                        </p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Tipo</label>
                                        <p class="text-gray-900">{{ pago.tipo }}</p>
                                    </div>
                                    <div v-if="pago.numero_cuota">
                                        <label class="text-sm font-medium text-gray-500">N├║mero de Cuota</label>
                                        <p class="text-gray-900">{{ pago.numero_cuota }}</p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">M├®todo de Pago</label>
                                        <p class="text-gray-900">{{ pago.metodo_pago?.nombre || 'N/A' }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Fechas -->
                            <div>
                                <h3 class="text-lg font-semibold mb-4">Fechas</h3>
                                <div class="space-y-3">
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Fecha de Pago</label>
                                        <p class="text-gray-900">{{ pago.fecha_pago ? formatDate(pago.fecha_pago) : 'No pagado' }}</p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Fecha de Vencimiento</label>
                                        <p class="text-gray-900">{{ pago.fecha_vencimiento ? formatDate(pago.fecha_vencimiento) : 'N/A' }}</p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Registrado Por</label>
                                        <p class="text-gray-900">{{ pago.usuario?.nombre }} {{ pago.usuario?.apellido }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Observaciones -->
                        <div v-if="pago.observaciones" class="mt-6">
                            <h3 class="text-lg font-semibold mb-2">Observaciones</h3>
                            <p class="text-gray-900 bg-gray-50 p-4 rounded-lg">{{ pago.observaciones }}</p>
                        </div>

                        <!-- Informaci├│n del Pedido -->
                        <div class="mt-6">
                            <h3 class="text-lg font-semibold mb-4">Pedido Relacionado</h3>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <p class="font-medium text-gray-900">
                                            <Link
                                                :href="route('pedidos.show', pago.pedido_id)"
                                                class="text-blue-600 hover:text-blue-900"
                                            >
                                                Pedido #{{ pago.pedido_id }}
                                            </Link>
                                        </p>
                                        <p class="text-sm text-gray-500 mt-1">
                                            Cliente: {{ pago.pedido?.usuario?.nombre }} {{ pago.pedido?.usuario?.apellido }}
                                        </p>
                                        <p class="text-sm text-gray-500">
                                            Total: ${{ formatCurrency(pago.pedido?.importe_total_desc) }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Bot├│n Registrar Pago -->
                        <div v-if="pago.estado === 'PENDIENTE' && canRegistrar" class="mt-6">
                            <form @submit.prevent="registrarPago">
                                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                    <h4 class="font-semibold text-blue-900 mb-3">Registrar Pago</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                        <Select
                                            v-model="form.metodo_pago_id"
                                            label="M├®todo de Pago"
                                            :options="metodosPago"
                                            option-value="id"
                                            option-label="nombre"
                                            :error="form.errors.metodo_pago_id"
                                        />
                                        <Textarea
                                            v-model="form.observaciones"
                                            label="Observaciones"
                                            :error="form.errors.observaciones"
                                        />
                                    </div>
                                    <button
                                        type="submit"
                                        :disabled="form.processing"
                                        class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 disabled:opacity-50"
                                    >
                                        <span v-if="form.processing">Registrando...</span>
                                        <span v-else>Registrar Pago</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { computed } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/Pages/Layout.vue';
import Badge from '@/Components/UI/Badge.vue';
import Select from '@/Components/Form/Select.vue';
import Textarea from '@/Components/Form/Textarea.vue';

const props = defineProps({
        auth: { type: Object, required: true },
    visitasPagina: { type: Number, default: 0 },
pago: Object,
    metodosPago: Array,
    menuItems: Array,
    pageVisits: Number,
});

const form = useForm({
    metodo_pago_id: props.pago.metodo_pago_id || '',
    observaciones: props.pago.observaciones || '',
});

const canRegistrar = computed(() => {
    const rol = window.$page?.props?.auth?.user?.rol?.nombre;
    return ['PROPIETARIO', 'SECRETARIA'].includes(rol);
});

const registrarPago = () => {
    form.post(route('pagos.registrar', props.pago.id));
};

const formatCurrency = (value) => {
    return Number(value || 0).toFixed(2);
};

const formatDate = (date) => {
    if (!date) return '';
    return new Date(date).toLocaleString('es-ES');
};

const getEstadoVariant = (estado) => {
    const variants = {
        'PAGADO': 'success',
        'PENDIENTE': 'warning',
        'VENCIDO': 'error',
        'CANCELADO': 'error',
    };
    return variants[estado] || 'info';
};
</script>

