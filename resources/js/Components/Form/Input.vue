<template>
    <div class="mb-4">
        <label v-if="label" :for="id" class="block text-sm font-medium text-gray-700 mb-1">
            {{ label }}
            <span v-if="required" class="text-red-500">*</span>
        </label>
        <input
            :id="id"
            :type="type"
            :value="modelValue"
            @input="$emit('update:modelValue', $event.target.value)"
            :placeholder="placeholder"
            :required="required"
            :disabled="disabled"
            :class="[
                'w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500',
                error ? 'border-red-500' : 'border-gray-300',
                disabled ? 'bg-gray-100 cursor-not-allowed' : 'bg-white'
            ]"
        />
        <p v-if="error" class="mt-1 text-sm text-red-600">{{ error }}</p>
        <p v-if="hint && !error" class="mt-1 text-sm text-gray-500">{{ hint }}</p>
    </div>
</template>

<script setup>
defineProps({
    id: {
        type: String,
        default: () => `input-${Math.random().toString(36).substr(2, 9)}`,
    },
    label: String,
    type: {
        type: String,
        default: 'text',
    },
    modelValue: [String, Number],
    placeholder: String,
    required: Boolean,
    disabled: Boolean,
    error: String,
    hint: String,
});

defineEmits(['update:modelValue']);
</script>
