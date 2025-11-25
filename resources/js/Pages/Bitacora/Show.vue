<template>
    <AppLayout :menu-items="menuItems" :page-visits="pageVisits">
        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex justify-between items-center">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900">Detalle de la Bitácora</h2>
                            <p class="text-sm text-gray-500 mt-1">ID: {{ bitacora.id }}</p>
                        </div>
                        <Link
                            :href="route('bitacora.index')"
                            class="px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50"
                        >
                            Volver
                        </Link>
                    </div>

                    <div class="px-6 py-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Información General -->
                            <div>
                                <h3 class="text-lg font-semibold mb-4">Información General</h3>
                                <div class="space-y-3">
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Fecha y Hora</label>
                                        <p class="text-gray-900">{{ formatDate(bitacora.fecha) }}</p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Usuario</label>
                                        <p class="text-gray-900">
                                            {{ bitacora.usuario?.nombre }} {{ bitacora.usuario?.apellido }}
                                        </p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Módulo</label>
                                        <p class="mt-1">
                                            <Badge variant="info">{{ bitacora.modulo }}</Badge>
                                        </p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Acción</label>
                                        <p class="text-gray-900">{{ bitacora.accion }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Detalles Técnicos -->
                            <div>
                                <h3 class="text-lg font-semibold mb-4">Detalles Técnicos</h3>
                                <div class="space-y-3">
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Tabla Afectada</label>
                                        <p class="text-gray-900">{{ bitacora.tabla_afectada || 'N/A' }}</p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Registro ID</label>
                                        <p class="text-gray-900">{{ bitacora.registro_id || 'N/A' }}</p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Dirección IP</label>
                                        <p class="text-gray-900">{{ bitacora.direccion_ip || 'N/A' }}</p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Navegador</label>
                                        <p class="text-gray-900">{{ bitacora.navegador || 'N/A' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Datos Anteriores y Nuevos -->
                        <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h3 class="text-lg font-semibold mb-4">Datos Anteriores</h3>
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <pre v-if="bitacora.datos_anteriores" class="text-sm text-gray-700 whitespace-pre-wrap">{{ JSON.stringify(bitacora.datos_anteriores, null, 2) }}</pre>
                                    <p v-else class="text-gray-400">No hay datos anteriores</p>
                                </div>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold mb-4">Datos Nuevos</h3>
                                <div class="bg-blue-50 p-4 rounded-lg">
                                    <pre v-if="bitacora.datos_nuevos" class="text-sm text-gray-700 whitespace-pre-wrap">{{ JSON.stringify(bitacora.datos_nuevos, null, 2) }}</pre>
                                    <p v-else class="text-gray-400">No hay datos nuevos</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Badge from '@/Components/UI/Badge.vue';

const props = defineProps({
    bitacora: Object,
    menuItems: Array,
    pageVisits: Number,
});

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

