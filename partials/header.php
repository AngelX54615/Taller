<?php
/**
 * Layout compartido. Cada página define $titulo (y opcionalmente $anchoAncho = true
 * para pantallas con tablas grandes) antes de incluir este archivo.
 */
require_once __DIR__ . '/../config/auth.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($titulo ?? 'Taller Jesús Gardea') ?> - Taller Jesús Gardea</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
    <header class="topbar">
        <a class="marca" href="index.php">🔧 Taller Jesús Gardea</a>
        <nav>
            <a href="index.php">Menú principal</a>
            <?php if (estaAutenticado()): ?>
                <a href="<?= panelDeRol($_SESSION['rol']) ?>">Panel</a>
                <span class="usuario"><strong><?= htmlspecialchars($_SESSION['nombre'] . ' ' . $_SESSION['apellido_pat']) ?></strong> · <?= htmlspecialchars(etiquetaRol($_SESSION['rol'])) ?></span>
                <a class="salir" href="logout.php">Cerrar sesión</a>
            <?php else: ?>
                <a class="salir" href="login.php">Iniciar sesión</a>
            <?php endif; ?>
        </nav>
    </header>
    <main class="container<?= !empty($anchoAncho) ? ' ancho' : '' ?>">
