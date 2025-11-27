import { ref, computed, onMounted } from 'vue'

// Estado global del tema
const theme = ref('adultos')
const fontSize = ref('normal')
const contrast = ref('normal')
const darkMode = ref(false)
let intervalId = null

// Detectar horario para modo día/noche
const detectTimeBasedTheme = () => {
    const hour = new Date().getHours()
    return hour >= 18 || hour < 6 ? 'dark' : 'light'
}

// Cargar preferencias del localStorage
const loadPreferences = () => {
    if (typeof window !== 'undefined') {
        const savedTheme = localStorage.getItem('theme') || 'adultos'
        const savedFontSize = localStorage.getItem('fontSize') || 'normal'
        const savedContrast = localStorage.getItem('contrast') || 'normal'
        const savedDarkMode = localStorage.getItem('darkMode') === 'true'
        const autoDarkMode = localStorage.getItem('autoDarkMode') === 'true'

        theme.value = savedTheme
        fontSize.value = savedFontSize
        contrast.value = savedContrast

        if (autoDarkMode) {
            darkMode.value = detectTimeBasedTheme() === 'dark'
        } else {
            darkMode.value = savedDarkMode
        }

        applyTheme()
    }
}

// Aplicar tema al documento
const applyTheme = () => {
    if (typeof document !== 'undefined') {
        const html = document.documentElement

        // Remover clases anteriores
        html.classList.remove('theme-ninos', 'theme-jovenes', 'theme-adultos')
        html.classList.remove('font-small', 'font-normal', 'font-large', 'font-xlarge')
        html.classList.remove('contrast-normal', 'contrast-high', 'contrast-very-high')
        html.classList.remove('dark-mode')

        // Aplicar tema
        html.classList.add(`theme-${theme.value}`)
        html.classList.add(`font-${fontSize.value}`)
        html.classList.add(`contrast-${contrast.value}`)

        if (darkMode.value) {
            html.classList.add('dark-mode')
        }
    }
}

// Guardar preferencias
const savePreferences = () => {
    if (typeof window !== 'undefined') {
        localStorage.setItem('theme', theme.value)
        localStorage.setItem('fontSize', fontSize.value)
        localStorage.setItem('contrast', contrast.value)
        localStorage.setItem('darkMode', darkMode.value.toString())
    }
}

// Cambiar tema
const setTheme = (newTheme) => {
    theme.value = newTheme
    applyTheme()
    savePreferences()
}

// Cambiar tamaño de fuente
const setFontSize = (size) => {
    fontSize.value = size
    applyTheme()
    savePreferences()
}

// Cambiar contraste
const setContrast = (level) => {
    contrast.value = level
    applyTheme()
    savePreferences()
}

// Toggle modo oscuro
const toggleDarkMode = (auto = false) => {
    if (auto) {
        localStorage.setItem('autoDarkMode', 'true')
        darkMode.value = detectTimeBasedTheme() === 'dark'
    } else {
        localStorage.setItem('autoDarkMode', 'false')
        darkMode.value = !darkMode.value
    }
    applyTheme()
    savePreferences()
}

// Inicializar inmediatamente si estamos en el navegador (solo una vez)
if (typeof window !== 'undefined' && !window.__themeInitialized) {
    loadPreferences()
    window.__themeInitialized = true

    // Verificar cada hora si está en modo automático
    intervalId = setInterval(() => {
        if (localStorage.getItem('autoDarkMode') === 'true') {
            const shouldBeDark = detectTimeBasedTheme() === 'dark'
            if (darkMode.value !== shouldBeDark) {
                darkMode.value = shouldBeDark
                applyTheme()
            }
        }
    }, 3600000) // Cada hora
}

export function useTheme() {
    // Asegurar que el tema esté aplicado cuando se use en un componente
    onMounted(() => {
        applyTheme()
    })

    return {
        theme: computed(() => theme.value),
        fontSize: computed(() => fontSize.value),
        contrast: computed(() => contrast.value),
        darkMode: computed(() => darkMode.value),
        setTheme,
        setFontSize,
        setContrast,
        toggleDarkMode,
        applyTheme,
    }
}
