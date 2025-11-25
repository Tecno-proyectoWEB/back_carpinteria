<template>
    <AppLayout :menu-items="menuItems" :page-visits="pageVisits">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-3xl font-bold text-gray-900">Métodos de Pago</h2>
                    <Link
                        v-if="canCreate"
                        :href="route('metodos-pago.create')"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                    >
                        + Nuevo Método
                    </Link>
                </div>

                <!-- Tabla -->
                <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Descripción</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pedidos</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pagos</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="metodo in metodosPago" :key="metodo.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    #{{ metodo.id }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ metodo.nombre }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    {{ metodo.descripcion || 'Sin descripción' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ metodo.pedidos_count || 0 }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ metodo.pagos_count || 0 }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <Link
                                        v-if="canEdit"
                                        :href="route('metodos-pago.edit', metodo.id)"
                                        class="text-indigo-600 hover:text-indigo-900 mr-3"
                                    >
                                        Editar
                                    </Link>
                                    <button
                                        v-if="canDelete && metodo.pedidos_count === 0 && metodo.pagos_count === 0"
                                        @click="deleteMetodo(metodo)"
                                        class="text-red-600 hover:text-red-900"
                                    >
                                        Eliminar
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="metodosPago.length === 0">
                                <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                    No hay métodos de pago registrados
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    metodosPago: Array,
    menuItems: Array,
    pageVisits: Number,
});

const canCreate = computed(() => {
    const rol = window.$page?.props?.auth?.user?.rol?.nombre;
    return ['PROPIETARIO'].includes(rol);
});

const canEdit = computed(() => canCreate.value);
const canDelete = computed(() => canCreate.value);

const deleteMetodo = (metodo) => {
    if (confirm(`¿Está seguro de eliminar el método de pago "${metodo.nombre}"?`)) {
        router.delete(route('metodos-pago.destroy', metodo.id), {
            preserveScroll: true,
        });
    }
};
</script>

