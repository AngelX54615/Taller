<?php
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/classes/Servicio.php';

requerirLogin('Mecanico');
$idMecanico = $_SESSION['id_empleado'];

$mensaje = "";
$error = "";

// Actualizar el estado de un servicio
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'actualizar_estado') {
    $idServicio = (int) $_POST['id_servicio'];
    $nuevoEstado = trim($_POST['nuevo_estado'] ?? '');

    try {
        $servicio = new Servicio();
        $servicio->actualizarEstado($idServicio, $nuevoEstado);
        $mensaje = "Estado actualizado a \"$nuevoEstado\" correctamente.";
    } catch (Exception $e) {
        $error = "Ocurrió un error: " . $e->getMessage();
    }
}

$servicioObj = new Servicio();
$serviciosMecanico = $servicioObj->serviciosDeMecanico($idMecanico);

// El mecánico avanza el trabajo (Pendiente -> En proceso -> Finalizado);
// cancelar una orden es decisión del administrativo (ver cancelar_servicio.php).
$estadosDisponibles = ['Pendiente', 'En proceso', 'Finalizado'];
$claseBadgeEstado = [
    'Pendiente'  => 'badge-pendiente',
    'En proceso' => 'badge-proceso',
    'Finalizado' => 'badge-finalizado',
    'Cancelado'  => 'badge-cancelado',
];

$titulo = 'Actualizar Estado de Servicio';
require __DIR__ . '/partials/header.php';
?>
    <h1>Actualizar Estado de Servicio</h1>

    <?php if ($mensaje): ?>
        <div class="mensaje exito">✅ <?= htmlspecialchars($mensaje) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="mensaje error">❌ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <h3>Tus órdenes de servicio</h3>

    <?php if (empty($serviciosMecanico)): ?>
        <p>No tienes órdenes de servicio asignadas.</p>
    <?php else: ?>
        <table>
            <tr><th>Cliente</th><th>Vehículo</th><th>Tipo</th><th>Estado actual</th><th>Cambiar a</th></tr>
            <?php foreach ($serviciosMecanico as $s): ?>
                <tr>
                    <td><?= htmlspecialchars($s['cliente_nombre'] . ' ' . $s['cliente_apellido']) ?></td>
                    <td><?= htmlspecialchars($s['marca'] . ' ' . $s['modelo']) ?></td>
                    <td><?= htmlspecialchars($s['tipo_servicio']) ?></td>
                    <td><span class="badge <?= $claseBadgeEstado[$s['estado']] ?? '' ?>"><?= htmlspecialchars($s['estado']) ?></span></td>
                    <td>
                        <form method="POST" action="" style="display:flex; gap:5px;">
                            <input type="hidden" name="accion" value="actualizar_estado">
                            <input type="hidden" name="id_servicio" value="<?= $s['id_servicio'] ?>">
                            <select name="nuevo_estado" class="cambiar">
                                <?php foreach ($estadosDisponibles as $estado): ?>
                                    <option value="<?= $estado ?>" <?= $s['estado'] === $estado ? 'selected' : '' ?>>
                                        <?= $estado ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" class="mini">Actualizar</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
<?php require __DIR__ . '/partials/footer.php'; ?>
