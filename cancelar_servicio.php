<?php
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/classes/Servicio.php';

requerirLogin('Administrativo');

$mensaje = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'cancelar_servicio') {
    $idServicio = (int) $_POST['id_servicio'];

    try {
        $servicio = new Servicio();
        $servicio->actualizarEstado($idServicio, 'Cancelado');
        $mensaje = "Servicio #$idServicio cancelado correctamente.";
    } catch (Exception $e) {
        $error = "Ocurrió un error: " . $e->getMessage();
    }
}

$servicioObj = new Servicio();
$serviciosActivos = $servicioObj->serviciosActivos();

$titulo = 'Cancelar Servicio';
require __DIR__ . '/partials/header.php';
?>
    <h1>Cancelar Servicio</h1>

    <?php if ($mensaje): ?>
        <div class="mensaje exito">✅ <?= htmlspecialchars($mensaje) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="mensaje error">❌ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <h3>Servicios activos (Pendiente o En proceso)</h3>

    <?php if (empty($serviciosActivos)): ?>
        <p>No hay servicios activos en este momento.</p>
    <?php else: ?>
        <table>
            <tr><th>Cliente</th><th>Vehículo</th><th>Tipo</th><th>Mecánico</th><th>Estado</th><th></th></tr>
            <?php foreach ($serviciosActivos as $s): ?>
                <tr>
                    <td><?= htmlspecialchars($s['cliente_nombre'] . ' ' . $s['cliente_apellido']) ?></td>
                    <td><?= htmlspecialchars($s['marca'] . ' ' . $s['modelo']) ?></td>
                    <td><?= htmlspecialchars($s['tipo_servicio']) ?></td>
                    <td><?= htmlspecialchars($s['mecanico_nombre'] . ' ' . $s['mecanico_apellido']) ?></td>
                    <td><span class="badge <?= $s['estado'] === 'Pendiente' ? 'badge-pendiente' : 'badge-proceso' ?>"><?= htmlspecialchars($s['estado']) ?></span></td>
                    <td>
                        <form method="POST" action="" onsubmit="return confirm('¿Cancelar este servicio? El cliente deberá ser notificado.');">
                            <input type="hidden" name="accion" value="cancelar_servicio">
                            <input type="hidden" name="id_servicio" value="<?= $s['id_servicio'] ?>">
                            <button type="submit" class="mini">Cancelar</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
<?php require __DIR__ . '/partials/footer.php'; ?>
