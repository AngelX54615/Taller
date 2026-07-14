<?php
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/classes/Solicitud.php';
require_once __DIR__ . '/classes/Proveedor.php';

requerirLogin('Administrativo');

$mensaje = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'atender_solicitud') {
    $idSolicitud = (int) $_POST['id_solicitud'];
    $idProveedor = (int) ($_POST['id_proveedor'] ?? 0);

    try {
        $solicitud = new Solicitud();
        $solicitud->actualizarEstado($idSolicitud, 'Atendida', $idProveedor > 0 ? $idProveedor : null);
        $mensaje = "Solicitud marcada como atendida.";
    } catch (Exception $e) {
        $error = "Ocurrió un error: " . $e->getMessage();
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'rechazar_solicitud') {
    $idSolicitud = (int) $_POST['id_solicitud'];

    try {
        $solicitud = new Solicitud();
        $solicitud->actualizarEstado($idSolicitud, 'Rechazada', null);
        $mensaje = "Solicitud rechazada.";
    } catch (Exception $e) {
        $error = "Ocurrió un error: " . $e->getMessage();
    }
}

$solicitudObj = new Solicitud();
$solicitudes = $solicitudObj->listarTodas();

$proveedorObj = new Proveedor();
$proveedores = $proveedorObj->listarTodos();

$claseBadgeSolicitud = [
    'Pendiente' => 'badge-pendiente',
    'Atendida'  => 'badge-finalizado',
    'Rechazada' => 'badge-cancelado',
];

$titulo = 'Gestión de Solicitudes';
require __DIR__ . '/partials/header.php';
?>
    <h1>Gestión de Solicitudes de Refacciones</h1>

    <?php if ($mensaje): ?>
        <div class="mensaje exito">✅ <?= htmlspecialchars($mensaje) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="mensaje error">❌ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if (empty($solicitudes)): ?>
        <p>No hay solicitudes registradas.</p>
    <?php else: ?>
        <?php foreach ($solicitudes as $s): ?>
            <fieldset>
                <legend>
                    <?= htmlspecialchars($s['nombre_pieza']) ?> (x<?= (int) $s['cantidad'] ?>)
                    <span class="badge <?= $claseBadgeSolicitud[$s['estado']] ?? '' ?>"><?= htmlspecialchars($s['estado']) ?></span>
                </legend>
                <p>Solicitada por: <strong><?= htmlspecialchars($s['mecanico_nombre'] . ' ' . $s['mecanico_apellido']) ?></strong></p>
                <?php if ($s['cliente_nombre']): ?>
                    <p>Para: <?= htmlspecialchars($s['cliente_nombre'] . ' ' . $s['cliente_apellido'] . ' — ' . $s['marca'] . ' ' . $s['modelo']) ?></p>
                <?php endif; ?>

                <?php if ($s['estado'] === 'Pendiente'): ?>
                    <form method="POST" action="" style="margin-top:10px;">
                        <input type="hidden" name="accion" value="atender_solicitud">
                        <input type="hidden" name="id_solicitud" value="<?= $s['id_solicitud'] ?>">

                        <label for="proveedor<?= $s['id_solicitud'] ?>">Pedir a proveedor</label>
                        <select id="proveedor<?= $s['id_solicitud'] ?>" name="id_proveedor">
                            <option value="">-- Sin especificar --</option>
                            <?php foreach ($proveedores as $p): ?>
                                <option value="<?= $p['id_proveedor'] ?>"><?= htmlspecialchars($p['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>

                        <button type="submit" class="mini">Marcar atendida</button>
                    </form>
                    <form method="POST" action="" style="margin-top:10px;">
                        <input type="hidden" name="accion" value="rechazar_solicitud">
                        <input type="hidden" name="id_solicitud" value="<?= $s['id_solicitud'] ?>">
                        <button type="submit" class="mini">Rechazar</button>
                    </form>
                <?php elseif ($s['proveedor_nombre']): ?>
                    <p>Pedida a: <?= htmlspecialchars($s['proveedor_nombre']) ?></p>
                <?php endif; ?>
            </fieldset>
        <?php endforeach; ?>
    <?php endif; ?>
<?php require __DIR__ . '/partials/footer.php'; ?>
