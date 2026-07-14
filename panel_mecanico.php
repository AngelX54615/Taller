<?php
require_once __DIR__ . '/config/auth.php';
requerirLogin('Mecanico');

$titulo = 'Panel del Mecánico';
require __DIR__ . '/partials/header.php';
?>
    <h1>Panel del Mecánico</h1>
    <p>Bienvenido, <strong><?= htmlspecialchars($_SESSION['nombre'] . ' ' . $_SESSION['apellido_pat']) ?></strong>.</p>

    <ul class="menu">
        <li><a href="registrar_diagnostico.php">Registrar diagnóstico</a></li>
        <li><a href="actualizar_estado_servicio.php">Actualizar estado de servicio</a></li>
        <li><a href="asignar_refaccion.php">Asignar refacciones a un servicio</a></li>
        <li><a href="solicitar_refaccion.php">Solicitar refacción</a></li>
    </ul>
<?php require __DIR__ . '/partials/footer.php'; ?>
