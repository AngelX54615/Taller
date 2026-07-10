<?php
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/classes/Cliente.php';
require_once __DIR__ . '/classes/Auto.php';

requerirLogin('Administrativo');

$mensaje = "";
$error = "";

// Si el formulario fue enviado (método POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Recoger y limpiar los datos del formulario
    $nombre       = trim($_POST['nombre'] ?? '');
    $apellido_pat = trim($_POST['apellido_pat'] ?? '');
    $apellido_mat = trim($_POST['apellido_mat'] ?? '');
    $telefono     = trim($_POST['telefono'] ?? '');
    $correo       = trim($_POST['correo'] ?? '');

    $tipo   = trim($_POST['tipo'] ?? '');
    $marca  = trim($_POST['marca'] ?? '');
    $modelo = trim($_POST['modelo'] ?? '');
    $color  = trim($_POST['color'] ?? '');
    $anio   = trim($_POST['anio'] ?? '');

    // Validación básica (RF2 requiere nombre, correo, teléfono)
    if ($nombre === '' || $apellido_pat === '' || $telefono === '') {
        $error = "Nombre, apellido paterno y teléfono son obligatorios.";
    } else {
        try {
            // Guardar cliente
            $cliente = new Cliente();
            $cliente->nombre = $nombre;
            $cliente->apellido_pat = $apellido_pat;
            $cliente->apellido_mat = $apellido_mat ?: null;
            $cliente->telefono = $telefono;
            $cliente->correo = $correo ?: null;
            $idCliente = $cliente->guardar();

            // Guardar auto asociado (si se llenaron datos del vehículo)
            if ($marca !== '' || $modelo !== '') {
                $auto = new Auto();
                $auto->tipo = $tipo ?: null;
                $auto->marca = $marca ?: null;
                $auto->modelo = $modelo ?: null;
                $auto->color = $color ?: null;
                $auto->anio = $anio !== '' ? (int) $anio : null;
                $auto->id_cliente = $idCliente;
                $auto->guardar();
            }

            $mensaje = "Cliente y vehículo registrados correctamente (id_cliente = $idCliente).";

        } catch (Exception $e) {
            $error = "Ocurrió un error al guardar: " . $e->getMessage();
        }
    }
}
$titulo = 'Registrar Cliente y Vehículo';
require __DIR__ . '/partials/header.php';
?>
    <h1>Registrar Cliente y Vehículo</h1>

    <?php if ($mensaje): ?>
        <div class="mensaje exito">✅ <?= htmlspecialchars($mensaje) ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="mensaje error">❌ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <fieldset>
            <legend>Datos del cliente</legend>

            <label for="nombre">Nombre *</label>
            <input type="text" id="nombre" name="nombre" required>

            <label for="apellido_pat">Apellido paterno *</label>
            <input type="text" id="apellido_pat" name="apellido_pat" required>

            <label for="apellido_mat">Apellido materno</label>
            <input type="text" id="apellido_mat" name="apellido_mat">

            <label for="telefono">Teléfono *</label>
            <input type="text" id="telefono" name="telefono" required>

            <label for="correo">Correo</label>
            <input type="email" id="correo" name="correo">
        </fieldset>

        <fieldset>
            <legend>Datos del vehículo (opcional)</legend>

            <label for="tipo">Tipo</label>
            <input type="text" id="tipo" name="tipo" placeholder="Sedán, Pickup, SUV...">

            <label for="marca">Marca</label>
            <input type="text" id="marca" name="marca">

            <label for="modelo">Modelo</label>
            <input type="text" id="modelo" name="modelo">

            <label for="color">Color</label>
            <input type="text" id="color" name="color">

            <label for="anio">Año</label>
            <input type="number" id="anio" name="anio" min="1900" max="2100">
        </fieldset>

        <button type="submit">Registrar</button>
    </form>
<?php require __DIR__ . '/partials/footer.php'; ?>
