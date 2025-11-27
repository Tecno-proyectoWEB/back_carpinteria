<template>
    <AppLayout :menu-items="menuItems" :page-visits="pageVisits">
        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white/80 backdrop-blur-sm shadow-lg rounded-xl p-6 border border-indigo-100">
                    <h2 class="text-2xl font-bold bg-gradient-to-r from-indigo-600 to-blue-600 bg-clip-text text-transparent mb-6">Editar Permiso</h2>

                    <form @submit.prevent="submit">
                        <Input
                            v-model="form.nombre"
                            label="Nombre del Permiso"
                            placeholder="Ej: productos.crear"
                            required
                            :error="form.errors.nombre"
                            help-text="Formato: modulo.accion (ej: productos.crear, usuarios.editar)"
                        />

                        <div class="flex justify-end space-x-4 mt-6">
                            <Link
                                :href="route('permisos.index')"
                                class="px-5 py-2.5 border border-indigo-200 rounded-lg hover:bg-indigo-50 text-indigo-700 transition-colors"
                            >
                                Cancelar
                            </Link>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="px-5 py-2.5 bg-gradient-to-r from-indigo-600 to-blue-600 text-white rounded-lg hover:from-indigo-700 hover:to-blue-700 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200 disabled:opacity-50 disabled:transform-none"
                            >
                                <span v-if="form.processing">Guardando...</span>
                                <span v-else>Actualizar Permiso</span>
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

const props = defineProps({
    permiso: Object,
    menuItems: Array,
    pageVisits: Number,
});

const form = useForm({
    nombre: props.permiso?.nombre || '',
});

const submit = () => {
    form.put(route('permisos.update', props.permiso.id));
};
</script>

