<template>
    <AppLayout :menu-items="menuItems" :page-visits="pageVisits">
        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white shadow-lg rounded-xl p-6 border border-gray-200">
                    <div class="mb-6">
                        <h2 class="text-3xl font-bold bg-gradient-to-r from-indigo-600 to-blue-600 bg-clip-text text-transparent mb-2">
                            Crear Nuevo Producto
                        </h2>
                        <p class="text-gray-600 text-sm">Complete el formulario para agregar un nuevo producto al catálogo</p>
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
                            label="Descripción"
                            :error="form.errors.descripcion"
                            :rows="4"
                        />

                        <Select
                            v-model="form.categoria_id"
                            label="Categoría"
                            :options="categorias"
                            option-value="id"
                            option-label="nombre"
                            required
                            :error="form.errors.categoria_id"
                        />

                        <div class="grid grid-cols-2 gap-4">
                            <Input
                                v-model.number="form.stock"
                                label="Stock"
                                type="number"
                                required
                                :error="form.errors.stock"
                            />

                            <Input
                                v-model.number="form.stock_minimo"
                                label="Stock Mínimo"
                                type="number"
                                :error="form.errors.stock_minimo"
                            />
                        </div>

                        <Input
                            v-model.number="form.precio_unitario"
                            label="Precio Unitario"
                            type="number"
                            step="0.01"
                            required
                            :error="form.errors.precio_unitario"
                        />

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Imagen
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
                                <img :src="imagePreview" alt="Preview" class="h-32 w-32 object-cover rounded" />
                            </div>
                        </div>

                        <div class="flex justify-end space-x-4 mt-6">
                            <button
                                type="button"
                                @click="cancelCreate"
                                class="px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50 text-gray-700 font-medium"
                            >
                                Cancelar
                            </button>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="px-4 py-2 bg-gradient-to-r from-indigo-600 to-blue-600 text-white rounded-md hover:from-indigo-700 hover:to-blue-700 disabled:opacity-50 font-medium shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200"
                            >
                                <span v-if="form.processing">Guardando...</span>
                                <span v-else>Guardar Producto</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Input from '@/Components/Form/Input.vue';
import Textarea from '@/Components/Form/Textarea.vue';
import Select from '@/Components/Form/Select.vue';

defineProps({
    categorias: Array,
    menuItems: Array,
    pageVisits: Number,
});

const form = useForm({
    nombre: '',
    descripcion: '',
    categoria_id: '',
    stock: 0,
    stock_minimo: 0,
    precio_unitario: 0,
    imagen: null,
});

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

const submit = () => {
    // Validar campos requeridos
    if (!form.nombre || !form.categoria_id || form.stock === null || form.precio_unitario === null) {
        alert('Por favor, complete todos los campos requeridos');
        return;
    }

    // Preparar datos
    const formData = {
        nombre: form.nombre,
        descripcion: form.descripcion || null,
        categoria_id: parseInt(form.categoria_id),
        stock: parseInt(form.stock) || 0,
        stock_minimo: parseInt(form.stock_minimo) || 0,
        precio_unitario: parseFloat(form.precio_unitario) || 0,
    };

    if (form.imagen) {
        formData.imagen = form.imagen;
    }

    console.log('Enviando datos de creación:', {
        ...formData,
        imagen: formData.imagen ? '(archivo)' : '(no enviado)',
    });

    form.post('/productos', {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: (page) => {
            console.log('Producto creado exitosamente', page);
            router.visit('/productos');
        },
        onError: (errors) => {
            console.error('Errores al crear producto:', errors);
            if (errors.nombre) {
                alert('Error: ' + errors.nombre);
            } else if (errors.categoria_id) {
                alert('Error: ' + errors.categoria_id);
            } else if (errors.precio_unitario) {
                alert('Error: ' + errors.precio_unitario);
            } else {
                alert('Error al crear producto. Por favor, verifique los datos e intente nuevamente.');
            }
        },
        onFinish: () => {
            console.log('Request finished');
        },
    });
};

const cancelCreate = () => {
    router.visit('/productos');
};
</script>

