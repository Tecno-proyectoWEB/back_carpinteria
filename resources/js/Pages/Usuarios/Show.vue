<template>
    <AppLayout>
        <div class="max-w-7xl mx-auto">
            <div class="max-w-4xl mx-auto">
                <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex justify-between items-center">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900">
                                {{ usuario?.nombre || '' }} {{ usuario?.apellido || '' }}
                            </h2>
                            <p class="text-sm text-gray-500 mt-1">{{ usuario?.email || '' }}</p>
                        </div>
                        <div class="flex space-x-2">
                            <Link
                                v-if="canEdit && usuario?.id"
                                :href="getRoute('usuarios.edit', usuario.id)"
                                class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700"
                            >
                                Editar
                            </Link>
                            <Link
                                :href="getRoute('usuarios.index')"
                                class="px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50"
                            >
                                Volver
                            </Link>
                        </div>
                    </div>

                    <div class="px-6 py-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Información Personal -->
                            <div>
                                <h3 class="text-lg font-semibold mb-4">Información Personal</h3>
                                <div class="space-y-3">
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Nombre Completo</label>
                                        <p class="text-gray-900">{{ usuario?.nombre || '' }} {{ usuario?.apellido || '' }}</p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Email</label>
                                        <p class="text-gray-900">{{ usuario?.email || 'N/A' }}</p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Teléfono</label>
                                        <p class="text-gray-900">{{ usuario?.telefono || 'No especificado' }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Información del Rol -->
                            <div>
                                <h3 class="text-lg font-semibold mb-4">Rol y Permisos</h3>
                                <div class="space-y-3">
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Rol</label>
                                        <p class="mt-1">
                                            <Badge variant="info">{{ usuario?.rol?.nombre || 'Sin rol' }}</Badge>
                                        </p>
                                    </div>
                                    <div v-if="usuario?.rol?.permisos && usuario.rol.permisos.length > 0">
                                        <label class="text-sm font-medium text-gray-500">Permisos</label>
                                        <div class="mt-2 flex flex-wrap gap-2">
                                            <Badge
                                                v-for="permiso in usuario.rol.permisos"
                                                :key="permiso?.id || Math.random()"
                                                variant="success"
                                                class="text-xs"
                                            >
                                                {{ permiso?.nombre || 'N/A' }}
                                            </Badge>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Estado de la Cuenta -->
                            <div>
                                <h3 class="text-lg font-semibold mb-4">Estado de la Cuenta</h3>
                                <div class="space-y-3">
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Estado</label>
                                        <p class="mt-1">
                                            <Badge :variant="usuario?.estado ? 'success' : 'error'">
                                                {{ usuario?.estado ? 'Activo' : 'Inactivo' }}
                                            </Badge>
                                        </p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Disponibilidad</label>
                                        <p class="mt-1">
                                            <Badge :variant="usuario?.disponibilidad ? 'success' : 'warning'">
                                                {{ usuario?.disponibilidad ? 'Disponible' : 'No disponible' }}
                                            </Badge>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Configuración de Seguridad -->
                            <div>
                                <h3 class="text-lg font-semibold mb-4">Configuración de Seguridad</h3>
                                <div class="space-y-3">
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Cuenta no expirada</label>
                                        <p class="mt-1">
                                            <Badge :variant="usuario?.cuenta_no_expirada ? 'success' : 'error'">
                                                {{ usuario?.cuenta_no_expirada ? 'Sí' : 'No' }}
                                            </Badge>
                                        </p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Cuenta no bloqueada</label>
                                        <p class="mt-1">
                                            <Badge :variant="usuario?.cuenta_no_bloqueada ? 'success' : 'error'">
                                                {{ usuario?.cuenta_no_bloqueada ? 'Sí' : 'No' }}
                                            </Badge>
                                        </p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Credenciales no expiradas</label>
                                        <p class="mt-1">
                                            <Badge :variant="usuario?.credenciales_no_expiradas ? 'success' : 'error'">
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
import { getRoute } from '@/utils/routeHelper';
import AppLayout from '@/Layouts/AppLayout.vue';
import Badge from '@/Components/UI/Badge.vue';

const page = usePage();

const props = defineProps({
    usuario: {
        type: Object,
        default: () => ({}),
    },
    },
    },
});

const canEdit = computed(() => {
    const rol = page.props.auth?.user?.rol?.nombre;
    return rol && ['PROPIETARIO'].includes(rol);
});
</script>


