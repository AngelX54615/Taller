<?php
require_once __DIR__ . '/classes/Mecanico.php';
require_once __DIR__ . '/classes/Administrativo.php';

echo "<h2>Prueba de conexión y clases</h2>";

try {
    // Crear un mecánico
    $mecanico = new Mecanico();
    $mecanico->nombre = "Juan";
    $mecanico->apellido_pat = "Pérez";
    $mecanico->apellido_mat = "López";
    $mecanico->telefono = "6561234567";
    $mecanico->direccion = "Calle Falsa 123";
    $mecanico->turno = "Matutino";
    $mecanico->especialidad = "Motor y transmisión";

    $idMecanico = $mecanico->guardar();
    echo "<p>✅ Mecánico guardado con id_empleado = $idMecanico</p>";

    // Crear un administrativo
    $admin = new Administrativo();
    $admin->nombre = "María";
    $admin->apellido_pat = "González";
    $admin->apellido_mat = "Ruiz";
    $admin->telefono = "6567654321";
    $admin->direccion = "Av. Siempre Viva 456";
    $admin->area = "Recepción";
    $admin->turno = "Matutino";

    $idAdmin = $admin->guardar();
    echo "<p>✅ Administrativo guardado con id_empleado = $idAdmin</p>";

    // Listar mecánicos
    echo "<h3>Mecánicos registrados:</h3><ul>";
    foreach ($mecanico->listarTodos() as $m) {
        echo "<li>{$m['nombre']} {$m['apellido_pat']} - Especialidad: {$m['especialidad']}</li>";
    }
    echo "</ul>";

    // Listar administrativos
    echo "<h3>Administrativos registrados:</h3><ul>";
    foreach ($admin->listarTodos() as $a) {
        echo "<li>{$a['nombre']} {$a['apellido_pat']} - Área: {$a['area']}, Turno: {$a['turno']}</li>";
    }
    echo "</ul>";

} catch (Exception $e) {
    echo "<p style='color:red'>❌ Error: " . $e->getMessage() . "</p>";
}
