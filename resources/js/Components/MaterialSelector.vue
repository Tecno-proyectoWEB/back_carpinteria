<template>
    <div class="material-selector">
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Agregar Material</label>
            <div class="flex space-x-2">
                <select
                    v-model="selectedMaterial"
                    class="flex-1 px-3 py-2 border border-gray-300 rounded-md"
                >
                    <option value="">Seleccione un material</option>
                    <option
                        v-for="material in materiales"
                        :key="material.id"
                        :value="material.id"
                        :disabled="!material.activo"
                    >
                        {{ material.nombre }} - Stock: {{ material.stock_actual }} {{ material.unidad_medida }} - ${{ material.precio || 'N/A' }}
                    </option>
                </select>
                <Input
                    v-model.number="cantidad"
                    type="number"
                    placeholder="Cantidad"
                    class="w-24"
                />
                <Input
                    v-model.number="precio"
                    type="number"
                    step="0.01"
                    placeholder="Precio"
                    class="w-32"
                />
                <button
                    @click="agregarItem"
                    :disabled="!puedeAgregar"
                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50"
                >
                    Agregar
                </button>
            </div>
        </div>

        <!-- Lista de items agregados -->
        <div v-if="items.length > 0" class="mt-4">
            <h3 class="text-sm font-medium text-gray-700 mb-2">Materiales de la Compra</h3>
            <div class="border rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Material</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Cantidad</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Precio Unit.</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Total</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="(item, index) in items" :key="index">
                            <td class="px-4 py-2 text-sm">{{ item.nombre }}</td>
                            <td class="px-4 py-2 text-sm">{{ item.cantidad }} {{ item.unidad_medida }}</td>
                            <td class="px-4 py-2 text-sm">${{ item.precio.toFixed(2) }}</td>
                            <td class="px-4 py-2 text-sm font-semibold">${{ item.importe.toFixed(2) }}</td>
                            <td class="px-4 py-2 text-sm">
                                <button
                                    @click="eliminarItem(index)"
                                    class="text-red-600 hover:text-red-900"
                                >
                                    Eliminar
                                </button>
                            </td>
                        </tr>
                    </tbody>
                    <tfoot class="bg-gray-50">
                        <tr>
                            <td colspan="3" class="px-4 py-2 text-right font-semibold">Subtotal:</td>
                            <td class="px-4 py-2 text-lg font-bold text-green-600">
                                ${{ subtotal.toFixed(2) }}
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import Input from '@/Components/Form/Input.vue';

const props = defineProps({
    materiales: {
        type: Array,
        required: true,
    },
    modelValue: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(['update:modelValue']);

const selectedMaterial = ref('');
const cantidad = ref(1);
const precio = ref(0);

const items = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value),
});

const puedeAgregar = computed(() => {
    return selectedMaterial.value && cantidad.value >= 1 && precio.value > 0;
});

const subtotal = computed(() => {
    return items.value.reduce((sum, item) => sum + item.importe, 0);
});

const agregarItem = () => {
    const material = props.materiales.find(m => m.id == selectedMaterial.value);
    if (material) {
        items.value.push({
            material_id: material.id,
            nombre: material.nombre,
            unidad_medida: material.unidad_medida || '',
            cantidad: cantidad.value,
            precio: precio.value,
            importe: cantidad.value * precio.value,
            importe_desc: cantidad.value * precio.value,
        });
        resetForm();
    }
};

const eliminarItem = (index) => {
    items.value.splice(index, 1);
};

const resetForm = () => {
    selectedMaterial.value = '';
    cantidad.value = 1;
    precio.value = 0;
};
</script>

