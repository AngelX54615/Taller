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

    /**
     * RF16: datos completos del ticket para mostrarlo en pantalla como un recibo
     * (cliente, vehículo, servicio, mecánico y administrativo que lo generó).
     */
    public function verDetalle(int $id): array|false
    {
        $sql = "SELECT t.*,
                       s.id_servicio, s.tipo_servicio, s.descripcion AS servicio_descripcion, s.costo,
                       cl.nombre AS cliente_nombre, cl.apellido_pat AS cliente_apellido,
                       cl.telefono AS cliente_telefono, cl.correo AS cliente_correo,
                       a.marca, a.modelo, a.anio, a.color, a.placa,
                       adm.nombre AS admin_nombre, adm.apellido_pat AS admin_apellido,
                       mec.nombre AS mecanico_nombre, mec.apellido_pat AS mecanico_apellido
                FROM ticket t
                INNER JOIN servicio s ON s.id_servicio = t.id_servicio
                INNER JOIN diagnostico d ON d.id_diagnostico = s.id_diagnostico
                INNER JOIN cita c ON c.id_cita = d.id_cita
                INNER JOIN cliente cl ON cl.id_cliente = c.id_cliente
                INNER JOIN auto a ON a.id_auto = c.id_auto
                INNER JOIN empleado adm ON adm.id_empleado = t.id_administrativo
                INNER JOIN empleado mec ON mec.id_empleado = s.id_mecanico
                WHERE t.id_ticket = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }
}
