<template>
    <div class="fixed bottom-4 right-4 z-50">
        <button 
            @click="showPanel = !showPanel"
            class="bg-primary text-white p-3 rounded-full shadow-lg hover:bg-secondary transition-colors"
            :title="'Configuración de Accesibilidad'"
        >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
        </button>

        <div v-if="showPanel" class="absolute bottom-16 right-0 w-80 bg-white dark:bg-gray-800 rounded-lg shadow-xl p-4 border border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-bold mb-4 text-primary">Configuración de Accesibilidad</h3>
            
            <!-- Selección de Tema -->
            <div class="mb-4">
                <label class="block text-sm font-medium mb-2 text-primary">Tema por Edad</label>
                <div class="grid grid-cols-3 gap-2">
                    <button 
                        @click="setTheme('ninos')"
                        :class="theme === 'ninos' ? 'bg-primary text-white' : 'bg-gray-100 text-gray-700'"
                        class="px-3 py-2 rounded text-sm font-medium transition-colors"
                    >
                        Niños
                    </button>
                    <button 
                        @click="setTheme('jovenes')"
                        :class="theme === 'jovenes' ? 'bg-primary text-white' : 'bg-gray-100 text-gray-700'"
                        class="px-3 py-2 rounded text-sm font-medium transition-colors"
                    >
                        Jóvenes
                    </button>
                    <button 
                        @click="setTheme('adultos')"
                        :class="theme === 'adultos' ? 'bg-primary text-white' : 'bg-gray-100 text-gray-700'"
                        class="px-3 py-2 rounded text-sm font-medium transition-colors"
                    >
                        Adultos
                    </button>
                </div>
            </div>

            <!-- Tamaño de Fuente -->
            <div class="mb-4">
                <label class="block text-sm font-medium mb-2 text-primary">Tamaño de Letra</label>
                <div class="grid grid-cols-4 gap-2">
                    <button 
                        v-for="size in ['small', 'normal', 'large', 'xlarge']"
                        :key="size"
                        @click="setFontSize(size)"
                        :class="fontSize === size ? 'bg-primary text-white' : 'bg-gray-100 text-gray-700'"
                        class="px-2 py-1 rounded text-xs font-medium transition-colors"
                    >
                        {{ size === 'small' ? 'A' : size === 'normal' ? 'A' : size === 'large' ? 'A' : 'A' }}
                    </button>
                </div>
            </div>

            <!-- Contraste -->
            <div class="mb-4">
                <label class="block text-sm font-medium mb-2 text-primary">Contraste</label>
                <div class="grid grid-cols-3 gap-2">
                    <button 
                        v-for="level in ['normal', 'high', 'very-high']"
                        :key="level"
                        @click="setContrast(level)"
                        :class="contrast === level ? 'bg-primary text-white' : 'bg-gray-100 text-gray-700'"
                        class="px-3 py-2 rounded text-sm font-medium transition-colors"
                    >
                        {{ level === 'normal' ? 'Normal' : level === 'high' ? 'Alto' : 'Muy Alto' }}
                    </button>
                </div>
            </div>

            <!-- Modo Oscuro -->
            <div class="mb-4">
                <label class="block text-sm font-medium mb-2 text-primary">Modo Oscuro</label>
                <div class="flex gap-2">
                    <button 
                        @click="toggleDarkMode(false)"
                        :class="darkMode ? 'bg-gray-100 text-gray-700' : 'bg-primary text-white'"
                        class="px-3 py-2 rounded text-sm font-medium transition-colors flex-1"
                    >
                        Manual
                    </button>
                    <button 
                        @click="toggleDarkMode(true)"
                        class="px-3 py-2 rounded text-sm font-medium transition-colors flex-1 bg-gray-100 text-gray-700 hover:bg-gray-200"
                    >
                        Automático
                    </button>
                </div>
            </div>

            <button 
                @click="showPanel = false"
                class="w-full mt-2 px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 transition-colors"
            >
                Cerrar
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useTheme } from '../composables/useTheme'

const showPanel = ref(false)
const { theme, fontSize, contrast, darkMode, setTheme, setFontSize, setContrast, toggleDarkMode } = useTheme()
</script>

