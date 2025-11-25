<template>
    <AppLayout :menu-items="menuItems" :page-visits="pageVisits">
        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white shadow-sm rounded-lg p-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">Crear Nuevo Método de Pago</h2>

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

                        <div class="flex justify-end space-x-4 mt-6">
                            <Link
                                :href="route('metodos-pago.index')"
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
                                <span v-else>Guardar Método</span>
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
import Textarea from '@/Components/Form/Textarea.vue';

const props = defineProps({
    menuItems: Array,
    pageVisits: Number,
});

const form = useForm({
    nombre: '',
    descripcion: '',
});

const submit = () => {
    form.post(route('metodos-pago.store'));
};
</script>

