import ziggyRoute from '@tofandel/ziggy-js/dist/index.m.js'

// Rutas de fallback
const fallbackRoutes = {
    'dashboard': '/dashboard',
    'productos.index': '/productos',
    'productos.create': '/productos/create',
    'productos.store': '/productos',
    'productos.update': (id) => `/productos/${id}`,
    'productos.destroy': (id) => `/productos/${id}`,
    'productos.edit': (id) => `/productos/${id}/edit`,
    'productos.show': (id) => `/productos/${id}`,
    'materiales.index': '/materiales',
    'materiales.create': '/materiales/create',
    'materiales.store': '/materiales',
    'materiales.update': (id) => `/materiales/${id}`,
    'materiales.destroy': (id) => `/materiales/${id}`,
    'materiales.edit': (id) => `/materiales/${id}/edit`,
    'ventas.index': '/ventas',
    'ventas.create': '/ventas/create',
    'ventas.storeContado': '/ventas/contado',
    'ventas.storeCredito': '/ventas/credito',
    'ventas.show': (id) => `/ventas/${id}`,
    'ventas.update': (id) => `/ventas/${id}`,
    'ventas.destroy': (id) => `/ventas/${id}`,
    'buscar': '/buscar',
    'payment.callback': '/payment/callback',
    'payment.status': (id) => `/payment/status/${id}`,
    'usuarios.index': '/usuarios',
    'usuarios.create': '/usuarios/create',
    'usuarios.destroy': (id) => `/usuarios/${id}`,
    'usuarios.edit': (id) => `/usuarios/${id}/edit`,
    'roles.index': '/roles',
    'roles.create': '/roles/create',
    'roles.store': '/roles',
    'roles.update': (id) => `/roles/${id}`,
    'roles.destroy': (id) => `/roles/${id}`,
    'roles.edit': (id) => `/roles/${id}/edit`,
    'servicios.index': '/servicios',
    'servicios.create': '/servicios/create',
    'servicios.store': '/servicios',
    'servicios.update': (id) => `/servicios/${id}`,
    'servicios.destroy': (id) => `/servicios/${id}`,
    'servicios.edit': (id) => `/servicios/${id}/edit`,
    'inventarios.index': '/inventarios',
    'inventarios.create': '/inventarios/create',
    'inventarios.store': '/inventarios',
    'inventarios.show': (id) => `/inventarios/${id}`,
    'pagos.index': '/pagos',
    'pagos.create': '/pagos/create',
    'pagos.store': '/pagos',
    'pagos.update': (id) => `/pagos/${id}`,
    'pagos.destroy': (id) => `/pagos/${id}`,
    'pagos.show': (id) => `/pagos/${id}`,
    'pagos.edit': (id) => `/pagos/${id}/edit`,
    'pagos.registrar': (id) => `/pagos/${id}/registrar`,
    'reportes.index': '/reportes',
    'reportes.ventas': '/reportes/ventas',
    'reportes.estadisticas': '/reportes/estadisticas',
    'reportes.inventario': '/reportes/inventario',
}

// Helper global para usar route() en todos los componentes
export function route(name, params = {}, absolute = true) {
    try {
        // Intentar usar Ziggy directamente
        // Si hay un objeto window.Ziggy disponible (desde @routes), usarlo
        if (typeof window !== 'undefined' && window.Ziggy) {
            return ziggyRoute(name, params, absolute, window.Ziggy)
        }

        return ziggyRoute(name, params, absolute)
    } catch (e) {
        console.warn(`Route "${name}" not found, using fallback:`, e)

        // Usar fallback
        const routePath = fallbackRoutes[name]
        if (typeof routePath === 'function') {
            // Corrección 5.7: Manejar diferentes formatos de parámetros
            let id = null
            if (typeof params === 'number') {
                id = params
            } else if (typeof params === 'object' && params !== null) {
                id = params.id || (Object.keys(params).length > 0 ? Object.values(params)[0] : null)
            } else if (params !== null && params !== undefined) {
                id = params
            }
            return routePath(id)
        }
        return routePath || '#'
    }
}
