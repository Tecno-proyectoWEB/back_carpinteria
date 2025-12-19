<template>
    <AppLayout :auth="auth" :menu-items="menuItems" :page-visits="pageVisits" :visitas-pagina="visitasPagina">
        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white/80 backdrop-blur-sm shadow-lg rounded-xl p-6 border border-indigo-100">
                    <h2 class="text-2xl font-bold bg-gradient-to-r from-indigo-600 to-blue-600 bg-clip-text text-transparent mb-6">Crear Nuevo Usuario</h2>

                    <form @submit.prevent="submit">
                        <div class="grid grid-cols-2 gap-4">
                            <Input
                                v-model="form.nombre"
                                label="Nombre"
                                required
                                :error="form.errors.nombre"
                            />

                            <Input
                                v-model="form.apellido"
                                label="Apellido"
                                required
                                :error="form.errors.apellido"
                            />
                        </div>

                        <Input
                            v-model="form.email"
                            label="Email"
                            type="email"
                            required
                            :error="form.errors.email"
                        />

                        <Input
                            v-model="form.telefono"
                            label="Tel├®fono"
                            type="tel"
                            :error="form.errors.telefono"
                        />

                        <div class="grid grid-cols-2 gap-4">
                            <Input
                                v-model="form.password"
                                label="Contrase├▒a"
                                type="password"
                                required
                                :error="form.errors.password"
                            />

                            <Input
                                v-model="form.password_confirmation"
                                label="Confirmar Contrase├▒a"
                                type="password"
                                required
                                :error="form.errors.password_confirmation"
                            />
                        </div>

                        <Select
                            v-model="form.rol_id"
                            label="Rol"
                            :options="roles"
                            option-value="id"
                            option-label="nombre"
                            required
                            :error="form.errors.rol_id"
                        />

                        <div class="grid grid-cols-2 gap-4">
                            <div class="mb-4">
                                <label class="flex items-center">
                                    <input
                                        v-model="form.estado"
                                        type="checkbox"
                                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                    />
                                    <span class="ml-2 text-sm text-gray-700">Usuario activo</span>
                                </label>
                            </div>

                            <div class="mb-4">
                                <label class="flex items-center">
                                    <input
                                        v-model="form.disponibilidad"
                                        type="checkbox"
                                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                    />
                                    <span class="ml-2 text-sm text-gray-700">Disponible</span>
                                </label>
                            </div>
                        </div>

                        <div class="flex justify-end space-x-4 mt-6">
                            <Link
                                :href="route('usuarios.index')"
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
                                <span v-else>Guardar Usuario</span>
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

const props = defineProps({
        auth: { type: Object, required: true },
    visitasPagina: { type: Number, default: 0 },
roles: Array,
    menuItems: Array,
    pageVisits: Number,
});

const form = useForm({
    nombre: '',
    apellido: '',
    email: '',
    telefono: '',
    password: '',
    password_confirmation: '',
    rol_id: '',
    estado: true,
    disponibilidad: true,
});

const submit = () => {
    form.post(route('usuarios.store'));
};
</script>


