<?php
require_once __DIR__ . '/../config/database.php';

class Ticket
{
    private PDO $db;

    public ?int $id_ticket = null;
    public string $fecha_ingreso = "";
    public ?string $fecha_entrega = null;
    public float $monto;
    public string $metodo_pago = "";
    public int $id_servicio;
    public int $id_administrativo;

    public function __construct()
    {
        $conexion = new Database();
        $this->db = $conexion->conectar();
    }

    public function guardar(): int
    {
        $sql = "INSERT INTO ticket (fecha_ingreso, fecha_entrega, monto, metodo_pago, id_servicio, id_administrativo)
                VALUES (:fecha_ingreso, :fecha_entrega, :monto, :metodo_pago, :id_servicio, :id_administrativo)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':fecha_ingreso'     => $this->fecha_ingreso,
            ':fecha_entrega'     => $this->fecha_entrega,
            ':monto'             => $this->monto,
            ':metodo_pago'       => $this->metodo_pago,
            ':id_servicio'       => $this->id_servicio,
            ':id_administrativo' => $this->id_administrativo,
        ]);

        $this->id_ticket = (int) $this->db->lastInsertId();
        return $this->id_ticket;
    }

    /**
     * Servicios ya finalizados que todavía no tienen un ticket generado.
     */
    public function serviciosSinTicket(): array
    {
        $sql = "SELECT s.id_servicio, s.costo, s.tipo_servicio,
                       cl.nombre AS cliente_nombre, cl.apellido_pat AS cliente_apellido,
                       a.marca, a.modelo
                FROM servicio s
                INNER JOIN diagnostico d ON d.id_diagnostico = s.id_diagnostico
                INNER JOIN cita c ON c.id_cita = d.id_cita
                INNER JOIN cliente cl ON cl.id_cliente = c.id_cliente
                INNER JOIN auto a ON a.id_auto = c.id_auto
                WHERE s.estado = 'Finalizado'
                  AND s.id_servicio NOT IN (SELECT id_servicio FROM ticket)
                ORDER BY s.creado_en";

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function buscarPorId(int $id): array|false
    {
        $sql = "SELECT * FROM ticket WHERE id_ticket = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }
}
