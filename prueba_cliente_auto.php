<?php
require_once __DIR__ . '/classes/Cliente.php';
require_once __DIR__ . '/classes/Auto.php';

echo "<h2>Prueba de Cliente y Auto</h2>";

try {
    // Crear cliente
    $cliente = new Cliente();
    $cliente->nombre = "Roberto";
    $cliente->apellido_pat = "Licea";
    $cliente->apellido_mat = "Ramos";
    $cliente->telefono = "6569998877";
    $cliente->correo = "roberto.licea@example.com";

    $idCliente = $cliente->guardar();
    echo "<p>✅ Cliente guardado con id_cliente = $idCliente</p>";

    // Crear un auto para ese cliente
    $auto = new Auto();
    $auto->tipo = "Sedán";
    $auto->marca = "Toyota";
    $auto->modelo = "Corolla";
    $auto->color = "Gris";
    $auto->anio = 2020;
    $auto->id_cliente = $idCliente;

    $idAuto = $auto->guardar();
    echo "<p>✅ Auto guardado con id_auto = $idAuto</p>";

    // Segundo auto del mismo cliente (para probar 1:N)
    $auto2 = new Auto();
    $auto2->tipo = "Pickup";
    $auto2->marca = "Ford";
    $auto2->modelo = "F-150";
    $auto2->color = "Negro";
    $auto2->anio = 2018;
    $auto2->id_cliente = $idCliente;
    $auto2->guardar();

    // Listar autos del cliente
    echo "<h3>Autos de $cliente->nombre $cliente->apellido_pat:</h3><ul>";
    foreach ($auto->listarPorCliente($idCliente) as $a) {
        echo "<li>{$a['marca']} {$a['modelo']} ({$a['anio']}) - Color: {$a['color']}</li>";
    }
    echo "</ul>";

    // Buscar cliente (RF1)
    echo "<h3>Búsqueda de cliente por nombre 'Roberto':</h3><ul>";
    foreach ($cliente->buscar("Roberto") as $c) {
        echo "<li>{$c['nombre']} {$c['apellido_pat']} - {$c['correo']}</li>";
    }
    echo "</ul>";

} catch (Exception $e) {
    echo "<p style='color:red'>❌ Error: " . $e->getMessage() . "</p>";
}
