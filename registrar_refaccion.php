<?php
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/classes/Proveedor.php';
require_once __DIR__ . '/classes/Refaccion.php';

requerirLogin('Administrativo');

$mensaje = "";
$error = "";

$proveedorObj = new Proveedor();
$listaProveedores = $proveedorObj->listarTodos();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'guardar_refaccion') {
    $nombrePieza = trim($_POST['nombre_pieza'] ?? '');
    $marca = trim($_POST['marca'] ?? '');
    $cantidad = trim($_POST['cantidad'] ?? '');
    $stockMinimo = trim($_POST['stock_minimo'] ?? '');
    $precio = trim($_POST['precio'] ?? '');
    $idProveedor = trim($_POST['id_proveedor'] ?? '');

    // Alta de proveedor nuevo (opcional, si no se eligió uno existente)
    $proveedorNombre = trim($_POST['proveedor_nombre'] ?? '');
    $proveedorTelefono = trim($_POST['proveedor_telefono'] ?? '');
    $proveedorCorreo = trim($_POST['proveedor_correo'] ?? '');

    if ($nombrePieza === '' || $cantidad === '' || ($idProveedor === '' && $proveedorNombre === '')) {
        $error = "El nombre de la pieza, la cantidad y el proveedor son obligatorios.";
    } else {
        try {
            if ($idProveedor === '' || $idProveedor === 'nuevo') {
                $proveedor = new Proveedor();
                $proveedor->nombre = $proveedorNombre;
                $proveedor->telefono = $proveedorTelefono ?: null;
                $proveedor->correo = $proveedorCorreo ?: null;
                $idProveedor = $proveedor->guardar();
            } else {
                $idProveedor = (int) $idProveedor;
            }

            $refaccion = new Refaccion();
            $refaccion->nombre_pieza = $nombrePieza;
            $refaccion->marca = $marca ?: null;
            $refaccion->cantidad = (int) $cantidad;
            $refaccion->stock_minimo = $stockMinimo !== '' ? (int) $stockMinimo : 0;
            $refaccion->precio = $precio !== '' ? (float) $precio : null;
            $refaccion->id_proveedor = $idProveedor;

            $idPieza = $refaccion->guardar();
            $mensaje = "Refacción registrada correctamente (id_pieza = $idPieza).";

        } catch (Exception $e) {
            $error = "Ocurrió un error al guardar: " . $e->getMessage();
        }
    }
}
$titulo = 'Registrar Refacción';
require __DIR__ . '/partials/header.php';
?>
    <h1>Registrar Refacción</h1>

    <?php if ($mensaje): ?>
        <div class="mensaje exito">✅ <?= htmlspecialchars($mensaje) ?></div>
        <p><a href="registrar_refaccion.php">Registrar otra refacción</a></p>
    <?php else: ?>

        <?php if ($error): ?>
            <div class="mensaje error">❌ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <input type="hidden" name="accion" value="guardar_refaccion">

            <fieldset>
                <legend>Datos de la refacción</legend>

                <label for="nombre_pieza">Nombre de la pieza *</label>
                <input type="text" id="nombre_pieza" name="nombre_pieza" required>

                <label for="marca">Marca</label>
                <input type="text" id="marca" name="marca">

                <label for="cantidad">Cantidad inicial *</label>
                <input type="number" min="0" id="cantidad" name="cantidad" required>

                <label for="stock_minimo">Stock mínimo</label>
                <input type="number" min="0" id="stock_minimo" name="stock_minimo" value="0">

                <label for="precio">Precio ($)</label>
                <input type="number" step="0.01" min="0" id="precio" name="precio">
            </fieldset>

            <fieldset>
                <legend>Proveedor</legend>

                <label for="id_proveedor">Proveedor</label>
                <select id="id_proveedor" name="id_proveedor" onchange="mostrarProveedorNuevo(this.value)">
                    <option value="">-- Selecciona un proveedor --</option>
                    <?php foreach ($listaProveedores as $p): ?>
                        <option value="<?= $p['id_proveedor'] ?>">
                            <?= htmlspecialchars($p['nombre'] . ' ' . ($p['apellido_pat'] ?? '')) ?>
                        </option>
                    <?php endforeach; ?>
                    <option value="nuevo">+ Registrar proveedor nuevo</option>
                </select>

                <div id="datos-proveedor-nuevo" style="display:none; margin-top:15px;">
                    <label for="proveedor_nombre">Nombre del proveedor *</label>
                    <input type="text" id="proveedor_nombre" name="proveedor_nombre">

                    <label for="proveedor_telefono">Teléfono</label>
                    <input type="text" id="proveedor_telefono" name="proveedor_telefono">

                    <label for="proveedor_correo">Correo</label>
                    <input type="email" id="proveedor_correo" name="proveedor_correo">
                </div>
            </fieldset>

            <button type="submit">Registrar refacción</button>
        </form>

        <script>
        function mostrarProveedorNuevo(valor) {
            document.getElementById('datos-proveedor-nuevo').style.display = (valor === 'nuevo') ? 'block' : 'none';
        }
        </script>

    <?php endif; ?>
<?php require __DIR__ . '/partials/footer.php'; ?>
