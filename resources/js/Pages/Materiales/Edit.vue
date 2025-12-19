<template>
    <AppLayout :auth="auth" :menu-items="menuItems" :page-visits="pageVisits" :visitas-pagina="visitasPagina">
        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white shadow-sm rounded-lg p-6">
                    <div class="mb-6">
                        <h2 class="text-2xl font-bold text-gray-900 mb-2">Editar Material</h2>
                        <p v-if="materialData?.nombre" class="text-gray-600 text-sm">
                            {{ materialData.nombre }}
                        </p>
                        <p v-else class="text-yellow-600 text-sm">
                            ÔÜá´©Å Cargando informaci├│n del material...
                        </p>
                    </div>

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

                        <div class="grid grid-cols-2 gap-4">
                            <Select
                                v-model="form.categoria_id"
                                label="Categor├¡a"
                                :options="categorias"
                                option-value="id"
                                option-label="nombre"
                                required
                                :error="form.errors.categoria_id"
                            />

                            <Select
                                v-model="form.sector_id"
                                label="Sector/Almac├®n"
                                :options="sectores"
                                option-value="id"
                                option-label="nombre"
                                required
                                :error="form.errors.sector_id"
                            />
                        </div>

                        <div class="grid grid-cols-3 gap-4">
                            <Input
                                v-model.number="form.stock_actual"
                                label="Stock Actual"
                                type="number"
                                required
                                :error="form.errors.stock_actual"
                            />

                            <Input
                                v-model.number="form.stock_minimo"
                                label="Stock M├¡nimo"
                                type="number"
                                :error="form.errors.stock_minimo"
                            />

                            <Input
                                v-model.number="form.punto_reorden"
                                label="Punto de Reorden"
                                type="number"
                                :error="form.errors.punto_reorden"
                            />
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <Input
                                v-model.number="form.precio"
                                label="Precio"
                                type="number"
                                step="0.01"
                                :error="form.errors.precio"
                            />

                            <Input
                                v-model="form.unidad_medida"
                                label="Unidad de Medida"
                                :error="form.errors.unidad_medida"
                            />
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Imagen Actual
                            </label>
                            <div v-if="materialData?.imagen" class="mb-2">
                                <img
                                    :src="`/storage/${materialData.imagen}`"
                                    alt="Imagen actual"
                                    class="h-32 w-32 object-cover rounded"
                                />
                            </div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Nueva Imagen (opcional)
                            </label>
                            <input
                                type="file"
                                @change="handleImageChange"
                                accept="image/*"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md"
                            />
                            <p v-if="form.errors.imagen" class="mt-1 text-sm text-red-600">
                                {{ form.errors.imagen }}
                            </p>
                            <div v-if="imagePreview" class="mt-2">
                                <p class="text-sm text-gray-600 mb-1">Vista previa:</p>
                                <img :src="imagePreview" alt="Preview" class="h-32 w-32 object-cover rounded" />
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="flex items-center">
                                <input
                                    v-model="form.activo"
                                    type="checkbox"
                                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                />
                                <span class="ml-2 text-sm text-gray-700">Material activo</span>
                            </label>
                        </div>

                        <div class="flex justify-end space-x-4 mt-6">
                            <button
                                type="button"
                                @click="goBack"
                                class="px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50"
                            >
                                Cancelar
                            </button>
                            <button
                                type="submit"
                                :disabled="form.processing || !materialData?.id"
                                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50"
                            >
                                <span v-if="form.processing">Actualizando...</span>
                                <span v-else>Actualizar Material</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useForm, Link, router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Pages/Layout.vue';
import Input from '@/Components/Form/Input.vue';
import Textarea from '@/Components/Form/Textarea.vue';
import Select from '@/Components/Form/Select.vue';

const props = defineProps({
        auth: { type: Object, required: true },
    visitasPagina: { type: Number, default: 0 },
material: {
        type: Object,
        default: () => ({}),
    },
    categorias: {
        type: Array,
        default: () => [],
    },
    sectores: {
        type: Array,
        default: () => [],
    },
    menuItems: {
        type: Array,
        default: () => [],
    },
    pageVisits: {
        type: Number,
        default: 0,
    },
});

const page = usePage();

// Verificar que material existe
const materialData = computed(() => {
    const material = props.material;
    console.log('Material recibido en props:', material);
    console.log('Tipo de material:', typeof material);
    console.log('Es objeto?:', material && typeof material === 'object');
    console.log('Tiene id?:', material?.id);
    console.log('Tiene nombre?:', material?.nombre);
    console.log('Keys del material:', material ? Object.keys(material) : 'no material');
    return material || {};
});

// Inicializar formulario con valores por defecto
const form = useForm({
    nombre: '',
    descripcion: '',
    categoria_id: '',
    sector_id: '',
    stock_actual: 0,
    stock_minimo: 0,
    punto_reorden: 0,
    precio: 0,
    unidad_medida: '',
    imagen: null,
    activo: true,
    _method: 'PATCH',
});

// Actualizar form cuando material est├® disponible
onMounted(() => {
    console.log('Componente montado, material:', materialData.value);
    if (materialData.value && Object.keys(materialData.value).length > 0) {
        form.nombre = materialData.value.nombre || '';
        form.descripcion = materialData.value.descripcion || '';
        form.categoria_id = materialData.value.categoria_id || '';
        form.sector_id = materialData.value.sector_id || '';
        form.stock_actual = materialData.value.stock_actual || 0;
        form.stock_minimo = materialData.value.stock_minimo || 0;
        form.punto_reorden = materialData.value.punto_reorden || 0;
        form.precio = materialData.value.precio || 0;
        form.unidad_medida = materialData.value.unidad_medida || '';
        // Manejar activo correctamente
        const activoValue = materialData.value.activo;
        if (typeof activoValue === 'boolean') {
            form.activo = activoValue;
        } else if (activoValue === 'true' || activoValue === '1' || activoValue === 1) {
            form.activo = true;
        } else if (activoValue === 'false' || activoValue === '0' || activoValue === 0) {
            form.activo = false;
        } else {
            form.activo = activoValue ?? true;
        }
        console.log('Formulario inicializado con:', {
            nombre: form.nombre,
            categoria_id: form.categoria_id,
            sector_id: form.sector_id,
        });
    } else {
        console.warn('Material no disponible al montar el componente');
    }
});

// Watch para actualizar cuando cambien los props
watch(() => props.material, (newMaterial) => {
    if (newMaterial && Object.keys(newMaterial).length > 0) {
        console.log('Material actualizado, sincronizando formulario:', newMaterial);
        form.nombre = newMaterial.nombre || '';
        form.descripcion = newMaterial.descripcion || '';
        form.categoria_id = newMaterial.categoria_id || '';
        form.sector_id = newMaterial.sector_id || '';
        form.stock_actual = newMaterial.stock_actual || 0;
        form.stock_minimo = newMaterial.stock_minimo || 0;
        form.punto_reorden = newMaterial.punto_reorden || 0;
        form.precio = newMaterial.precio || 0;
        form.unidad_medida = newMaterial.unidad_medida || '';
        // Manejar activo correctamente
        const activoValue = newMaterial.activo;
        if (typeof activoValue === 'boolean') {
            form.activo = activoValue;
        } else if (activoValue === 'true' || activoValue === '1' || activoValue === 1) {
            form.activo = true;
        } else if (activoValue === 'false' || activoValue === '0' || activoValue === 0) {
            form.activo = false;
        } else {
            form.activo = activoValue ?? true;
        }
        console.log('Activo sincronizado en watch:', {
            original: activoValue,
            final: form.activo,
            type: typeof activoValue,
        });
    }
}, { immediate: true, deep: true });

const imagePreview = ref(null);

const handleImageChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        form.imagen = file;
        const reader = new FileReader();
        reader.onload = (e) => {
            imagePreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};

const getRoute = (name, params = null) => {
    try {
        if (window.route && typeof window.route === 'function') {
            return params ? window.route(name, params) : window.route(name);
        }
    } catch (e) {
        console.warn('route function not available:', e);
    }
    // Fallback a URLs directas
    if (name === 'materiales.index') return '/materiales';
    if (name === 'materiales.edit' && params) return `/materiales/${params}/edit`;
    return '#';
};

const goBack = () => {
    try {
        const routeUrl = getRoute('materiales.index');
        if (routeUrl && routeUrl !== '#') {
            router.visit(routeUrl);
        } else {
            router.visit('/materiales');
        }
    } catch (e) {
        console.error('Error al volver:', e);
        router.visit('/materiales');
    }
};

const submit = () => {
    if (!materialData.value?.id) {
        console.error('Material ID no disponible');
        alert('Error: No se pudo identificar el material a actualizar');
        return;
    }

    const formData = {
        nombre: form.nombre,
        descripcion: form.descripcion || null,
        categoria_id: parseInt(form.categoria_id),
        sector_id: parseInt(form.sector_id),
        stock_actual: parseInt(form.stock_actual) || 0,
        stock_minimo: parseInt(form.stock_minimo) || 0,
        punto_reorden: parseInt(form.punto_reorden) || 0,
        precio: parseFloat(form.precio) || 0,
        unidad_medida: form.unidad_medida || null,
        activo: form.activo === true || form.activo === 'true' || form.activo === 1 || form.activo === '1',
        _method: 'PATCH',
    };
    
    if (form.imagen) {
        formData.imagen = form.imagen;
    }
    
    console.log('Enviando datos:', formData);
    console.log('Material ID:', materialData.value.id);
    
    form.transform(() => formData).post(`/materiales/${materialData.value.id}`, {
        forceFormData: true,
        preserveScroll: true,
        onError: (errors) => {
            console.error('Errores de validaci├│n:', errors);
        },
        onSuccess: () => {
            console.log('Material actualizado exitosamente');
        },
    });
};
</script>
