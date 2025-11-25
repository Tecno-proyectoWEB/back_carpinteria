<template>
    <AppLayout :menu-items="menuItems" :page-visits="pageVisits">
        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white shadow-sm rounded-lg p-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">Editar Categoría</h2>

                    <form @submit.prevent="submit">
                        <Input
                            v-model="form.nombre"
                            label="Nombre *"
                            required
                            :error="form.errors.nombre"
                        />

                        <Textarea
                            v-model="form.descripcion"
                            label="Descripción"
                            :error="form.errors.descripcion"
                        />

                        <Select
                            v-model="form.subcategoria_id"
                            label="Subcategoría"
                            :options="subcategorias"
                            option-value="id"
                            option-label="nombre"
                            :error="form.errors.subcategoria_id"
                        >
                            <option value="">Sin subcategoría</option>
                        </Select>

                        <div class="mb-4">
                            <label class="flex items-center">
                                <input
                                    v-model="form.activo"
                                    type="checkbox"
                                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                />
                                <span class="ml-2 text-sm text-gray-700">Categoría activa</span>
                            </label>
                        </div>

                        <div class="flex justify-end space-x-4 mt-6">
                            <Link
                                :href="route('categorias.index')"
                                class="px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50"
                            >
                                Cancelar
                            </Link>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50"
                            >
                                <span v-if="form.processing">Actualizando...</span>
                                <span v-else>Actualizar Categoría</span>
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
import AppLayout from '@/Layouts/AppLayout.vue';
import Input from '@/Components/Form/Input.vue';
import Select from '@/Components/Form/Select.vue';
import Textarea from '@/Components/Form/Textarea.vue';

const props = defineProps({
    categoria: Object,
    subcategorias: Array,
    menuItems: Array,
    pageVisits: Number,
});

const form = useForm({
    nombre: props.categoria.nombre,
    descripcion: props.categoria.descripcion || '',
    activo: props.categoria.activo,
    subcategoria_id: props.categoria.subcategoria_id || null,
    _method: 'PUT',
});

const submit = () => {
    form.post(route('categorias.update', props.categoria.id));
};
</script>

