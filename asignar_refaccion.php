<?php
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/classes/Servicio.php';
require_once __DIR__ . '/classes/Refaccion.php';

requerirLogin('Mecanico');
$idMecanico = $_SESSION['id_empleado'];

$mensaje = "";
$error = "";

$refaccionObj = new Refaccion();
$refaccionesDisponibles = $refaccionObj->disponibles();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'asignar_refaccion') {
    $idServicio = (int) $_POST['id_servicio'];
    $idPieza = (int) $_POST['id_pieza'];
    $cantidadUsada = (int) ($_POST['cantidad_usada'] ?? 0);

    if ($idPieza <= 0 || $cantidadUsada <= 0) {
        $error = "Selecciona una refacción y una cantidad válida.";
    } else {
        try {
            $refaccionObj->asignarAServicio($idServicio, $idPieza, $cantidadUsada);
            $mensaje = "Refacción asignada correctamente al servicio #$idServicio.";
        } catch (Exception $e) {
            $error = "Ocurrió un error al asignar: " . $e->getMessage();
        }
        $refaccionesDisponibles = $refaccionObj->disponibles();
    }
}

$servicioObj = new Servicio();
$serviciosActivos = $servicioObj->serviciosActivosDeMecanico($idMecanico);
$titulo = 'Asignar Refacciones';
require __DIR__ . '/partials/header.php';
?>
    <h1>Asignar Refacciones a un Servicio</h1>

    <?php if ($mensaje): ?>
        <div class="mensaje exito">✅ <?= htmlspecialchars($mensaje) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="mensaje error">❌ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <h3>Tus servicios activos</h3>

    <?php if (empty($serviciosActivos)): ?>
        <p>No tienes servicios pendientes o en proceso.</p>
    <?php else: ?>
        <table>
            <tr><th>Cliente</th><th>Vehículo</th><th>Tipo</th><th>Estado</th><th>Refacciones usadas</th><th></th></tr>
            <?php foreach ($serviciosActivos as $s): ?>
                <?php $usadas = $refaccionObj->deServicio($s['id_servicio']); ?>
                <tr>
                    <td><?= htmlspecialchars($s['cliente_nombre'] . ' ' . $s['cliente_apellido']) ?></td>
                    <td><?= htmlspecialchars($s['marca'] . ' ' . $s['modelo']) ?></td>
                    <td><?= htmlspecialchars($s['tipo_servicio']) ?></td>
                    <td><?= htmlspecialchars($s['estado']) ?></td>
                    <td>
                        <?php if (empty($usadas)): ?>
                            <em>Ninguna</em>
                        <?php else: ?>
                            <?php foreach ($usadas as $u): ?>
                                <?= htmlspecialchars($u['nombre_pieza']) ?> (x<?= (int) $u['cantidad_usada'] ?>)<br>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a class="seleccionar" href="#"
                           onclick="mostrarFormulario(<?= $s['id_servicio'] ?>); return false;">Asignar</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>

        <div id="form-refaccion" style="display:none; margin-top:20px;">
            <form method="POST" action="">
                <input type="hidden" name="accion" value="asignar_refaccion">
                <input type="hidden" name="id_servicio" id="id_servicio_hidden">

                    <fieldset>
                        <legend>Refacción usada</legend>

                        <label for="id_pieza">Refacción *</label>
                        <select id="id_pieza" name="id_pieza" required>
                            <option value="">-- Selecciona una refacción --</option>
                            <?php foreach ($refaccionesDisponibles as $r): ?>
                                <option value="<?= $r['id_pieza'] ?>">
                                    <?= htmlspecialchars($r['nombre_pieza'] . ' ' . ($r['marca'] ?? '')) ?> (disponibles: <?= (int) $r['cantidad'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <label for="cantidad_usada">Cantidad usada *</label>
                        <input type="number" min="1" id="cantidad_usada" name="cantidad_usada" required>
                    </fieldset>

                    <button type="submit">Asignar refacción</button>
                </form>
            </div>

            <script>
            function mostrarFormulario(idServicio) {
                document.getElementById('id_servicio_hidden').value = idServicio;
                document.getElementById('form-refaccion').style.display = 'block';
                document.getElementById('form-refaccion').scrollIntoView({behavior: 'smooth'});
            }
            </script>
    <?php endif; ?>
<?php require __DIR__ . '/partials/footer.php'; ?>
