<?php
require_once __DIR__ . '/../config/database.php';

class Auto
{
    private PDO $db;

    public ?int $id_auto = null;
    public ?string $tipo = null;
    public ?string $marca = null;
    public ?string $modelo = null;
    public ?string $color = null;
    public ?int $anio = null;
    public int $id_cliente;

    public function __construct()
    {
        $conexion = new Database();
        $this->db = $conexion->conectar();
    }

    public function guardar(): int
    {
        $sql = "INSERT INTO auto (tipo, marca, modelo, color, anio, id_cliente)
                VALUES (:tipo, :marca, :modelo, :color, :anio, :id_cliente)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':tipo'       => $this->tipo,
            ':marca'      => $this->marca,
            ':modelo'     => $this->modelo,
            ':color'      => $this->color,
            ':anio'       => $this->anio,
            ':id_cliente' => $this->id_cliente,
        ]);

        $this->id_auto = (int) $this->db->lastInsertId();
        return $this->id_auto;
    }

    public function buscarPorId(int $id): array|false
    {
        $sql = "SELECT * FROM auto WHERE id_auto = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Todos los autos de un cliente (un cliente puede tener varios).
     */
    public function listarPorCliente(int $idCliente): array
    {
        $sql = "SELECT * FROM auto WHERE id_cliente = :id_cliente";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id_cliente' => $idCliente]);
        return $stmt->fetchAll();
    }

    /**
     * RF6: modificar datos del vehículo (tipo, modelo, marca, año, color).
     */
    public function actualizar(int $id, array $datos): bool
    {
        $sql = "UPDATE auto SET tipo = :tipo, marca = :marca, modelo = :modelo,
                color = :color, anio = :anio WHERE id_auto = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':tipo'   => $datos['tipo'],
            ':marca'  => $datos['marca'],
            ':modelo' => $datos['modelo'],
            ':color'  => $datos['color'],
            ':anio'   => $datos['anio'],
            ':id'     => $id,
        ]);
    }
}
