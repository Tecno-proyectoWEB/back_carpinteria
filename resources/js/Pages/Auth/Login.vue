<template>
    <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div>
                <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                    Iniciar Sesión
                </h2>
            </div>
            <form @submit.prevent="submit" class="mt-8 space-y-6">
                <div class="rounded-md shadow-sm -space-y-px">
                    <div>
                        <label for="email" class="sr-only">Email</label>
                        <input
                            id="email"
                            v-model="form.email"
                            type="email"
                            required
                            class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
                            placeholder="Email"
                        />
                    </div>
                    <div>
                        <label for="password" class="sr-only">Contraseña</label>
                        <input
                            id="password"
                            v-model="form.password"
                            type="password"
                            required
                            class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
                            placeholder="Contraseña"
                        />
                    </div>
                </div>

                <div v-if="$page.props.errors?.email" class="text-red-600 text-sm text-center">
                    {{ $page.props.errors.email }}
                </div>

                <div>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    >
                        {{ form.processing ? 'Iniciando sesión...' : 'Iniciar sesión' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { useForm, router } from '@inertiajs/vue3'
import { route } from '../../ziggy.js'

const form = useForm({
    email: '',
    password: '',
})

const submit = () => {
    form.post(route('login'), {
        preserveState: false,
        preserveScroll: false,
        onSuccess: () => {
            // Redirección completa del navegador inmediatamente
            // Esto asegura que las cookies de sesión se envíen correctamente
            window.location.href = route('dashboard')
        },
        onError: (errors) => {
            // Los errores se muestran automáticamente en el template
            console.error('Errores de login:', errors)
        },
        onFinish: () => {
            // Fallback: si por alguna razón onSuccess no se ejecutó, redirigir aquí
            // Solo si no hay errores y aún estamos en /login
            setTimeout(() => {
                if (window.location.pathname === '/login' && !form.hasErrors) {
                    window.location.href = route('dashboard')
                }
            }, 200)
        }
    })
}
</script>

