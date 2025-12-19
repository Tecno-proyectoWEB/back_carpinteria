<template>
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900 transition-colors duration-300">
        <!-- Sidebar -->
        <aside class="fixed inset-y-0 left-0 w-64 bg-gray-900 dark:bg-gray-800 text-white transition-colors duration-300 z-50">
            <div class="flex flex-col h-full">
                <!-- Logo -->
                <div class="p-6 border-b border-gray-800 dark:border-gray-700">
                    <h1 class="text-xl font-bold text-legible-heading dark:text-white">Carpintería</h1>
                </div>

                <!-- Navigation -->
                <nav class="flex-1 p-4 space-y-2 overflow-y-auto">
                    <Link
                        :href="route('dashboard')"
                        class="flex items-center gap-3 px-4 py-3 rounded-lg transition-all duration-200 group"
                        :class="{
                            'bg-gradient-to-r from-blue-600 to-blue-500 text-white shadow-lg': $page.url.startsWith('/dashboard'),
                            'text-gray-300 hover:bg-gray-700 hover:text-white': !$page.url.startsWith('/dashboard')
                        }"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        <span>Dashboard</span>
                    </Link>
                    <Link
                        :href="route('usuarios.index')"
                        class="flex items-center gap-3 px-4 py-3 rounded-lg transition-all duration-200 group"
                        :class="{
                            'bg-gradient-to-r from-blue-600 to-blue-500 text-white shadow-lg': $page.url.startsWith('/usuarios'),
                            'text-gray-300 hover:bg-gray-700 hover:text-white': !$page.url.startsWith('/usuarios')
                        }"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <span>Usuarios</span>
                    </Link>
                    <Link
                        :href="route('roles.index')"
                        class="flex items-center gap-3 px-4 py-3 rounded-lg transition-all duration-200 group"
                        :class="{
                            'bg-gradient-to-r from-blue-600 to-blue-500 text-white shadow-lg': $page.url.startsWith('/roles'),
                            'text-gray-300 hover:bg-gray-700 hover:text-white': !$page.url.startsWith('/roles')
                        }"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                        <span>Roles y Permisos</span>
                    </Link>
                    <Link
                        :href="route('productos.index')"
                        class="flex items-center gap-3 px-4 py-3 rounded-lg transition-all duration-200 group"
                        :class="{
                            'bg-gradient-to-r from-blue-600 to-blue-500 text-white shadow-lg': $page.url.startsWith('/productos'),
                            'text-gray-300 hover:bg-gray-700 hover:text-white': !$page.url.startsWith('/productos')
                        }"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                        <span>Productos</span>
                    </Link>
                    <Link
                        :href="route('servicios.index')"
                        class="flex items-center gap-3 px-4 py-3 rounded-lg transition-all duration-200 group"
                        :class="{
                            'bg-gradient-to-r from-blue-600 to-blue-500 text-white shadow-lg': $page.url.startsWith('/servicios'),
                            'text-gray-300 hover:bg-gray-700 hover:text-white': !$page.url.startsWith('/servicios')
                        }"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        <span>Servicios</span>
                    </Link>
                    <Link
                        :href="route('materiales.index')"
                        class="flex items-center gap-3 px-4 py-3 rounded-lg transition-all duration-200 group"
                        :class="{
                            'bg-gradient-to-r from-blue-600 to-blue-500 text-white shadow-lg': $page.url.startsWith('/materiales'),
                            'text-gray-300 hover:bg-gray-700 hover:text-white': !$page.url.startsWith('/materiales')
                        }"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                        <span>Insumos</span>
                    </Link>
                    <Link
                        :href="route('inventarios.index')"
                        class="flex items-center gap-3 px-4 py-3 rounded-lg transition-all duration-200 group"
                        :class="{
                            'bg-gradient-to-r from-blue-600 to-blue-500 text-white shadow-lg': $page.url.startsWith('/inventarios'),
                            'text-gray-300 hover:bg-gray-700 hover:text-white': !$page.url.startsWith('/inventarios')
                        }"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        <span>Inventarios</span>
                    </Link>
                    <Link
                        :href="route('ventas.index')"
                        class="flex items-center gap-3 px-4 py-3 rounded-lg transition-all duration-200 group"
                        :class="{
                            'bg-gradient-to-r from-blue-600 to-blue-500 text-white shadow-lg': $page.url.startsWith('/ventas'),
                            'text-gray-300 hover:bg-gray-700 hover:text-white': !$page.url.startsWith('/ventas')
                        }"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                        <span>Ventas</span>
                    </Link>
                    <Link
                        :href="route('pagos.index')"
                        class="flex items-center gap-3 px-4 py-3 rounded-lg transition-all duration-200 group"
                        :class="{
                            'bg-gradient-to-r from-blue-600 to-blue-500 text-white shadow-lg': $page.url.startsWith('/pagos'),
                            'text-gray-300 hover:bg-gray-700 hover:text-white': !$page.url.startsWith('/pagos')
                        }"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <span>Pagos</span>
                    </Link>
                    <Link
                        :href="route('reportes.index')"
                        class="flex items-center gap-3 px-4 py-3 rounded-lg transition-all duration-200 group"
                        :class="{
                            'bg-gradient-to-r from-blue-600 to-blue-500 text-white shadow-lg': $page.url.startsWith('/reportes'),
                            'text-gray-300 hover:bg-gray-700 hover:text-white': !$page.url.startsWith('/reportes')
                        }"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        <span>Reportes</span>
                    </Link>
                </nav>

                <!-- User Info -->
                <div class="p-4 border-t border-gray-800 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium">{{ auth.user?.nombre }} {{ auth.user?.apellido }}</p>
                            <p class="text-xs text-gray-400">{{ auth.user?.rol?.nombre }}</p>
                        </div>
                        <button @click="logout" class="text-gray-400 hover:text-white transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Header con Búsqueda y Controles -->
        <header class="fixed top-0 left-64 right-0 h-16 bg-white dark:bg-gray-800 border-b-2 border-gray-200 dark:border-gray-700 flex items-center justify-between px-6 z-40 shadow-sm transition-colors duration-300">
            <div class="flex items-center gap-4 flex-1">
                <!-- Barra de Búsqueda -->
                <form @submit.prevent="buscar" class="flex-1 max-w-md">
                    <div class="relative">
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Buscar productos, servicios, materiales, ventas..."
                            class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors"
                        />
                        <svg class="absolute left-3 top-2.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </form>

                <!-- Controles de Tema y Accesibilidad -->
                <div class="flex items-center gap-2">
                    <!-- Selector de Tema -->
                    <div class="relative" data-theme-menu>
                        <button
                            @click="showThemeMenu = !showThemeMenu"
                            class="px-3 py-2 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors text-sm font-medium"
                        >
                            Tema
                        </button>
                        <div v-if="showThemeMenu" class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 py-2 z-50">
                            <button
                                @click="setTheme('ninos'); showThemeMenu = false"
                                :class="theme === 'ninos' ? 'bg-primary text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                                class="w-full px-3 py-2 rounded text-sm font-medium transition-colors mb-1"
                            >
                                Niños
                            </button>
                            <button
                                @click="setTheme('jovenes'); showThemeMenu = false"
                                :class="theme === 'jovenes' ? 'bg-primary text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                                class="w-full px-3 py-2 rounded text-sm font-medium transition-colors mb-1"
                            >
                                Jóvenes
                            </button>
                            <button
                                @click="setTheme('adultos'); showThemeMenu = false"
                                :class="theme === 'adultos' ? 'bg-primary text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                                class="w-full px-3 py-2 rounded text-sm font-medium transition-colors mb-1"
                            >
                                Adultos
                            </button>
                        </div>
                    </div>

                    <!-- Modo Día/Noche -->
                    <button
                        @click="toggleDarkMode"
                        class="px-3 py-2 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
                        :title="darkMode ? 'Modo Día' : 'Modo Noche'"
                    >
                        <svg v-if="darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                    </button>

                    <!-- Tamaño de Fuente -->
                    <div class="relative" data-font-menu>
                        <button
                            @click="showFontMenu = !showFontMenu"
                            class="px-3 py-2 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors text-sm font-medium"
                        >
                            Aa
                        </button>
                        <div v-if="showFontMenu" class="absolute right-0 mt-2 w-40 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 py-2 z-50">
                            <button
                                @click="setFontSize('small'); showFontMenu = false"
                                :class="fontSize === 'small' ? 'bg-blue-100 text-blue-800' : 'text-gray-700 dark:text-gray-300'"
                                class="w-full px-3 py-2 text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                            >
                                Pequeño
                            </button>
                            <button
                                @click="setFontSize('normal'); showFontMenu = false"
                                :class="fontSize === 'normal' ? 'bg-blue-100 text-blue-800' : 'text-gray-700 dark:text-gray-300'"
                                class="w-full px-3 py-2 text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                            >
                                Normal
                            </button>
                            <button
                                @click="setFontSize('large'); showFontMenu = false"
                                :class="fontSize === 'large' ? 'bg-blue-100 text-blue-800' : 'text-gray-700 dark:text-gray-300'"
                                class="w-full px-3 py-2 text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                            >
                                Grande
                            </button>
                            <button
                                @click="setFontSize('xlarge'); showFontMenu = false"
                                :class="fontSize === 'xlarge' ? 'bg-blue-100 text-blue-800' : 'text-gray-700 dark:text-gray-300'"
                                class="w-full px-3 py-2 text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                            >
                                Muy Grande
                            </button>
                        </div>
                    </div>

                    <!-- Contraste -->
                    <div class="relative" data-contrast-menu>
                        <button
                            @click="showContrastMenu = !showContrastMenu"
                            class="px-3 py-2 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors text-sm font-medium"
                        >
                            ⚫
                        </button>
                        <div v-if="showContrastMenu" class="absolute right-0 mt-2 w-40 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 py-2 z-50">
                            <button
                                @click="setContrast('normal'); showContrastMenu = false"
                                :class="contrast === 'normal' ? 'bg-blue-100 text-blue-800' : 'text-gray-700 dark:text-gray-300'"
                                class="w-full px-3 py-2 text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                            >
                                Normal
                            </button>
                            <button
                                @click="setContrast('high'); showContrastMenu = false"
                                :class="contrast === 'high' ? 'bg-blue-100 text-blue-800' : 'text-gray-700 dark:text-gray-300'"
                                class="w-full px-3 py-2 text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                            >
                                Alto
                            </button>
                            <button
                                @click="setContrast('veryHigh'); showContrastMenu = false"
                                :class="contrast === 'veryHigh' ? 'bg-blue-100 text-blue-800' : 'text-gray-700 dark:text-gray-300'"
                                class="w-full px-3 py-2 text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                            >
                                Muy Alto
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="ml-64 pt-16 pb-20 p-8 transition-colors duration-300">
            <slot />
        </main>

        <!-- Footer con Contador de Visitas -->
        <footer class="fixed bottom-0 left-64 right-0 h-16 bg-white dark:bg-gray-800 border-t-2 border-gray-200 dark:border-gray-700 flex items-center justify-center px-6 z-40 shadow-lg transition-colors duration-300">
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-2 bg-gradient-to-r from-blue-500 to-purple-500 text-white px-6 py-2.5 rounded-lg shadow-md hover:shadow-lg transition-all duration-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    <span class="font-semibold">Visitas a esta página:</span>
                    <span class="font-bold text-xl">{{ visitasPagina || 0 }}</span>
                </div>
            </div>
        </footer>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import { route } from '../ziggy.js'
import { useTheme } from '../composables/useTheme.js'

const props = defineProps({
    auth: Object,
    visitasPagina: {
        type: Number,
        default: 0,
    },
})

// Destructure props for easier template access
const { auth, visitasPagina } = props

const page = usePage()
const { theme, fontSize, contrast, darkMode, setTheme, setFontSize, setContrast, setDarkMode } = useTheme()

const searchQuery = ref('')
const showThemeMenu = ref(false)
const showFontMenu = ref(false)
const showContrastMenu = ref(false)

const buscar = () => {
    if (searchQuery.value.trim()) {
        router.get(route('buscar'), { q: searchQuery.value.trim() })
    }
}

const toggleDarkMode = () => {
    setDarkMode(!darkMode.value)
}

const handleClickOutside = (e) => {
    if (!e.target.closest('[data-theme-menu]')) {
        showThemeMenu.value = false
    }
    if (!e.target.closest('[data-font-menu]')) {
        showFontMenu.value = false
    }
    if (!e.target.closest('[data-contrast-menu]')) {
        showContrastMenu.value = false
    }
}

onMounted(() => {
    document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside)
})

const logout = () => {
    router.post('/logout')
}
</script>
