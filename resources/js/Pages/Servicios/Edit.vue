<template>
    <AppLayout :auth="auth" :menu-items="menuItems" :page-visits="pageVisits" :visitas-pagina="visitasPagina">
        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white shadow-sm rounded-lg p-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">Editar Servicio</h2>

                    <form @submit.prevent="submit">
                        <Input
                            v-model="form.nombre"
                            label="Nombre"
                            required
                            :error="form.errors.nombre"
                        />

                        <Textarea
                            v-model="form.descripcion"
                            label="Descripci├│n"
                            :error="form.errors.descripcion"
                            :rows="4"
                        />

                        <Select
                            v-model="form.categoria_id"
                            label="Categor├¡a"
                            :options="categorias"
                            option-value="id"
                            option-label="nombre"
                            placeholder="Seleccione una categor├¡a (opcional)"
                            :error="form.errors.categoria_id"
                        />

                        <div class="grid grid-cols-2 gap-4">
                            <Input
                                v-model.number="form.precio_base"
                                label="Precio Base"
                                type="number"
                                step="0.01"
                                required
                                :error="form.errors.precio_base"
                            />

                            <Input
                                v-model.number="form.tiempo_estimado"
                                label="Tiempo Estimado (horas)"
                                type="number"
                                :error="form.errors.tiempo_estimado"
                            />
                        </div>

                        <div class="mb-4">
                            <label class="flex items-center">
                                <input
                                    v-model="form.activo"
                                    type="checkbox"
                                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                />
                                <span class="ml-2 text-sm text-gray-700">Servicio activo</span>
                            </label>
                        </div>

                        <div class="flex justify-end space-x-4 mt-6">
                            <Link
                                :href="route('servicios.index')"
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
                                <span v-else>Actualizar Servicio</span>
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
import Input from '@/Components/Form/Input.vue';
import Textarea from '@/Components/Form/Textarea.vue';
import Select from '@/Components/Form/Select.vue';

const props = defineProps({
        auth: { type: Object, required: true },
    visitasPagina: { type: Number, default: 0 },
servicio: Object,
    categorias: Array,
    menuItems: Array,
    pageVisits: Number,
});

const form = useForm({
    nombre: props.servicio.nombre,
    descripcion: props.servicio.descripcion || '',
    categoria_id: props.servicio.categoria_id,
    precio_base: props.servicio.precio_base,
    tiempo_estimado: props.servicio.tiempo_estimado,
    activo: props.servicio.activo,
    _method: 'PUT',
});

const submit = () => {
    form.post(route('servicios.update', props.servicio.id));
};
</script>

