<template>
    <AppLayout :menu-items="menuItems" :page-visits="pageVisits">
        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex justify-between items-center">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900">Detalle del Movimiento</h2>
                            <p class="text-sm text-gray-500 mt-1">ID: {{ movimiento?.id || 'Cargando...' }}</p>
                        </div>
                        <button
                            @click="goBack"
                            class="px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50"
                        >
                            Volver
                        </button>
                    </div>

                    <div class="px-6 py-4" v-if="movimiento && movimiento.id">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Información General -->
                            <div>
                                <h3 class="text-lg font-semibold mb-4">Información General</h3>
                                <div class="space-y-3">
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Tipo de Movimiento</label>
                                        <p class="mt-1">
                                            <Badge :variant="movimiento.tipo === 'INGRESO' ? 'success' : 'error'">
                                                {{ movimiento.tipo || 'N/A' }}
                                            </Badge>
                                        </p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Cantidad</label>
                                        <p class="text-gray-900 font-semibold">{{ movimiento.cantidad || 0 }}</p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Fecha y Hora</label>
                                        <p class="text-gray-900">{{ formatDate(movimiento.fecha) }}</p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Usuario</label>
                                        <p class="text-gray-900">
                                            {{ movimiento.usuario?.nombre || '' }} {{ movimiento.usuario?.apellido || '' }}
                                            <span v-if="!movimiento.usuario" class="text-gray-400">N/A</span>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Item Afectado -->
                            <div>
                                <h3 class="text-lg font-semibold mb-4">Item Afectado</h3>
                                <div class="space-y-3">
                                    <div v-if="movimiento.material">
                                        <label class="text-sm font-medium text-gray-500">Tipo</label>
                                        <p class="text-gray-900">Material</p>
                                    </div>
                                    <div v-else-if="movimiento.producto">
                                        <label class="text-sm font-medium text-gray-500">Tipo</label>
                                        <p class="text-gray-900">Producto</p>
                                    </div>
                                    <div v-else>
                                        <label class="text-sm font-medium text-gray-500">Tipo</label>
                                        <p class="text-gray-400">N/A</p>
                                    </div>
                                    <div v-if="movimiento.material">
                                        <label class="text-sm font-medium text-gray-500">Nombre</label>
                                        <p class="text-gray-900 font-medium">{{ movimiento.material.nombre || 'N/A' }}</p>
                                    </div>
                                    <div v-if="movimiento.producto">
                                        <label class="text-sm font-medium text-gray-500">Nombre</label>
                                        <p class="text-gray-900 font-medium">{{ movimiento.producto.nombre || 'N/A' }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Motivo y Observaciones -->
                            <div>
                                <h3 class="text-lg font-semibold mb-4">Motivo</h3>
                                <p class="text-gray-900">{{ movimiento.motivo || 'Sin motivo especificado' }}</p>
                            </div>

                            <div>
                                <h3 class="text-lg font-semibold mb-4">Observaciones</h3>
                                <p class="text-gray-900">{{ movimiento.observaciones || 'Sin observaciones' }}</p>
                            </div>

                            <!-- Origen del Movimiento -->
                            <div v-if="movimiento.compra || movimiento.pedido">
                                <h3 class="text-lg font-semibold mb-4">Origen</h3>
                                <div class="space-y-3">
                                    <div v-if="movimiento.compra && movimiento.compra.id">
                                        <label class="text-sm font-medium text-gray-500">Compra</label>
                                        <p class="text-gray-900">
                                            <Link
                                                :href="getRoute('compras.show', movimiento.compra.id)"
                                                class="text-blue-600 hover:text-blue-900"
                                            >
                                                Compra #{{ movimiento.compra.id }}
                                            </Link>
                                        </p>
                                    </div>
                                    <div v-if="movimiento.pedido && movimiento.pedido.id">
                                        <label class="text-sm font-medium text-gray-500">Pedido</label>
                                        <p class="text-gray-900">
                                            <Link
                                                :href="getRoute('pedidos.show', movimiento.pedido.id)"
                                                class="text-blue-600 hover:text-blue-900"
                                            >
                                                Pedido #{{ movimiento.pedido.id }}
                                            </Link>
                                        </p>
                                    </div>
                                    <div v-if="!movimiento.compra && !movimiento.pedido">
                                        <p class="text-gray-500">Movimiento manual</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else class="px-6 py-4">
                        <p class="text-red-600">⚠️ No se pudo cargar la información del movimiento</p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Badge from '@/Components/UI/Badge.vue';

const props = defineProps({
    movimiento: {
        type: Object,
        default: () => ({}),
    },
    menuItems: {
        type: Array,
        default: () => [],
    },
    pageVisits: {
        type: Number,
        default: 0,
    },
});

const getRoute = (name, params = null) => {
    if (window.route && typeof window.route === 'function') {
        try {
            return params ? window.route(name, params) : window.route(name);
        } catch (e) {
            console.error(`Error al generar ruta '${name}' con params:`, params, e);
            if (name === 'inventario.index') return '/inventario';
            if (name === 'compras.show' && params) return `/compras/${params}`;
            if (name === 'pedidos.show' && params) return `/pedidos/${params}`;
            return '#';
        }
    }
    console.warn('route function not available');
    if (name === 'inventario.index') return '/inventario';
    if (name === 'compras.show' && params) return `/compras/${params}`;
    if (name === 'pedidos.show' && params) return `/pedidos/${params}`;
    return '#';
};

const goBack = () => {
    try {
        const routeUrl = getRoute('inventario.index');
        router.visit(routeUrl);
    } catch (e) {
        console.error('Error al volver:', e);
        router.visit('/inventario');
    }
};

const formatDate = (date) => {
    if (!date) return '';
    return new Date(date).toLocaleString('es-ES', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
    });
};
</script>

