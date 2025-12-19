<template>
    <AppLayout :auth="auth" :menu-items="menuItems" :page-visits="pageVisits" :visitas-pagina="visitasPagina">
        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex justify-between items-center">
                        <h2 class="text-2xl font-bold text-gray-900">{{ material?.nombre || 'Cargando...' }}</h2>
                        <div class="flex space-x-2">
                            <button
                                v-if="canEdit && material?.id"
                                @click="editMaterial"
                                class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700"
                            >
                                Editar
                            </button>
                            <button
                                @click="goBack"
                                class="px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50"
                            >
                                Volver
                            </button>
                        </div>
                    </div>

                    <div class="px-6 py-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Imagen -->
                            <div>
                                <img
                                    v-if="material?.imagen"
                                    :src="`/storage/${material.imagen}`"
                                    :alt="material?.nombre || 'Material'"
                                    class="w-full h-64 object-cover rounded-lg"
                                />
                                <div v-else class="w-full h-64 bg-gray-200 rounded-lg flex items-center justify-center">
                                    <span class="text-gray-400">Sin imagen</span>
                                </div>
                            </div>

                            <!-- Informaci├│n -->
                            <div class="space-y-4" v-if="material">
                                <div>
                                    <label class="text-sm font-medium text-gray-500">Descripci├│n</label>
                                    <p class="mt-1 text-gray-900">{{ material.descripcion || 'Sin descripci├│n' }}</p>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Categor├¡a</label>
                                        <p class="mt-1">
                                            <Badge variant="info">{{ material.categoria?.nombre || 'Sin categor├¡a' }}</Badge>
                                        </p>
                                    </div>

                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Sector</label>
                                        <p class="mt-1 text-gray-900">{{ material.sector?.nombre || 'Sin sector' }}</p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-3 gap-4">
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Stock Actual</label>
                                        <p
                                            class="mt-1 text-lg font-semibold"
                                            :class="(material.stock_actual || 0) <= (material.stock_minimo || 0) ? 'text-red-600' : 'text-gray-900'"
                                        >
                                            {{ material.stock_actual || 0 }} {{ material.unidad_medida || '' }}
                                            <span v-if="(material.stock_actual || 0) <= (material.stock_minimo || 0)" class="text-xs">ÔÜá´©Å</span>
                                        </p>
                                    </div>

                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Stock M├¡nimo</label>
                                        <p class="mt-1 text-lg font-semibold text-gray-900">
                                            {{ material.stock_minimo || 0 }} {{ material.unidad_medida || '' }}
                                        </p>
                                    </div>

                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Punto Reorden</label>
                                        <p class="mt-1 text-lg font-semibold text-gray-900">
                                            {{ material.punto_reorden || 0 }} {{ material.unidad_medida || '' }}
                                        </p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Precio</label>
                                        <p class="mt-1 text-2xl font-bold text-green-600">
                                            {{ material.precio ? `$${parseFloat(material.precio).toFixed(2)}` : 'N/A' }}
                                        </p>
                                    </div>

                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Estado</label>
                                        <p class="mt-1">
                                            <Badge :variant="material.activo ? 'success' : 'error'">
                                                {{ material.activo ? 'Activo' : 'Inactivo' }}
                                            </Badge>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="space-y-4">
                                <p class="text-red-600">ÔÜá´©Å No se pudo cargar la informaci├│n del material</p>
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
import { Link, router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Pages/Layout.vue';
import Badge from '@/Components/UI/Badge.vue';

const props = defineProps({
        auth: { type: Object, required: true },
    visitasPagina: { type: Number, default: 0 },
material: Object,
    menuItems: Array,
    pageVisits: Number,
});

const page = usePage();

const getRoute = (name, params = null) => {
    try {
        if (window.route && typeof window.route === 'function') {
            return params ? window.route(name, params) : window.route(name);
        }
    } catch (e) {
        console.warn('route function not available:', e);
    }
    // Fallback a URLs directas
    if (name === 'materiales.index') return '/materiales';
    if (name === 'materiales.edit' && params) return `/materiales/${params}/edit`;
    if (name === 'materiales.show' && params) return `/materiales/${params}`;
    return '#';
};

const canEdit = computed(() => {
    try {
        const user = page.props.auth?.user;
        const rol = user?.rol?.nombre;
        return rol === 'PROPIETARIO' || rol === 'CARPINTERO';
    } catch (e) {
        console.error('Error checking canEdit:', e);
        return false;
    }
});

const editMaterial = () => {
    if (!props.material?.id) {
        console.error('Material inv├ílido para editar:', props.material);
        return;
    }
    try {
        const routeUrl = getRoute('materiales.edit', props.material.id);
        router.visit(routeUrl);
    } catch (e) {
        console.error('Error al editar material:', e);
        router.visit(`/materiales/${props.material.id}/edit`);
    }
};

const goBack = () => {
    try {
        const routeUrl = getRoute('materiales.index');
        if (routeUrl && routeUrl !== '#') {
            router.visit(routeUrl);
        } else {
            router.visit('/materiales');
        }
    } catch (e) {
        console.error('Error al volver:', e);
        router.visit('/materiales');
    }
};
</script>
