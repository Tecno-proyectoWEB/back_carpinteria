# Instrucciones para Importar el Diagrama en Enterprise Architect

## 📋 Archivo Generado

- **`Diagrama_Navegacion_EA.xml`**: Archivo XMI 2.5.1 compatible con Enterprise Architect

## 🚀 Pasos para Importar en Enterprise Architect

### Método 1: Importar XMI (Recomendado)

1. **Abre Enterprise Architect**
2. **Crea un nuevo proyecto** o abre uno existente
   - File → New Project (o File → Open Project)
3. **Importa el archivo XMI**:
   - **Project** → **Import/Export** → **Import Package from XMI**
   - O usa el menú: **Tools** → **Import/Export** → **Import Package from XMI**
4. **Selecciona el archivo**:
   - Navega hasta: `diagramas/Diagrama_Navegacion_EA.xml`
   - Selecciona el archivo
5. **Configura la importación**:
   - **XMI Version**: Selecciona "XMI 2.5.1" o "XMI 2.1"
   - **Package**: Puedes crear un nuevo paquete o importar en uno existente
   - **Options**: 
     - ✅ Marca "Create Diagram" si quieres que se genere el diagrama automáticamente
     - ✅ Marca "Import Stereotypes" para mantener los estereotipos
6. **Click en "Import"**
7. **Espera** a que se complete la importación

### Método 2: Importar como Modelo Completo

1. **Abre Enterprise Architect**
2. **File** → **Import/Export** → **Import Model from XMI**
3. **Selecciona** el archivo `Diagrama_Navegacion_EA.xml`
4. **Configura**:
   - **XMI Version**: 2.5.1
   - **Target Package**: Selecciona dónde importar
5. **Click en "Import"**

### Método 3: Copiar y Pegar desde el Navegador de Proyectos

Si tienes otro proyecto con el modelo:

1. Abre el proyecto fuente
2. Selecciona el paquete/modelo
3. **Right-click** → **Copy**
4. Abre tu proyecto destino
5. **Right-click** → **Paste Package**

## 📊 Crear el Diagrama Visual

Después de importar, puedes crear el diagrama visual:

### Opción A: Crear Diagrama de Componentes

1. **Right-click** en el paquete importado
2. **New Diagram** → **Component**
3. **Arrastra** los componentes desde el navegador de proyectos al diagrama
4. **Conecta** los componentes con las relaciones (Dependencies)

### Opción B: Usar el Diagrama Generado Automáticamente

Si marcaste "Create Diagram" durante la importación, el diagrama debería aparecer automáticamente.

## 🎨 Personalizar el Diagrama

### Agregar Estereotipos

1. **Selecciona** un componente
2. **Right-click** → **Properties** → **Stereotypes**
3. Agrega el estereotipo `<<page>>` si no está presente

### Cambiar Colores por Módulo

1. **Selecciona** los componentes de un módulo
2. **Right-click** → **Appearance** → **Fill Color**
3. Asigna colores diferentes por módulo:
   - CU1 (Usuarios/Roles): Azul claro
   - CU2 (Productos): Morado claro
   - CU3 (Servicios): Verde claro
   - CU4 (Materiales): Naranja claro
   - CU5 (Inventarios): Rosa claro
   - CU6 (Ventas): Turquesa claro
   - CU7 (Pagos): Amarillo claro
   - CU8 (Reportes): Verde lima

### Agregar Notas

1. **Toolbox** → **Common** → **Note**
2. Arrastra una nota al diagrama
3. **Conecta** la nota al componente con un **Note Link**

## 📝 Verificar la Importación

Después de importar, verifica que:

- ✅ Todos los paquetes están presentes (8 CU + Dashboard + Autenticación)
- ✅ Todos los componentes están importados
- ✅ Las relaciones (Dependencies) están conectadas
- ✅ Los atributos (rutas) están visibles en las propiedades

## 🔧 Solución de Problemas

### Error: "Invalid XMI Format"

- **Solución**: Asegúrate de seleccionar "XMI 2.5.1" o "XMI 2.1" en las opciones de importación
- Verifica que el archivo XML esté bien formado

### Los Componentes no se muestran en el Diagrama

- **Solución**: 
  1. Crea un nuevo diagrama de Componentes
  2. Arrastra manualmente los componentes desde el navegador de proyectos

### Las Relaciones no aparecen

- **Solución**:
  1. Abre el diagrama
  2. **Toolbox** → **Common** → **Dependency**
  3. Conecta manualmente los componentes según el diagrama

### Estereotipos no se importan

- **Solución**:
  1. **Settings** → **UML Types** → **Stereotypes**
  2. Agrega manualmente el estereotipo `<<page>>` si es necesario

## 📚 Recursos Adicionales

- **Enterprise Architect User Guide**: Consulta la documentación oficial
- **XMI Import Documentation**: Revisa la sección de importación XMI en el manual de EA

## 💡 Consejos

1. **Guarda** el proyecto después de importar
2. **Crea una copia de seguridad** antes de hacer cambios importantes
3. **Usa versiones** del modelo si trabajas en equipo
4. **Exporta** el diagrama como imagen (PNG, PDF) para presentaciones

---

**Formato**: XMI 2.5.1  
**Compatible con**: Enterprise Architect 13.0+  
**Fecha**: 2025

