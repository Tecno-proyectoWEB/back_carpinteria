<template>
    <div class="producto-selector">
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Agregar Producto o Servicio</label>
            <div class="flex space-x-2">
                <select
                    v-model="selectedType"
                    class="px-3 py-2 border border-gray-300 rounded-md"
                >
                    <option value="producto">Producto</option>
                    <option value="servicio">Servicio</option>
                </select>
                <select
                    v-if="selectedType === 'producto'"
                    v-model="selectedProducto"
                    class="flex-1 px-3 py-2 border border-gray-300 rounded-md"
                >
                    <option value="">Seleccione un producto</option>
                    <option
                        v-for="producto in productos"
                        :key="producto.id"
                        :value="producto.id"
                        :disabled="producto.stock <= 0"
                    >
                        {{ producto.nombre }} - Stock: {{ producto.stock }} - ${{ producto.precio_unitario }}
                    </option>
                </select>
                <select
                    v-else
                    v-model="selectedServicio"
                    class="flex-1 px-3 py-2 border border-gray-300 rounded-md"
                >
                    <option value="">Seleccione un servicio</option>
                    <option
                        v-for="servicio in servicios"
                        :key="servicio.id"
                        :value="servicio.id"
                    >
                        {{ servicio.nombre }} - ${{ servicio.precio_base }}
                    </option>
                </select>
                <Input
                    v-model.number="cantidad"
                    type="number"
                    placeholder="Cantidad"
                    class="w-24"
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
            <h3 class="text-sm font-medium text-gray-700 mb-2">Items del Pedido</h3>
            <div class="border rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Tipo</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Nombre</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Cantidad</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Precio Unit.</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Total</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="(item, index) in items" :key="index">
                            <td class="px-4 py-2 text-sm">
                                <Badge :variant="item.tipo === 'producto' ? 'info' : 'success'">
                                    {{ item.tipo === 'producto' ? 'Producto' : 'Servicio' }}
                                </Badge>
                            </td>
                            <td class="px-4 py-2 text-sm">{{ item.nombre }}</td>
                            <td class="px-4 py-2 text-sm">{{ item.cantidad }}</td>
                            <td class="px-4 py-2 text-sm">${{ item.precio_unitario.toFixed(2) }}</td>
                            <td class="px-4 py-2 text-sm font-semibold">${{ item.importe_total.toFixed(2) }}</td>
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
                            <td colspan="4" class="px-4 py-2 text-right font-semibold">Total:</td>
                            <td class="px-4 py-2 text-lg font-bold text-green-600">
                                ${{ total.toFixed(2) }}
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
import Badge from '@/Components/UI/Badge.vue';

const props = defineProps({
    productos: {
        type: Array,
        default: () => [],
    },
    servicios: {
        type: Array,
        default: () => [],
    },
    modelValue: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(['update:modelValue']);

const selectedType = ref('producto');
const selectedProducto = ref('');
const selectedServicio = ref('');
const cantidad = ref(1);

const items = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value),
});

const puedeAgregar = computed(() => {
    if (cantidad.value < 1) return false;
    if (selectedType.value === 'producto') {
        if (!selectedProducto.value) return false;
        const producto = props.productos.find(p => p.id == selectedProducto.value);
        return producto && producto.stock >= cantidad.value;
    } else {
        return !!selectedServicio.value;
    }
});

const total = computed(() => {
    return items.value.reduce((sum, item) => sum + item.importe_total, 0);
});

const agregarItem = () => {
    if (selectedType.value === 'producto') {
        const producto = props.productos.find(p => p.id == selectedProducto.value);
        if (producto && producto.stock >= cantidad.value) {
            items.value.push({
                producto_id: producto.id,
                servicio_id: null,
                nombre: producto.nombre,
                tipo: 'producto',
                cantidad: cantidad.value,
                precio_unitario: producto.precio_unitario,
                importe_total: cantidad.value * producto.precio_unitario,
                importe_total_desc: cantidad.value * producto.precio_unitario,
            });
            resetForm();
        }
    } else {
        const servicio = props.servicios.find(s => s.id == selectedServicio.value);
        if (servicio) {
            items.value.push({
                producto_id: null,
                servicio_id: servicio.id,
                nombre: servicio.nombre,
                tipo: 'servicio',
                cantidad: cantidad.value,
                precio_unitario: servicio.precio_base,
                importe_total: cantidad.value * servicio.precio_base,
                importe_total_desc: cantidad.value * servicio.precio_base,
            });
            resetForm();
        }
    }
};

const eliminarItem = (index) => {
    items.value.splice(index, 1);
};

const resetForm = () => {
    selectedProducto.value = '';
    selectedServicio.value = '';
    cantidad.value = 1;
};
</script>

