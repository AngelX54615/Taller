<?php
require_once __DIR__ . '/../config/database.php';

class Cliente
{
    private PDO $db;

    public ?int $id_cliente = null;
    public string $nombre = "";
    public string $apellido_pat = "";
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
        $sql = "INSERT INTO cliente (nombre, apellido_pat, apellido_mat, telefono, correo)
                VALUES (:nombre, :apellido_pat, :apellido_mat, :telefono, :correo)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':nombre'       => $this->nombre,
            ':apellido_pat' => $this->apellido_pat,
            ':apellido_mat' => $this->apellido_mat,
            ':telefono'     => $this->telefono,
            ':correo'       => $this->correo,
        ]);

        $this->id_cliente = (int) $this->db->lastInsertId();
        return $this->id_cliente;
    }

    public function buscarPorId(int $id): array|false
    {
        $sql = "SELECT * FROM cliente WHERE id_cliente = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    /**
     * RF1: consultar clientes por correo, teléfono, nombre o id.
     */
    public function buscar(string $criterio): array
    {
        $sql = "SELECT * FROM cliente
                WHERE id_cliente = :criterio
                   OR nombre LIKE :like
                   OR telefono LIKE :like
                   OR correo LIKE :like";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':criterio' => is_numeric($criterio) ? $criterio : 0,
            ':like'     => "%$criterio%",
        ]);
        return $stmt->fetchAll();
    }

    public function listarTodos(): array
    {
        $sql = "SELECT * FROM cliente ORDER BY nombre";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * RF6: modificar datos autorizados del cliente (teléfono, correo).
     */
    public function actualizar(int $id, string $telefono, string $correo): bool
    {
        $sql = "UPDATE cliente SET telefono = :telefono, correo = :correo WHERE id_cliente = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':telefono' => $telefono,
            ':correo'   => $correo,
            ':id'       => $id,
        ]);
    }
}
