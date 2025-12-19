<template>
    <div class="mb-4">
        <label v-if="label" :for="id" class="block text-sm font-medium text-gray-700 mb-1">
            {{ label }}
            <span v-if="required" class="text-red-500">*</span>
        </label>
        <select
            :id="id"
            :value="modelValue"
            @change="$emit('update:modelValue', $event.target.value)"
            :required="required"
            :disabled="disabled"
            :class="[
                'w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500',
                error ? 'border-red-500' : 'border-gray-300',
                disabled ? 'bg-gray-100 cursor-not-allowed' : 'bg-white'
            ]"
        >
            <option v-if="placeholder" value="">{{ placeholder }}</option>
            <option
                v-for="option in options"
                :key="getOptionValue(option)"
                :value="getOptionValue(option)"
            >
                {{ getOptionLabel(option) }}
            </option>
        </select>
        <p v-if="error" class="mt-1 text-sm text-red-600">{{ error }}</p>
        <p v-if="hint && !error" class="mt-1 text-sm text-gray-500">{{ hint }}</p>
    </div>
</template>

<script setup>
const props = defineProps({
    id: {
        type: String,
        default: () => `select-${Math.random().toString(36).substr(2, 9)}`,
    },
    label: String,
    modelValue: [String, Number],
    options: {
        type: Array,
        required: true,
    },
    optionValue: {
        type: String,
        default: 'id',
    },
    optionLabel: {
        type: String,
        default: 'nombre',
    },
    placeholder: String,
    required: Boolean,
    disabled: Boolean,
    error: String,
    hint: String,
});

defineEmits(['update:modelValue']);

const getOptionValue = (option) => {
    return typeof option === 'object' ? option[props.optionValue] : option;
};

const getOptionLabel = (option) => {
    return typeof option === 'object' ? option[props.optionLabel] : option;
};
</script>
