<template>
    <AppLayout>
        <div class="max-w-7xl mx-auto">
            <div class="max-w-3xl mx-auto">
                <div class="bg-white shadow-sm rounded-lg p-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">Editar Producto</h2>

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
                                Imagen Actual
                            </label>
                            <div v-if="producto?.imagen" class="mb-2">
                                <img
                                    :src="`/storage/${producto.imagen}`"
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

                        <div class="flex justify-end space-x-4 mt-6">
                            <Link
                                :href="getRoute('productos.index')"
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
                                <span v-else>Actualizar Producto</span>
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
import { useForm, Link } from '@inertiajs/vue3';
import { getRoute } from '@/utils/routeHelper';
import AppLayout from '@/Layouts/AppLayout.vue';
import Input from '@/Components/Form/Input.vue';
import Textarea from '@/Components/Form/Textarea.vue';
import Select from '@/Components/Form/Select.vue';

const props = defineProps({
    producto: {
        type: Object,
        default: () => ({}),
    },
    categorias: {
        type: Array,
        default: () => [],
    },
});

const form = useForm({
    nombre: props.producto?.nombre || '',
    descripcion: props.producto?.descripcion || '',
    categoria_id: props.producto?.categoria_id || '',
    stock: props.producto?.stock || 0,
    stock_minimo: props.producto?.stock_minimo || 0,
    precio_unitario: props.producto?.precio_unitario || 0,
    imagen: null,
    _method: 'PUT',
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
    if (!props.producto?.id) return;
    form.post(getRoute('productos.update', props.producto.id), {
        forceFormData: true,
    });
};
</script>

