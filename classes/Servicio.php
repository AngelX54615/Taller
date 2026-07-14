<?php
require_once __DIR__ . '/../config/database.php';

class Servicio
{
    private PDO $db;

    public ?int $id_servicio = null;
    public ?float $costo = null;
    public ?string $descripcion = null;
    public ?string $tipo_servicio = null;
    public ?string $tiempo_estimado = null;
    public string $estado = 'Pendiente';
    public int $id_diagnostico;
    public int $id_administrativo;
    public int $id_mecanico;

    public function __construct()
    {
        $conexion = new Database();
        $this->db = $conexion->conectar();
    }

    /**
     * Un mecánico solo puede trabajar en un servicio a la vez: no se le puede
     * asignar uno nuevo mientras tenga otro Pendiente o En proceso.
     */
    public function guardar(): int
    {
        $sqlOcupado = "SELECT COUNT(*) FROM servicio
                        WHERE id_mecanico = :id_mecanico AND estado IN ('Pendiente', 'En proceso')";
        $stmtOcupado = $this->db->prepare($sqlOcupado);
        $stmtOcupado->execute([':id_mecanico' => $this->id_mecanico]);

        if ((int) $stmtOcupado->fetchColumn() > 0) {
            throw new Exception("Este mecánico ya tiene un servicio activo; no puede trabajar en otro a la vez.");
        }

        $sql = "INSERT INTO servicio (costo, descripcion, tipo_servicio, tiempo_estimado, estado,
                                       id_diagnostico, id_administrativo, id_mecanico)
                VALUES (:costo, :descripcion, :tipo_servicio, :tiempo_estimado, :estado,
                        :id_diagnostico, :id_administrativo, :id_mecanico)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':costo'             => $this->costo,
            ':descripcion'       => $this->descripcion,
            ':tipo_servicio'     => $this->tipo_servicio,
            ':tiempo_estimado'   => $this->tiempo_estimado,
            ':estado'            => $this->estado,
            ':id_diagnostico'    => $this->id_diagnostico,
            ':id_administrativo' => $this->id_administrativo,
            ':id_mecanico'       => $this->id_mecanico,
        ]);

        $this->id_servicio = (int) $this->db->lastInsertId();
        return $this->id_servicio;
    }

    /**
     * Diagnósticos que ya existen pero que todavía no tienen una orden de servicio generada.
     */
    public function diagnosticosSinServicio(): array
    {
        $sql = "SELECT d.id_diagnostico, d.descripcion, d.presupuesto,
                       c.id_mecanico,
                       cl.nombre AS cliente_nombre, cl.apellido_pat AS cliente_apellido,
                       a.marca, a.modelo,
                       e.nombre AS mecanico_nombre, e.apellido_pat AS mecanico_apellido
                FROM diagnostico d
                INNER JOIN cita c ON c.id_cita = d.id_cita
                INNER JOIN cliente cl ON cl.id_cliente = c.id_cliente
                INNER JOIN auto a ON a.id_auto = c.id_auto
                INNER JOIN empleado e ON e.id_empleado = c.id_mecanico
                WHERE d.id_diagnostico NOT IN (SELECT id_diagnostico FROM servicio)
                ORDER BY d.creado_en";

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * RF8: actualizar el estado del servicio (Pendiente, En proceso, Finalizado, Cancelado).
     * El trigger trg_historial_estado ya guarda el historial automáticamente.
     */
    public function actualizarEstado(int $idServicio, string $nuevoEstado): bool
    {
        $sql = "UPDATE servicio SET estado = :estado WHERE id_servicio = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':estado' => $nuevoEstado, ':id' => $idServicio]);
    }

    /**
     * Servicios asignados a un mecánico (para que actualice su estado).
     */
    public function serviciosDeMecanico(int $idMecanico): array
    {
        $sql = "SELECT s.*, cl.nombre AS cliente_nombre, cl.apellido_pat AS cliente_apellido,
                       a.marca, a.modelo
                FROM servicio s
                INNER JOIN diagnostico d ON d.id_diagnostico = s.id_diagnostico
                INNER JOIN cita c ON c.id_cita = d.id_cita
                INNER JOIN cliente cl ON cl.id_cliente = c.id_cliente
                INNER JOIN auto a ON a.id_auto = c.id_auto
                WHERE s.id_mecanico = :id_mecanico
                ORDER BY s.creado_en DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id_mecanico' => $idMecanico]);
        return $stmt->fetchAll();
    }

    public function buscarPorId(int $id): array|false
    {
        $sql = "SELECT * FROM servicio WHERE id_servicio = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Todos los servicios activos (Pendiente o En proceso) de cualquier mecánico,
     * para que el administrativo pueda cancelarlos si el cliente así lo decide.
     */
    public function serviciosActivos(): array
    {
        $sql = "SELECT s.*, cl.nombre AS cliente_nombre, cl.apellido_pat AS cliente_apellido,
                       a.marca, a.modelo,
                       e.nombre AS mecanico_nombre, e.apellido_pat AS mecanico_apellido
                FROM servicio s
                INNER JOIN diagnostico d ON d.id_diagnostico = s.id_diagnostico
                INNER JOIN cita c ON c.id_cita = d.id_cita
                INNER JOIN cliente cl ON cl.id_cliente = c.id_cliente
                INNER JOIN auto a ON a.id_auto = c.id_auto
                INNER JOIN empleado e ON e.id_empleado = s.id_mecanico
                WHERE s.estado IN ('Pendiente', 'En proceso')
                ORDER BY s.creado_en DESC";

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Servicios de un mecánico que aún admiten que se les asignen refacciones
     * (no tiene sentido asignar piezas a uno ya Finalizado o Cancelado).
     */
    public function serviciosActivosDeMecanico(int $idMecanico): array
    {
        $sql = "SELECT s.*, cl.nombre AS cliente_nombre, cl.apellido_pat AS cliente_apellido,
                       a.marca, a.modelo
                FROM servicio s
                INNER JOIN diagnostico d ON d.id_diagnostico = s.id_diagnostico
                INNER JOIN cita c ON c.id_cita = d.id_cita
                INNER JOIN cliente cl ON cl.id_cliente = c.id_cliente
                INNER JOIN auto a ON a.id_auto = c.id_auto
                WHERE s.id_mecanico = :id_mecanico
                  AND s.estado IN ('Pendiente', 'En proceso')
                ORDER BY s.creado_en DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id_mecanico' => $idMecanico]);
        return $stmt->fetchAll();
    }
}
