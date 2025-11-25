<template>
    <AppLayout :menu-items="menuItems" :page-visits="pageVisits">
        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white shadow-sm rounded-lg p-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">Cupón de Pago - Pagofacil</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Información del Cupón -->
                        <div>
                            <h3 class="text-lg font-semibold mb-4">Información del Cupón</h3>
                            <div class="space-y-3">
                                <div>
                                    <label class="text-sm font-medium text-gray-500">ID del Cupón</label>
                                    <p class="text-gray-900 font-mono font-semibold">{{ cupon.id }}</p>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-gray-500">Monto</label>
                                    <p class="text-gray-900 font-semibold text-xl">${{ formatCurrency(cupon.monto) }}</p>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-gray-500">Vencimiento</label>
                                    <p class="text-gray-900">{{ formatDate(cupon.vencimiento) }}</p>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-gray-500">Código de Barras</label>
                                    <p class="text-gray-900 font-mono text-sm">{{ cupon.codigo_barras }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- QR Code -->
                        <div class="text-center">
                            <h3 class="text-lg font-semibold mb-4">Código QR</h3>
                            <div class="bg-white p-4 border-2 border-gray-200 rounded-lg inline-block">
                                <img :src="cupon.qr_code" alt="QR Code" class="w-48 h-48" />
                            </div>
                        </div>
                    </div>

                    <!-- Instrucciones -->
                    <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <h4 class="font-semibold text-blue-900 mb-2">Instrucciones de Pago</h4>
                        <p class="text-blue-800 text-sm">{{ cupon.instrucciones }}</p>
                    </div>

                    <!-- Información del Pedido -->
                    <div class="mt-6">
                        <h3 class="text-lg font-semibold mb-4">Pedido Relacionado</h3>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <p class="font-medium text-gray-900">
                                Pedido #{{ pedido.id }} - Cliente: {{ pedido.usuario?.nombre }} {{ pedido.usuario?.apellido }}
                            </p>
                            <p class="text-sm text-gray-500 mt-1">
                                Total: ${{ formatCurrency(pedido.importe_total_desc) }}
                            </p>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <Link
                            :href="route('pedidos.show', pedido.id)"
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                        >
                            Volver al Pedido
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    pedido: Object,
    cupon: Object,
    menuItems: Array,
    pageVisits: Number,
});

const formatCurrency = (value) => {
    return Number(value || 0).toFixed(2);
};

const formatDate = (date) => {
    if (!date) return '';
    return new Date(date).toLocaleDateString('es-ES');
};
</script>

