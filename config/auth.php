<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function estaAutenticado(): bool
{
    return isset($_SESSION['id_empleado'], $_SESSION['rol']);
}

function panelDeRol(string $rol): string
{
    return $rol === 'Mecanico' ? 'panel_mecanico.php' : 'panel_administrativo.php';
}

function etiquetaRol(string $rol): string
{
    return $rol === 'Mecanico' ? 'Mecánico' : 'Administrativo';
}

/**
 * Corta la ejecución si no hay sesión iniciada, o si el rol de la sesión
 * no coincide con el que la página requiere (lo manda a su propio panel).
 */
function requerirLogin(?string $rolRequerido = null): void
{
    if (!estaAutenticado()) {
        header('Location: login.php');
        exit;
    }

    if ($rolRequerido !== null && $_SESSION['rol'] !== $rolRequerido) {
        header('Location: ' . panelDeRol($_SESSION['rol']));
        exit;
    }
}
