export function useStorage() {
    // Obtener la URL base desde la configuración de Laravel o usar una por defecto
    // En Laravel, esto normalmente viene de config('app.url') o APP_URL
    // Intentar obtener desde window si está disponible (desde Inertia o configuración global)


    const baseUrl = 'https://www.tecnoweb.org.bo/inf513/grupo11sc/proyecto2/public';

    // Función normal para productos, documentos, etc.
    const storageUrl = (path) => {
        if (!path) return '';
        const cleanPath = path.replace(/^\/+/, '');
        return `${baseUrl}/storage/${cleanPath}`;
    };

    // Función especial para QR y otros casos donde el path puede venir con 'storage/' incluido
    const storageUrlSafe = (path) => {
        if (!path) return '';

        let cleanPath = path.replace(/^\/+/, ''); // Remover slashes iniciales

        // Si el path ya incluye 'storage/', no duplicarlo
        if (cleanPath.startsWith('storage/')) {
            cleanPath = cleanPath.substring(8); // Remover 'storage/'
        }

        return `${baseUrl}/storage/${cleanPath}`;
    };

    return {
        storageUrl,      // Para uso normal
        storageUrlSafe   // Para casos especiales como QR
    };
}

