<?php
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/classes/Diagnostico.php';

requerirLogin('Mecanico');
$idMecanico = $_SESSION['id_empleado'];

$mensaje = "";
$error = "";
$citasPendientes = [];

// Se envió el formulario para guardar el diagnóstico
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'guardar_diagnostico') {
    $idCita = (int) $_POST['id_cita'];
    $descripcion = trim($_POST['descripcion'] ?? '');
    $presupuesto = trim($_POST['presupuesto'] ?? '');

    if ($descripcion === '' || $presupuesto === '') {
        $error = "La descripción y el presupuesto son obligatorios.";
    } else {
        try {
            $diagnostico = new Diagnostico();
            $diagnostico->descripcion = $descripcion;
            $diagnostico->presupuesto = (float) $presupuesto;
            $diagnostico->id_cita = $idCita;
            $diagnostico->id_mecanico = $idMecanico;

            $idDiagnostico = $diagnostico->guardar();
            $mensaje = "Diagnóstico registrado correctamente (id_diagnostico = $idDiagnostico).";

        } catch (Exception $e) {
            $error = "Ocurrió un error al guardar: " . $e->getMessage();
        }
    }
}

if (!$mensaje) {
    $diagnosticoObj = new Diagnostico();
    $citasPendientes = $diagnosticoObj->citasPendientesDeMecanico($idMecanico);
}
$titulo = 'Registrar Diagnóstico';
require __DIR__ . '/partials/header.php';
?>
    <h1>Registrar Diagnóstico</h1>

    <?php if ($mensaje): ?>
        <div class="mensaje exito">✅ <?= htmlspecialchars($mensaje) ?></div>
        <p><a href="registrar_diagnostico.php">Registrar otro diagnóstico</a></p>
    <?php else: ?>

        <?php if ($error): ?>
            <div class="mensaje error">❌ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <h3>Tus citas pendientes de diagnóstico</h3>

        <?php if (empty($citasPendientes)): ?>
            <p>No tienes citas pendientes de diagnóstico.</p>
        <?php else: ?>
            <table>
                <tr><th>Fecha</th><th>Hora</th><th>Cliente</th><th>Vehículo</th><th>Motivo</th><th></th></tr>
                <?php foreach ($citasPendientes as $c): ?>
                    <tr id="fila-<?= $c['id_cita'] ?>">
                        <td><?= htmlspecialchars($c['fecha']) ?></td>
                        <td><?= htmlspecialchars($c['hora']) ?></td>
                        <td><?= htmlspecialchars($c['cliente_nombre'] . ' ' . $c['cliente_apellido']) ?></td>
                        <td><?= htmlspecialchars($c['marca'] . ' ' . $c['modelo']) ?></td>
                        <td><?= htmlspecialchars($c['motivo'] ?? '') ?></td>
                        <td>
                            <a class="seleccionar" href="#"
                               onclick="mostrarFormulario(<?= $c['id_cita'] ?>); return false;">Diagnosticar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>

            <div id="form-diagnostico" style="display:none; margin-top:20px;">
                <form method="POST" action="">
                    <input type="hidden" name="accion" value="guardar_diagnostico">
                    <input type="hidden" name="id_cita" id="id_cita_hidden">

                    <fieldset>
                        <legend>Diagnóstico</legend>
                        <label for="descripcion">Descripción del problema *</label>
                        <textarea id="descripcion" name="descripcion" rows="4" required></textarea>

                        <label for="presupuesto">Presupuesto estimado ($) *</label>
                        <input type="number" step="0.01" id="presupuesto" name="presupuesto" required>
                    </fieldset>

                    <button type="submit">Guardar diagnóstico</button>
                </form>
            </div>

            <script>
            function mostrarFormulario(idCita) {
                document.getElementById('id_cita_hidden').value = idCita;
                document.getElementById('form-diagnostico').style.display = 'block';
                document.getElementById('form-diagnostico').scrollIntoView({behavior: 'smooth'});
            }
            </script>
        <?php endif; ?>

    <?php endif; ?>
<?php require __DIR__ . '/partials/footer.php'; ?>
