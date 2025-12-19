<template>
    <AppLayout :auth="auth" :menu-items="menuItems" :page-visits="pageVisits" :visitas-pagina="visitasPagina">
        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white shadow-lg rounded-xl p-6 border border-gray-200">
                    <div class="mb-6">
                        <h2 class="text-3xl font-bold bg-gradient-to-r from-indigo-600 to-blue-600 bg-clip-text text-transparent mb-2">
                            Editar Usuario
                        </h2>
                        <p class="text-gray-600 text-sm">
                            {{ usuario?.nombre || '' }} {{ usuario?.apellido || '' }} - {{ usuario?.email || '' }}
                        </p>
                    </div>

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

                        <div class="mb-4 p-4 bg-yellow-50 border border-yellow-200 rounded-md">
                            <p class="text-sm text-yellow-800">
                                <strong>Nota:</strong> Deje los campos de contrase├▒a vac├¡os si no desea cambiarla.
                            </p>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <Input
                                v-model="form.password"
                                label="Nueva Contrase├▒a (opcional)"
                                type="password"
                                :error="form.errors.password"
                            />

                            <Input
                                v-model="form.password_confirmation"
                                label="Confirmar Nueva Contrase├▒a"
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

                        <div class="flex justify-end space-x-4 mt-6">
                            <button
                                type="button"
                                @click="cancelEdit"
                                class="px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50 text-gray-700 font-medium"
                            >
                                Cancelar
                            </button>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="px-4 py-2 bg-gradient-to-r from-indigo-600 to-blue-600 text-white rounded-md hover:from-indigo-700 hover:to-blue-700 disabled:opacity-50 font-medium shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200"
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
import { useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/Pages/Layout.vue';
import Input from '@/Components/Form/Input.vue';
import Select from '@/Components/Form/Select.vue';

const props = defineProps({
        auth: { type: Object, required: true },
    visitasPagina: { type: Number, default: 0 },
usuario: Object,
    roles: Array,
    menuItems: Array,
    pageVisits: Number,
});

const form = useForm({
    nombre: props.usuario?.nombre || '',
    apellido: props.usuario?.apellido || '',
    email: props.usuario?.email || '',
    telefono: props.usuario?.telefono || '',
    password: '',
    password_confirmation: '',
    rol_id: props.usuario?.rol_id || '',
    estado: props.usuario?.estado ?? true,
    disponibilidad: props.usuario?.disponibilidad ?? true,
    _method: 'PATCH',
});

// Funci├│n route segura
const getRoute = (name, params = null) => {
    try {
        if (typeof window !== 'undefined' && window.route) {
            return params !== null ? window.route(name, params) : window.route(name);
        }
        if (typeof globalThis !== 'undefined' && globalThis.route) {
            return params !== null ? globalThis.route(name, params) : globalThis.route(name);
        }
        if (typeof route !== 'undefined') {
            return params !== null ? route(name, params) : route(name);
        }
        // Fallback
        const baseUrl = window.location.origin;
        if (name === 'usuarios.index') return `${baseUrl}/usuarios`;
        if (name === 'usuarios.update' && params) return `${baseUrl}/usuarios/${params}`;
        return '#';
    } catch (e) {
        console.warn('Error getting route:', e, name, params);
        const baseUrl = window.location.origin;
        if (name === 'usuarios.update' && params) return `${baseUrl}/usuarios/${params}`;
        return '#';
    }
};

const submit = () => {
    // Validar que la contrase├▒a coincida si se proporciona
    if (form.password && form.password !== form.password_confirmation) {
        alert('Las contrase├▒as no coinciden');
        return;
    }

    // Preparar los datos del formulario
    const formData = {
        nombre: form.nombre,
        apellido: form.apellido,
        email: form.email,
        telefono: form.telefono || null,
        rol_id: form.rol_id ? parseInt(form.rol_id) : form.rol_id,
        estado: form.estado ?? true,
        disponibilidad: form.disponibilidad ?? true,
        _method: 'PATCH',
    };

    // Solo agregar password si se proporciona
    if (form.password && form.password.length > 0) {
        formData.password = form.password;
        formData.password_confirmation = form.password_confirmation;
    }

    console.log('Enviando datos de actualizaci├│n:', {
        ...formData,
        password: formData.password ? '***' : '(no enviado)',
        password_confirmation: formData.password_confirmation ? '***' : '(no enviado)',
    });

    const routeUrl = `/usuarios/${props.usuario?.id}`;
    
    form.post(routeUrl, {
        preserveScroll: true,
        onSuccess: (page) => {
            console.log('Usuario actualizado exitosamente', page);
            router.visit('/usuarios');
        },
        onError: (errors) => {
            console.error('Errores al actualizar usuario:', errors);
            // Mostrar errores espec├¡ficos
            if (errors.email) {
                alert('Error: ' + errors.email);
            } else if (errors.password) {
                alert('Error: ' + errors.password);
            } else if (errors.rol_id) {
                alert('Error: ' + errors.rol_id);
            } else {
                alert('Error al actualizar usuario. Por favor, verifique los datos e intente nuevamente.');
            }
        },
        onFinish: () => {
            console.log('Request finished');
        },
    });
};

const cancelEdit = () => {
    router.visit(getRoute('usuarios.index'));
};
</script>


