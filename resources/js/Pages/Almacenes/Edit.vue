<template>
    <AppLayout :auth="auth" :visitas-pagina="visitasPagina">`n        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h2 class="text-2xl font-bold mb-6">Editar Almacén</h2>

                    <form @submit.prevent="submit">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Nombre *</label>
                                <input
                                    v-model="form.nombre"
                                    type="text"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                                <span v-if="form.errors.nombre" class="text-red-600 text-sm">{{ form.errors.nombre }}</span>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Ubicación *</label>
                                <input
                                    v-model="form.ubicacion"
                                    type="text"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                                <span v-if="form.errors.ubicacion" class="text-red-600 text-sm">{{ form.errors.ubicacion }}</span>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Descripción</label>
                                <textarea
                                    v-model="form.descripcion"
                                    rows="3"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                ></textarea>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Capacidad (m²)</label>
                                <input
                                    v-model.number="form.capacidad"
                                    type="number"
                                    step="0.01"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                            </div>

                            <div class="flex items-center">
                                <input
                                    v-model="form.activo"
                                    type="checkbox"
                                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                                <label class="ml-2 block text-sm text-gray-900">Activo</label>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end space-x-3">
                            <Link
                                :href="route('almacenes.index')"
                                class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300"
                            >
                                Cancelar
                            </Link>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50"
                            >
                                Actualizar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/Pages/Layout.vue';

const props = defineProps({
        auth: { type: Object, required: true },
    visitasPagina: { type: Number, default: 0 },
almacen: Object
});

const form = useForm({
    nombre: props.almacen.nombre,
    ubicacion: props.almacen.ubicacion,
    descripcion: props.almacen.descripcion,
    capacidad: props.almacen.capacidad,
    activo: props.almacen.activo
});

const submit = () => {
    form.put(route('almacenes.update', props.almacen.id));
};
</script>
