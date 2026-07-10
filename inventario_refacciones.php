<?php
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/classes/Refaccion.php';

requerirLogin('Administrativo');

$refaccionObj = new Refaccion();
$inventario = $refaccionObj->listarTodos();
$titulo = 'Inventario de Refacciones';
$anchoAncho = true;
require __DIR__ . '/partials/header.php';
?>
    <h1>Inventario de Refacciones</h1>

    <?php if (empty($inventario)): ?>
        <p>No hay refacciones registradas todavía.</p>
    <?php else: ?>
        <table>
            <tr>
                <th>Pieza</th><th>Marca</th><th>Cantidad</th><th>Stock mínimo</th>
                <th>Precio</th><th>Proveedor</th><th>Estado</th>
            </tr>
            <?php foreach ($inventario as $r): ?>
                <tr class="<?= $r['stock_bajo'] ? 'stock-bajo' : '' ?>">
                    <td><?= htmlspecialchars($r['nombre_pieza']) ?></td>
                    <td><?= htmlspecialchars($r['marca'] ?? '') ?></td>
                    <td><?= (int) $r['cantidad'] ?></td>
                    <td><?= (int) $r['stock_minimo'] ?></td>
                    <td>$<?= htmlspecialchars($r['precio'] ?? '0.00') ?></td>
                    <td><?= htmlspecialchars($r['proveedor_nombre']) ?></td>
                    <td><?= $r['stock_bajo'] ? '<span class="badge badge-alerta">Stock bajo</span>' : 'OK' ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>

    <p><a class="seleccionar" href="registrar_refaccion.php">+ Registrar refacción</a></p>
<?php require __DIR__ . '/partials/footer.php'; ?>
