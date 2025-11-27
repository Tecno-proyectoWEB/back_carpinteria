<template>
    <AppLayout>
        <div class="max-w-4xl mx-auto">
            <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                <!-- Header -->
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex justify-between items-center">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">{{ rol?.nombre || 'Rol' }}</h2>
                        <p class="text-sm text-gray-600 mt-1">Detalles del rol y permisos asignados</p>
                    </div>
                    <div class="flex space-x-2">
                        <Link
                            v-if="canEdit && rol?.id"
                            :href="getRoute('roles.edit', rol.id)"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700"
                        >
                            Editar Permisos
                        </Link>
                        <Link
                            :href="getRoute('roles.index')"
                            class="px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50"
                        >
                            Volver
                        </Link>
                    </div>
                </div>

                <!-- Content -->
                <div class="px-6 py-4">
                    <!-- Información del Rol -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Información del Rol</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="text-sm font-medium text-gray-500">Nombre del Rol</label>
                                <p class="mt-1 text-lg font-semibold text-gray-900">{{ rol?.nombre || 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-500">Usuarios con este Rol</label>
                                <p class="mt-1 text-lg font-semibold text-gray-900">
                                    {{ rol?.usuarios?.length || 0 }} usuario(s)
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Permisos -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            Permisos Asignados ({{ rol?.permisos?.length || 0 }})
                        </h3>
                        <div v-if="rol?.permisos && rol.permisos.length > 0" class="space-y-4">
                            <div
                                v-for="(permisosGrupo, modulo) in permisosAgrupados"
                                :key="modulo"
                                class="border rounded-lg p-4"
                            >
                                <h4 class="text-md font-semibold mb-3 text-gray-800">
                                    {{ modulo.charAt(0).toUpperCase() + modulo.slice(1) }}
                                </h4>
                                <div class="flex flex-wrap gap-2">
                                    <Badge
                                        v-for="permiso in permisosGrupo"
                                        :key="permiso.id"
                                        variant="success"
                                        class="text-xs"
                                    >
                                        {{ permiso.nombre }}
                                    </Badge>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center py-8 text-gray-400">
                            <p>Este rol no tiene permisos asignados</p>
                        </div>
                    </div>

                    <!-- Usuarios con este Rol -->
                    <div v-if="rol?.usuarios && rol.usuarios.length > 0" class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Usuarios con este Rol</h3>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                <div
                                    v-for="usuario in rol.usuarios"
                                    :key="usuario.id"
                                    class="bg-white p-3 rounded border"
                                >
                                    <p class="font-medium text-gray-900">
                                        {{ usuario.nombre }} {{ usuario.apellido }}
                                    </p>
                                    <p class="text-sm text-gray-600">{{ usuario.email }}</p>
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
    rol: {
        type: Object,
        default: () => ({}),
    },
});

const canEdit = computed(() => {
    const permisos = page.props.auth?.user?.permisos || [];
    return permisos.includes('roles.editar') || page.props.auth?.user?.rol?.nombre === 'PROPIETARIO';
});

// Agrupar permisos por módulo
const permisosAgrupados = computed(() => {
    const grupos = {};
    if (!props.rol?.permisos || !Array.isArray(props.rol.permisos)) return grupos;
    
    props.rol.permisos.forEach(permiso => {
        if (!permiso?.nombre) return;
        const partes = permiso.nombre.split('.');
        const modulo = partes[0] || 'Otros';
        if (!grupos[modulo]) {
            grupos[modulo] = [];
        }
        grupos[modulo].push(permiso);
    });
    
    return grupos;
});
</script>

