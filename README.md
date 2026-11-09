# Clínica Dental

Aplicación web académica para la gestión de una clínica dental, desarrollada como proyecto final del CFGS de Desarrollo de Aplicaciones Web (DAW).

El proyecto permite gestionar usuarios, citas, horarios, productos, carrito de compra, stock y notificaciones de cancelación.

> El proceso de compra es únicamente una simulación. No existe una pasarela de pago real ni una gestión completa de pedidos.

## Tecnologías

- PHP.
- MySQL / MariaDB.
- MySQLi.
- HTML5, CSS3 y JavaScript.
- XAMPP.
- Flatpickr.

## Funcionalidades principales

- Registro e inicio de sesión.
- Gestión del perfil de usuario.
- Reserva y cancelación de citas.
- Gestión de horarios y disponibilidad.
- Panel de administración.
- Gestión de usuarios y productos.
- Carrito de compra y control de stock.
- Notificaciones de cancelación de citas.

## Requisitos

- XAMPP con Apache, PHP y MariaDB.
- PHP con las extensiones MySQLi, Fileinfo y GD recomendadas.
- Un navegador web actualizado.

## Instalación local con XAMPP

1. Copia la carpeta `CLINICA_DENTAL` dentro de `C:\xampp\htdocs\`.
2. Inicia Apache y MySQL desde el Panel de Control de XAMPP.
3. Crea la base de datos importando `CLINICA_DENTAL/database.sql` desde phpMyAdmin.
4. Si necesitas credenciales distintas a las de XAMPP por defecto, copia:

   ```text
   CLINICA_DENTAL/includes/config.example.php
   ```

   como:

   ```text
   CLINICA_DENTAL/includes/config.local.php
   ```

   y edita sus valores. `config.local.php` no se publica en GitHub.

5. Configura Apache para que el `DocumentRoot` apunte a `C:\xampp\htdocs\CLINICA_DENTAL`.
6. Abre la aplicación en:

   ```text
   http://localhost/
   ```

También puede ejecutarse dentro de una subcarpeta de `htdocs`, ajustando `SITE_URL` en `CLINICA_DENTAL/includes/config.php` y las rutas locales que correspondan.

## Base de datos

El archivo `database.sql` crea la estructura necesaria y añade los horarios iniciales de la clínica. No incluye usuarios, contraseñas, correos ni datos personales.

Después de importar el esquema, se puede crear un usuario desde la pantalla de registro. La cuenta de administrador debe configurarse únicamente en el entorno local.

## Imágenes y archivos locales

Las imágenes de la aplicación no se incluyen en el repositorio público y están excluidas mediante `.gitignore`. Se conservan en la copia local para poder ejecutar la aplicación con su apariencia original.

También se mantienen fuera de GitHub los documentos académicos, archivos de depuración y registros generados localmente. No se eliminan automáticamente de tu ordenador.

## Estructura principal

```text
CLINICA_DENTAL/
├── api/          # Endpoints de la aplicación
├── assets/       # CSS, JavaScript e imágenes locales
├── includes/     # Configuración y componentes compartidos
├── pages/        # Páginas públicas y de administración
├── database.sql  # Esquema inicial de la base de datos
└── index.php     # Página principal
```

## Estado del proyecto

Este proyecto se conserva como trabajo académico y portfolio. Se han aplicado correcciones de seguridad y preparación para publicación sin rehacer la aplicación ni convertirla en un producto comercial.

Entre las medidas incorporadas se incluyen protección CSRF, gestión reforzada de sesiones, separación de la configuración local, comprobación de permisos administrativos y validación de imágenes subidas.

## Limitaciones conocidas

- La compra no conecta con ningún proveedor de pagos.
- Las imágenes no están disponibles al clonar el repositorio, salvo que se restauren desde la copia local.
- La instalación está orientada a un entorno local con XAMPP.
