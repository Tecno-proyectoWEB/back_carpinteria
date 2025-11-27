<template>
    <AppLayout>
        <div class="max-w-7xl mx-auto w-full">
                <div class="mb-6 flex justify-between items-center">
                    <div>
                        <h2 class="text-3xl font-bold text-gray-900">Roles y Permisos</h2>
                        <p class="text-gray-600 mt-2">Gestione los permisos asignados a cada rol del sistema</p>
                    </div>
                    <Link
                        v-if="canCreate"
                        :href="getRoute('roles.create')"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                    >
                        + Nuevo Rol
                    </Link>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div
                        v-for="rol in roles"
                        :key="rol.id"
                        class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow"
                    >
                        <div class="flex justify-between items-start mb-4">
                            <h3 class="text-xl font-bold text-gray-900">{{ rol.nombre }}</h3>
                            <div class="flex space-x-2">
                                <Link
                                    v-if="canEdit && rol?.id"
                                    :href="getRoute('roles.edit', rol.id)"
                                    class="text-indigo-600 hover:text-indigo-900 text-sm font-medium"
                                >
                                    Editar
                                </Link>
                                <button
                                    v-if="canDelete && rol?.id && rol.nombre !== 'PROPIETARIO' && (!rol.usuarios || rol.usuarios.length === 0)"
                                    @click="deleteRol(rol)"
                                    class="text-red-600 hover:text-red-900 text-sm font-medium"
                                >
                                    Eliminar
                                </button>
                            </div>
                        </div>

                        <div v-if="rol.permisos && rol.permisos.length > 0" class="space-y-2">
                            <p class="text-sm font-medium text-gray-500 mb-2">
                                Permisos ({{ rol.permisos.length }}):
                            </p>
                            <div class="flex flex-wrap gap-2">
                                <Badge
                                    v-for="permiso in rol.permisos"
                                    :key="permiso.id"
                                    variant="success"
                                    class="text-xs"
                                >
                                    {{ permiso.nombre }}
                                </Badge>
                            </div>
                        </div>
                        <div v-else class="text-sm text-gray-400">
                            Sin permisos asignados
                        </div>

                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <p class="text-xs text-gray-500">
                                Usuarios con este rol: {{ rol.usuarios?.length || 0 }}
                            </p>
                        </div>
                    </div>
                </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { getRoute } from '@/utils/routeHelper';
import AppLayout from '@/Layouts/AppLayout.vue';
import Badge from '@/Components/UI/Badge.vue';

const page = usePage();

const props = defineProps({
    roles: {
        type: Array,
        default: () => [],
    },
    permisos: {
        type: Array,
        default: () => [],
    },
});

const canEdit = computed(() => {
    const permisos = page.props.auth?.user?.permisos || [];
    return permisos.includes('roles.editar') || page.props.auth?.user?.rol?.nombre === 'PROPIETARIO';
});

const canCreate = computed(() => {
    const permisos = page.props.auth?.user?.permisos || [];
    return permisos.includes('roles.crear') || page.props.auth?.user?.rol?.nombre === 'PROPIETARIO';
});

const canDelete = computed(() => {
    const permisos = page.props.auth?.user?.permisos || [];
    return permisos.includes('roles.eliminar') || page.props.auth?.user?.rol?.nombre === 'PROPIETARIO';
});

const deleteRol = (rol) => {
    if (!rol?.id) return;
    if (rol.nombre === 'PROPIETARIO') {
        alert('No se puede eliminar el rol PROPIETARIO');
        return;
    }
    if (rol.usuarios && rol.usuarios.length > 0) {
        alert('No se puede eliminar el rol porque tiene usuarios asignados');
        return;
    }
    if (confirm(`¿Está seguro de eliminar el rol "${rol.nombre}"?`)) {
        router.delete(getRoute('roles.destroy', rol.id), {
            preserveScroll: true,
        });
    }
};
</script>


