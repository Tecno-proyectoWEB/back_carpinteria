<template>
    <AppLayout :menu-items="menuItems" :page-visits="pageVisits">
        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white shadow-sm rounded-lg p-6">
                    <div class="mb-6">
                        <h2 class="text-2xl font-bold text-gray-900">Editar Permisos del Rol: {{ rol.nombre }}</h2>
                        <p class="text-gray-600 mt-1">Seleccione los permisos que desea asignar a este rol</p>
                    </div>

                    <form @submit.prevent="submit">
                        <!-- Agrupar permisos por módulo -->
                        <div class="space-y-6">
                            <div
                                v-for="(permisosGrupo, modulo) in permisosAgrupados"
                                :key="modulo"
                                class="border rounded-lg p-4"
                            >
                                <h3 class="text-lg font-semibold mb-3 flex items-center">
                                    <input
                                        type="checkbox"
                                        :checked="todosSeleccionados(modulo)"
                                        :indeterminate="algunosSeleccionados(modulo)"
                                        @change="toggleModulo(modulo, $event.target.checked)"
                                        class="mr-2 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                    />
                                    {{ modulo }}
                                </h3>
                                <div class="grid grid-cols-2 md:grid-cols-3 gap-3 ml-6">
                                    <label
                                        v-for="permiso in permisosGrupo"
                                        :key="permiso.id"
                                        class="flex items-center space-x-2 cursor-pointer hover:bg-gray-50 p-2 rounded"
                                    >
                                        <input
                                            type="checkbox"
                                            :value="permiso.id"
                                            v-model="form.permisos"
                                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                        />
                                        <span class="text-sm text-gray-700">{{ permiso.nombre }}</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Resumen -->
                        <div class="mt-6 p-4 bg-blue-50 rounded-lg">
                            <p class="text-sm text-blue-800">
                                <strong>Permisos seleccionados:</strong> {{ form.permisos.length }} de {{ permisos.length }}
                            </p>
                        </div>

                        <div class="flex justify-end space-x-4 mt-6">
                            <Link
                                :href="route('roles.index')"
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
                                <span v-else>Guardar Permisos</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    rol: Object,
    permisos: Array,
    menuItems: Array,
    pageVisits: Number,
});

const form = useForm({
    permisos: props.rol.permisos?.map(p => p.id) || [],
});

// Agrupar permisos por módulo (asumiendo formato: modulo.accion)
const permisosAgrupados = computed(() => {
    const grupos = {};
    props.permisos.forEach(permiso => {
        const partes = permiso.nombre.split('.');
        const modulo = partes[0] || 'Otros';
        if (!grupos[modulo]) {
            grupos[modulo] = [];
        }
        grupos[modulo].push(permiso);
    });
    return grupos;
});

const todosSeleccionados = (modulo) => {
    const permisosModulo = permisosAgrupados.value[modulo];
    return permisosModulo.every(p => form.permisos.includes(p.id));
};

const algunosSeleccionados = (modulo) => {
    const permisosModulo = permisosAgrupados.value[modulo];
    const seleccionados = permisosModulo.filter(p => form.permisos.includes(p.id));
    return seleccionados.length > 0 && seleccionados.length < permisosModulo.length;
};

const toggleModulo = (modulo, checked) => {
    const permisosModulo = permisosAgrupados.value[modulo];
    if (checked) {
        permisosModulo.forEach(p => {
            if (!form.permisos.includes(p.id)) {
                form.permisos.push(p.id);
            }
        });
    } else {
        form.permisos = form.permisos.filter(id => !permisosModulo.some(p => p.id === id));
    }
};

const submit = () => {
    form.put(route('roles.update', props.rol.id));
};
</script>


