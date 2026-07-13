<?php
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/classes/Usuario.php';

if (estaAutenticado()) {
    header('Location: ' . panelDeRol($_SESSION['rol']));
    exit;
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = trim($_POST['correo'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($correo === '' || $password === '') {
        $error = "Correo y contraseña son obligatorios.";
    } else {
        $usuarioObj = new Usuario();
        $datos = $usuarioObj->autenticar($correo, $password);

        if ($datos === false) {
            $error = "Correo o contraseña incorrectos.";
        } else {
            $_SESSION['id_empleado'] = $datos['id_empleado'];
            $_SESSION['nombre'] = $datos['nombre'];
            $_SESSION['apellido_pat'] = $datos['apellido_pat'];
            $_SESSION['rol'] = $datos['rol'];

            header('Location: ' . panelDeRol($datos['rol']));
            exit;
        }
    }
}

$titulo = 'Iniciar Sesión';
require __DIR__ . '/partials/header.php';
?>
    <h1>Iniciar Sesión</h1>

    <?php if ($error): ?>
        <div class="mensaje error">❌ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <label for="correo">Correo</label>
        <input type="email" id="correo" name="correo" required value="<?= htmlspecialchars($_POST['correo'] ?? '') ?>">

        <label for="password">Contraseña</label>
        <input type="password" id="password" name="password" required>

        <button type="submit">Iniciar sesión</button>
    </form>
<?php require __DIR__ . '/partials/footer.php'; ?>
