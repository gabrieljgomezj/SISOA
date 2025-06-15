# Guía de Usuario: Gestión de Usuarios

Este módulo le permite administrar las cuentas de los usuarios que acceden al sistema Ferrominera SST. Aquí podrá añadir, editar y eliminar usuarios, así como controlar sus permisos de acceso.

**Acceso:** Este módulo solo es accesible para usuarios con el perfil **Administrador**.

## 1. Listar y Buscar Usuarios

Al ingresar al módulo de "Gestión de Usuarios", verá una tabla que muestra todos los usuarios registrados en el sistema.

-   **Información visible:** Cada fila de la tabla muestra el nombre completo del usuario, su cédula, nombre de usuario, los perfiles asignados (por ejemplo, Administrador, Operador, Consultor) y su estado (Activo o Inactivo).
-   **Buscar usuarios:**
    -   Utilice el campo de búsqueda en la parte superior para encontrar usuarios específicos.
    -   Puede buscar por cualquier parte del nombre, apellido, cédula, ficha o nombre de usuario.
    -   A medida que escribe, la tabla se filtrará automáticamente para mostrar solo los resultados que coincidan.
-   **Paginación:** Si hay muchos usuarios, la tabla se dividirá en páginas para facilitar la navegación. Puede usar los controles de paginación para moverse entre ellas.

## 2. Añadir un Nuevo Usuario

Para crear una nueva cuenta de usuario en el sistema:

1.  Haga clic en el botón **"Nuevo Usuario"** (usualmente ubicado en la parte superior derecha de la tabla).
2.  Se abrirá un formulario donde deberá completar la siguiente información:
    * **Nombre y Apellido:** Nombres y apellidos completos del usuario.
    * **Cédula:** Número de identificación del usuario. Debe ser único.
    * **Ficha de Trabajo (Opcional):** Si el usuario tiene un número de ficha asignado, puede introducirlo aquí. Debe ser único si se proporciona.
    * **Correo Electrónico:** Dirección de correo electrónico del usuario. Debe ser única y tener un formato válido (ej. `usuario@dominio.com`).
    * **Nombre de Usuario:** El nombre que el usuario utilizará para iniciar sesión. Debe ser único.
    * **Contraseña y Confirmar Contraseña:** Establezca una contraseña segura para el nuevo usuario. Debe tener al menos 6 caracteres y las dos contraseñas deben coincidir.
    * **Perfiles:** Seleccione uno o más perfiles que definirán los permisos y el nivel de acceso del usuario al sistema (ej. Administrador, Operador, Consultor). Debe seleccionar al menos uno.
    * **Activo:** Marque esta casilla si el usuario debe estar activo y poder iniciar sesión inmediatamente. Si la desmarca, el usuario quedará registrado pero no podrá acceder hasta que sea activado.
3.  Haga clic en el botón **"Guardar Usuario"** para crear la cuenta.
4.  Si la información es correcta, verá un mensaje de éxito y el nuevo usuario aparecerá en la lista. Si hay errores, el sistema le indicará qué campos necesitan corrección.

## 3. Editar un Usuario Existente

Para modificar la información de un usuario:

1.  En la tabla de usuarios, busque el usuario que desea editar.
2.  Haga clic en el **icono de "Editar"** (un lápiz) en la columna de acciones de ese usuario.
3.  El formulario de edición se abrirá, prellenado con la información actual del usuario.
4.  Realice los cambios necesarios en cualquiera de los campos.
    * **Cambiar Contraseña:** Si desea cambiar la contraseña del usuario, introduzca la nueva contraseña y confírmela en los campos correspondientes. Si deja estos campos vacíos, la contraseña actual del usuario no se modificará.
    * **Modificar Perfiles:** Puede añadir o quitar perfiles del usuario marcando o desmarcando las casillas.
    * **Activar/Desactivar Usuario:** Marque o desmarque la casilla "Activo" para cambiar el estado del usuario. Un usuario "Inactivo" no puede iniciar sesión en el sistema.
5.  Haga clic en el botón **"Guardar Cambios"** para aplicar las modificaciones.
6.  Verá un mensaje de éxito si los cambios se guardaron correctamente, o un mensaje de error si hay algún problema con los datos.

## 4. Eliminar un Usuario

Para eliminar una cuenta de usuario del sistema:

1.  En la tabla de usuarios, busque el usuario que desea eliminar.
2.  Haga clic en el **icono de "Eliminar"** (una papelera) en la columna de acciones de ese usuario.
3.  Aparecerá un **mensaje de confirmación**. Asegúrese de que es el usuario correcto, ya que esta acción es irreversible.
4.  Haga clic en **"Eliminar"** en el cuadro de confirmación para proceder con la eliminación.
5.  Verá un mensaje de éxito si el usuario fue eliminado, o un mensaje de error si ocurrió un problema.

**Importante:** La eliminación de un usuario es permanente y no se puede deshacer.