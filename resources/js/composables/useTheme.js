import { ref, computed, watch, onMounted } from 'vue'

const theme = ref(localStorage.getItem('theme') || 'adultos')
const fontSize = ref(localStorage.getItem('fontSize') || 'normal')
const contrast = ref(localStorage.getItem('contrast') || 'normal')
const darkMode = ref(localStorage.getItem('darkMode') === 'true' || false)

// Detectar modo día/noche según horario del cliente
const detectDayNightMode = () => {
    const hora = new Date().getHours()
    return hora >= 6 && hora < 20 // Día: 6 AM - 8 PM
}

// Aplicar modo día/noche automático si está habilitado
const autoDayNight = ref(localStorage.getItem('autoDayNight') === 'true' || true)

if (autoDayNight.value) {
    darkMode.value = !detectDayNightMode()
}

const themes = {
    ninos: {
        name: 'Niños',
        primary: '#FF6B9D',
        secondary: '#C44569',
        accent: '#F8B500',
    },
    jovenes: {
        name: 'Jóvenes',
        primary: '#4ECDC4',
        secondary: '#45B7B8',
        accent: '#FFE66D',
    },
    adultos: {
        name: 'Adultos',
        primary: '#2C3E50',
        secondary: '#34495E',
        accent: '#3498DB',
    },
}

const fontSizes = {
    small: { name: 'Pequeño', size: '0.875rem' },
    normal: { name: 'Normal', size: '1rem' },
    large: { name: 'Grande', size: '1.125rem' },
    xlarge: { name: 'Muy Grande', size: '1.25rem' },
}

const contrastLevels = {
    normal: { name: 'Normal', value: 1 },
    high: { name: 'Alto', value: 1.2 },
    veryHigh: { name: 'Muy Alto', value: 1.4 },
}

export function useTheme() {
    const setTheme = (newTheme) => {
        theme.value = newTheme
        localStorage.setItem('theme', newTheme)
        applyTheme()
    }

    const setFontSize = (newSize) => {
        fontSize.value = newSize
        localStorage.setItem('fontSize', newSize)
        applyFontSize()
    }

    const setContrast = (newContrast) => {
        contrast.value = newContrast
        localStorage.setItem('contrast', newContrast)
        applyContrast()
    }

    const setDarkMode = (isDark) => {
        darkMode.value = isDark
        localStorage.setItem('darkMode', isDark)
        applyDarkMode()
    }

    const applyTheme = () => {
        const root = document.documentElement
        const currentTheme = themes[theme.value] || themes.adultos
        
        root.style.setProperty('--primary-color', currentTheme.primary)
        root.style.setProperty('--secondary-color', currentTheme.secondary)
        root.style.setProperty('--accent-color', currentTheme.accent)
        
        root.classList.remove('theme-ninos', 'theme-jovenes', 'theme-adultos')
        root.classList.add(`theme-${theme.value}`)
    }

    const applyFontSize = () => {
        const root = document.documentElement
        const currentSize = fontSizes[fontSize.value] || fontSizes.normal
        root.style.setProperty('--font-size-base', currentSize.size)
    }

    const applyContrast = () => {
        const root = document.documentElement
        const currentContrast = contrastLevels[contrast.value] || contrastLevels.normal
        root.style.setProperty('--contrast-multiplier', currentContrast.value)
    }

    const applyDarkMode = () => {
        const root = document.documentElement
        if (darkMode.value) {
            root.classList.add('dark-mode')
        } else {
            root.classList.remove('dark-mode')
        }
    }

    const getThemeClasses = () => {
        const baseClasses = {
            bg: darkMode.value ? 'bg-gray-900' : 'bg-white',
            text: darkMode.value ? 'text-gray-100' : 'text-gray-900',
            textSecondary: darkMode.value ? 'text-gray-400' : 'text-gray-600',
            border: darkMode.value ? 'border-gray-700' : 'border-gray-200',
        }
        return baseClasses
    }

    // Aplicar al montar
    onMounted(() => {
        applyTheme()
        applyFontSize()
        applyContrast()
        applyDarkMode()
    })

    // Observar cambios
    watch([theme, fontSize, contrast, darkMode], () => {
        applyTheme()
        applyFontSize()
        applyContrast()
        applyDarkMode()
    })

    return {
        theme,
        fontSize,
        contrast,
        darkMode,
        themes,
        fontSizes,
        contrastLevels,
        setTheme,
        setFontSize,
        setContrast,
        setDarkMode,
        getThemeClasses,
    }
}

