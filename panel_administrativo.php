<?php
require_once __DIR__ . '/config/auth.php';
requerirLogin('Administrativo');

$titulo = 'Panel del Administrativo';
require __DIR__ . '/partials/header.php';
?>
    <h1>Panel del Administrativo</h1>
    <p>Bienvenido, <strong><?= htmlspecialchars($_SESSION['nombre'] . ' ' . $_SESSION['apellido_pat']) ?></strong>.</p>

    <ul class="menu">
        <li><a href="registrar_cliente.php">Registrar cliente y vehículo</a></li>
        <li><a href="editar_cliente.php">Editar cliente y vehículos</a></li>
        <li><a href="agendar_cita.php">Agendar cita</a></li>
        <li><a href="generar_servicio.php">Generar orden de servicio</a></li>
        <li><a href="cancelar_servicio.php">Cancelar servicio</a></li>
        <li><a href="generar_ticket.php">Generar ticket</a></li>
        <li><a href="registrar_refaccion.php">Registrar refacción</a></li>
        <li><a href="inventario_refacciones.php">Ver inventario de refacciones</a></li>
        <li><a href="gestionar_solicitudes.php">Gestión de solicitudes de refacciones</a></li>
        <li><a href="gestionar_proveedores.php">Gestión de proveedores</a></li>
        <li><a href="gestionar_empleados.php">Gestión de empleados</a></li>
    </ul>
<?php require __DIR__ . '/partials/footer.php'; ?>
