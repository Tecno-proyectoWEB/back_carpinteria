<template>
    <AppLayout :menu-items="menuItems" :page-visits="pageVisits">
        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex justify-between items-center">
                        <h2 class="text-2xl font-bold text-gray-900">{{ servicio.nombre }}</h2>
                        <div class="flex space-x-2">
                            <Link
                                v-if="canEdit"
                                :href="route('servicios.edit', servicio.id)"
                                class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700"
                            >
                                Editar
                            </Link>
                            <Link
                                :href="route('servicios.index')"
                                class="px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50"
                            >
                                Volver
                            </Link>
                        </div>
                    </div>

                    <div class="px-6 py-4">
                        <div class="space-y-4">
                            <div>
                                <label class="text-sm font-medium text-gray-500">Descripción</label>
                                <p class="mt-1 text-gray-900">{{ servicio.descripcion || 'Sin descripción' }}</p>
                            </div>

                            <div>
                                <label class="text-sm font-medium text-gray-500">Categoría</label>
                                <p class="mt-1">
                                    <Badge variant="info">{{ servicio.categoria?.nombre || 'Sin categoría' }}</Badge>
                                </p>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="text-sm font-medium text-gray-500">Precio Base</label>
                                    <p class="mt-1 text-2xl font-bold text-green-600">
                                        ${{ parseFloat(servicio.precio_base).toFixed(2) }}
                                    </p>
                                </div>

                                <div>
                                    <label class="text-sm font-medium text-gray-500">Tiempo Estimado</label>
                                    <p class="mt-1 text-lg font-semibold text-gray-900">
                                        {{ servicio.tiempo_estimado ? `${servicio.tiempo_estimado} horas` : 'No especificado' }}
                                    </p>
                                </div>
                            </div>

                            <div>
                                <label class="text-sm font-medium text-gray-500">Estado</label>
                                <p class="mt-1">
                                    <Badge :variant="servicio.activo ? 'success' : 'error'">
                                        {{ servicio.activo ? 'Activo' : 'Inactivo' }}
                                    </Badge>
                                </p>
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
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Badge from '@/Components/UI/Badge.vue';

const props = defineProps({
    servicio: Object,
    menuItems: Array,
    pageVisits: Number,
});

const canEdit = computed(() => {
    const rol = window.$page?.props?.auth?.user?.rol?.nombre;
    return ['PROPIETARIO', 'CARPINTERO'].includes(rol);
});
</script>

