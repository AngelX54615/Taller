<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/classes/Mecanico.php';
require_once __DIR__ . '/classes/Administrativo.php';
require_once __DIR__ . '/classes/Usuario.php';

$mensaje = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $apellidoPat = trim($_POST['apellido_pat'] ?? '');
    $apellidoMat = trim($_POST['apellido_mat'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');
    $turno = trim($_POST['turno'] ?? '');
    $tipo = $_POST['tipo'] ?? '';
    $especialidad = trim($_POST['especialidad'] ?? '');
    $area = trim($_POST['area'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmar = $_POST['confirmar'] ?? '';

    if ($nombre === '' || $apellidoPat === '' || $correo === '' || $password === '') {
        $error = "Nombre, apellido paterno, correo y contraseña son obligatorios.";
    } elseif (!in_array($tipo, ['Mecanico', 'Administrativo'], true)) {
        $error = "Selecciona si el empleado es Mecánico o Administrativo.";
    } elseif ($password !== $confirmar) {
        $error = "Las contraseñas no coinciden.";
    } elseif (strlen($password) < 6) {
        $error = "La contraseña debe tener al menos 6 caracteres.";
    } else {
        // Un empleado nuevo y su cuenta de acceso se crean juntos: si algo falla
        // (p. ej. el correo ya existe), no debe quedar un empleado "huérfano" sin cuenta.
        $db = (new Database())->conectar();
        $db->beginTransaction();
        try {
            if ($tipo === 'Mecanico') {
                $empleado = new Mecanico();
                $empleado->especialidad = $especialidad ?: null;
            } else {
                $empleado = new Administrativo();
                $empleado->area = $area ?: null;
            }
            $empleado->nombre = $nombre;
            $empleado->apellido_pat = $apellidoPat;
            $empleado->apellido_mat = $apellidoMat ?: null;
            $empleado->telefono = $telefono ?: null;
            $empleado->direccion = $direccion ?: null;
            $empleado->turno = $turno ?: null;

            $idEmpleado = $empleado->guardar();

            $usuarioObj = new Usuario();
            $usuarioObj->registrar($correo, $password, $idEmpleado);

            $db->commit();
            $mensaje = "Empleado y cuenta de acceso creados correctamente (id_empleado = $idEmpleado). Ya puede iniciar sesión.";
        } catch (Exception $e) {
            $db->rollBack();
            $error = "Ocurrió un error al guardar: " . $e->getMessage();
        }
    }
}

$titulo = 'Crear Cuenta de Acceso';
require __DIR__ . '/partials/header.php';
?>
    <h1>Registrar Empleado y Crear Cuenta</h1>

    <?php if ($mensaje): ?>
        <div class="mensaje exito">✅ <?= htmlspecialchars($mensaje) ?></div>
        <p><a href="login.php">Ir a iniciar sesión</a></p>
    <?php else: ?>

        <?php if ($error): ?>
            <div class="mensaje error">❌ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <fieldset>
                <legend>Datos del empleado</legend>

                <label for="nombre">Nombre *</label>
                <input type="text" id="nombre" name="nombre" required value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>">

                <label for="apellido_pat">Apellido paterno *</label>
                <input type="text" id="apellido_pat" name="apellido_pat" required value="<?= htmlspecialchars($_POST['apellido_pat'] ?? '') ?>">

                <label for="apellido_mat">Apellido materno</label>
                <input type="text" id="apellido_mat" name="apellido_mat" value="<?= htmlspecialchars($_POST['apellido_mat'] ?? '') ?>">

                <label for="telefono">Teléfono</label>
                <input type="text" id="telefono" name="telefono" value="<?= htmlspecialchars($_POST['telefono'] ?? '') ?>">

                <label for="direccion">Dirección</label>
                <input type="text" id="direccion" name="direccion" value="<?= htmlspecialchars($_POST['direccion'] ?? '') ?>">

                <label for="turno">Turno</label>
                <input type="text" id="turno" name="turno" placeholder="Matutino, vespertino, nocturno..." value="<?= htmlspecialchars($_POST['turno'] ?? '') ?>">
            </fieldset>

            <fieldset>
                <legend>Tipo de empleado *</legend>

                <?php $tipoPost = $_POST['tipo'] ?? ''; ?>
                <div class="radio-auto">
                    <input type="radio" id="tipo_mecanico" name="tipo" value="Mecanico" required
                           onchange="mostrarCampos(this.value)" <?= $tipoPost === 'Mecanico' ? 'checked' : '' ?>>
                    <label for="tipo_mecanico" style="margin:0;font-weight:normal">Mecánico</label>
                </div>
                <div class="radio-auto">
                    <input type="radio" id="tipo_administrativo" name="tipo" value="Administrativo" required
                           onchange="mostrarCampos(this.value)" <?= $tipoPost === 'Administrativo' ? 'checked' : '' ?>>
                    <label for="tipo_administrativo" style="margin:0;font-weight:normal">Administrativo</label>
                </div>

                <div id="campos-mecanico" style="display:none; margin-top:15px;">
                    <label for="especialidad">Especialidad</label>
                    <input type="text" id="especialidad" name="especialidad" placeholder="Motor, frenos, eléctrico..."
                           value="<?= htmlspecialchars($_POST['especialidad'] ?? '') ?>">
                </div>

                <div id="campos-administrativo" style="display:none; margin-top:15px;">
                    <label for="area">Área</label>
                    <input type="text" id="area" name="area" placeholder="Recepción, caja..."
                           value="<?= htmlspecialchars($_POST['area'] ?? '') ?>">
                </div>
            </fieldset>

            <fieldset>
                <legend>Datos de acceso</legend>

                <label for="correo">Correo *</label>
                <input type="email" id="correo" name="correo" required value="<?= htmlspecialchars($_POST['correo'] ?? '') ?>">

                <label for="password">Contraseña *</label>
                <input type="password" id="password" name="password" required minlength="6">

                <label for="confirmar">Confirmar contraseña *</label>
                <input type="password" id="confirmar" name="confirmar" required minlength="6">
            </fieldset>

            <button type="submit">Registrar empleado y crear cuenta</button>
        </form>

        <script>
        function mostrarCampos(tipo) {
            document.getElementById('campos-mecanico').style.display = (tipo === 'Mecanico') ? 'block' : 'none';
            document.getElementById('campos-administrativo').style.display = (tipo === 'Administrativo') ? 'block' : 'none';
        }
        <?php if ($tipoPost !== ''): ?>
        mostrarCampos('<?= htmlspecialchars($tipoPost) ?>');
        <?php endif; ?>
        </script>

        <p><a href="login.php">Volver al inicio de sesión</a></p>

    <?php endif; ?>
<?php require __DIR__ . '/partials/footer.php'; ?>
