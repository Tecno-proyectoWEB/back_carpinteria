<template>
    <div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 flex">
        <!-- Sidebar -->
        <aside
            :class="[
                'fixed inset-y-0 left-0 z-50 w-64 bg-gradient-to-b from-indigo-600 to-indigo-700 shadow-2xl transform transition-transform duration-300 ease-in-out flex flex-col',
                sidebarOpen ? 'translate-x-0' : '-translate-x-full',
                'lg:translate-x-0 lg:static lg:inset-0'
            ]"
        >
            <!-- Sidebar Header -->
            <div class="flex items-center justify-between h-16 px-4 border-b border-indigo-500/30 bg-indigo-700/50">
                <h1 class="text-xl font-bold text-white">Carpintería Jorge</h1>
                <button
                    @click="toggleSidebar"
                    class="lg:hidden text-white hover:text-indigo-200 transition-colors"
                    aria-label="Cerrar menú"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Sidebar Menu -->
            <nav class="flex-1 overflow-y-auto py-4">
                <div class="px-2 space-y-1">
                    <template v-if="props.menuItems && Array.isArray(props.menuItems) && props.menuItems.length > 0">
                        <Link
                            v-for="item in props.menuItems"
                            :key="item?.id || `menu-${item?.nombre}`"
                            :href="item?.ruta || '#'"
                            class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200"
                            :class="(item?.ruta && isActiveRoute(item.ruta)) 
                                ? 'bg-white text-indigo-700 shadow-md' 
                                : 'text-indigo-100 hover:bg-indigo-500/50 hover:text-white'"
                        >
                            <span class="flex-1">{{ item?.nombre || 'Sin nombre' }}</span>
                        </Link>
                    </template>
                    <div v-else class="px-3 py-2 text-sm text-indigo-200">
                        No hay elementos de menú disponibles
                    </div>
                </div>
            </nav>

            <!-- Sidebar Footer -->
            <div class="border-t border-indigo-500/30 p-4 bg-indigo-700/30 mt-auto">
                <template v-if="authUser && authUser.id">
                    <div class="space-y-3">
                        <div class="text-sm text-white">
                            <div class="font-medium truncate">{{ authUser.nombre || '' }} {{ authUser.apellido || '' }}</div>
                            <div class="text-xs mt-1">
                                <span class="px-2 py-1 rounded-full bg-white/20 text-white backdrop-blur-sm">
                                    {{ authUser.rol?.nombre || 'Sin rol' }}
                                </span>
                            </div>
                        </div>
                        <button
                            @click="logout"
                            class="w-full flex items-center justify-center px-4 py-2.5 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-lg transition-all duration-200 shadow-md hover:shadow-lg transform hover:-translate-y-0.5"
                            title="Cerrar sesión"
                        >
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                            Cerrar Sesión
                        </button>
                    </div>
                </template>
                <template v-else>
                    <div class="text-sm text-indigo-200 text-center py-2">
                        <p class="mb-2">No autenticado</p>
                        <button
                            @click="goToLogin"
                            type="button"
                            class="w-full inline-flex items-center justify-center px-3 py-2 text-xs font-medium text-white bg-indigo-500 hover:bg-indigo-400 rounded-md transition-colors"
                        >
                            Iniciar Sesión
                        </button>
                    </div>
                </template>
            </div>
        </aside>

        <!-- Overlay para móvil -->
        <div
            v-if="sidebarOpen"
            @click="toggleSidebar"
            class="fixed inset-0 bg-gray-600 bg-opacity-75 z-40 lg:hidden"
        ></div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col lg:ml-0">
            <!-- Top Bar -->
            <header class="bg-white/80 backdrop-blur-md shadow-md h-16 flex items-center justify-between px-4 lg:px-6 border-b border-indigo-100">
                <div class="flex items-center gap-4">
                    <button
                        @click="toggleSidebar"
                        class="lg:hidden text-indigo-600 hover:text-indigo-700 hover:bg-indigo-50 p-2 rounded-lg transition-colors"
                        aria-label="Abrir menú"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <h2 class="text-lg font-semibold text-indigo-700 hidden sm:block">Carpintería Jorge</h2>
                </div>
                
                <div class="flex-1 flex items-center justify-end space-x-4">
                    <!-- Barra de búsqueda -->
                    <SearchBar />
                    
                    <!-- Selector de temas -->
                    <ThemeSelector />
                    
                    <!-- Controles de accesibilidad -->
                    <button
                        @click="showAccessibility = !showAccessibility"
                        class="px-3 py-1.5 text-sm border border-indigo-200 rounded-lg hover:bg-indigo-50 text-indigo-700 transition-colors"
                        title="Accesibilidad"
                    >
                        ♿
                    </button>
                    
                    <!-- Información del usuario (sin botón de cerrar sesión, solo en sidebar) -->
                    <template v-if="authUser && authUser.id">
                        <div class="hidden md:flex items-center gap-2 text-sm text-indigo-600 border-r border-indigo-200 pr-4">
                            <span class="font-medium">{{ authUser.nombre || '' }} {{ authUser.apellido || '' }}</span>
                            <span class="px-2 py-1 rounded-full bg-indigo-100 text-indigo-700 text-xs">
                                {{ authUser.rol?.nombre || 'Sin rol' }}
                            </span>
                        </div>
                    </template>
                </div>

                <div class="flex-1 flex items-center justify-end space-x-4">
                    <!-- Barra de búsqueda -->
                    <SearchBar />
                    
                    <!-- Selector de temas -->
                    <ThemeSelector />
                    
                    <!-- Controles de accesibilidad -->
                    <button
                        @click="showAccessibility = !showAccessibility"
                        class="px-3 py-1.5 text-sm border border-indigo-200 rounded-lg hover:bg-indigo-50 text-indigo-700 transition-colors"
                        title="Accesibilidad"
                    >
                        ♿
                    </button>
                </div>
            </header>
            
            <!-- Panel de accesibilidad (desplegable) -->
            <div v-if="showAccessibility" class="absolute right-4 top-20 z-50">
                <AccessibilityControls />
            </div>

        <!-- Mensajes Flash -->
        <div v-if="flashSuccess" class="fixed top-4 right-4 z-50">
            <Alert type="success" :message="flashSuccess" />
        </div>
        <div v-if="flashError" class="fixed top-4 right-4 z-50">
            <Alert type="error" :message="flashError" />
        </div>
        <div v-if="flashMessage" class="fixed top-4 right-4 z-50">
            <Alert type="info" :message="flashMessage" />
        </div>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto">
                <slot />
            </main>

            <!-- Footer -->
            <footer class="bg-white/60 backdrop-blur-sm border-t border-indigo-100 py-4 px-4 lg:px-6">
                <div class="flex justify-between items-center text-sm text-indigo-600">
                    <div>
                        <p>&copy; {{ new Date().getFullYear() }} Carpintería Jorge. Todos los derechos reservados.</p>
                    </div>
                    <div v-if="props.pageVisits !== null && props.pageVisits !== undefined" class="text-right">
                        <p class="px-3 py-1 bg-indigo-100 rounded-full inline-block">Visitas: {{ props.pageVisits || 0 }}</p>
                    </div>
                </div>
            </footer>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import { router, usePage, Link } from '@inertiajs/vue3';
import ThemeSelector from '@/Components/ThemeSelector.vue';
import AccessibilityControls from '@/Components/AccessibilityControls.vue';
import SearchBar from '@/Components/SearchBar.vue';
import Alert from '@/Components/UI/Alert.vue';

const props = defineProps({
    menuItems: {
        type: Array,
        default: () => [],
    },
    pageVisits: {
        type: Number,
        default: null,
    },
});

let page = null;
try {
    page = usePage();
} catch (e) {
    console.warn('Error initializing usePage:', e);
}

const sidebarOpen = ref(false);
const showAccessibility = ref(false);

// Computed para obtener el usuario de forma segura
const authUser = computed(() => {
    try {
        if (!page) {
            console.warn('Page no disponible en authUser computed');
            return null;
        }
        if (!page.props) {
            console.warn('Page.props no disponible en authUser computed');
            return null;
        }
        
        // Debug: mostrar toda la estructura de props
        console.log('Page props completo:', {
            hasAuth: !!page.props.auth,
            authKeys: page.props.auth ? Object.keys(page.props.auth) : [],
            hasUser: !!page.props.user,
            allKeys: Object.keys(page.props || {}),
        });
        
        // Acceder directamente a page.props.auth.user
        const user = page.props.auth?.user || null;
        
        // Log siempre para debugging
        if (user) {
            console.log('✅ Usuario autenticado detectado:', {
                id: user.id,
                nombre: user.nombre,
                apellido: user.apellido,
                email: user.email,
                rol: user.rol?.nombre,
            });
        } else {
            console.warn('⚠️ No se detectó usuario autenticado. Props:', {
                auth: page.props.auth,
                user: page.props.user,
            });
        }
        
        return user;
    } catch (e) {
        console.error('❌ Error accessing auth user:', e);
        return null;
    }
});

// Computed para obtener flash messages de forma segura
const flashSuccess = computed(() => {
    try {
        if (!page || !page.props || !page.props.flash) return null;
        return page.props.flash.success || null;
    } catch (e) {
        return null;
    }
});

const flashError = computed(() => {
    try {
        if (!page || !page.props || !page.props.flash) return null;
        return page.props.flash.error || null;
    } catch (e) {
        return null;
    }
});

const flashMessage = computed(() => {
    try {
        if (!page || !page.props || !page.props.flash) return null;
        return page.props.flash.message || null;
    } catch (e) {
        return null;
    }
});

const toggleSidebar = () => {
    sidebarOpen.value = !sidebarOpen.value;
};

const isActiveRoute = (route) => {
    if (!route || typeof route !== 'string') return false;
    try {
        const currentUrl = window.location.pathname;
        // Si la ruta es una URL completa, extraer el pathname
        let routePath = route;
        if (route.startsWith('http://') || route.startsWith('https://')) {
            try {
                routePath = new URL(route).pathname;
            } catch (e) {
                // Si falla, usar la ruta tal cual
                routePath = route.replace(/^https?:\/\/[^\/]+/, '');
            }
        }
        // Normalizar rutas (remover trailing slash)
        const normalizedCurrent = currentUrl.replace(/\/$/, '');
        const normalizedRoute = routePath.replace(/\/$/, '');
        return normalizedCurrent === normalizedRoute || normalizedCurrent.startsWith(normalizedRoute + '/');
    } catch (e) {
        console.warn('Error checking active route:', e, route);
        return false;
    }
};

// Cerrar sidebar en móvil cuando se hace clic fuera
const closeSidebarOnMobile = () => {
    if (window.innerWidth < 1024) {
        sidebarOpen.value = false;
    }
};

// Manejar resize y estado inicial
const handleResize = () => {
    if (window.innerWidth >= 1024) {
        sidebarOpen.value = true; // Siempre visible en desktop
    } else {
        sidebarOpen.value = false; // Oculto por defecto en móvil
    }
};

onMounted(() => {
    window.addEventListener('resize', handleResize);
    
    // Estado inicial según el tamaño de pantalla
    if (window.innerWidth >= 1024) {
        sidebarOpen.value = true;
    }
});

onUnmounted(() => {
    window.removeEventListener('resize', handleResize);
});

// Observar cambios en la URL para cerrar sidebar en móvil
const currentUrl = computed(() => {
    try {
        if (page && page.url) {
            return page.url;
        }
        return window.location.pathname;
    } catch (e) {
        return window.location.pathname;
    }
});

// Solo hacer watch si page está disponible
if (page) {
    watch(currentUrl, () => {
        closeSidebarOnMobile();
    });
}

const logout = () => {
    if (confirm('¿Está seguro de que desea cerrar sesión?')) {
        // Usar GET directamente a /logout (más simple y directo)
        window.location.href = '/logout';
    }
};

const goToLogin = () => {
    console.log('Redirigiendo a login...');
    // Usar window.location directamente para asegurar que funcione
    window.location.href = '/login';
};
</script>

