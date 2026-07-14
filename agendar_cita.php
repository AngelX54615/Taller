<?php
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/classes/Cliente.php';
require_once __DIR__ . '/classes/Auto.php';
require_once __DIR__ . '/classes/Cita.php';

requerirLogin('Administrativo');

$mensaje = "";
$error = "";
$resultadosBusqueda = [];
$clienteSeleccionado = null;
$autosCliente = [];

// PASO 3: se envió el formulario final -> guardar la cita
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'guardar_cita') {
    $idCliente = (int) $_POST['id_cliente'];
    $idAuto    = (int) $_POST['id_auto'];
    $fecha     = trim($_POST['fecha'] ?? '');
    $hora      = trim($_POST['hora'] ?? '');
    $motivo    = trim($_POST['motivo'] ?? '');

    if ($fecha === '' || $hora === '') {
        $error = "La fecha y la hora son obligatorias.";
    } else {
        try {
            $cita = new Cita();
            $cita->fecha = $fecha;
            $cita->hora = $hora;
            $cita->motivo = $motivo ?: null;
            $cita->id_cliente = $idCliente;
            $cita->id_auto = $idAuto;

            $idCita = $cita->guardar();
            $citaCompleta = $cita->buscarPorId($idCita);

            $mensaje = "Cita agendada (id_cita = $idCita). Mecánico asignado: "
                . htmlspecialchars($citaCompleta['mecanico_nombre'] . ' ' . $citaCompleta['mecanico_apellido']);

        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

// PASO 2: ya se eligió un cliente (viene por GET) -> mostrar sus autos y el form de la cita
elseif (isset($_GET['id_cliente'])) {
    $idCliente = (int) $_GET['id_cliente'];

    $clienteObj = new Cliente();
    $clienteSeleccionado = $clienteObj->buscarPorId($idCliente);

    $autoObj = new Auto();
    $autosCliente = $autoObj->listarPorCliente($idCliente);
}

// PASO 1: se envió una búsqueda de cliente (por nombre, teléfono, correo, id, o placa de un vehículo)
elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'buscar') {
    $criterio = trim($_POST['criterio'] ?? '');
    if ($criterio !== '') {
        $clienteObj = new Cliente();
        $resultadosBusqueda = $clienteObj->buscar($criterio);

        $autoObj = new Auto();
        $autoPorPlaca = $autoObj->buscarPorPlaca($criterio);
        if ($autoPorPlaca) {
            $clienteDePlaca = $clienteObj->buscarPorId($autoPorPlaca['id_cliente']);
            $yaEstaEnResultados = in_array($autoPorPlaca['id_cliente'], array_column($resultadosBusqueda, 'id_cliente'), true);
            if ($clienteDePlaca && !$yaEstaEnResultados) {
                $resultadosBusqueda[] = $clienteDePlaca;
            }
        }
    }
}
$titulo = 'Agendar Cita';
require __DIR__ . '/partials/header.php';
?>
    <h1>Agendar Cita</h1>

    <?php if ($mensaje): ?>
        <div class="mensaje exito">✅ <?= $mensaje ?></div>
        <p><a href="agendar_cita.php">Agendar otra cita</a></p>
    <?php elseif ($error): ?>
        <div class="mensaje error">❌ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if (!$mensaje && $clienteSeleccionado): ?>
        <!-- PASO 2: cliente ya elegido, mostrar sus autos y el formulario de la cita -->
        <p>Cliente: <strong><?= htmlspecialchars($clienteSeleccionado['nombre'] . ' ' . $clienteSeleccionado['apellido_pat']) ?></strong>
           (<a href="agendar_cita.php">cambiar cliente</a>)</p>

        <?php if (empty($autosCliente)): ?>
            <div class="mensaje error">Este cliente no tiene ningún auto registrado. Regístralo primero en "Registrar Cliente".</div>
        <?php else: ?>
            <form method="POST" action="">
                <input type="hidden" name="accion" value="guardar_cita">
                <input type="hidden" name="id_cliente" value="<?= $clienteSeleccionado['id_cliente'] ?>">

                <fieldset>
                    <legend>Vehículo</legend>
                    <?php foreach ($autosCliente as $a): ?>
                        <div class="radio-auto">
                            <input type="radio" name="id_auto" id="auto<?= $a['id_auto'] ?>"
                                   value="<?= $a['id_auto'] ?>" <?= $a === $autosCliente[0] ? 'checked' : '' ?>>
                            <label for="auto<?= $a['id_auto'] ?>" style="margin:0;font-weight:normal">
                                <?= htmlspecialchars($a['marca'] . ' ' . $a['modelo'] . ' (' . $a['anio'] . ')') ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </fieldset>

                <fieldset>
                    <legend>Datos de la cita</legend>
                    <label for="fecha">Fecha *</label>
                    <input type="date" id="fecha" name="fecha" min="<?= date('Y-m-d') ?>" required
                           onchange="ajustarHoraMinima()">

                    <label for="hora">Hora *</label>
                    <input type="time" id="hora" name="hora" required>

                    <label for="motivo">Motivo</label>
                    <input type="text" id="motivo" name="motivo" placeholder="Ruido en frenos, cambio de aceite...">
                </fieldset>

                <button type="submit">Agendar cita</button>
            </form>

            <script>
            function ajustarHoraMinima() {
                var fechaInput = document.getElementById('fecha');
                var horaInput = document.getElementById('hora');
                var hoy = fechaInput.min;

                if (fechaInput.value === hoy) {
                    var ahora = new Date();
                    var hh = String(ahora.getHours()).padStart(2, '0');
                    var mm = String(ahora.getMinutes()).padStart(2, '0');
                    horaInput.min = hh + ':' + mm;
                } else {
                    horaInput.removeAttribute('min');
                }
            }
            </script>
        <?php endif; ?>

    <?php elseif (!$mensaje): ?>
        <!-- PASO 1: buscar cliente -->
        <form method="POST" action="">
            <input type="hidden" name="accion" value="buscar">
            <label for="criterio">Buscar cliente por nombre, teléfono, correo, id o placa del vehículo</label>
            <input type="text" id="criterio" name="criterio" required
                   value="<?= htmlspecialchars($_POST['criterio'] ?? '') ?>">
            <button type="submit">Buscar</button>
        </form>

        <?php if (!empty($resultadosBusqueda)): ?>
            <table>
                <tr><th>Nombre</th><th>Teléfono</th><th>Correo</th><th></th></tr>
                <?php foreach ($resultadosBusqueda as $c): ?>
                    <tr>
                        <td><?= htmlspecialchars($c['nombre'] . ' ' . $c['apellido_pat']) ?></td>
                        <td><?= htmlspecialchars($c['telefono'] ?? '') ?></td>
                        <td><?= htmlspecialchars($c['correo'] ?? '') ?></td>
                        <td><a class="seleccionar" href="agendar_cita.php?id_cliente=<?= $c['id_cliente'] ?>">Seleccionar</a></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php elseif (isset($_POST['criterio'])): ?>
            <p>No se encontraron clientes con ese criterio.</p>
        <?php endif; ?>
    <?php endif; ?>
<?php require __DIR__ . '/partials/footer.php'; ?>
