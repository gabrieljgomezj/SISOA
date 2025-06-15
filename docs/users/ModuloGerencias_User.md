
### Documentación para Usuarios Finales (Archivo Markdown)

Este documento será más conciso y se centrará en cómo usar el módulo, sin entrar en detalles técnicos.

**Nombre de archivo sugerido:** `ModuloGerencias_User.md`

```markdown
# Módulo de Gerencias: Guía para Usuarios

## ¿Qué es el Módulo de Gerencias?

El Módulo de Gerencias es una sección del sistema de Ferrominera SST que le permite **gestionar las diferentes áreas o departamentos (gerencias)** de la empresa. Aquí podrá ver, añadir, modificar y organizar la información de cada gerencia.

Este módulo es fundamental porque ayuda a mantener la información organizada y a clasificar otros datos del sistema (como usuarios o centros de trabajo) bajo la gerencia correcta.

## ¿Quién puede usar este módulo?

Solo los usuarios con el perfil de **"Administrador"** tienen acceso a este módulo y pueden realizar cambios en la información de las gerencias.

## Cómo Acceder al Módulo de Gerencias

1.  Inicie sesión en el sistema Ferrominera SST con su usuario y contraseña de **Administrador**.
2.  Desde el Dashboard principal, navegue hasta el menú de **"Mantenimiento"**.
3.  Dentro del submenú de "Mantenimiento", haga clic en **"Gerencias"**.

## Funcionalidades y Cómo Usarlas

Al acceder al módulo, verá una tabla con el listado de todas las gerencias registradas:

### 1. Ver el Listado de Gerencias

La tabla principal muestra la siguiente información para cada gerencia:

* **ID:** Un número de identificación interno del sistema.
* **Código:** Un código único asignado a cada gerencia (ej. "G-RRHH", "G-OPERACIONES").
* **Nombre de Gerencia:** El nombre completo de la gerencia (ej. "Gerencia de Recursos Humanos").
* **Estado:** Indica si la gerencia está "Activa" (disponible para uso) o "Inactiva" (no disponible).

### 2. Buscar una Gerencia

Si necesita encontrar una gerencia específica, puede usar la barra de búsqueda ubicada en la parte superior de la tabla.

1.  Escriba el **Código** o el **Nombre** de la gerencia que desea buscar en el campo de texto.
2.  Haga clic en el botón de **búsqueda** (icono de lupa) o presione Enter.
3.  La tabla se actualizará mostrando solo las gerencias que coincidan con su búsqueda.

### 3. Añadir una Nueva Gerencia

Para registrar una nueva gerencia en el sistema:

1.  Haga clic en el botón **"Añadir Gerencia"** (icono de más) que se encuentra encima de la tabla.
2.  Se abrirá un formulario donde deberá completar los siguientes campos:
    * **Código de Gerencia:** Ingrese un código único y corto para la nueva gerencia (ej. "G-FINANZAS"). Este campo es obligatorio.
    * **Nombre de la Gerencia:** Escriba el nombre completo de la gerencia (ej. "Gerencia de Finanzas y Contabilidad"). Este campo es obligatorio.
    * **Activo (Casilla de verificación):** Marque esta casilla si desea que la gerencia esté activa y disponible para su uso en el sistema. Desmárquela si prefiere que inicie como inactiva.
3.  Haga clic en el botón **"Guardar Gerencia"**.
4.  Si los datos son correctos, recibirá un mensaje de confirmación y la nueva gerencia aparecerá en el listado. Si hay errores (ej. código o nombre ya existen), el sistema le informará.

### 4. Editar una Gerencia Existente

Para modificar la información de una gerencia ya registrada:

1.  En el listado de gerencias, busque la gerencia que desea editar.
2.  En la columna "Acciones", haga clic en el **icono de lápiz** (Editar) al lado de la gerencia.
3.  Se abrirá un formulario con los datos actuales de la gerencia precargados.
4.  Modifique los campos necesarios (Código, Nombre, o estado Activo/Inactivo).
5.  Haga clic en el botón **"Guardar Cambios"**.
6.  Si los cambios son exitosos, recibirá un mensaje de confirmación y la tabla se actualizará.

### 5. Eliminar una Gerencia

**¡Precaución!** La eliminación de una gerencia es una acción permanente y no se puede deshacer.

1.  En el listado de gerencias, busque la gerencia que desea eliminar.
2.  En la columna "Acciones", haga clic en el **icono de papelera** (Eliminar) al lado de la gerencia.
3.  Aparecerá una ventana de confirmación pidiéndole que confirme la eliminación de la gerencia. Se le mostrará el nombre de la gerencia para que verifique que es la correcta.
4.  Haga clic en **"Eliminar"** si está seguro. Haga clic en "Cancelar" si no desea eliminarla.
5.  **Importante:** Si la gerencia que intenta eliminar ya está siendo utilizada o asociada a otros registros en el sistema (ej. un usuario o un centro de trabajo está asignado a esa gerencia), el sistema NO le permitirá eliminarla y le mostrará un mensaje de error. Deberá desvincular o reasignar esos registros primero.
