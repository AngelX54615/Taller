<?php
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/classes/Servicio.php';
require_once __DIR__ . '/classes/Solicitud.php';

requerirLogin('Mecanico');
$idMecanico = $_SESSION['id_empleado'];

$mensaje = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'solicitar_refaccion') {
    $idServicio = (int) ($_POST['id_servicio'] ?? 0);
    $nombrePieza = trim($_POST['nombre_pieza'] ?? '');
    $cantidad = (int) ($_POST['cantidad'] ?? 0);

    if ($idServicio <= 0 || $nombrePieza === '' || $cantidad <= 0) {
        $error = "Selecciona el servicio, y captura el nombre de la pieza y una cantidad válida.";
    } else {
        try {
            $solicitud = new Solicitud();
            $solicitud->id_mecanico = $idMecanico;
            $solicitud->id_servicio = $idServicio;
            $solicitud->nombre_pieza = $nombrePieza;
            $solicitud->cantidad = $cantidad;
            $solicitud->guardar();
            $mensaje = "Solicitud enviada correctamente. El administrativo la revisará.";
        } catch (Exception $e) {
            $error = "Ocurrió un error al guardar: " . $e->getMessage();
        }
    }
}

$servicioObj = new Servicio();
$serviciosActivos = $servicioObj->serviciosActivosDeMecanico($idMecanico);

$solicitudObj = new Solicitud();
$misSolicitudes = $solicitudObj->listarDeMecanico($idMecanico);

$claseBadgeSolicitud = [
    'Pendiente' => 'badge-pendiente',
    'Atendida'  => 'badge-finalizado',
    'Rechazada' => 'badge-cancelado',
];

$titulo = 'Solicitar Refacción';
require __DIR__ . '/partials/header.php';
?>
    <h1>Solicitar Refacción</h1>

    <?php if ($mensaje): ?>
        <div class="mensaje exito">✅ <?= htmlspecialchars($mensaje) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="mensaje error">❌ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if (empty($serviciosActivos)): ?>
        <p>No tienes servicios activos a los que asociar una solicitud.</p>
    <?php else: ?>
        <fieldset>
            <legend>Nueva solicitud</legend>
            <form method="POST" action="">
                <input type="hidden" name="accion" value="solicitar_refaccion">

                <label for="id_servicio">Servicio *</label>
                <select id="id_servicio" name="id_servicio" required>
                    <option value="">-- Selecciona un servicio --</option>
                    <?php foreach ($serviciosActivos as $s): ?>
                        <option value="<?= $s['id_servicio'] ?>">
                            <?= htmlspecialchars($s['cliente_nombre'] . ' ' . $s['cliente_apellido'] . ' — ' . $s['marca'] . ' ' . $s['modelo'] . ' (' . $s['tipo_servicio'] . ')') ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label for="nombre_pieza">Refacción necesaria *</label>
                <input type="text" id="nombre_pieza" name="nombre_pieza" required placeholder="Balatas delanteras, banda de tiempo...">

                <label for="cantidad">Cantidad *</label>
                <input type="number" id="cantidad" name="cantidad" min="1" value="1" required>

                <button type="submit">Enviar solicitud</button>
            </form>
        </fieldset>
    <?php endif; ?>

    <h3>Tus solicitudes</h3>

    <?php if (empty($misSolicitudes)): ?>
        <p>No has enviado ninguna solicitud todavía.</p>
    <?php else: ?>
        <table>
            <tr><th>Cliente</th><th>Vehículo</th><th>Pieza</th><th>Cantidad</th><th>Estado</th><th>Proveedor</th></tr>
            <?php foreach ($misSolicitudes as $s): ?>
                <tr>
                    <td><?= htmlspecialchars(($s['cliente_nombre'] ?? '') . ' ' . ($s['cliente_apellido'] ?? '')) ?></td>
                    <td><?= htmlspecialchars(($s['marca'] ?? '') . ' ' . ($s['modelo'] ?? '')) ?></td>
                    <td><?= htmlspecialchars($s['nombre_pieza']) ?></td>
                    <td><?= (int) $s['cantidad'] ?></td>
                    <td><span class="badge <?= $claseBadgeSolicitud[$s['estado']] ?? '' ?>"><?= htmlspecialchars($s['estado']) ?></span></td>
                    <td><?= htmlspecialchars($s['proveedor_nombre'] ?? '—') ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
<?php require __DIR__ . '/partials/footer.php'; ?>
