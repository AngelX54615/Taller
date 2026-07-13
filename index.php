<?php
require_once __DIR__ . '/config/auth.php';

$titulo = 'Menú Principal';
require __DIR__ . '/partials/header.php';
?>
    <h1>Menú Principal</h1>

    <?php if (estaAutenticado()): ?>
        <div class="sesion">
            Sesión iniciada como <strong><?= htmlspecialchars($_SESSION['nombre'] . ' ' . $_SESSION['apellido_pat']) ?></strong>
            (<?= htmlspecialchars(etiquetaRol($_SESSION['rol'])) ?>) ·
            <a href="<?= panelDeRol($_SESSION['rol']) ?>">Ir a mi panel</a>
        </div>
    <?php else: ?>
        <ul class="menu">
            <li><a href="login.php">Iniciar sesión<span>Administrativo o Mecánico, según tu cuenta</span></a></li>
        </ul>
    <?php endif; ?>
<?php require __DIR__ . '/partials/footer.php'; ?>
