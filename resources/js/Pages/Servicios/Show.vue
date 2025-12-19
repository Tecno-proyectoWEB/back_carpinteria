<template>
    <AppLayout :auth="auth" :menu-items="menuItems" :page-visits="pageVisits" :visitas-pagina="visitasPagina">
        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white shadow-lg rounded-xl overflow-hidden border border-gray-200">
                    <!-- Header -->
                    <div class="px-6 py-5 bg-gradient-to-r from-indigo-600 to-blue-600 text-white flex justify-between items-center">
                        <div>
                            <h2 class="text-2xl font-bold">{{ servicio?.nombre || '' }}</h2>
                            <p class="text-sm text-indigo-100 mt-1">{{ servicio?.categoria?.nombre || 'Sin categoría' }}</p>
                        </div>
                        <div class="flex space-x-2">
                            <button
                                v-if="canEdit"
                                @click="editServicio"
                                class="px-4 py-2 bg-white text-indigo-600 rounded-md hover:bg-indigo-50 font-medium transition-colors"
                            >
                                Editar
                            </button>
                            <button
                                @click="goBack"
                                class="px-4 py-2 bg-indigo-700 text-white rounded-md hover:bg-indigo-800 font-medium transition-colors"
                            >
                                Volver
                            </button>
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="px-6 py-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Información General -->
                            <div class="bg-gray-50 rounded-lg p-5 border border-gray-200">
                                <h3 class="text-lg font-semibold mb-4 text-gray-800 flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Información General
                                </h3>
                                <div class="space-y-4">
                                    <div>
                                        <label class="text-sm font-medium text-gray-500 block mb-1">Nombre</label>
                                        <p class="text-gray-900 font-medium">{{ servicio?.nombre || 'Sin nombre' }}</p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-500 block mb-1">Descripción</label>
                                        <p class="text-gray-900">{{ servicio?.descripcion || 'Sin descripción' }}</p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-500 block mb-1">Categoría</label>
                                        <p class="mt-1">
                                            <Badge variant="info" class="text-sm px-3 py-1">{{ servicio?.categoria?.nombre || 'Sin categoría' }}</Badge>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Precio y Tiempo -->
                            <div class="bg-gray-50 rounded-lg p-5 border border-gray-200">
                                <h3 class="text-lg font-semibold mb-4 text-gray-800 flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Precio y Tiempo
                                </h3>
                                <div class="space-y-4">
                                    <div>
                                        <label class="text-sm font-medium text-gray-500 block mb-1">Precio Base</label>
                                        <p class="mt-1 text-2xl font-bold text-green-600">
                                            ${{ parseFloat(servicio?.precio_base || 0).toFixed(2) }}
                                        </p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-500 block mb-1">Tiempo Estimado</label>
                                        <p class="text-gray-900 font-medium">
                                            {{ servicio?.tiempo_estimado ? `${servicio.tiempo_estimado} horas` : 'No especificado' }}
                                        </p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-500 block mb-1">Estado</label>
                                        <p class="mt-1">
                                            <Badge :variant="servicio?.activo ? 'success' : 'error'" class="text-sm px-3 py-1">
                                                {{ servicio?.activo ? 'Activo' : 'Inactivo' }}
                                            </Badge>
                                        </p>
                                    </div>
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
import { computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Pages/Layout.vue';
import Badge from '@/Components/UI/Badge.vue';

const props = defineProps({
    auth: { type: Object, required: true },
    visitasPagina: { type: Number, default: 0 },
    servicio: Object,
    menuItems: Array,
    pageVisits: Number,
});

const page = usePage();

const canEdit = computed(() => {
    try {
        const rol = page.props.auth?.user?.rol?.nombre;
        return ['PROPIETARIO', 'CARPINTERO', 'ADMINISTRADOR'].includes(rol);
    } catch (e) {
        return false;
    }
});

const editServicio = () => {
    if (!props.servicio?.id) return;
    try {
        router.visit(`/servicios/${props.servicio.id}/edit`);
    } catch (e) {
        console.error('Error al editar servicio:', e);
    }
};

const goBack = () => {
    router.visit('/servicios');
};
</script>
