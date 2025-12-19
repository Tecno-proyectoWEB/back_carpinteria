<template>
    <AppLayout :auth="auth" :menu-items="menuItems" :page-visits="pageVisits" :visitas-pagina="visitasPagina">
        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white shadow-lg rounded-xl p-6 border border-gray-200">
                    <div class="mb-6">
                        <h2 class="text-3xl font-bold bg-gradient-to-r from-indigo-600 to-blue-600 bg-clip-text text-transparent mb-2">
                            Editar Producto
                        </h2>
                        <p class="text-gray-600 text-sm">
                            {{ producto?.nombre || '' }} - {{ producto?.categoria?.nombre || '' }}
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

                        <Select
                            v-model="form.categoria_id"
                            label="Categor├¡a"
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
                                label="Stock M├¡nimo"
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
                                    class="h-32 w-32 object-cover rounded border border-gray-300"
                                />
                            </div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Nueva Imagen (opcional)
                            </label>
                            <input
                                type="file"
                                @change="handleImageChange"
                                accept="image/*"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            />
                            <p v-if="form.errors.imagen" class="mt-1 text-sm text-red-600">
                                {{ form.errors.imagen }}
                            </p>
                            <div v-if="imagePreview" class="mt-2">
                                <p class="text-sm text-gray-600 mb-1">Vista previa:</p>
                                <img :src="imagePreview" alt="Preview" class="h-32 w-32 object-cover rounded border border-gray-300" />
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
import { useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/Pages/Layout.vue';
import Input from '@/Components/Form/Input.vue';
import Textarea from '@/Components/Form/Textarea.vue';
import Select from '@/Components/Form/Select.vue';

const props = defineProps({
        auth: { type: Object, required: true },
    visitasPagina: { type: Number, default: 0 },
producto: Object,
    categorias: Array,
    menuItems: Array,
    pageVisits: Number,
});

const form = useForm({
    nombre: props.producto?.nombre || '',
    descripcion: props.producto?.descripcion || '',
    categoria_id: props.producto?.categoria_id || '',
    stock: props.producto?.stock || 0,
    stock_minimo: props.producto?.stock_minimo || 0,
    precio_unitario: props.producto?.precio_unitario || 0,
    imagen: null,
    _method: 'PATCH',
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
    // Preparar los datos del formulario
    const formData = {
        nombre: form.nombre,
        descripcion: form.descripcion || null,
        categoria_id: form.categoria_id ? parseInt(form.categoria_id) : form.categoria_id,
        stock: parseInt(form.stock) || 0,
        stock_minimo: parseInt(form.stock_minimo) || 0,
        precio_unitario: parseFloat(form.precio_unitario) || 0,
        _method: 'PATCH',
    };

    // Solo agregar imagen si se proporciona
    if (form.imagen) {
        formData.imagen = form.imagen;
    }

    console.log('Enviando datos de actualizaci├│n:', {
        ...formData,
        imagen: formData.imagen ? '(archivo)' : '(no enviado)',
    });

    const routeUrl = `/productos/${props.producto?.id}`;
    
    form.post(routeUrl, {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: (page) => {
            console.log('Producto actualizado exitosamente', page);
            router.visit('/productos');
        },
        onError: (errors) => {
            console.error('Errores al actualizar producto:', errors);
            if (errors.nombre) {
                alert('Error: ' + errors.nombre);
            } else if (errors.categoria_id) {
                alert('Error: ' + errors.categoria_id);
            } else if (errors.precio_unitario) {
                alert('Error: ' + errors.precio_unitario);
            } else {
                alert('Error al actualizar producto. Por favor, verifique los datos e intente nuevamente.');
            }
        },
        onFinish: () => {
            console.log('Request finished');
        },
    });
};

const cancelEdit = () => {
    router.visit('/productos');
};
</script>
