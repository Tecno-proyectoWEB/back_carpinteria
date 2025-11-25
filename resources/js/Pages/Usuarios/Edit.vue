<template>
    <AppLayout :menu-items="menuItems" :page-visits="pageVisits">
        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white shadow-sm rounded-lg p-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">Editar Usuario</h2>

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
                            label="Teléfono"
                            type="tel"
                            :error="form.errors.telefono"
                        />

                        <div class="mb-4 p-4 bg-yellow-50 border border-yellow-200 rounded-md">
                            <p class="text-sm text-yellow-800">
                                <strong>Nota:</strong> Deje los campos de contraseña vacíos si no desea cambiarla.
                            </p>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <Input
                                v-model="form.password"
                                label="Nueva Contraseña (opcional)"
                                type="password"
                                :error="form.errors.password"
                            />

                            <Input
                                v-model="form.password_confirmation"
                                label="Confirmar Nueva Contraseña"
                                type="password"
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

                        <div class="grid grid-cols-3 gap-4">
                            <div class="mb-4">
                                <label class="flex items-center">
                                    <input
                                        v-model="form.cuenta_no_expirada"
                                        type="checkbox"
                                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                    />
                                    <span class="ml-2 text-sm text-gray-700">Cuenta no expirada</span>
                                </label>
                            </div>

                            <div class="mb-4">
                                <label class="flex items-center">
                                    <input
                                        v-model="form.cuenta_no_bloqueada"
                                        type="checkbox"
                                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                    />
                                    <span class="ml-2 text-sm text-gray-700">Cuenta no bloqueada</span>
                                </label>
                            </div>

                            <div class="mb-4">
                                <label class="flex items-center">
                                    <input
                                        v-model="form.credenciales_no_expiradas"
                                        type="checkbox"
                                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                    />
                                    <span class="ml-2 text-sm text-gray-700">Credenciales válidas</span>
                                </label>
                            </div>
                        </div>

                        <div class="flex justify-end space-x-4 mt-6">
                            <Link
                                :href="route('usuarios.index')"
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
                                <span v-else>Actualizar Usuario</span>
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

const props = defineProps({
    usuario: Object,
    roles: Array,
    menuItems: Array,
    pageVisits: Number,
});

const form = useForm({
    nombre: props.usuario.nombre,
    apellido: props.usuario.apellido,
    email: props.usuario.email,
    telefono: props.usuario.telefono || '',
    password: '',
    password_confirmation: '',
    rol_id: props.usuario.rol_id,
    estado: props.usuario.estado,
    disponibilidad: props.usuario.disponibilidad,
    cuenta_no_expirada: props.usuario.cuenta_no_expirada,
    cuenta_no_bloqueada: props.usuario.cuenta_no_bloqueada,
    credenciales_no_expiradas: props.usuario.credenciales_no_expiradas,
    _method: 'PUT',
});

const submit = () => {
    form.post(route('usuarios.update', props.usuario.id));
};
</script>


