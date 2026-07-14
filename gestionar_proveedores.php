<?php
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/classes/Proveedor.php';

requerirLogin('Administrativo');

$mensaje = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'registrar_proveedor') {
    $nombre = trim($_POST['nombre'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $correo = trim($_POST['correo'] ?? '');

    if ($nombre === '') {
        $error = "El nombre del proveedor es obligatorio.";
    } else {
        try {
            $proveedor = new Proveedor();
            $proveedor->nombre = $nombre;
            $proveedor->telefono = $telefono ?: null;
            $proveedor->correo = $correo ?: null;
            $proveedor->guardar();
            $mensaje = "Proveedor registrado correctamente.";
        } catch (Exception $e) {
            $error = "Ocurrió un error al guardar: " . $e->getMessage();
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'actualizar_proveedor') {
    $idProveedor = (int) $_POST['id_proveedor'];
    $telefono = trim($_POST['telefono'] ?? '');
    $correo = trim($_POST['correo'] ?? '');

    try {
        $proveedorObj = new Proveedor();
        $proveedorObj->actualizar($idProveedor, $telefono, $correo);
        $mensaje = "Datos del proveedor actualizados correctamente.";
    } catch (Exception $e) {
        $error = "Ocurrió un error al guardar: " . $e->getMessage();
    }
}

$proveedorObj = new Proveedor();
$proveedores = $proveedorObj->listarTodos();

$titulo = 'Gestión de Proveedores';
require __DIR__ . '/partials/header.php';
?>
    <h1>Gestión de Proveedores</h1>

    <?php if ($mensaje): ?>
        <div class="mensaje exito">✅ <?= htmlspecialchars($mensaje) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="mensaje error">❌ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <fieldset>
        <legend>Registrar proveedor</legend>
        <form method="POST" action="">
            <input type="hidden" name="accion" value="registrar_proveedor">

            <label for="nombre">Nombre *</label>
            <input type="text" id="nombre" name="nombre" required placeholder="Refaccionaria del Valle...">

            <label for="telefono">Teléfono</label>
            <input type="text" id="telefono" name="telefono">

            <label for="correo">Correo</label>
            <input type="email" id="correo" name="correo">

            <button type="submit">Registrar proveedor</button>
        </form>
    </fieldset>

    <h3>Proveedores registrados</h3>

    <?php if (empty($proveedores)): ?>
        <p>No hay proveedores registrados todavía.</p>
    <?php else: ?>
        <?php foreach ($proveedores as $p): ?>
            <fieldset>
                <legend><?= htmlspecialchars($p['nombre']) ?></legend>
                <form method="POST" action="">
                    <input type="hidden" name="accion" value="actualizar_proveedor">
                    <input type="hidden" name="id_proveedor" value="<?= $p['id_proveedor'] ?>">

                    <label for="telefono<?= $p['id_proveedor'] ?>">Teléfono</label>
                    <input type="text" id="telefono<?= $p['id_proveedor'] ?>" name="telefono"
                           value="<?= htmlspecialchars($p['telefono'] ?? '') ?>">

                    <label for="correo<?= $p['id_proveedor'] ?>">Correo</label>
                    <input type="email" id="correo<?= $p['id_proveedor'] ?>" name="correo"
                           value="<?= htmlspecialchars($p['correo'] ?? '') ?>">

                    <button type="submit">Guardar</button>
                </form>
            </fieldset>
        <?php endforeach; ?>
    <?php endif; ?>
<?php require __DIR__ . '/partials/footer.php'; ?>
