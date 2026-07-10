<?php
require_once __DIR__ . '/../config/database.php';

class Diagnostico
{
    private PDO $db;

    public ?int $id_diagnostico = null;
    public ?string $descripcion = null;
    public ?float $presupuesto = null;
    public int $id_cita;
    public int $id_mecanico;

    public function __construct()
    {
        $conexion = new Database();
        $this->db = $conexion->conectar();
    }

    public function guardar(): int
    {
        $sql = "INSERT INTO diagnostico (descripcion, presupuesto, id_cita, id_mecanico)
                VALUES (:descripcion, :presupuesto, :id_cita, :id_mecanico)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':descripcion' => $this->descripcion,
            ':presupuesto' => $this->presupuesto,
            ':id_cita'     => $this->id_cita,
            ':id_mecanico' => $this->id_mecanico,
        ]);

        $this->id_diagnostico = (int) $this->db->lastInsertId();
        return $this->id_diagnostico;
    }

    /**
     * Citas asignadas a un mecánico que AÚN no tienen diagnóstico registrado.
     * Así el mecánico solo ve pendientes, no toda la lista de citas.
     */
    public function citasPendientesDeMecanico(int $idMecanico): array
    {
        $sql = "SELECT c.id_cita, c.fecha, c.hora, c.motivo,
                       cl.nombre AS cliente_nombre, cl.apellido_pat AS cliente_apellido,
                       a.marca, a.modelo
                FROM cita c
                INNER JOIN cliente cl ON cl.id_cliente = c.id_cliente
                INNER JOIN auto a ON a.id_auto = c.id_auto
                WHERE c.id_mecanico = :id_mecanico
                  AND c.estado <> 'Cancelada'
                  AND c.id_cita NOT IN (SELECT id_cita FROM diagnostico)
                ORDER BY c.fecha, c.hora";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id_mecanico' => $idMecanico]);
        return $stmt->fetchAll();
    }

    public function buscarPorId(int $id): array|false
    {
        $sql = "SELECT d.*, c.motivo AS cita_motivo,
                       cl.nombre AS cliente_nombre, cl.apellido_pat AS cliente_apellido
                FROM diagnostico d
                INNER JOIN cita c ON c.id_cita = d.id_cita
                INNER JOIN cliente cl ON cl.id_cliente = c.id_cliente
                WHERE d.id_diagnostico = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }
}
