<template>
    <div class="min-h-screen bg-primary">
        <!-- Header con Búsqueda y Controles -->
        <header class="fixed top-0 left-64 right-0 h-20 bg-secondary border-b border-theme z-40">
            <div class="flex items-center justify-between h-full px-6">
                <h1 class="text-xl font-bold text-legible-heading dark:text-white">Carpintería</h1>

                <!-- Barra de Búsqueda -->
                <form @submit.prevent="buscar" class="flex-1 max-w-2xl mx-8">
                    <div class="relative">
                        <input
                            v-model="busquedaQuery"
                            type="text"
                            placeholder="🔍 Buscar productos, servicios, materiales, ventas, usuarios..."
                            class="w-full px-4 py-2.5 pl-12 pr-12 rounded-xl border-2 border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-md hover:shadow-lg transition-all duration-200"
                            @keydown.escape="busquedaQuery = ''"
                        />
                        <svg class="absolute left-4 top-3 w-5 h-5 text-gray-400 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <button
                            v-if="busquedaQuery"
                            @click="busquedaQuery = ''"
                            type="button"
                            class="absolute right-3 top-2.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors"
                            title="Limpiar búsqueda"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                        <div v-if="busquedaQuery && busquedaQuery.length > 0" class="absolute right-12 top-2.5">
                            <kbd class="px-2 py-1 text-xs font-semibold text-gray-500 bg-gray-100 dark:bg-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-500 rounded">Enter</kbd>
                        </div>
                    </div>
                </form>

                <!-- Controles de Accesibilidad y Usuario -->
                <div class="flex items-center gap-3">
                    <!-- Selector de Tema -->
                    <div class="relative" @click.stop>
                        <button
                            @click="showThemeMenu = !showThemeMenu"
                            class="p-2 rounded-lg hover:bg-primary hover:text-white transition-colors"
                            title="Seleccionar Tema"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                            </svg>
                        </button>
                        <div v-if="showThemeMenu" class="absolute right-0 top-12 w-48 bg-white rounded-lg shadow-xl border border-gray-200 p-2 z-50">
                            <div class="text-xs font-semibold text-gray-700 mb-2 px-2">Tema por Edad</div>
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
                                class="w-full px-3 py-2 rounded text-sm font-medium transition-colors"
                            >
                                Adultos
                            </button>
                        </div>
                    </div>

                    <!-- Tamaño de Letra -->
                    <div class="relative" @click.stop>
                        <button
                            @click="showFontMenu = !showFontMenu"
                            class="p-2 rounded-lg hover:bg-primary hover:text-white transition-colors"
                            title="Tamaño de Letra"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                        <div v-if="showFontMenu" class="absolute right-0 top-12 w-40 bg-white rounded-lg shadow-xl border border-gray-200 p-2 z-50">
                            <div class="text-xs font-semibold text-gray-700 mb-2 px-2">Tamaño</div>
                            <button
                                v-for="size in ['small', 'normal', 'large', 'xlarge']"
                                :key="size"
                                @click="setFontSize(size); showFontMenu = false"
                                :class="fontSize === size ? 'bg-primary text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                                class="w-full px-3 py-2 rounded text-sm font-medium transition-colors mb-1"
                            >
                                {{ size === 'small' ? 'Pequeño' : size === 'normal' ? 'Normal' : size === 'large' ? 'Grande' : 'Muy Grande' }}
                            </button>
                        </div>
                    </div>

                    <!-- Contraste -->
                    <div class="relative" @click.stop>
                        <button
                            @click="showContrastMenu = !showContrastMenu"
                            class="p-2 rounded-lg hover:bg-primary hover:text-white transition-colors"
                            title="Contraste"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                            </svg>
                        </button>
                        <div v-if="showContrastMenu" class="absolute right-0 top-12 w-44 bg-white rounded-lg shadow-xl border border-gray-200 p-2 z-50">
                            <div class="text-xs font-semibold text-gray-700 mb-2 px-2">Contraste</div>
                            <button
                                v-for="level in ['normal', 'high', 'very-high']"
                                :key="level"
                                @click="setContrast(level); showContrastMenu = false"
                                :class="contrast === level ? 'bg-primary text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                                class="w-full px-3 py-2 rounded text-sm font-medium transition-colors mb-1"
                            >
                                {{ level === 'normal' ? 'Normal' : level === 'high' ? 'Alto' : 'Muy Alto' }}
                            </button>
                        </div>
                    </div>

                    <!-- Modo Oscuro -->
                    <button
                        @click="toggleDarkMode(false)"
                        class="p-2 rounded-lg hover:bg-primary hover:text-white transition-colors"
                        :title="darkMode ? 'Modo Oscuro Activado' : 'Modo Oscuro Desactivado'"
                    >
                        <svg v-if="darkMode" class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                        <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </button>

                    <!-- Usuario -->
                    <div class="flex items-center gap-2 pl-3 border-l border-theme">
                        <span class="text-sm text-legible dark:text-white font-medium">{{ auth.user?.nombre }} {{ auth.user?.apellido }}</span>
                    </div>
                </div>
            </div>
        </header>

        <!-- Sidebar -->
        <aside class="fixed inset-y-0 left-0 w-64 bg-gradient-to-b from-gray-800 via-gray-800 to-gray-900 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 text-white pt-20 shadow-2xl border-r border-gray-700">
            <div class="flex flex-col h-full">
                <!-- Navigation -->
                <nav class="flex-1 p-4 space-y-1 overflow-y-auto">
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
                        <span class="font-medium">Dashboard</span>
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
                        <span class="font-medium">Usuarios</span>
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
                        <span class="font-medium">Roles y Permisos</span>
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
                        <span class="font-medium">Productos</span>
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
                        <span class="font-medium">Servicios</span>
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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                        <span class="font-medium">Insumos</span>
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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                        </svg>
                        <span class="font-medium">Inventarios</span>
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
                        <span class="font-medium">Ventas</span>
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
                        <span class="font-medium">Pagos</span>
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
                        <span class="font-medium">Reportes</span>
                    </Link>
                </nav>

                <!-- User Info -->
                <div class="p-4 border-t border-gray-700 bg-gray-800/50">
                    <div class="flex items-center justify-between">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-white truncate">{{ auth.user?.nombre }} {{ auth.user?.apellido }}</p>
                            <p class="text-xs text-gray-400 truncate">{{ auth.user?.rol?.nombre }}</p>
                        </div>
                        <button
                            @click="logout"
                            class="ml-2 p-2 text-gray-400 hover:text-white hover:bg-gray-700 rounded-lg transition-all duration-200"
                            title="Cerrar Sesión"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="ml-64 pt-20 pb-20 p-8 min-h-screen">
            <slot />
        </main>

        <!-- Footer con Contador de Visitas -->
        <footer class="fixed bottom-0 left-64 right-0 h-16 bg-white dark:bg-gray-800 border-t-2 border-gray-200 dark:border-gray-700 flex items-center justify-center px-6 z-40 shadow-lg">
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-2 bg-gradient-to-r from-blue-500 to-purple-500 text-white px-6 py-2.5 rounded-lg shadow-md hover:shadow-lg transition-all duration-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    <span class="text-sm font-semibold">Visitas a esta página: <span class="font-bold text-lg">{{ visitasPagina }}</span></span>
                </div>
            </div>
        </footer>

        <!-- Selector de Temas -->
        <ThemeSelector />
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import { route } from '../ziggy.js'
import ThemeSelector from '../Components/ThemeSelector.vue'
import { useTheme } from '../composables/useTheme'

const props = defineProps({
    auth: Object,
    visitasPagina: {
        type: Number,
        default: 0,
    },
})

// Obtener visitasPagina de las props compartidas de Inertia
const visitasPagina = computed(() => {
    return page.props.visitasPagina || props.visitasPagina || 0
})

const page = usePage()
const { theme, fontSize, contrast, darkMode, setTheme, setFontSize, setContrast, toggleDarkMode } = useTheme()

const busquedaQuery = ref('')
const showThemeMenu = ref(false)
const showFontMenu = ref(false)
const showContrastMenu = ref(false)

// Cerrar menús al hacer click fuera
const closeMenus = () => {
    showThemeMenu.value = false
    showFontMenu.value = false
    showContrastMenu.value = false
}

onMounted(() => {
    document.addEventListener('click', closeMenus)
})

onUnmounted(() => {
    document.removeEventListener('click', closeMenus)
})

const buscar = () => {
    const query = busquedaQuery.value.trim()
    if (query) {
        router.get(route('busqueda.buscar'), { q: query }, {
            preserveState: true,
            preserveScroll: true,
        })
    }
}

const logout = () => {
    router.post('/logout')
}
</script>
