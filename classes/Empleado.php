<?php
require_once __DIR__ . '/../config/database.php';

/**
 * Clase base Empleado.
 * Mecanico y Administrativo heredan de esta clase (igual que en el diagrama de clases).
 */
class Empleado
{
    protected PDO $db;

    public ?int $id_empleado = null;
    public string $nombre = "";
    public string $apellido_pat = "";
    public ?string $apellido_mat = null;
    public ?string $telefono = null;
    public ?string $direccion = null;
    public ?string $turno = null;

    public function __construct()
    {
        $conexion = new Database();
        $this->db = $conexion->conectar();
    }

    /**
     * Inserta un nuevo empleado en la tabla "empleado".
     * Devuelve el id_empleado generado (lo usan las clases hijas).
     */
    public function guardar(): int
    {
        $sql = "INSERT INTO empleado (nombre, apellido_pat, apellido_mat, telefono, direccion, turno)
                VALUES (:nombre, :apellido_pat, :apellido_mat, :telefono, :direccion, :turno)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':nombre'       => $this->nombre,
            ':apellido_pat' => $this->apellido_pat,
            ':apellido_mat' => $this->apellido_mat,
            ':telefono'     => $this->telefono,
            ':direccion'    => $this->direccion,
            ':turno'        => $this->turno,
        ]);

        $this->id_empleado = (int) $this->db->lastInsertId();
        return $this->id_empleado;
    }

    /**
     * Trae un empleado por su id. Devuelve un arreglo asociativo o false si no existe.
     */
    public function buscarPorId(int $id): array|false
    {
        $sql = "SELECT * FROM empleado WHERE id_empleado = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Devuelve todos los empleados activos.
     */
    public function listarTodos(): array
    {
        $sql = "SELECT * FROM empleado WHERE activo = 1";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Marca un empleado como inactivo (RF de baja lógica, no se borra el registro).
     */
    public function darDeBaja(int $id): bool
    {
        $sql = "UPDATE empleado SET activo = 0 WHERE id_empleado = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }
}
