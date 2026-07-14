<?php
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/classes/Ticket.php';
require_once __DIR__ . '/classes/Refaccion.php';

requerirLogin('Administrativo');

$idTicket = (int) ($_GET['id_ticket'] ?? 0);

$ticketObj = new Ticket();
$ticket = $idTicket > 0 ? $ticketObj->verDetalle($idTicket) : false;

$refaccionesUsadas = [];
if ($ticket) {
    $refaccionObj = new Refaccion();
    $refaccionesUsadas = $refaccionObj->deServicio($ticket['id_servicio']);
}

$titulo = 'Ticket';
require __DIR__ . '/partials/header.php';
?>
    <h1>Ticket</h1>

    <?php if (!$ticket): ?>
        <div class="mensaje error">❌ No se encontró el ticket solicitado.</div>
        <p><a href="generar_ticket.php">Volver a generar ticket</a></p>
    <?php else: ?>
        <div class="sesion">
            <strong>Ticket #<?= $ticket['id_ticket'] ?></strong> ·
            Fecha de ingreso: <?= htmlspecialchars($ticket['fecha_ingreso']) ?>
            <?php if ($ticket['fecha_entrega']): ?>
                · Fecha de entrega: <?= htmlspecialchars($ticket['fecha_entrega']) ?>
            <?php endif; ?>
        </div>

        <fieldset>
            <legend>Cliente y vehículo</legend>
            <p><strong><?= htmlspecialchars($ticket['cliente_nombre'] . ' ' . $ticket['cliente_apellido']) ?></strong></p>
            <p>Tel: <?= htmlspecialchars($ticket['cliente_telefono'] ?? '—') ?> ·
               Correo: <?= htmlspecialchars($ticket['cliente_correo'] ?? '—') ?></p>
            <p><?= htmlspecialchars($ticket['marca'] . ' ' . $ticket['modelo'] . ' ' . ($ticket['anio'] ?? '')) ?>
               <?= $ticket['color'] ? '· ' . htmlspecialchars($ticket['color']) : '' ?>
               <?= $ticket['placa'] ? '· Placa: ' . htmlspecialchars($ticket['placa']) : '' ?></p>
        </fieldset>

        <fieldset>
            <legend>Servicio</legend>
            <p><strong><?= htmlspecialchars($ticket['tipo_servicio']) ?></strong></p>
            <?php if ($ticket['servicio_descripcion']): ?>
                <p><?= htmlspecialchars($ticket['servicio_descripcion']) ?></p>
            <?php endif; ?>
            <p>Atendido por: <?= htmlspecialchars($ticket['mecanico_nombre'] . ' ' . $ticket['mecanico_apellido']) ?></p>
        </fieldset>

        <?php if (!empty($refaccionesUsadas)): ?>
            <fieldset>
                <legend>Refacciones utilizadas</legend>
                <table>
                    <tr><th>Pieza</th><th>Marca</th><th>Cantidad</th><th>Precio</th></tr>
                    <?php foreach ($refaccionesUsadas as $r): ?>
                        <tr>
                            <td><?= htmlspecialchars($r['nombre_pieza']) ?></td>
                            <td><?= htmlspecialchars($r['marca'] ?? '') ?></td>
                            <td><?= (int) $r['cantidad_usada'] ?></td>
                            <td>$<?= htmlspecialchars($r['precio'] ?? '0.00') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </fieldset>
        <?php endif; ?>

        <fieldset>
            <legend>Pago</legend>
            <p>Monto total: <strong>$<?= htmlspecialchars(number_format((float) $ticket['monto'], 2)) ?></strong></p>
            <p>Método de pago: <?= htmlspecialchars($ticket['metodo_pago']) ?></p>
            <p>Generado por: <?= htmlspecialchars($ticket['admin_nombre'] . ' ' . $ticket['admin_apellido']) ?></p>
        </fieldset>

        <p><a href="generar_ticket.php">Volver a generar ticket</a></p>
    <?php endif; ?>
<?php require __DIR__ . '/partials/footer.php'; ?>
