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

El sistema no tiene pantalla de registro: los empleados y sus cuentas de
acceso se dan de alta directamente en la base de datos.

1. **Inserta el empleado** en `empleado`, y luego en `mecanico` o
   `administrativo` según su rol (comparten el mismo `id_empleado`):

   ```sql
   INSERT INTO empleado (nombre, apellido_pat, apellido_mat, telefono, direccion, turno)
   VALUES ('Juan', 'Pérez', 'López', '6561234567', 'Calle Falsa 123', 'Matutino');

   -- usa el id_empleado generado arriba
   INSERT INTO mecanico (id_empleado, especialidad) VALUES (LAST_INSERT_ID(), 'Motor y transmisión');
   -- o, si es administrativo:
   -- INSERT INTO administrativo (id_empleado, area) VALUES (LAST_INSERT_ID(), 'Recepción');
   ```

2. **Genera el hash de la contraseña** (la tabla `usuario` nunca guarda
   contraseñas en texto plano). Con PHP desde línea de comandos:

   ```bash
   php -r "echo password_hash('la_contraseña_que_quieras', PASSWORD_DEFAULT);"
   ```

3. **Inserta la cuenta de acceso**, usando el `id_empleado` del paso 1 y el
   hash del paso 2:

   ```sql
   INSERT INTO usuario (correo, password_hash, id_empleado)
   VALUES ('juan.perez@taller.com', '<hash_generado_en_el_paso_2>', <id_empleado>);
   ```

4. Inicia sesión en `login.php` — el sistema te manda automáticamente al
   panel de Mecánico o de Administrativo según en cuál de las dos tablas
   (`mecanico` o `administrativo`) esté dado de alta el empleado.

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
