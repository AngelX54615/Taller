<?php
/**
 * Conexión a la base de datos usando PDO.
 * Ajusta $host, $usuario y $password si tu configuración de XAMPP es distinta.
 */

class Database
{
    private string $host = "localhost";
    private string $dbName = "taller_jesus_gardea";
    private string $usuario = "root";
    private string $password = ""; // XAMPP por defecto no tiene contraseña

    // Compartida por todas las instancias: así, cuando una operación necesita
    // guardar en varias clases dentro de una misma transacción (p. ej. crear un
    // empleado y su cuenta de acceso), todas usan la misma conexión.
    private static ?PDO $conexion = null;

    public function conectar(): PDO
    {
        if (self::$conexion === null) {
            try {
                $dsn = "mysql:host={$this->host};dbname={$this->dbName};charset=utf8mb4";
                self::$conexion = new PDO($dsn, $this->usuario, $this->password, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]);
            } catch (PDOException $e) {
                die("Error de conexión: " . $e->getMessage());
            }
        }
        return self::$conexion;
    }
}
