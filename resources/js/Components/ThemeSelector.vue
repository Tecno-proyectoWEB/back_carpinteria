<template>
    <div class="theme-selector">
        <div class="flex items-center space-x-4">
            <!-- Selector de tema por edad -->
            <div class="flex items-center space-x-2">
                <label class="text-sm font-medium">Tema:</label>
                <select
                    v-model="selectedTheme"
                    @change="applyTheme"
                    class="px-3 py-1 border border-gray-300 rounded-md text-sm"
                >
                    <option value="ninos">Niños</option>
                    <option value="jovenes">Jóvenes</option>
                    <option value="adultos">Adultos</option>
                </select>
            </div>

            <!-- Selector de modo día/noche -->
            <div class="flex items-center space-x-2">
                <label class="text-sm font-medium">Modo:</label>
                <button
                    @click="toggleMode"
                    class="px-3 py-1 border border-gray-300 rounded-md text-sm hover:bg-gray-100"
                >
                    {{ currentMode === 'dia' ? '☀️ Día' : '🌙 Noche' }}
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';

const selectedTheme = ref('adultos');
const currentMode = ref('dia');

// Detectar hora del día para modo automático
const detectTimeMode = () => {
    const hour = new Date().getHours();
    return hour >= 6 && hour < 20 ? 'dia' : 'noche';
};

// Aplicar tema
const applyTheme = () => {
    const root = document.documentElement;
    root.setAttribute('data-theme', selectedTheme.value);
    localStorage.setItem('theme', selectedTheme.value);
};

// Cambiar modo día/noche
const toggleMode = () => {
    currentMode.value = currentMode.value === 'dia' ? 'noche' : 'dia';
    const root = document.documentElement;
    root.setAttribute('data-mode', currentMode.value);
    localStorage.setItem('mode', currentMode.value);
};

// Cargar preferencias guardadas
onMounted(() => {
    const savedTheme = localStorage.getItem('theme') || 'adultos';
    const savedMode = localStorage.getItem('mode') || detectTimeMode();
    
    selectedTheme.value = savedTheme;
    currentMode.value = savedMode;
    
    applyTheme();
    toggleMode(); // Aplicar modo guardado
});
</script>

