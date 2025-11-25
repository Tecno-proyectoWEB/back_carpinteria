<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Navbar -->
        <nav class="bg-white shadow-lg">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <div class="flex-shrink-0 flex items-center">
                            <h1 class="text-xl font-bold text-gray-900">Carpintería Jorge</h1>
                        </div>
                        <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
                            <a
                                v-for="item in menuItems"
                                :key="item.id"
                                :href="item.ruta"
                                class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium"
                            >
                                {{ item.nombre }}
                            </a>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4">
                        <!-- Barra de búsqueda -->
                        <SearchBar />
                        
                        <!-- Selector de temas -->
                        <ThemeSelector />
                        
                        <!-- Controles de accesibilidad (botón para abrir modal) -->
                        <button
                            @click="showAccessibility = !showAccessibility"
                            class="px-3 py-1 text-sm border border-gray-300 rounded-md hover:bg-gray-50"
                            title="Accesibilidad"
                        >
                            ♿
                        </button>
                        
                        <div v-if="$page.props.auth.user" class="flex items-center space-x-4">
                            <span class="text-sm text-gray-700">
                                {{ $page.props.auth.user.nombre }} {{ $page.props.auth.user.apellido }}
                            </span>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-indigo-100 text-indigo-800">
                                {{ $page.props.auth.user.rol?.nombre }}
                            </span>
                            <button
                                @click="logout"
                                class="text-sm text-gray-500 hover:text-gray-700"
                            >
                                Cerrar Sesión
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Panel de accesibilidad (desplegable) -->
            <div v-if="showAccessibility" class="absolute right-4 top-16 z-50">
                <AccessibilityControls />
            </div>
        </nav>

        <!-- Mensajes Flash -->
        <div v-if="$page.props.flash?.success" class="fixed top-4 right-4 z-50">
            <Alert type="success" :message="$page.props.flash.success" />
        </div>
        <div v-if="$page.props.flash?.error" class="fixed top-4 right-4 z-50">
            <Alert type="error" :message="$page.props.flash.error" />
        </div>
        <div v-if="$page.props.flash?.message" class="fixed top-4 right-4 z-50">
            <Alert type="info" :message="$page.props.flash.message" />
        </div>

        <!-- Page Content -->
        <main>
            <slot />
        </main>

        <!-- Footer -->
        <footer class="bg-white border-t mt-auto">
            <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center text-sm text-gray-500">
                    <div>
                        <p>&copy; {{ new Date().getFullYear() }} Carpintería Jorge. Todos los derechos reservados.</p>
                    </div>
                    <div v-if="pageVisits" class="text-right">
                        <p>Visitas: {{ pageVisits }}</p>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import ThemeSelector from '@/Components/ThemeSelector.vue';
import AccessibilityControls from '@/Components/AccessibilityControls.vue';
import SearchBar from '@/Components/SearchBar.vue';
import Alert from '@/Components/UI/Alert.vue';

defineProps({
    menuItems: {
        type: Array,
        default: () => [],
    },
    pageVisits: {
        type: Number,
        default: null,
    },
});

const showAccessibility = ref(false);

const logout = () => {
    router.post('/logout');
};
</script>

