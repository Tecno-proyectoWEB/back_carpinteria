<template>
    <AppLayout :menu-items="menuItems" :page-visits="pageVisits">
        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white shadow-sm rounded-lg p-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">Registrar Movimiento de Inventario</h2>

                    <form @submit.prevent="submit">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de Movimiento *</label>
                            <div class="flex space-x-4">
                                <label class="flex items-center">
                                    <input
                                        v-model="form.tipo"
                                        type="radio"
                                        value="INGRESO"
                                        class="text-blue-600 focus:ring-blue-500"
                                        required
                                    />
                                    <span class="ml-2 text-sm text-gray-700">Ingreso</span>
                                </label>
                                <label class="flex items-center">
                                    <input
                                        v-model="form.tipo"
                                        type="radio"
                                        value="SALIDA"
                                        class="text-blue-600 focus:ring-blue-500"
                                        required
                                    />
                                    <span class="ml-2 text-sm text-gray-700">Salida</span>
                                </label>
                            </div>
                            <span v-if="form.errors.tipo" class="text-red-600 text-sm">{{ form.errors.tipo }}</span>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Seleccionar Item *</label>
                            <div class="flex space-x-4 mb-2">
                                <label class="flex items-center">
                                    <input
                                        v-model="itemType"
                                        type="radio"
                                        value="material"
                                        class="text-blue-600 focus:ring-blue-500"
                                    />
                                    <span class="ml-2 text-sm text-gray-700">Material</span>
                                </label>
                                <label class="flex items-center">
                                    <input
                                        v-model="itemType"
                                        type="radio"
                                        value="producto"
                                        class="text-blue-600 focus:ring-blue-500"
                                    />
                                    <span class="ml-2 text-sm text-gray-700">Producto</span>
                                </label>
                            </div>

                            <Select
                                v-if="itemType === 'material'"
                                v-model="form.material_id"
                                label="Material"
                                :options="materiales"
                                option-value="id"
                                option-label="nombre"
                                required
                                :error="form.errors.material_id"
                                @change="onMaterialChange"
                            />

                            <Select
                                v-if="itemType === 'producto'"
                                v-model="form.producto_id"
                                label="Producto"
                                :options="productos"
                                option-value="id"
                                option-label="nombre"
                                required
                                :error="form.errors.producto_id"
                                @change="onProductoChange"
                            />
                        </div>

                        <div v-if="selectedItem" class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-md">
                            <p class="text-sm text-blue-800">
                                <strong>Stock actual:</strong> {{ selectedItem.stock || selectedItem.stock_actual }}
                                <span v-if="selectedItem.stock_minimo" class="ml-4">
                                    <strong>Stock mínimo:</strong> {{ selectedItem.stock_minimo }}
                                </span>
                            </p>
                            <p v-if="form.tipo === 'SALIDA' && selectedItem && (selectedItem.stock || selectedItem.stock_actual) < form.cantidad" class="text-red-600 text-sm mt-2">
                                ⚠️ La cantidad a retirar excede el stock disponible
                            </p>
                        </div>

                        <Input
                            v-model.number="form.cantidad"
                            label="Cantidad"
                            type="number"
                            min="1"
                            required
                            :error="form.errors.cantidad"
                        />

                        <Input
                            v-model="form.motivo"
                            label="Motivo"
                            placeholder="Ej: Ajuste de inventario, Corrección, etc."
                            :error="form.errors.motivo"
                        />

                        <Textarea
                            v-model="form.observaciones"
                            label="Observaciones"
                            :error="form.errors.observaciones"
                        />

                        <div class="flex justify-end space-x-4 mt-6">
                            <Link
                                :href="route('inventario.index')"
                                class="px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50"
                            >
                                Cancelar
                            </Link>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50"
                            >
                                <span v-if="form.processing">Registrando...</span>
                                <span v-else>Registrar Movimiento</span>
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
import Input from '@/Components/Form/Input.vue';
import Select from '@/Components/Form/Select.vue';
import Textarea from '@/Components/Form/Textarea.vue';

const props = defineProps({
    materiales: Array,
    productos: Array,
    menuItems: Array,
    pageVisits: Number,
});

const itemType = ref('material');

const form = useForm({
    tipo: 'INGRESO',
    cantidad: 1,
    motivo: '',
    observaciones: '',
    material_id: null,
    producto_id: null,
});

const selectedItem = computed(() => {
    if (itemType.value === 'material' && form.material_id) {
        return props.materiales.find(m => m.id === form.material_id);
    } else if (itemType.value === 'producto' && form.producto_id) {
        return props.productos.find(p => p.id === form.producto_id);
    }
    return null;
});

const onMaterialChange = () => {
    form.producto_id = null;
};

const onProductoChange = () => {
    form.material_id = null;
};

const submit = () => {
    form.post(route('inventario.store'));
};
</script>

