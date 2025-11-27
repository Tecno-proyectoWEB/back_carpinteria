import route from '@tofandel/ziggy-js';

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
        const routeFn = typeof route === 'function' ? route : (window.route || null);
        
        if (!routeFn) {
            // Fallback: construir la URL manualmente basándose en el nombre
            return buildFallbackRoute(name, params);
        }
        
        if (params !== null && params !== undefined) {
            return routeFn(name, params);
        }
        return routeFn(name);
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
    
    if (params !== null && params !== undefined) {
        if (name.includes('show') || name.includes('edit') || name.includes('destroy') || name.includes('update')) {
            return `/${resource}/${params}`;
        }
        if (name.includes('confirmar')) {
            return `/${resource}/${params}/confirmar`;
        }
    }
    
    if (name.includes('create')) {
        return `/${resource}/create`;
    }
    
    if (name.includes('index')) {
        return `/${resource}`;
    }
    
    // Rutas especiales
    if (name.includes('ventas')) return '/reportes/ventas';
    if (name.includes('compras') && name.includes('reportes')) return '/reportes/compras';
    if (name.includes('inventario') && name.includes('reportes')) return '/reportes/inventario';
    if (name.includes('stock')) return '/inventario/stock';
    if (name.includes('store-contado')) return '/pedidos/store-contado';
    if (name.includes('store-credito')) return '/pedidos/store-credito';
    
    return `/${resource}`;
}

