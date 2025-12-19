<template>
    <AppLayout :auth="auth" :visitas-pagina="visitasPagina">`n        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-bold">Detalles del Almacén</h2>
                        <div class="space-x-2">
                            <Link
                                :href="route('almacenes.edit', almacen.id)"
                                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                            >
                                Editar
                            </Link>
                            <Link
                                :href="route('almacenes.index')"
                                class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300"
                            >
                                Volver
                            </Link>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-500">ID</label>
                                <p class="mt-1 text-lg">{{ almacen.id }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Estado</label>
                                <p class="mt-1">
                                    <span :class="almacen.activo ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" class="px-2 py-1 text-sm font-semibold rounded-full">
                                        {{ almacen.activo ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </p>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500">Nombre</label>
                            <p class="mt-1 text-lg font-semibold">{{ almacen.nombre }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500">Ubicación</label>
                            <p class="mt-1 text-lg">{{ almacen.ubicacion }}</p>
                        </div>

                        <div v-if="almacen.descripcion">
                            <label class="block text-sm font-medium text-gray-500">Descripción</label>
                            <p class="mt-1">{{ almacen.descripcion }}</p>
                        </div>

                        <div v-if="almacen.capacidad">
                            <label class="block text-sm font-medium text-gray-500">Capacidad</label>
                            <p class="mt-1 text-lg">{{ almacen.capacidad }} m²</p>
                        </div>

                        <div class="grid grid-cols-2 gap-4 pt-4 border-t">
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Creado</label>
                                <p class="mt-1">{{ formatDate(almacen.created_at) }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Actualizado</label>
                                <p class="mt-1">{{ formatDate(almacen.updated_at) }}</p>
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
import AppLayout from '@/Pages/Layout.vue';

defineProps({
        auth: { type: Object, required: true },
    visitasPagina: { type: Number, default: 0 },
almacen: Object
});

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleString('es-BO');
};
</script>
