<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Sidebar -->
        <aside class="fixed inset-y-0 left-0 w-64 bg-gray-900 text-white">
            <div class="flex flex-col h-full">
                <!-- Logo -->
                <div class="p-6 border-b border-gray-800">
                    <h1 class="text-2xl font-bold">Carpintería</h1>
                </div>

                <!-- Navigation -->
                <nav class="flex-1 p-4 space-y-2">
                    <Link :href="route('dashboard')" class="block px-4 py-2 rounded hover:bg-gray-800" :class="{ 'bg-gray-800': $page.url.startsWith('/dashboard') }">
                        Dashboard
                    </Link>
                    <Link :href="route('usuarios.index')" class="block px-4 py-2 rounded hover:bg-gray-800" :class="{ 'bg-gray-800': $page.url.startsWith('/usuarios') }">
                        Usuarios
                    </Link>
                    <Link :href="route('roles.index')" class="block px-4 py-2 rounded hover:bg-gray-800" :class="{ 'bg-gray-800': $page.url.startsWith('/roles') }">
                        Roles y Permisos
                    </Link>
                    <Link :href="route('productos.index')" class="block px-4 py-2 rounded hover:bg-gray-800" :class="{ 'bg-gray-800': $page.url.startsWith('/productos') }">
                        Productos
                    </Link>
                    <Link :href="route('servicios.index')" class="block px-4 py-2 rounded hover:bg-gray-800" :class="{ 'bg-gray-800': $page.url.startsWith('/servicios') }">
                        Servicios
                    </Link>
                    <Link :href="route('materiales.index')" class="block px-4 py-2 rounded hover:bg-gray-800" :class="{ 'bg-gray-800': $page.url.startsWith('/materiales') }">
                        Insumos
                    </Link>
                    <Link :href="route('inventarios.index')" class="block px-4 py-2 rounded hover:bg-gray-800" :class="{ 'bg-gray-800': $page.url.startsWith('/inventarios') }">
                        Inventarios
                    </Link>
                    <Link :href="route('pedidos.index')" class="block px-4 py-2 rounded hover:bg-gray-800" :class="{ 'bg-gray-800': $page.url.startsWith('/pedidos') }">
                        Ventas
                    </Link>
                    <Link :href="route('pagos.index')" class="block px-4 py-2 rounded hover:bg-gray-800" :class="{ 'bg-gray-800': $page.url.startsWith('/pagos') }">
                        Pagos
                    </Link>
                    <Link :href="route('reportes.index')" class="block px-4 py-2 rounded hover:bg-gray-800" :class="{ 'bg-gray-800': $page.url.startsWith('/reportes') }">
                        Reportes
                    </Link>
                </nav>

                <!-- User Info -->
                <div class="p-4 border-t border-gray-800">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium">{{ auth.user?.nombre }} {{ auth.user?.apellido }}</p>
                            <p class="text-xs text-gray-400">{{ auth.user?.rol?.nombre }}</p>
                        </div>
                        <button @click="logout" class="text-gray-400 hover:text-white">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="ml-64 p-8">
            <slot />
        </main>
    </div>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3'
import { route } from '../ziggy.js'

defineProps({
    auth: Object,
})

const logout = () => {
    router.post('/logout')
}
</script>

