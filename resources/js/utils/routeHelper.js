/**
 * Función helper para generar rutas de forma segura
 * Maneja casos donde Ziggy no está inicializado o las rutas no están disponibles
 *
 * @param {string} name - Nombre de la ruta
 * @param {number|string|null} params - Parámetros de la ruta (opcional)
 * @returns {string} URL de la ruta o fallback
 */
export function getRoute(name, params = null) {
    try {
        // Intentar usar Ziggy desde window (disponible después de @routes en blade)
        // Ziggy se expone globalmente como window.route cuando se usa @routes
        if (typeof window !== 'undefined' && window.route && typeof window.route === 'function') {
            if (params !== null && params !== undefined) {
                return window.route(name, params);
            }
            return window.route(name);
        }

        // Fallback: construir la URL manualmente basándose en el nombre
        return buildFallbackRoute(name, params);
    } catch (error) {
        console.warn(`Route ${name} not found:`, error);
        return buildFallbackRoute(name, params);
    }
}

/**
 * Construye una ruta de fallback basándose en el nombre
 */
function buildFallbackRoute(name, params) {
    // Extraer el recurso base del nombre de la ruta
    const resource = name.split('.')[0];
    const action = name.split('.')[1] || '';

    // Construir la ruta según la acción
    if (params !== null && params !== undefined) {
        if (action === 'show' || action === 'edit' || action === 'update' || action === 'destroy') {
            if (action === 'edit') {
                return `/${resource}/${params}/edit`;
            }
            if (action === 'update') {
                return `/${resource}/${params}`;
            }
            if (action === 'destroy') {
                return `/${resource}/${params}`;
            }
            return `/${resource}/${params}`;
        }
        if (action === 'confirmar') {
            return `/${resource}/${params}/confirmar`;
        }
    }

    if (action === 'create') {
        return `/${resource}/create`;
    }

    if (action === 'index' || action === '') {
        return `/${resource}`;
    }

    // Rutas especiales
    if (name.includes('ventas')) return '/reportes/ventas';
    if (name.includes('compras') && name.includes('reportes')) return '/reportes/compras';
    if (name.includes('inventario') && name.includes('reportes')) return '/reportes/inventario';
    if (name.includes('stock')) return '/inventario/stock';
    if (name.includes('store-contado')) return '/ventas/store-contado';
    if (name.includes('store-credito')) return '/ventas/store-credito';

    return `/${resource}`;
}

