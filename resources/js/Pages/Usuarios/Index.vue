<template>
    <AppLayout :auth="auth" :menu-items="menuItems" :page-visits="pageVisits" :visitas-pagina="visitasPagina">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h2 class="text-3xl font-bold bg-gradient-to-r from-indigo-600 to-blue-600 bg-clip-text text-transparent">Usuarios</h2>
                        <p class="text-gray-600 mt-1">Gestione los usuarios del sistema</p>
                    </div>
                    <button
                        @click="showCreateModal = true"
                        class="px-5 py-2.5 bg-gradient-to-r from-indigo-600 to-blue-600 text-white rounded-lg hover:from-indigo-700 hover:to-blue-700 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200 font-medium flex items-center space-x-2"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>Nuevo Usuario</span>
                    </button>
                </div>

                <!-- Modal para Crear Usuario -->
                <Modal
                    :show="showCreateModal"
                    title="Crear Nuevo Usuario"
                    max-width="sm:max-w-2xl"
                    @update:show="showCreateModal = $event"
                    @close="showCreateModal = false"
                >
                    <form @submit.prevent="submitCreate">
                        <div class="grid grid-cols-2 gap-4">
                            <Input
                                v-model="createForm.nombre"
                                label="Nombre"
                                required
                                :error="createForm.errors.nombre"
                            />

                            <Input
                                v-model="createForm.apellido"
                                label="Apellido"
                                required
                                :error="createForm.errors.apellido"
                            />
                        </div>

                        <Input
                            v-model="createForm.email"
                            label="Email"
                            type="email"
                            required
                            :error="createForm.errors.email"
                        />

                        <Input
                            v-model="createForm.telefono"
                            label="Tel├®fono"
                            type="tel"
                            :error="createForm.errors.telefono"
                        />

                        <div class="grid grid-cols-2 gap-4">
                            <Input
                                v-model="createForm.password"
                                label="Contrase├▒a"
                                type="password"
                                required
                                :error="createForm.errors.password"
                            />

                            <Input
                                v-model="createForm.password_confirmation"
                                label="Confirmar Contrase├▒a"
                                type="password"
                                required
                                :error="createForm.errors.password_confirmation"
                            />
                        </div>

                        <Select
                            v-model="createForm.rol_id"
                            label="Rol"
                            :options="roles"
                            option-value="id"
                            option-label="nombre"
                            required
                            :error="createForm.errors.rol_id"
                        />

                        <div class="grid grid-cols-2 gap-4">
                            <div class="mb-4">
                                <label class="flex items-center">
                                    <input
                                        v-model="createForm.estado"
                                        type="checkbox"
                                        class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                    />
                                    <span class="ml-2 text-sm text-gray-700">Usuario activo</span>
                                </label>
                            </div>

                            <div class="mb-4">
                                <label class="flex items-center">
                                    <input
                                        v-model="createForm.disponibilidad"
                                        type="checkbox"
                                        class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                    />
                                    <span class="ml-2 text-sm text-gray-700">Disponible</span>
                                </label>
                            </div>
                        </div>
                    </form>

                    <template #footer>
                        <button
                            type="button"
                            @click="showCreateModal = false"
                            class="px-5 py-2.5 border border-indigo-200 rounded-lg hover:bg-indigo-50 text-indigo-700 transition-colors"
                        >
                            Cancelar
                        </button>
                        <button
                            type="button"
                            @click="submitCreate"
                            :disabled="createForm.processing"
                            class="px-5 py-2.5 bg-gradient-to-r from-indigo-600 to-blue-600 text-white rounded-lg hover:from-indigo-700 hover:to-blue-700 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200 disabled:opacity-50 disabled:transform-none"
                        >
                            <span v-if="createForm.processing">Guardando...</span>
                            <span v-else>Guardar Usuario</span>
                        </button>
                    </template>
                </Modal>

                <!-- Filtros -->
                <div class="mb-4 bg-white/80 backdrop-blur-sm p-4 rounded-xl shadow-lg border border-indigo-100">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
                            <input
                                v-model="filters.search"
                                type="text"
                                placeholder="Nombre, apellido o email..."
                                class="w-full px-3 py-2 border border-gray-300 rounded-md"
                                @input="applyFilters"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Rol</label>
                            <select
                                v-model="filters.rol_id"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md"
                                @change="applyFilters"
                            >
                                <option value="">Todos</option>
                                <option v-for="rol in roles" :key="rol.id" :value="rol.id">
                                    {{ rol.nombre }}
                                </option>
                            </select>
                        </div>
                        <div class="flex items-end space-x-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                            <select
                                v-model="filters.estado"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md"
                                @change="applyFilters"
                            >
                                <option value="">Todos</option>
                                <option value="activo">Activo</option>
                                <option value="inactivo">Inactivo</option>
                            </select>
                            <button
                                @click="clearFilters"
                                class="px-4 py-2 border border-indigo-200 rounded-lg hover:bg-indigo-50 text-indigo-700 transition-colors"
                            >
                                Limpiar
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Tabla -->
                <DataTable
                    v-if="usuarios && usuarios.data"
                    :data="usuarios.data || []"
                    :columns="columns"
                    :loading="false"
                    :show-search="false"
                    :paginated="false"
                    :on-edit="editUsuario"
                    :on-delete="deleteUsuario"
                >
                    <template #cell-nombre_completo="{ row }">
                        <template v-if="row && typeof row === 'object'">
                            {{ (row.nombre || '') }} {{ (row.apellido || '') }}
                        </template>
                        <span v-else class="text-gray-400">-</span>
                    </template>
                    <template #cell-rol="{ value, row }">
                        <template v-if="value && typeof value === 'object' && value.nombre">
                            <Badge variant="info">{{ value.nombre }}</Badge>
                        </template>
                        <template v-else-if="row && row.rol && typeof row.rol === 'object' && row.rol.nombre">
                            <Badge variant="info">{{ row.rol.nombre }}</Badge>
                        </template>
                        <Badge v-else variant="warning">Sin rol</Badge>
                    </template>
                    <template #cell-estado="{ row }">
                        <template v-if="row && typeof row === 'object'">
                            <Badge :variant="row.estado ? 'success' : 'error'">
                                {{ row.estado ? 'Activo' : 'Inactivo' }}
                            </Badge>
                        </template>
                        <Badge v-else variant="warning">-</Badge>
                    </template>
                    <template #cell-disponibilidad="{ row }">
                        <template v-if="row && typeof row === 'object'">
                            <Badge :variant="row.disponibilidad ? 'success' : 'warning'">
                                {{ row.disponibilidad ? 'Disponible' : 'No disponible' }}
                            </Badge>
                        </template>
                        <Badge v-else variant="warning">-</Badge>
                    </template>
                    <template #actions="{ row }">
                        <template v-if="row && typeof row === 'object' && row.id">
                            <div class="flex items-center space-x-2">
                                <button
                                    @click="() => viewUsuario(row)"
                                    class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-blue-700 bg-blue-50 rounded-md hover:bg-blue-100 transition-colors"
                                    title="Ver detalles"
                                >
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    Ver
                                </button>
                                <button
                                    @click="() => editUsuario(row)"
                                    class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-indigo-700 bg-indigo-50 rounded-md hover:bg-indigo-100 transition-colors"
                                    title="Editar usuario"
                                >
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                    Editar
                                </button>
                                <button
                                    v-if="row.id !== currentUserId"
                                    @click="() => safeDeleteUsuario(row)"
                                    class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-red-700 bg-red-50 rounded-md hover:bg-red-100 transition-colors"
                                    title="Eliminar usuario"
                                >
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    Eliminar
                                </button>
                            </div>
                        </template>
                    </template>
                </DataTable>

                <!-- Paginaci├│n -->
                <div v-if="usuarios.links" class="mt-4">
                    <div class="flex justify-center">
                        <div v-for="link in usuarios.links" :key="link.label">
                            <Link
                                v-if="link.url"
                                :href="link.url"
                                v-html="link.label"
                                :class="[
                                    'px-3 py-2 border rounded-md mx-1',
                                    link.active ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50'
                                ]"
                            ></Link>
                            <span
                                v-else
                                v-html="link.label"
                                class="px-3 py-2 border rounded-md mx-1 bg-gray-100 text-gray-400"
                            ></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { Link, router, usePage, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Pages/Layout.vue';
import DataTable from '@/Components/Table/DataTable.vue';
import Badge from '@/Components/UI/Badge.vue';
import Modal from '@/Components/UI/Modal.vue';
import Input from '@/Components/Form/Input.vue';
import Select from '@/Components/Form/Select.vue';

// Funci├│n route segura usando window.route (Ziggy global)
const getRoute = (name, params = null) => {
    try {
        // Intentar diferentes formas de acceder a route
        if (typeof window !== 'undefined' && window.route) {
            return params !== null ? window.route(name, params) : window.route(name);
        }
        // Intentar con globalThis
        if (typeof globalThis !== 'undefined' && globalThis.route) {
            return params !== null ? globalThis.route(name, params) : globalThis.route(name);
        }
        // Intentar acceder directamente (si est├í en el scope global)
        if (typeof route !== 'undefined') {
            return params !== null ? route(name, params) : route(name);
        }
        // Fallback: construir la URL manualmente
        console.warn('route function not available, using fallback');
        const baseUrl = window.location.origin;
        if (name === 'usuarios.index') return `${baseUrl}/usuarios`;
        if (name === 'usuarios.show' && params) return `${baseUrl}/usuarios/${params}`;
        if (name === 'usuarios.edit' && params) return `${baseUrl}/usuarios/${params}/edit`;
        if (name === 'usuarios.destroy' && params) return `${baseUrl}/usuarios/${params}`;
        return `${baseUrl}/usuarios`;
    } catch (e) {
        console.warn('Error getting route:', e, name, params);
        const baseUrl = window.location.origin;
        if (name === 'usuarios.show' && params) return `${baseUrl}/usuarios/${params}`;
        if (name === 'usuarios.edit' && params) return `${baseUrl}/usuarios/${params}/edit`;
        return `${baseUrl}/usuarios`;
    }
};

// Funci├│n para ver usuario
const viewUsuario = (usuario) => {
    if (!usuario || !usuario.id) {
        console.error('Usuario inv├ílido para ver:', usuario);
        return;
    }
    try {
        const routeUrl = getRoute('usuarios.show', usuario.id);
        if (routeUrl && routeUrl !== '#') {
            router.visit(routeUrl);
        } else {
            // Fallback directo
            router.visit(`/usuarios/${usuario.id}`);
        }
    } catch (e) {
        console.error('Error al ver usuario:', e);
        router.visit(`/usuarios/${usuario.id}`);
    }
};

const props = defineProps({
    auth: {
        type: Object,
        required: true,
    },
    visitasPagina: {
        type: Number,
        default: 0,
    },
    usuarios: Object,
    roles: Array,
    menuItems: Array,
    pageVisits: Number,
    filters: Object,
});

const page = usePage();

const columns = [
    { key: 'id', label: 'ID', sortable: true },
    { key: 'nombre_completo', label: 'Nombre Completo', sortable: false },
    { key: 'email', label: 'Email', sortable: true },
    { key: 'telefono', label: 'Tel├®fono', sortable: false },
    { key: 'rol', label: 'Rol', sortable: false },
    { key: 'estado', label: 'Estado', sortable: true },
    { key: 'disponibilidad', label: 'Disponibilidad', sortable: false },

];

const filters = ref({
    search: props.filters?.search || '',
    rol_id: props.filters?.rol_id || '',
    estado: props.filters?.estado || '',
});

const showCreateModal = ref(false);

const createForm = useForm({
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

const submitCreate = () => {
    // Validar que todos los campos requeridos est├®n llenos
    if (!createForm.nombre || !createForm.apellido || !createForm.email || !createForm.password || !createForm.rol_id) {
        alert('Por favor, complete todos los campos requeridos');
        return;
    }

    // Convertir rol_id a n├║mero si es string
    if (typeof createForm.rol_id === 'string') {
        createForm.rol_id = parseInt(createForm.rol_id);
    }

    // Validar que la contrase├▒a tenga al menos 8 caracteres
    if (createForm.password.length < 8) {
        alert('La contrase├▒a debe tener al menos 8 caracteres');
        return;
    }

    // Validar que las contrase├▒as coincidan
    if (createForm.password !== createForm.password_confirmation) {
        alert('Las contrase├▒as no coinciden');
        return;
    }

    console.log('Enviando formulario a: /usuarios');
    console.log('Datos del formulario:', {
        nombre: createForm.nombre,
        apellido: createForm.apellido,
        email: createForm.email,
        telefono: createForm.telefono || null,
        password: '***',
        password_confirmation: '***',
        rol_id: createForm.rol_id,
        estado: createForm.estado,
        disponibilidad: createForm.disponibilidad,
    });
    
    createForm.post('/usuarios', {
        preserveScroll: true,
        onSuccess: (page) => {
            console.log('Usuario creado exitosamente', page);
            showCreateModal.value = false;
            createForm.reset();
            createForm.clearErrors();
            // Recargar la p├ígina para mostrar el nuevo usuario
            router.reload({ only: ['usuarios'] });
        },
        onError: (errors) => {
            console.error('Errores al crear usuario:', errors);
            // Mostrar errores de validaci├│n
            if (errors.email) {
                alert('Error: ' + errors.email);
            } else if (errors.password) {
                alert('Error: ' + errors.password);
            } else if (errors.rol_id) {
                alert('Error: ' + errors.rol_id);
            } else {
                alert('Error al crear usuario. Por favor, verifique los datos e intente nuevamente.');
            }
        },
        onFinish: () => {
            console.log('Request finished');
        },
    });
};

const currentUserId = computed(() => {
    return page.props.auth?.user?.id || null;
});

const canCreate = computed(() => {
    try {
        const rol = page.props.auth?.user?.rol?.nombre;
        // Permitir crear usuarios a PROPIETARIO, ADMINISTRADOR y SECRETARIA
        return ['PROPIETARIO', 'ADMINISTRADOR', 'SECRETARIA'].includes(rol);
    } catch (e) {
        console.error('Error checking canCreate:', e);
        return false;
    }
});

const canEdit = computed(() => {
    try {
        const rol = page.props.auth?.user?.rol?.nombre;
        // Permitir editar usuarios a PROPIETARIO, ADMINISTRADOR y SECRETARIA
        const can = ['PROPIETARIO', 'ADMINISTRADOR', 'SECRETARIA'].includes(rol);
        console.log('canEdit check:', { rol, can });
        return can;
    } catch (e) {
        console.error('Error checking canEdit:', e);
        return true; // Temporalmente true para debug
    }
});

const canDelete = computed(() => {
    try {
        const rol = page.props.auth?.user?.rol?.nombre;
        // Permitir eliminar usuarios a PROPIETARIO y ADMINISTRADOR
        const can = ['PROPIETARIO', 'ADMINISTRADOR'].includes(rol);
        console.log('canDelete check:', { rol, can });
        return can;
    } catch (e) {
        console.error('Error checking canDelete:', e);
        return true; // Temporalmente true para debug
    }
});

// Debug: mostrar informaci├│n del usuario actual
onMounted(() => {
    console.log('Usuario actual:', {
        id: currentUserId.value,
        rol: page.props.auth?.user?.rol?.nombre,
        canEdit: canEdit.value,
        canDelete: canDelete.value,
    });
});

const applyFilters = () => {
    router.get(getRoute('usuarios.index'), filters.value, {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    filters.value = { search: '', rol_id: '', estado: '' };
    applyFilters();
};

const deleteUsuario = (usuario) => {
    if (!usuario || !usuario.id) {
        console.error('Usuario inv├ílido para eliminar:', usuario);
        return;
    }
    const nombreCompleto = `${usuario.nombre || ''} ${usuario.apellido || ''}`.trim() || 'este usuario';
    if (confirm(`┬┐Est├í seguro de eliminar al usuario "${nombreCompleto}"?`)) {
        try {
            const routeUrl = getRoute('usuarios.destroy', usuario.id);
            if (routeUrl && routeUrl !== '#') {
                router.delete(routeUrl, {
                    preserveScroll: true,
                    onSuccess: () => {
                        // Recargar la lista
                        router.reload({ only: ['usuarios'] });
                    },
                });
            } else {
                // Fallback directo
                router.delete(`/usuarios/${usuario.id}`, {
                    preserveScroll: true,
                    onSuccess: () => {
                        router.reload({ only: ['usuarios'] });
                    },
                });
            }
        } catch (e) {
            console.error('Error al eliminar usuario:', e);
            router.delete(`/usuarios/${usuario.id}`, {
                preserveScroll: true,
                onSuccess: () => {
                    router.reload({ only: ['usuarios'] });
                },
            });
        }
    }
};

const editUsuario = (usuario) => {
    if (!usuario || !usuario.id) {
        console.error('Usuario inv├ílido para editar:', usuario);
        return;
    }
    try {
        const routeUrl = getRoute('usuarios.edit', usuario.id);
        if (routeUrl && routeUrl !== '#') {
            router.visit(routeUrl);
        } else {
            // Fallback directo
            router.visit(`/usuarios/${usuario.id}/edit`);
        }
    } catch (e) {
        console.error('Error al editar usuario:', e);
        router.visit(`/usuarios/${usuario.id}/edit`);
    }
};

const safeDeleteUsuario = (usuario) => {
    try {
        deleteUsuario(usuario);
    } catch (e) {
        console.error('Error deleting usuario:', e);
    }
};
</script>


