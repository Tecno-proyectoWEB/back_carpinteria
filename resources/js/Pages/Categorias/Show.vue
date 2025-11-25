<template>
    <AppLayout :menu-items="menuItems" :page-visits="pageVisits">
        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex justify-between items-center">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900">{{ categoria.nombre }}</h2>
                            <p class="text-sm text-gray-500 mt-1">ID: {{ categoria.id }}</p>
                        </div>
                        <div class="flex space-x-2">
                            <Link
                                v-if="canEdit"
                                :href="route('categorias.edit', categoria.id)"
                                class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700"
                            >
                                Editar
                            </Link>
                            <Link
                                :href="route('categorias.index')"
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
                                        <p class="text-gray-900">{{ categoria.nombre }}</p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Descripción</label>
                                        <p class="text-gray-900">{{ categoria.descripcion || 'Sin descripción' }}</p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Subcategoría</label>
                                        <p class="mt-1">
                                            <Badge v-if="categoria.subcategoria" variant="info">
                                                {{ categoria.subcategoria.nombre }}
                                            </Badge>
                                            <span v-else class="text-gray-400">Sin subcategoría</span>
                                        </p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Estado</label>
                                        <p class="mt-1">
                                            <Badge :variant="categoria.activo ? 'success' : 'error'">
                                                {{ categoria.activo ? 'Activo' : 'Inactivo' }}
                                            </Badge>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Estadísticas -->
                            <div>
                                <h3 class="text-lg font-semibold mb-4">Estadísticas</h3>
                                <div class="space-y-3">
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Productos</label>
                                        <p class="text-gray-900 font-semibold">{{ categoria.productos?.length || 0 }}</p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Servicios</label>
                                        <p class="text-gray-900 font-semibold">{{ categoria.servicios?.length || 0 }}</p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Materiales</label>
                                        <p class="text-gray-900 font-semibold">{{ categoria.materiales?.length || 0 }}</p>
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
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Badge from '@/Components/UI/Badge.vue';

const props = defineProps({
    categoria: Object,
    menuItems: Array,
    pageVisits: Number,
});

const canEdit = computed(() => {
    const rol = window.$page?.props?.auth?.user?.rol?.nombre;
    return ['PROPIETARIO', 'SECRETARIA'].includes(rol);
});
</script>

