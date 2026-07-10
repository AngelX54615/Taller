<?php
require_once __DIR__ . '/../config/database.php';

class Refaccion
{
    private PDO $db;

    public ?int $id_pieza = null;
    public string $nombre_pieza = "";
    public ?string $marca = null;
    public int $cantidad = 0;
    public int $stock_minimo = 0;
    public ?float $precio = null;
    public int $id_proveedor;

    public function __construct()
    {
        $conexion = new Database();
        $this->db = $conexion->conectar();
    }

    public function guardar(): int
    {
        $sql = "INSERT INTO refaccion (nombre_pieza, marca, cantidad, stock_minimo, precio, id_proveedor)
                VALUES (:nombre_pieza, :marca, :cantidad, :stock_minimo, :precio, :id_proveedor)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':nombre_pieza'  => $this->nombre_pieza,
            ':marca'         => $this->marca,
            ':cantidad'      => $this->cantidad,
            ':stock_minimo'  => $this->stock_minimo,
            ':precio'        => $this->precio,
            ':id_proveedor'  => $this->id_proveedor,
        ]);

        $this->id_pieza = (int) $this->db->lastInsertId();
        return $this->id_pieza;
    }

    public function buscarPorId(int $id): array|false
    {
        $sql = "SELECT r.*, p.nombre AS proveedor_nombre
                FROM refaccion r
                INNER JOIN proveedor p ON p.id_proveedor = r.id_proveedor
                WHERE r.id_pieza = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Inventario completo, con el nombre del proveedor y una bandera de stock bajo (RF9).
     */
    public function listarTodos(): array
    {
        $sql = "SELECT r.*, p.nombre AS proveedor_nombre,
                       (r.cantidad <= r.stock_minimo) AS stock_bajo
                FROM refaccion r
                INNER JOIN proveedor p ON p.id_proveedor = r.id_proveedor
                ORDER BY r.nombre_pieza";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Refacciones con existencia disponible, para elegir al asignarlas a un servicio.
     */
    public function disponibles(): array
    {
        $sql = "SELECT id_pieza, nombre_pieza, marca, cantidad, precio
                FROM refaccion
                WHERE cantidad > 0
                ORDER BY nombre_pieza";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Refacciones ya asignadas a un servicio (tabla servicio_refaccion).
     */
    public function deServicio(int $idServicio): array
    {
        $sql = "SELECT sr.cantidad_usada, r.id_pieza, r.nombre_pieza, r.marca, r.precio
                FROM servicio_refaccion sr
                INNER JOIN refaccion r ON r.id_pieza = sr.id_pieza
                WHERE sr.id_servicio = :id_servicio";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id_servicio' => $idServicio]);
        return $stmt->fetchAll();
    }

    /**
     * Asigna una refacción usada a un servicio. El trigger trg_descontar_stock
     * ya se encarga de descontar la cantidad del inventario.
     */
    public function asignarAServicio(int $idServicio, int $idPieza, int $cantidadUsada): void
    {
        $sql = "SELECT cantidad FROM refaccion WHERE id_pieza = :id_pieza";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id_pieza' => $idPieza]);
        $refaccion = $stmt->fetch();

        if (!$refaccion) {
            throw new Exception("La refacción seleccionada no existe.");
        }

        if ($cantidadUsada > (int) $refaccion['cantidad']) {
            throw new Exception("No hay suficiente existencia de esa refacción (disponible: {$refaccion['cantidad']}).");
        }

        $sql = "INSERT INTO servicio_refaccion (id_servicio, id_pieza, cantidad_usada)
                VALUES (:id_servicio, :id_pieza, :cantidad_usada)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id_servicio'    => $idServicio,
            ':id_pieza'       => $idPieza,
            ':cantidad_usada' => $cantidadUsada,
        ]);
    }
}
