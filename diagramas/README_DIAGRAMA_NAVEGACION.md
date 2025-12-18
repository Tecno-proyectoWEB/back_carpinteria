# Diagrama de Navegación - Sistema de Gestión de Carpintería

## 📋 Descripción

Este diagrama representa la estructura de navegación completa del sistema de gestión de carpintería, siguiendo el estándar **UML 2.5**. Muestra todas las páginas (vistas) del sistema y las relaciones de navegación entre ellas.

## 📁 Archivos

- **`Diagrama_Navegacion_UML.puml`**: Archivo fuente en PlantUML que contiene la definición del diagrama

## 🛠️ Cómo Generar el Diagrama

### Opción 1: PlantUML Online (Recomendado - Más Fácil)

1. **Visita**: http://www.plantuml.com/plantuml/uml/
2. **Copia** todo el contenido del archivo `Diagrama_Navegacion_UML.puml`
3. **Pega** el contenido en el editor online
4. **Exporta** el diagrama:
   - Click derecho en el diagrama → "Descargar como PNG"
   - O usa el botón de exportar para obtener PDF, SVG, etc.

### Opción 2: PlantUML Desktop

1. **Descarga PlantUML**: http://plantuml.com/download
2. **Instala Java** (requerido para PlantUML)
3. **Abre** el archivo `.puml` con PlantUML
4. **Exporta** a PNG, PDF o SVG

### Opción 3: Extensiones de VS Code

1. **Instala** la extensión "PlantUML" en VS Code
2. **Abre** el archivo `.puml`
3. **Presiona** `Alt + D` para previsualizar
4. **Exporta** desde el menú de la extensión

### Opción 4: Draw.io / diagrams.net

1. **Visita**: https://app.diagrams.net/
2. **Crea** un nuevo diagrama
3. **Importa** el archivo `.puml` (si soporta) o recrea manualmente basándote en la estructura

## 📊 Formatos de Exportación

Una vez generado, puedes exportar el diagrama en:

- **PNG** → Para insertar en PowerPoint, Word, etc.
- **PDF** → Para documentos formales
- **SVG** → Para escalado sin pérdida de calidad
- **EPS** → Para impresión profesional

## 💡 Cómo Usar en PowerPoint

1. **Genera** el diagrama en formato PNG o PDF usando una de las opciones anteriores
2. **Abre** PowerPoint
3. **Inserta** → **Imágenes** → Selecciona el archivo PNG/PDF generado
4. **Ajusta** el tamaño según necesites

**Nota**: PowerPoint NO puede abrir directamente archivos `.puml`, pero sí puede insertar las imágenes generadas (PNG, PDF, SVG).

## 📐 Estructura del Diagrama

El diagrama está organizado en:

### 1. **Autenticación**
- Página de Login

### 2. **Dashboard**
- Hub central de navegación

### 3. **8 Casos de Uso (CU)**
- **CU1**: Usuarios y Roles
- **CU2**: Productos
- **CU3**: Servicios
- **CU4**: Materiales
- **CU5**: Inventarios
- **CU6**: Ventas
- **CU7**: Pagos
- **CU8**: Reportes

### 4. **Funcionalidades Adicionales**
- Búsqueda Global
- Resultados de Búsqueda

## 🔗 Tipos de Navegación Representados

- **Flechas sólidas (→)**: Navegación directa entre páginas
- **Flechas punteadas (..>)**: Relaciones indirectas o contextuales
- **Etiquetas en flechas**: Acciones que disparan la navegación (ej: [crear], [editar])

## 🎨 Convenciones UML 2.5

- **Componentes con estereotipo `<<page>>`**: Representan páginas/vistas web
- **Paquetes**: Agrupan páginas relacionadas por módulo
- **Notas**: Proporcionan información adicional sobre rutas y métodos
- **Colores**: Diferentes colores por módulo para mejor visualización

## 📝 Notas Técnicas

- Todas las rutas requieren autenticación excepto `/login`
- El Dashboard es el punto central de navegación
- Cada módulo tiene operaciones CRUD estándar (Index, Create, Edit, Show)
- Las ventas pueden navegar a pagos y viceversa (relación cruzada)

## 🔄 Actualización del Diagrama

Si agregas nuevas páginas o rutas al sistema:

1. **Edita** el archivo `Diagrama_Navegacion_UML.puml`
2. **Agrega** los nuevos componentes en el paquete correspondiente
3. **Define** las relaciones de navegación
4. **Regenera** el diagrama usando una de las opciones anteriores

## 📚 Recursos Adicionales

- **Documentación PlantUML**: https://plantuml.com/
- **UML 2.5 Specification**: https://www.omg.org/spec/UML/2.5/
- **PlantUML Syntax Guide**: https://plantuml.com/guide

---

**Generado para**: Sistema de Gestión de Carpintería  
**Estándar**: UML 2.5  
**Fecha**: 2025

