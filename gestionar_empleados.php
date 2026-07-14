<?php
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/classes/Mecanico.php';
require_once __DIR__ . '/classes/Administrativo.php';

requerirLogin('Administrativo');

$mensaje = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'actualizar_empleado') {
    $idEmpleado = (int) $_POST['id_empleado'];
    $rol = $_POST['rol'] ?? '';
    $telefono = trim($_POST['telefono'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');
    $turno = trim($_POST['turno'] ?? '');
    $especialidad = trim($_POST['especialidad'] ?? '');
    $area = trim($_POST['area'] ?? '');

    try {
        $empleadoObj = new Empleado();
        $empleadoObj->actualizar($idEmpleado, [
            'telefono'  => $telefono ?: null,
            'direccion' => $direccion ?: null,
            'turno'     => $turno ?: null,
        ]);

        if ($rol === 'Mecanico') {
            (new Mecanico())->actualizarEspecialidad($idEmpleado, $especialidad);
        } elseif ($rol === 'Administrativo') {
            (new Administrativo())->actualizarArea($idEmpleado, $area);
        }

        $mensaje = "Datos del empleado actualizados correctamente.";
    } catch (Exception $e) {
        $error = "Ocurrió un error al guardar: " . $e->getMessage();
    }
}

$mecanicoObj = new Mecanico();
$administrativoObj = new Administrativo();

$empleados = array_merge(
    array_map(fn($m) => $m + ['rol' => 'Mecanico'], $mecanicoObj->listarTodos()),
    array_map(fn($a) => $a + ['rol' => 'Administrativo'], $administrativoObj->listarTodos())
);
usort($empleados, fn($a, $b) => strcmp($a['nombre'], $b['nombre']));

$titulo = 'Gestión de Empleados';
require __DIR__ . '/partials/header.php';
?>
    <h1>Gestión de Empleados</h1>

    <?php if ($mensaje): ?>
        <div class="mensaje exito">✅ <?= htmlspecialchars($mensaje) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="mensaje error">❌ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if (empty($empleados)): ?>
        <p>No hay empleados activos registrados.</p>
    <?php else: ?>
        <?php foreach ($empleados as $e): ?>
            <fieldset>
                <legend>
                    <?= htmlspecialchars($e['nombre'] . ' ' . $e['apellido_pat']) ?>
                    <span class="badge badge-pendiente"><?= htmlspecialchars($e['rol']) ?></span>
                </legend>
                <form method="POST" action="">
                    <input type="hidden" name="accion" value="actualizar_empleado">
                    <input type="hidden" name="id_empleado" value="<?= $e['id_empleado'] ?>">
                    <input type="hidden" name="rol" value="<?= htmlspecialchars($e['rol']) ?>">

                    <label for="telefono<?= $e['id_empleado'] ?>">Teléfono</label>
                    <input type="text" id="telefono<?= $e['id_empleado'] ?>" name="telefono"
                           value="<?= htmlspecialchars($e['telefono'] ?? '') ?>">

                    <label for="direccion<?= $e['id_empleado'] ?>">Dirección</label>
                    <input type="text" id="direccion<?= $e['id_empleado'] ?>" name="direccion"
                           value="<?= htmlspecialchars($e['direccion'] ?? '') ?>">

                    <label for="turno<?= $e['id_empleado'] ?>">Turno</label>
                    <input type="text" id="turno<?= $e['id_empleado'] ?>" name="turno"
                           value="<?= htmlspecialchars($e['turno'] ?? '') ?>">

                    <?php if ($e['rol'] === 'Mecanico'): ?>
                        <label for="especialidad<?= $e['id_empleado'] ?>">Especialidad</label>
                        <input type="text" id="especialidad<?= $e['id_empleado'] ?>" name="especialidad"
                               value="<?= htmlspecialchars($e['especialidad'] ?? '') ?>">
                    <?php else: ?>
                        <label for="area<?= $e['id_empleado'] ?>">Área</label>
                        <input type="text" id="area<?= $e['id_empleado'] ?>" name="area"
                               value="<?= htmlspecialchars($e['area'] ?? '') ?>">
                    <?php endif; ?>

                    <button type="submit">Guardar</button>
                </form>
            </fieldset>
        <?php endforeach; ?>
    <?php endif; ?>
<?php require __DIR__ . '/partials/footer.php'; ?>
