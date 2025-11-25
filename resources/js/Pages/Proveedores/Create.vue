<template>
    <AppLayout :menu-items="menuItems" :page-visits="pageVisits">
        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white shadow-sm rounded-lg p-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">Crear Nuevo Proveedor</h2>

                    <form @submit.prevent="submit">
                        <Input
                            v-model="form.nombre"
                            label="Nombre *"
                            required
                            :error="form.errors.nombre"
                        />

                        <Input
                            v-model="form.ruc"
                            label="RUC"
                            :error="form.errors.ruc"
                        />

                        <Textarea
                            v-model="form.direccion"
                            label="Dirección"
                            :error="form.errors.direccion"
                        />

                        <div class="grid grid-cols-2 gap-4">
                            <Input
                                v-model="form.telefono"
                                label="Teléfono"
                                type="tel"
                                :error="form.errors.telefono"
                            />

                            <Input
                                v-model="form.email"
                                label="Email"
                                type="email"
                                :error="form.errors.email"
                            />
                        </div>

                        <Input
                            v-model="form.persona_contacto"
                            label="Persona de Contacto"
                            :error="form.errors.persona_contacto"
                        />

                        <div class="mb-4">
                            <label class="flex items-center">
                                <input
                                    v-model="form.activo"
                                    type="checkbox"
                                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                />
                                <span class="ml-2 text-sm text-gray-700">Proveedor activo</span>
                            </label>
                        </div>

                        <div class="flex justify-end space-x-4 mt-6">
                            <Link
                                :href="route('proveedores.index')"
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
                                <span v-else>Guardar Proveedor</span>
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
    ruc: '',
    direccion: '',
    telefono: '',
    email: '',
    persona_contacto: '',
    activo: true,
});

const submit = () => {
    form.post(route('proveedores.store'));
};
</script>

