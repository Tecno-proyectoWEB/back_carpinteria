<template>
    <AppLayout :menu-items="menuItems" :page-visits="pageVisits">
        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white/80 backdrop-blur-sm shadow-lg rounded-xl p-6 border border-indigo-100">
                    <div class="flex justify-between items-start mb-6">
                        <div>
                            <h2 class="text-2xl font-bold bg-gradient-to-r from-indigo-600 to-blue-600 bg-clip-text text-transparent">Detalle del Permiso</h2>
                            <p class="text-gray-600 mt-1">Información completa del permiso</p>
                        </div>
                        <Link
                            v-if="canEdit"
                            :href="route('permisos.edit', permiso.id)"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors"
                        >
                            Editar
                        </Link>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">ID</label>
                            <p class="text-gray-900">{{ permiso.id }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nombre</label>
                            <p class="text-gray-900 font-mono">{{ permiso.nombre }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Roles con este Permiso</label>
                            <div v-if="permiso.roles && permiso.roles.length > 0" class="flex flex-wrap gap-2">
                                <Badge
                                    v-for="rol in permiso.roles"
                                    :key="rol.id"
                                    variant="info"
                                >
                                    {{ rol.nombre }}
                                </Badge>
                            </div>
                            <p v-else class="text-gray-400">No hay roles asignados</p>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <Link
                            :href="route('permisos.index')"
                            class="px-5 py-2.5 border border-indigo-200 rounded-lg hover:bg-indigo-50 text-indigo-700 transition-colors"
                        >
                            Volver
                        </Link>
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
    permiso: Object,
    menuItems: Array,
    pageVisits: Number,
});

const page = usePage();

const canEdit = computed(() => {
    const rol = page.props.auth?.user?.rol?.nombre;
    return ['PROPIETARIO', 'ADMINISTRADOR'].includes(rol);
});
</script>

