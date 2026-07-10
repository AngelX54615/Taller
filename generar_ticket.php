<?php
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/classes/Ticket.php';

requerirLogin('Administrativo');
$idAdmin = $_SESSION['id_empleado'];

$mensaje = "";
$error = "";
$serviciosPendientes = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'guardar_ticket') {
    $idServicio = (int) $_POST['id_servicio'];
    $fechaIngreso = trim($_POST['fecha_ingreso'] ?? '');
    $fechaEntrega = trim($_POST['fecha_entrega'] ?? '');
    $monto = trim($_POST['monto'] ?? '');
    $metodoPago = trim($_POST['metodo_pago'] ?? '');

    if ($fechaIngreso === '' || $monto === '' || $metodoPago === '') {
        $error = "La fecha de ingreso, el monto y el método de pago son obligatorios.";
    } else {
        try {
            $ticket = new Ticket();
            $ticket->fecha_ingreso = $fechaIngreso;
            $ticket->fecha_entrega = $fechaEntrega ?: null;
            $ticket->monto = (float) $monto;
            $ticket->metodo_pago = $metodoPago;
            $ticket->id_servicio = $idServicio;
            $ticket->id_administrativo = $idAdmin;

            $idTicket = $ticket->guardar();
            $mensaje = "Ticket generado correctamente (id_ticket = $idTicket).";

        } catch (Exception $e) {
            $error = "Ocurrió un error al guardar: " . $e->getMessage();
        }
    }
}

if (!$mensaje) {
    $ticketObj = new Ticket();
    $serviciosPendientes = $ticketObj->serviciosSinTicket();
}

$metodosPago = ['Efectivo', 'Tarjeta', 'Transferencia'];
$titulo = 'Generar Ticket';
require __DIR__ . '/partials/header.php';
?>
    <h1>Generar Ticket</h1>

    <?php if ($mensaje): ?>
        <div class="mensaje exito">✅ <?= htmlspecialchars($mensaje) ?></div>
        <p><a href="generar_ticket.php">Generar otro ticket</a></p>
    <?php else: ?>

        <?php if ($error): ?>
            <div class="mensaje error">❌ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <h3>Servicios finalizados sin ticket</h3>

        <?php if (empty($serviciosPendientes)): ?>
            <p>No hay servicios finalizados pendientes de facturar.</p>
        <?php else: ?>
            <table>
                <tr><th>Cliente</th><th>Vehículo</th><th>Tipo</th><th>Costo</th><th></th></tr>
                <?php foreach ($serviciosPendientes as $s): ?>
                    <tr>
                        <td><?= htmlspecialchars($s['cliente_nombre'] . ' ' . $s['cliente_apellido']) ?></td>
                        <td><?= htmlspecialchars($s['marca'] . ' ' . $s['modelo']) ?></td>
                        <td><?= htmlspecialchars($s['tipo_servicio']) ?></td>
                        <td>$<?= htmlspecialchars($s['costo']) ?></td>
                        <td>
                            <a class="seleccionar" href="#"
                               onclick="mostrarFormulario(<?= $s['id_servicio'] ?>, <?= $s['costo'] ?>); return false;">
                               Generar ticket
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>

            <div id="form-ticket" style="display:none; margin-top:20px;">
                <form method="POST" action="">
                    <input type="hidden" name="accion" value="guardar_ticket">
                    <input type="hidden" name="id_servicio" id="id_servicio_hidden">

                        <fieldset>
                            <legend>Datos del ticket</legend>

                            <label for="fecha_ingreso">Fecha de ingreso *</label>
                            <input type="date" id="fecha_ingreso" name="fecha_ingreso" value="<?= date('Y-m-d') ?>" required>

                            <label for="fecha_entrega">Fecha de entrega</label>
                            <input type="date" id="fecha_entrega" name="fecha_entrega" value="<?= date('Y-m-d') ?>">

                            <label for="monto">Monto ($) *</label>
                            <input type="number" step="0.01" id="monto" name="monto" required>

                            <label for="metodo_pago">Método de pago *</label>
                            <select id="metodo_pago" name="metodo_pago" required>
                                <option value="">-- Selecciona --</option>
                                <?php foreach ($metodosPago as $mp): ?>
                                    <option value="<?= $mp ?>"><?= $mp ?></option>
                                <?php endforeach; ?>
                            </select>
                        </fieldset>

                        <button type="submit">Generar ticket</button>
                    </form>
                </div>

                <script>
                function mostrarFormulario(idServicio, costo) {
                    document.getElementById('id_servicio_hidden').value = idServicio;
                    document.getElementById('monto').value = costo;
                    document.getElementById('form-ticket').style.display = 'block';
                    document.getElementById('form-ticket').scrollIntoView({behavior: 'smooth'});
                }
                </script>
            <?php endif; ?>

    <?php endif; ?>
<?php require __DIR__ . '/partials/footer.php'; ?>
