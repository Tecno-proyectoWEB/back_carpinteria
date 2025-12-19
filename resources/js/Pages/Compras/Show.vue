<template>
    <AppLayout :auth="auth" :menu-items="menuItems" :page-visits="pageVisits" :visitas-pagina="visitasPagina">
        <div class="py-12">
            <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex justify-between items-center">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900">Compra #{{ compra.id }}</h2>
                            <p class="text-sm text-gray-500 mt-1">
                                {{ new Date(compra.fecha).toLocaleDateString('es-AR') }}
                            </p>
                        </div>
                        <div class="flex items-center space-x-2">
                            <Badge
                                :variant="compra.estado === 'COMPLETADA' ? 'success' : compra.estado === 'CANCELADA' ? 'error' : 'warning'"
                            >
                                {{ compra.estado }}
                            </Badge>
                            <button
                                v-if="compra.estado === 'PENDIENTE' && canConfirm"
                                @click="confirmar"
                                class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700"
                            >
                                Confirmar Compra
                            </button>
                            <Link
                                :href="route('compras.index')"
                                class="px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50"
                            >
                                Volver
                            </Link>
                        </div>
                    </div>

                    <div class="px-6 py-4">
                        <!-- Informaci├│n del Proveedor -->
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold mb-4">Informaci├│n del Proveedor</h3>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="text-sm font-medium text-gray-500">Proveedor</label>
                                    <p class="text-gray-900">{{ compra.proveedor?.nombre }}</p>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-gray-500">Registrado por</label>
                                    <p class="text-gray-900">{{ compra.usuario?.nombre }} {{ compra.usuario?.apellido }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Detalles de la Compra -->
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold mb-4">Materiales Comprados</h3>
                            <div class="border rounded-lg overflow-hidden">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500">Material</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500">Cantidad</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500">Precio Unit.</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500">Total</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500">Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr v-for="detalle in compra.detalles" :key="detalle.id">
                                            <td class="px-4 py-3 text-sm">{{ detalle.material?.nombre }}</td>
                                            <td class="px-4 py-3 text-sm">{{ detalle.cantidad }} {{ detalle.material?.unidad_medida || '' }}</td>
                                            <td class="px-4 py-3 text-sm">${{ parseFloat(detalle.precio).toFixed(2) }}</td>
                                            <td class="px-4 py-3 text-sm font-semibold">
                                                ${{ parseFloat(detalle.importe_desc).toFixed(2) }}
                                            </td>
                                            <td class="px-4 py-3 text-sm">
                                                <Badge :variant="detalle.estado === 'RECIBIDO' ? 'success' : 'warning'">
                                                    {{ detalle.estado }}
                                                </Badge>
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tfoot class="bg-gray-50">
                                        <tr>
                                            <td colspan="3" class="px-4 py-3 text-right font-semibold">Subtotal:</td>
                                            <td class="px-4 py-3 font-semibold">
                                                ${{ subtotal.toFixed(2) }}
                                            </td>
                                            <td></td>
                                        </tr>
                                        <tr v-if="compra.importe_descuento > 0">
                                            <td colspan="3" class="px-4 py-3 text-right font-semibold">Descuento:</td>
                                            <td class="px-4 py-3 text-red-600 font-semibold">
                                                -${{ parseFloat(compra.importe_descuento).toFixed(2) }}
                                            </td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td colspan="3" class="px-4 py-3 text-right font-semibold">Total:</td>
                                            <td class="px-4 py-3 text-xl font-bold text-green-600">
                                                ${{ parseFloat(compra.importe_total).toFixed(2) }}
                                            </td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        <!-- Movimientos de Inventario -->
                        <div v-if="compra.movimientos_inventario && compra.movimientos_inventario.length > 0">
                            <h3 class="text-lg font-semibold mb-4">Movimientos de Inventario</h3>
                            <div class="border rounded-lg overflow-hidden">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500">Material</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500">Cantidad</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500">Tipo</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500">Fecha</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr v-for="movimiento in compra.movimientos_inventario" :key="movimiento.id">
                                            <td class="px-4 py-3 text-sm">{{ movimiento.material?.nombre }}</td>
                                            <td class="px-4 py-3 text-sm">{{ movimiento.cantidad }}</td>
                                            <td class="px-4 py-3 text-sm">
                                                <Badge variant="success">{{ movimiento.tipo }}</Badge>
                                            </td>
                                            <td class="px-4 py-3 text-sm">
                                                {{ new Date(movimiento.fecha).toLocaleDateString('es-AR') }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Pages/Layout.vue';
import Badge from '@/Components/UI/Badge.vue';

const props = defineProps({
        auth: { type: Object, required: true },
    visitasPagina: { type: Number, default: 0 },
compra: Object,
    menuItems: Array,
    pageVisits: Number,
});

const subtotal = computed(() => {
    return props.compra.detalles?.reduce((sum, detalle) => sum + parseFloat(detalle.importe_desc || detalle.importe), 0) || 0;
});

const canConfirm = computed(() => {
    const rol = window.$page?.props?.auth?.user?.rol?.nombre;
    return ['PROPIETARIO', 'SECRETARIA'].includes(rol);
});

const confirmar = () => {
    if (confirm('┬┐Est├í seguro de confirmar esta compra? Esto actualizar├í el stock de los materiales.')) {
        router.post(route('compras.confirmar', props.compra.id), {}, {
            preserveScroll: true,
        });
    }
};
</script>

