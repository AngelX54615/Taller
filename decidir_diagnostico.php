<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/classes/Diagnostico.php';
require_once __DIR__ . '/classes/Servicio.php';

requerirLogin('Administrativo');
$idAdmin = $_SESSION['id_empleado'];

$mensaje = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'aceptar') {
    $idDiagnostico = (int) $_POST['id_diagnostico'];

    try {
        $diagnosticoObj = new Diagnostico();
        $diagnosticoObj->actualizarDecision($idDiagnostico, 'Aceptado');
        $mensaje = "Decisión registrada: el cliente aceptó. Ya puedes generar la orden de servicio.";
    } catch (Exception $e) {
        $error = "Ocurrió un error: " . $e->getMessage();
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'rechazar') {
    $idDiagnostico = (int) $_POST['id_diagnostico'];
    $idMecanico = (int) $_POST['id_mecanico'];
    $montoDiagnostico = trim($_POST['monto_diagnostico'] ?? '');

    if ($montoDiagnostico === '' || (float) $montoDiagnostico <= 0) {
        $error = "Captura el monto a cobrar por el diagnóstico.";
    } else {
        // Registrar el rechazo y cerrar el caso con un servicio ya Finalizado
        // (solo se cobra el diagnóstico, no hay reparación) es una sola operación.
        $db = (new Database())->conectar();
        $db->beginTransaction();
        try {
            $diagnosticoObj = new Diagnostico();
            $diagnosticoObj->actualizarDecision($idDiagnostico, 'Rechazado');

            $servicio = new Servicio();
            $servicio->tipo_servicio = 'Diagnóstico (rechazado)';
            $servicio->descripcion = 'El cliente rechazó la reparación tras el diagnóstico; solo se cobra la revisión.';
            $servicio->costo = (float) $montoDiagnostico;
            $servicio->estado = 'Finalizado';
            $servicio->id_diagnostico = $idDiagnostico;
            $servicio->id_administrativo = $idAdmin;
            $servicio->id_mecanico = $idMecanico;
            $servicio->guardar();

            $db->commit();
            $mensaje = "Decisión registrada: el cliente rechazó la reparación. Ya puedes generar el ticket del diagnóstico.";
        } catch (Exception $e) {
            $db->rollBack();
            $error = "Ocurrió un error al guardar: " . $e->getMessage();
        }
    }
}

$diagnosticoObj = new Diagnostico();
$pendientes = $diagnosticoObj->pendientesDeDecision();

$titulo = 'Decisión del Cliente';
require __DIR__ . '/partials/header.php';
?>
    <h1>Decisión del Cliente</h1>

    <?php if ($mensaje): ?>
        <div class="mensaje exito">✅ <?= htmlspecialchars($mensaje) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="mensaje error">❌ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <h3>Diagnósticos pendientes de decisión</h3>

    <?php if (empty($pendientes)): ?>
        <p>No hay diagnósticos esperando la decisión del cliente.</p>
    <?php else: ?>
        <?php foreach ($pendientes as $d): ?>
            <fieldset>
                <legend><?= htmlspecialchars($d['cliente_nombre'] . ' ' . $d['cliente_apellido']) ?> — <?= htmlspecialchars($d['marca'] . ' ' . $d['modelo']) ?></legend>
                <p><?= htmlspecialchars($d['descripcion']) ?></p>
                <p>Presupuesto: <strong>$<?= htmlspecialchars($d['presupuesto']) ?></strong> ·
                   Mecánico: <?= htmlspecialchars($d['mecanico_nombre'] . ' ' . $d['mecanico_apellido']) ?></p>

                <div style="display:flex; gap:10px; flex-wrap:wrap; align-items:flex-end; margin-top:10px;">
                    <form method="POST" action="">
                        <input type="hidden" name="accion" value="aceptar">
                        <input type="hidden" name="id_diagnostico" value="<?= $d['id_diagnostico'] ?>">
                        <button type="submit" class="mini">Cliente acepta</button>
                    </form>

                    <form method="POST" action="" style="display:flex; gap:5px; align-items:flex-end;">
                        <input type="hidden" name="accion" value="rechazar">
                        <input type="hidden" name="id_diagnostico" value="<?= $d['id_diagnostico'] ?>">
                        <input type="hidden" name="id_mecanico" value="<?= $d['id_mecanico'] ?>">
                        <div>
                            <label for="monto<?= $d['id_diagnostico'] ?>">Cobrar por el diagnóstico ($)</label>
                            <input type="number" step="0.01" min="0.01" id="monto<?= $d['id_diagnostico'] ?>" name="monto_diagnostico" required style="width:140px;">
                        </div>
                        <button type="submit" class="mini">Cliente rechaza</button>
                    </form>
                </div>
            </fieldset>
        <?php endforeach; ?>
    <?php endif; ?>
<?php require __DIR__ . '/partials/footer.php'; ?>
