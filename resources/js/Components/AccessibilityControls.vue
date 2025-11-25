<template>
    <div class="accessibility-controls bg-white p-4 rounded-lg shadow-md">
        <h3 class="text-lg font-semibold mb-4">Accesibilidad</h3>
        
        <!-- Control de tamaño de fuente -->
        <div class="mb-4">
            <label class="block text-sm font-medium mb-2">Tamaño de letra:</label>
            <div class="flex items-center space-x-2">
                <button
                    v-for="size in fontSizes"
                    :key="size.value"
                    @click="setFontSize(size.value)"
                    :class="[
                        'px-3 py-1 rounded-md text-sm border',
                        currentFontSize === size.value
                            ? 'bg-blue-500 text-white border-blue-500'
                            : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'
                    ]"
                >
                    {{ size.label }}
                </button>
            </div>
        </div>

        <!-- Control de contraste -->
        <div>
            <label class="block text-sm font-medium mb-2">Contraste:</label>
            <div class="flex items-center space-x-2">
                <button
                    v-for="contrast in contrasts"
                    :key="contrast.value"
                    @click="setContrast(contrast.value)"
                    :class="[
                        'px-3 py-1 rounded-md text-sm border',
                        currentContrast === contrast.value
                            ? 'bg-blue-500 text-white border-blue-500'
                            : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'
                    ]"
                >
                    {{ contrast.label }}
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';

const fontSizes = [
    { value: 'small', label: 'Pequeño' },
    { value: 'normal', label: 'Normal' },
    { value: 'large', label: 'Grande' },
    { value: 'xlarge', label: 'Muy Grande' },
];

const contrasts = [
    { value: 'normal', label: 'Normal' },
    { value: 'high', label: 'Alto' },
    { value: 'very-high', label: 'Muy Alto' },
];

const currentFontSize = ref('normal');
const currentContrast = ref('normal');

const setFontSize = (size) => {
    currentFontSize.value = size;
    document.body.setAttribute('data-font-size', size);
    localStorage.setItem('fontSize', size);
};

const setContrast = (contrast) => {
    currentContrast.value = contrast;
    document.body.setAttribute('data-contrast', contrast);
    localStorage.setItem('contrast', contrast);
    
    // Habilitar accesibilidad si se cambia el contraste
    if (contrast !== 'normal') {
        document.body.setAttribute('data-accessibility', 'enabled');
    } else {
        document.body.removeAttribute('data-accessibility');
    }
};

onMounted(() => {
    const savedFontSize = localStorage.getItem('fontSize') || 'normal';
    const savedContrast = localStorage.getItem('contrast') || 'normal';
    
    setFontSize(savedFontSize);
    setContrast(savedContrast);
});
</script>

