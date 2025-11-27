<template>
    <AppLayout :menu-items="menuItems" :page-visits="pageVisits">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h2 class="text-3xl font-bold bg-gradient-to-r from-indigo-600 to-blue-600 bg-clip-text text-transparent">Roles y Permisos</h2>
                        <p class="text-gray-600 mt-2">Gestione los roles y permisos del sistema</p>
                    </div>
                    <Link
                        v-if="canCreate"
                        :href="route('permisos.index')"
                        class="px-5 py-2.5 bg-gradient-to-r from-indigo-600 to-blue-600 text-white rounded-lg hover:from-indigo-700 hover:to-blue-700 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200 font-medium flex items-center space-x-2"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>Gestionar Permisos</span>
                    </Link>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div
                        v-for="rol in roles"
                        :key="rol.id"
                        class="bg-white/80 backdrop-blur-sm rounded-xl shadow-lg p-6 hover:shadow-xl transition-all duration-200 border border-indigo-100"
                    >
                        <div class="flex justify-between items-start mb-4">
                            <h3 class="text-xl font-bold text-indigo-700">{{ rol.nombre }}</h3>
                            <div class="flex space-x-2">
                                <Link
                                    v-if="canEdit"
                                    :href="route('roles.edit', rol.id)"
                                    class="text-indigo-600 hover:text-indigo-800 text-sm font-medium px-3 py-1 rounded-lg hover:bg-indigo-50 transition-colors"
                                >
                                    Editar
                                </Link>
                            </div>
                        </div>

                        <div v-if="rol.permisos && rol.permisos.length > 0" class="space-y-2">
                            <p class="text-sm font-medium text-gray-600 mb-2">
                                Permisos ({{ rol.permisos.length }}):
                            </p>
                            <div class="flex flex-wrap gap-2">
                                <Badge
                                    v-for="permiso in rol.permisos.slice(0, 5)"
                                    :key="permiso.id"
                                    variant="success"
                                    class="text-xs"
                                >
                                    {{ permiso.nombre }}
                                </Badge>
                                <Badge
                                    v-if="rol.permisos.length > 5"
                                    variant="info"
                                    class="text-xs"
                                >
                                    +{{ rol.permisos.length - 5 }} más
                                </Badge>
                            </div>
                        </div>
                        <div v-else class="text-sm text-gray-400">
                            Sin permisos asignados
                        </div>

                        <div class="mt-4 pt-4 border-t border-indigo-100">
                            <p class="text-xs text-gray-500">
                                Usuarios con este rol: <span class="font-semibold text-indigo-600">{{ rol.usuarios?.length || 0 }}</span>
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
import { Link, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Badge from '@/Components/UI/Badge.vue';

const props = defineProps({
    roles: Array,
    permisos: Array,
    menuItems: Array,
    pageVisits: Number,
});

const page = usePage();

const canEdit = computed(() => {
    const rol = page.props.auth?.user?.rol?.nombre;
    return ['PROPIETARIO', 'ADMINISTRADOR'].includes(rol);
});

const canCreate = computed(() => canEdit.value);
</script>


