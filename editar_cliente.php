<?php
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/classes/Cliente.php';
require_once __DIR__ . '/classes/Auto.php';

requerirLogin('Administrativo');

$mensaje = "";
$error = "";
$resultadosBusqueda = [];
$clienteSeleccionado = null;
$autosCliente = [];

// Guardar los datos de contacto del cliente
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'actualizar_cliente') {
    $idCliente = (int) $_POST['id_cliente'];
    $telefono = trim($_POST['telefono'] ?? '');
    $correo = trim($_POST['correo'] ?? '');

    if ($telefono === '') {
        $error = "El teléfono es obligatorio.";
    } else {
        try {
            $clienteObj = new Cliente();
            $clienteObj->actualizar($idCliente, $telefono, $correo);
            $mensaje = "Datos del cliente actualizados correctamente.";
        } catch (Exception $e) {
            $error = "Ocurrió un error al guardar: " . $e->getMessage();
        }
    }

    $clienteObj = new Cliente();
    $clienteSeleccionado = $clienteObj->buscarPorId($idCliente);
    $autoObj = new Auto();
    $autosCliente = $autoObj->listarPorCliente($idCliente);
}

// Guardar los datos de un vehículo del cliente
elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'actualizar_auto') {
    $idCliente = (int) $_POST['id_cliente'];
    $idAuto = (int) $_POST['id_auto'];
    $datos = [
        'tipo'   => trim($_POST['tipo'] ?? '') ?: null,
        'marca'  => trim($_POST['marca'] ?? '') ?: null,
        'modelo' => trim($_POST['modelo'] ?? '') ?: null,
        'color'  => trim($_POST['color'] ?? '') ?: null,
        'anio'   => trim($_POST['anio'] ?? '') !== '' ? (int) $_POST['anio'] : null,
        'placa'  => trim($_POST['placa'] ?? '') ?: null,
    ];

    try {
        $autoObj = new Auto();
        $autoObj->actualizar($idAuto, $datos);
        $mensaje = "Datos del vehículo actualizados correctamente.";
    } catch (Exception $e) {
        $error = "Ocurrió un error al guardar: " . $e->getMessage();
    }

    $clienteObj = new Cliente();
    $clienteSeleccionado = $clienteObj->buscarPorId($idCliente);
    $autoObj = new Auto();
    $autosCliente = $autoObj->listarPorCliente($idCliente);
}

// Agregar un vehículo nuevo al cliente
elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'agregar_auto') {
    $idCliente = (int) $_POST['id_cliente'];
    $tipo = trim($_POST['tipo'] ?? '');
    $marca = trim($_POST['marca'] ?? '');
    $modelo = trim($_POST['modelo'] ?? '');
    $color = trim($_POST['color'] ?? '');
    $anio = trim($_POST['anio'] ?? '');
    $placa = trim($_POST['placa'] ?? '');

    if ($placa === '') {
        $error = "La placa es obligatoria para agregar un vehículo.";
    } else {
        try {
            $auto = new Auto();
            $auto->tipo = $tipo ?: null;
            $auto->marca = $marca ?: null;
            $auto->modelo = $modelo ?: null;
            $auto->color = $color ?: null;
            $auto->anio = $anio !== '' ? (int) $anio : null;
            $auto->placa = $placa;
            $auto->id_cliente = $idCliente;
            $auto->guardar();
            $mensaje = "Vehículo agregado correctamente.";
        } catch (Exception $e) {
            $error = "Ocurrió un error al guardar: " . $e->getMessage();
        }
    }

    $clienteObj = new Cliente();
    $clienteSeleccionado = $clienteObj->buscarPorId($idCliente);
    $autoObj = new Auto();
    $autosCliente = $autoObj->listarPorCliente($idCliente);
}

// Ya se eligió un cliente (viene por GET) -> mostrar su ficha editable
elseif (isset($_GET['id_cliente'])) {
    $idCliente = (int) $_GET['id_cliente'];

    $clienteObj = new Cliente();
    $clienteSeleccionado = $clienteObj->buscarPorId($idCliente);

    $autoObj = new Auto();
    $autosCliente = $autoObj->listarPorCliente($idCliente);
}

// Se envió una búsqueda de cliente (por nombre, teléfono, correo, id, o placa de un vehículo)
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
$titulo = 'Editar Cliente';
require __DIR__ . '/partials/header.php';
?>
    <h1>Editar Cliente</h1>

    <?php if ($mensaje): ?>
        <div class="mensaje exito">✅ <?= htmlspecialchars($mensaje) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="mensaje error">❌ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($clienteSeleccionado): ?>
        <p><a href="editar_cliente.php">buscar otro cliente</a></p>

        <fieldset>
            <legend>Datos de contacto</legend>
            <form method="POST" action="">
                <input type="hidden" name="accion" value="actualizar_cliente">
                <input type="hidden" name="id_cliente" value="<?= $clienteSeleccionado['id_cliente'] ?>">

                <p><strong><?= htmlspecialchars($clienteSeleccionado['nombre'] . ' ' . $clienteSeleccionado['apellido_pat']) ?></strong></p>

                <label for="telefono">Teléfono *</label>
                <input type="text" id="telefono" name="telefono" required
                       value="<?= htmlspecialchars($clienteSeleccionado['telefono'] ?? '') ?>">

                <label for="correo">Correo</label>
                <input type="email" id="correo" name="correo"
                       value="<?= htmlspecialchars($clienteSeleccionado['correo'] ?? '') ?>">

                <button type="submit">Guardar datos del cliente</button>
            </form>
        </fieldset>

        <h3>Vehículos</h3>

        <?php if (empty($autosCliente)): ?>
            <p>Este cliente no tiene vehículos registrados.</p>
        <?php else: ?>
            <?php foreach ($autosCliente as $a): ?>
                <fieldset>
                    <legend><?= htmlspecialchars($a['marca'] . ' ' . $a['modelo'] . ' — ' . ($a['placa'] ?: 'sin placa')) ?></legend>
                    <form method="POST" action="">
                        <input type="hidden" name="accion" value="actualizar_auto">
                        <input type="hidden" name="id_cliente" value="<?= $clienteSeleccionado['id_cliente'] ?>">
                        <input type="hidden" name="id_auto" value="<?= $a['id_auto'] ?>">

                        <label for="tipo<?= $a['id_auto'] ?>">Tipo</label>
                        <input type="text" id="tipo<?= $a['id_auto'] ?>" name="tipo" value="<?= htmlspecialchars($a['tipo'] ?? '') ?>">

                        <label for="marca<?= $a['id_auto'] ?>">Marca</label>
                        <input type="text" id="marca<?= $a['id_auto'] ?>" name="marca" value="<?= htmlspecialchars($a['marca'] ?? '') ?>">

                        <label for="modelo<?= $a['id_auto'] ?>">Modelo</label>
                        <input type="text" id="modelo<?= $a['id_auto'] ?>" name="modelo" value="<?= htmlspecialchars($a['modelo'] ?? '') ?>">

                        <label for="color<?= $a['id_auto'] ?>">Color</label>
                        <input type="text" id="color<?= $a['id_auto'] ?>" name="color" value="<?= htmlspecialchars($a['color'] ?? '') ?>">

                        <label for="anio<?= $a['id_auto'] ?>">Año</label>
                        <input type="number" id="anio<?= $a['id_auto'] ?>" name="anio" min="1900" max="2100" value="<?= htmlspecialchars($a['anio'] ?? '') ?>">

                        <label for="placa<?= $a['id_auto'] ?>">Placa *</label>
                        <input type="text" id="placa<?= $a['id_auto'] ?>" name="placa" maxlength="10" required value="<?= htmlspecialchars($a['placa'] ?? '') ?>">

                        <button type="submit">Guardar vehículo</button>
                    </form>
                </fieldset>
            <?php endforeach; ?>
        <?php endif; ?>

        <fieldset>
            <legend>Agregar vehículo</legend>
            <form method="POST" action="">
                <input type="hidden" name="accion" value="agregar_auto">
                <input type="hidden" name="id_cliente" value="<?= $clienteSeleccionado['id_cliente'] ?>">

                <label for="tipo_nuevo">Tipo</label>
                <input type="text" id="tipo_nuevo" name="tipo" placeholder="Sedán, Pickup, SUV...">

                <label for="marca_nuevo">Marca</label>
                <input type="text" id="marca_nuevo" name="marca">

                <label for="modelo_nuevo">Modelo</label>
                <input type="text" id="modelo_nuevo" name="modelo">

                <label for="color_nuevo">Color</label>
                <input type="text" id="color_nuevo" name="color">

                <label for="anio_nuevo">Año</label>
                <input type="number" id="anio_nuevo" name="anio" min="1900" max="2100">

                <label for="placa_nuevo">Placa *</label>
                <input type="text" id="placa_nuevo" name="placa" maxlength="10" required>

                <button type="submit">Agregar vehículo</button>
            </form>
        </fieldset>

    <?php else: ?>
        <!-- Buscar cliente -->
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
                        <td><a class="seleccionar" href="editar_cliente.php?id_cliente=<?= $c['id_cliente'] ?>">Editar</a></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php elseif (isset($_POST['criterio'])): ?>
            <p>No se encontraron clientes con ese criterio.</p>
        <?php endif; ?>
    <?php endif; ?>
<?php require __DIR__ . '/partials/footer.php'; ?>
