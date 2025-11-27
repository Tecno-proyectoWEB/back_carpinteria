<template>
    <AppLayout>
        <div class="max-w-7xl mx-auto">
            <div class="max-w-4xl mx-auto">
                <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex justify-between items-center">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900">{{ proveedor?.nombre || 'Proveedor' }}</h2>
                            <p v-if="proveedor?.id" class="text-sm text-gray-500 mt-1">ID: {{ proveedor.id }}</p>
                        </div>
                        <div class="flex space-x-2">
                            <Link
                                v-if="canEdit && proveedor?.id"
                                :href="getRoute('proveedores.edit', proveedor.id)"
                                class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700"
                            >
                                Editar
                            </Link>
                            <Link
                                :href="getRoute('proveedores.index')"
                                class="px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50"
                            >
                                Volver
                            </Link>
                        </div>
                    </div>

                    <div class="px-6 py-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Información General -->
                            <div>
                                <h3 class="text-lg font-semibold mb-4">Información General</h3>
                                <div class="space-y-3">
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Nombre</label>
                                        <p class="text-gray-900">{{ proveedor.nombre }}</p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">RUC</label>
                                        <p class="text-gray-900">{{ proveedor.ruc || 'No especificado' }}</p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Dirección</label>
                                        <p class="text-gray-900">{{ proveedor.direccion || 'No especificada' }}</p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Estado</label>
                                        <p class="mt-1">
                                            <Badge :variant="proveedor.activo ? 'success' : 'error'">
                                                {{ proveedor.activo ? 'Activo' : 'Inactivo' }}
                                            </Badge>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Información de Contacto -->
                            <div>
                                <h3 class="text-lg font-semibold mb-4">Información de Contacto</h3>
                                <div class="space-y-3">
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Teléfono</label>
                                        <p class="text-gray-900">{{ proveedor.telefono || 'No especificado' }}</p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Email</label>
                                        <p class="text-gray-900">{{ proveedor.email || 'No especificado' }}</p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Persona de Contacto</label>
                                        <p class="text-gray-900">{{ proveedor.persona_contacto || 'No especificada' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Compras del Proveedor -->
                        <div class="mt-6">
                            <h3 class="text-lg font-semibold mb-4">Compras Realizadas</h3>
                            <div v-if="proveedor.compras && proveedor.compras.length > 0" class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr v-for="compra in proveedor.compras" :key="compra.id">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                #{{ compra.id }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ formatDate(compra.fecha) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <Badge :variant="compra.estado === 'COMPLETADA' ? 'success' : 'warning'">
                                                    {{ compra.estado }}
                                                </Badge>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                ${{ formatCurrency(compra.importe_total) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <Link
                                                    v-if="compra?.id"
                                                    :href="getRoute('compras.show', compra.id)"
                                                    class="text-indigo-600 hover:text-indigo-900"
                                                >
                                                    Ver Detalle
                                                </Link>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div v-else class="text-center py-8 text-gray-500">
                                No hay compras registradas para este proveedor
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
import { Link, usePage } from '@inertiajs/vue3';
import { getRoute } from '@/utils/routeHelper';
import AppLayout from '@/Layouts/AppLayout.vue';
import Badge from '@/Components/UI/Badge.vue';

const page = usePage();

const props = defineProps({
    proveedor: {
        type: Object,
        default: () => ({}),
    },
    },
    },
});

const canEdit = computed(() => {
    const rol = page.props.auth?.user?.rol?.nombre;
    return rol && ['PROPIETARIO', 'SECRETARIA'].includes(rol);
});

const formatDate = (date) => {
    if (!date) return '';
    return new Date(date).toLocaleDateString('es-ES');
};

const formatCurrency = (value) => {
    return Number(value || 0).toFixed(2);
};
</script>

