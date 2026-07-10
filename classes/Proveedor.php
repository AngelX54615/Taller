<?php
require_once __DIR__ . '/../config/database.php';

class Proveedor
{
    private PDO $db;

    public ?int $id_proveedor = null;
    public string $nombre = "";
    public ?string $apellido_pat = null;
    public ?string $apellido_mat = null;
    public ?string $telefono = null;
    public ?string $correo = null;

    public function __construct()
    {
        $conexion = new Database();
        $this->db = $conexion->conectar();
    }

    public function guardar(): int
    {
        $sql = "INSERT INTO proveedor (nombre, apellido_pat, apellido_mat, telefono, correo)
                VALUES (:nombre, :apellido_pat, :apellido_mat, :telefono, :correo)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':nombre'       => $this->nombre,
            ':apellido_pat' => $this->apellido_pat,
            ':apellido_mat' => $this->apellido_mat,
            ':telefono'     => $this->telefono,
            ':correo'       => $this->correo,
        ]);

        $this->id_proveedor = (int) $this->db->lastInsertId();
        return $this->id_proveedor;
    }

    public function buscarPorId(int $id): array|false
    {
        $sql = "SELECT * FROM proveedor WHERE id_proveedor = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function listarTodos(): array
    {
        $sql = "SELECT * FROM proveedor ORDER BY nombre";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
}
