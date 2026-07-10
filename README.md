# Taller Jesús Gardea

Sistema de administración para un taller mecánico: clientes y vehículos, citas,
diagnósticos, órdenes de servicio, refacciones/inventario y tickets de cobro.
PHP puro (sin frameworks) + MySQL, pensado para correr con XAMPP.

## Requisitos

- [XAMPP](https://www.apachefriends.org/) (Apache + MySQL + PHP 8+)

## Instalación

1. **Clona el proyecto dentro de `htdocs`.**

   ```bash
   cd C:\xampp\htdocs
   git clone https://github.com/AngelX54615/Taller.git taller_gardea
   ```

2. **Arranca Apache y MySQL** desde el panel de control de XAMPP.

3. **Crea la base de datos.** Importa `schema_taller.sql` — crea la base
   `taller_jesus_gardea`, todas las tablas y los triggers que necesita el
   sistema (descuento automático de stock, historial de estados de servicio).

   Por línea de comandos:

   ```bash
   cd C:\xampp\mysql\bin
   mysql -u root < C:\xampp\htdocs\taller_gardea\schema_taller.sql
   ```

   O desde phpMyAdmin (`http://localhost/phpmyadmin`): pestaña **Importar** →
   selecciona `schema_taller.sql` → Continuar.

4. **Revisa la conexión a la base de datos** en
   [`config/database.php`](config/database.php). Los valores por defecto
   (`root` sin contraseña) funcionan con una instalación estándar de XAMPP;
   ajústalos si tu MySQL usa otro usuario/contraseña.

5. **Abre el sistema:**

   ```
   http://localhost/taller_gardea/
   ```

## Primer uso

La base de datos importada no trae empleados ni cuentas de acceso. Para
entrar por primera vez:

1. En el menú principal, entra a **Crear cuenta de acceso**
   (`registrar_usuario.php`).
2. Llena los datos del empleado (nombre, apellidos, teléfono, dirección,
   turno), elige si es **Mecánico** o **Administrativo** (con su
   especialidad, o área), y captura correo + contraseña.
3. Al guardar, el sistema crea el empleado y su cuenta de acceso juntos.
   Repite el paso para dar de alta más empleados (por ejemplo, uno de cada
   rol para probar ambos flujos).
4. Inicia sesión — el sistema te manda automáticamente al panel de
   Mecánico o de Administrativo según el rol de la cuenta.

## Estructura del proyecto

```
taller_gardea/
├── classes/      Modelo (una clase por entidad: Cliente, Auto, Cita, Servicio...)
├── config/       Conexión a BD (database.php) y sesión/autenticación (auth.php)
├── css/          Hoja de estilos compartida
├── partials/     Header/footer del layout, incluidos por cada pantalla
├── *.php         Una pantalla por acción (registrar_cliente.php, agendar_cita.php...)
└── schema_taller.sql   Esquema completo de la base de datos
```

## Flujo funcional

Cliente y auto → Cita (asigna mecánico automáticamente) → Diagnóstico →
Orden de servicio → Refacciones usadas (descuentan inventario) → Servicio
finalizado → Ticket de cobro.
