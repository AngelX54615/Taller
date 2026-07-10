<?php
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/classes/Servicio.php';

requerirLogin('Administrativo');
$idAdmin = $_SESSION['id_empleado'];

$mensaje = "";
$error = "";
$diagnosticosPendientes = [];

// Guardar la orden de servicio
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'guardar_servicio') {
    $idDiagnostico = (int) $_POST['id_diagnostico'];
    $idMecanico = (int) $_POST['id_mecanico'];
    $costo = trim($_POST['costo'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $tipoServicio = trim($_POST['tipo_servicio'] ?? '');
    $tiempoEstimado = trim($_POST['tiempo_estimado'] ?? '');

    if ($costo === '' || $tipoServicio === '') {
        $error = "El costo y el tipo de servicio son obligatorios.";
    } else {
        try {
            $servicio = new Servicio();
            $servicio->costo = (float) $costo;
            $servicio->descripcion = $descripcion ?: null;
            $servicio->tipo_servicio = $tipoServicio;
            $servicio->tiempo_estimado = $tiempoEstimado ?: null;
            $servicio->id_diagnostico = $idDiagnostico;
            $servicio->id_administrativo = $idAdmin;
            $servicio->id_mecanico = $idMecanico;

            $idServicio = $servicio->guardar();
            $mensaje = "Orden de servicio generada correctamente (id_servicio = $idServicio, estado: Pendiente).";

        } catch (Exception $e) {
            $error = "Ocurrió un error al guardar: " . $e->getMessage();
        }
    }
}

if (!$mensaje) {
    $servicioObj = new Servicio();
    $diagnosticosPendientes = $servicioObj->diagnosticosSinServicio();
}
$titulo = 'Generar Orden de Servicio';
require __DIR__ . '/partials/header.php';
?>
    <h1>Generar Orden de Servicio</h1>

    <?php if ($mensaje): ?>
        <div class="mensaje exito">✅ <?= htmlspecialchars($mensaje) ?></div>
        <p><a href="generar_servicio.php">Generar otra orden</a></p>
    <?php else: ?>

        <?php if ($error): ?>
            <div class="mensaje error">❌ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <h3>Diagnósticos sin orden de servicio</h3>

        <?php if (empty($diagnosticosPendientes)): ?>
            <p>No hay diagnósticos pendientes de generar orden.</p>
        <?php else: ?>
            <table>
                <tr><th>Cliente</th><th>Vehículo</th><th>Diagnóstico</th><th>Presupuesto</th><th>Mecánico</th><th></th></tr>
                <?php foreach ($diagnosticosPendientes as $d): ?>
                    <tr>
                        <td><?= htmlspecialchars($d['cliente_nombre'] . ' ' . $d['cliente_apellido']) ?></td>
                        <td><?= htmlspecialchars($d['marca'] . ' ' . $d['modelo']) ?></td>
                        <td><?= htmlspecialchars($d['descripcion']) ?></td>
                        <td>$<?= htmlspecialchars($d['presupuesto']) ?></td>
                        <td><?= htmlspecialchars($d['mecanico_nombre'] . ' ' . $d['mecanico_apellido']) ?></td>
                        <td>
                            <a class="seleccionar" href="#"
                               onclick="mostrarFormulario(<?= $d['id_diagnostico'] ?>, <?= $d['id_mecanico'] ?>, <?= $d['presupuesto'] ?>); return false;">
                               Generar orden
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>

            <div id="form-servicio" style="display:none; margin-top:20px;">
                <form method="POST" action="">
                    <input type="hidden" name="accion" value="guardar_servicio">
                    <input type="hidden" name="id_diagnostico" id="id_diagnostico_hidden">
                    <input type="hidden" name="id_mecanico" id="id_mecanico_hidden">

                        <fieldset>
                            <legend>Datos de la orden</legend>

                            <label for="tipo_servicio">Tipo de servicio *</label>
                            <input type="text" id="tipo_servicio" name="tipo_servicio" placeholder="Mecánica general, frenos, motor..." required>

                            <label for="descripcion">Descripción del servicio</label>
                            <textarea id="descripcion" name="descripcion" rows="3"></textarea>

                            <label for="costo">Costo ($) *</label>
                            <input type="number" step="0.01" id="costo" name="costo" required>

                            <label for="tiempo_estimado">Tiempo estimado</label>
                            <input type="text" id="tiempo_estimado" name="tiempo_estimado" placeholder="2 horas, 1 día...">
                        </fieldset>

                        <button type="submit">Generar orden</button>
                    </form>
                </div>

                <script>
                function mostrarFormulario(idDiagnostico, idMecanico, presupuesto) {
                    document.getElementById('id_diagnostico_hidden').value = idDiagnostico;
                    document.getElementById('id_mecanico_hidden').value = idMecanico;
                    document.getElementById('costo').value = presupuesto;
                    document.getElementById('form-servicio').style.display = 'block';
                    document.getElementById('form-servicio').scrollIntoView({behavior: 'smooth'});
                }
                </script>
            <?php endif; ?>

    <?php endif; ?>
<?php require __DIR__ . '/partials/footer.php'; ?>
