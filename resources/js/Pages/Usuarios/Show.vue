<template>
    <AppLayout :menu-items="menuItems" :page-visits="pageVisits">
        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white shadow-lg rounded-xl overflow-hidden border border-gray-200">
                    <!-- Header -->
                    <div class="px-6 py-5 bg-gradient-to-r from-indigo-600 to-blue-600 text-white flex justify-between items-center">
                        <div>
                            <h2 class="text-2xl font-bold">
                                {{ usuario?.nombre || '' }} {{ usuario?.apellido || '' }}
                            </h2>
                            <p class="text-sm text-indigo-100 mt-1">{{ usuario?.email || '' }}</p>
                        </div>
                        <div class="flex space-x-2">
                            <Link
                                v-if="canEdit"
                                :href="getRoute('usuarios.edit', usuario?.id)"
                                class="px-4 py-2 bg-white text-indigo-600 rounded-md hover:bg-indigo-50 font-medium transition-colors"
                            >
                                Editar
                            </Link>
                            <Link
                                :href="getRoute('usuarios.index')"
                                class="px-4 py-2 bg-indigo-700 text-white rounded-md hover:bg-indigo-800 font-medium transition-colors"
                            >
                                Volver
                            </Link>
                        </div>
                    </div>

                    <div class="px-6 py-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Información Personal -->
                            <div class="bg-gray-50 rounded-lg p-5 border border-gray-200">
                                <h3 class="text-lg font-semibold mb-4 text-gray-800 flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    Información Personal
                                </h3>
                                <div class="space-y-4">
                                    <div>
                                        <label class="text-sm font-medium text-gray-500 block mb-1">Nombre Completo</label>
                                        <p class="text-gray-900 font-medium">{{ usuario?.nombre || '' }} {{ usuario?.apellido || '' }}</p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-500 block mb-1">Email</label>
                                        <p class="text-gray-900">{{ usuario?.email || 'No especificado' }}</p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-500 block mb-1">Teléfono</label>
                                        <p class="text-gray-900">{{ usuario?.telefono || 'No especificado' }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Información del Rol -->
                            <div class="bg-gray-50 rounded-lg p-5 border border-gray-200">
                                <h3 class="text-lg font-semibold mb-4 text-gray-800 flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                    </svg>
                                    Rol y Permisos
                                </h3>
                                <div class="space-y-4">
                                    <div>
                                        <label class="text-sm font-medium text-gray-500 block mb-1">Rol</label>
                                        <p class="mt-1">
                                            <Badge variant="info" class="text-sm px-3 py-1">{{ usuario?.rol?.nombre || 'Sin rol' }}</Badge>
                                        </p>
                                    </div>
                                    <div v-if="usuario?.rol?.permisos && usuario.rol.permisos.length > 0">
                                        <label class="text-sm font-medium text-gray-500 block mb-2">Permisos ({{ usuario.rol.permisos.length }})</label>
                                        <div class="mt-2 flex flex-wrap gap-2 max-h-48 overflow-y-auto">
                                            <Badge
                                                v-for="permiso in usuario.rol.permisos"
                                                :key="permiso.id"
                                                variant="success"
                                                class="text-xs px-2 py-1"
                                            >
                                                {{ permiso.nombre }}
                                            </Badge>
                                        </div>
                                    </div>
                                    <div v-else>
                                        <label class="text-sm font-medium text-gray-500 block mb-1">Permisos</label>
                                        <p class="text-gray-400 text-sm">No hay permisos asignados</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Estado de la Cuenta -->
                            <div class="bg-gray-50 rounded-lg p-5 border border-gray-200">
                                <h3 class="text-lg font-semibold mb-4 text-gray-800 flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Estado de la Cuenta
                                </h3>
                                <div class="space-y-4">
                                    <div>
                                        <label class="text-sm font-medium text-gray-500 block mb-1">Estado</label>
                                        <p class="mt-1">
                                            <Badge :variant="usuario?.estado ? 'success' : 'error'" class="text-sm px-3 py-1">
                                                {{ usuario?.estado ? 'Activo' : 'Inactivo' }}
                                            </Badge>
                                        </p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-500 block mb-1">Disponibilidad</label>
                                        <p class="mt-1">
                                            <Badge :variant="usuario?.disponibilidad ? 'success' : 'warning'" class="text-sm px-3 py-1">
                                                {{ usuario?.disponibilidad ? 'Disponible' : 'No disponible' }}
                                            </Badge>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Configuración de Seguridad -->
                            <div class="bg-gray-50 rounded-lg p-5 border border-gray-200">
                                <h3 class="text-lg font-semibold mb-4 text-gray-800 flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                    Configuración de Seguridad
                                </h3>
                                <div class="space-y-4">
                                    <div>
                                        <label class="text-sm font-medium text-gray-500 block mb-1">Cuenta no expirada</label>
                                        <p class="mt-1">
                                            <Badge :variant="usuario?.cuenta_no_expirada ? 'success' : 'error'" class="text-sm px-3 py-1">
                                                {{ usuario?.cuenta_no_expirada ? 'Sí' : 'No' }}
                                            </Badge>
                                        </p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-500 block mb-1">Cuenta no bloqueada</label>
                                        <p class="mt-1">
                                            <Badge :variant="usuario?.cuenta_no_bloqueada ? 'success' : 'error'" class="text-sm px-3 py-1">
                                                {{ usuario?.cuenta_no_bloqueada ? 'Sí' : 'No' }}
                                            </Badge>
                                        </p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-500 block mb-1">Credenciales no expiradas</label>
                                        <p class="mt-1">
                                            <Badge :variant="usuario?.credenciales_no_expiradas ? 'success' : 'error'" class="text-sm px-3 py-1">
                                                {{ usuario?.credenciales_no_expiradas ? 'Sí' : 'No' }}
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
import { Link, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Badge from '@/Components/UI/Badge.vue';

const props = defineProps({
    usuario: Object,
    menuItems: Array,
    pageVisits: Number,
});

const page = usePage();

const canEdit = computed(() => {
    try {
        const rol = page.props.auth?.user?.rol?.nombre;
        return ['PROPIETARIO', 'ADMINISTRADOR'].includes(rol);
    } catch (e) {
        return false;
    }
});

// Función route segura
const getRoute = (name, params = null) => {
    try {
        if (typeof window !== 'undefined' && window.route) {
            return params !== null ? window.route(name, params) : window.route(name);
        }
        // Fallback
        const baseUrl = window.location.origin;
        if (name === 'usuarios.index') return `${baseUrl}/usuarios`;
        if (name === 'usuarios.edit' && params) return `${baseUrl}/usuarios/${params}/edit`;
        return '#';
    } catch (e) {
        console.warn('Error getting route:', e, name, params);
        return '#';
    }
};
</script>


