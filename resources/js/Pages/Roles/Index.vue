<template>
    <AppLayout :menu-items="menuItems" :page-visits="pageVisits">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="mb-6">
                    <h2 class="text-3xl font-bold text-gray-900">Roles y Permisos</h2>
                    <p class="text-gray-600 mt-2">Gestione los permisos asignados a cada rol del sistema</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div
                        v-for="rol in roles"
                        :key="rol.id"
                        class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow"
                    >
                        <div class="flex justify-between items-start mb-4">
                            <h3 class="text-xl font-bold text-gray-900">{{ rol.nombre }}</h3>
                            <Link
                                v-if="canEdit"
                                :href="route('roles.edit', rol.id)"
                                class="text-indigo-600 hover:text-indigo-900 text-sm font-medium"
                            >
                                Editar Permisos
                            </Link>
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
        </div>
    </AppLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Badge from '@/Components/UI/Badge.vue';

const props = defineProps({
    roles: Array,
    permisos: Array,
    menuItems: Array,
    pageVisits: Number,
});

const canEdit = computed(() => {
    const rol = window.$page?.props?.auth?.user?.rol?.nombre;
    return ['PROPIETARIO'].includes(rol);
});
</script>


