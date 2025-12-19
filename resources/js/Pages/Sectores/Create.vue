<template>
    <AppLayout :auth="auth" :visitas-pagina="visitasPagina">
        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white shadow-sm rounded-lg p-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">Crear Nuevo Sector</h2>

                    <form @submit.prevent="submit">
                        <Input
                            v-model="form.nombre"
                            label="Nombre *"
                            required
                            :error="form.errors.nombre"
                        />

                        <Select
                            v-model="form.almacen_id"
                            label="Almacén *"
                            :options="almacenes"
                            option-value="id"
                            option-label="nombre"
                            required
                            :error="form.errors.almacen_id"
                        />

                        <Input
                            v-model="form.tipo"
                            label="Tipo"
                            :error="form.errors.tipo"
                        />

                        <div class="grid grid-cols-2 gap-4">
                            <Input
                                v-model.number="form.stock"
                                label="Stock"
                                type="number"
                                min="0"
                                :error="form.errors.stock"
                            />

                            <Input
                                v-model.number="form.capacidad_maxima"
                                label="Capacidad Máxima"
                                type="number"
                                min="0"
                                :error="form.errors.capacidad_maxima"
                            />
                        </div>

                        <Textarea
                            v-model="form.descripcion"
                            label="Descripción"
                            :error="form.errors.descripcion"
                        />

                        <div class="flex justify-end space-x-4 mt-6">
                            <Link
                                :href="route('sectores.index')"
                                class="px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50"
                            >
                                Cancelar
                            </Link>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50"
                            >
                                <span v-if="form.processing">Guardando...</span>
                                <span v-else>Guardar Sector</span>
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
import Select from '@/Components/Form/Select.vue';
import Textarea from '@/Components/Form/Textarea.vue';

const props = defineProps({
    auth: { type: Object, required: true },
    visitasPagina: { type: Number, default: 0 },
    almacenes: Array,
});

const form = useForm({
    nombre: '',
    almacen_id: '',
    tipo: '',
    stock: null,
    capacidad_maxima: null,
    descripcion: '',
});

const submit = () => {
    form.post(route('sectores.store'));
};
</script>
