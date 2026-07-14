<?php
require_once __DIR__ . '/../config/database.php';

/**
 * Solicitud de refacción: un mecánico pide una pieza que necesita para un
 * servicio (RF18, Caso de uso 7). El administrativo la atiende decidiendo
 * a qué proveedor pedirla (Caso de uso 8), o la rechaza.
 */
class Solicitud
{
    private PDO $db;

    public ?int $id_solicitud = null;
    public int $id_mecanico;
    public ?int $id_servicio = null;
    public string $nombre_pieza = "";
    public int $cantidad = 1;
    public string $estado = 'Pendiente';
    public ?int $id_proveedor = null;

    public function __construct()
    {
        $conexion = new Database();
        $this->db = $conexion->conectar();
    }

    public function guardar(): int
    {
        $sql = "INSERT INTO solicitud_refaccion (id_mecanico, id_servicio, nombre_pieza, cantidad, estado)
                VALUES (:id_mecanico, :id_servicio, :nombre_pieza, :cantidad, :estado)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id_mecanico'  => $this->id_mecanico,
            ':id_servicio'  => $this->id_servicio,
            ':nombre_pieza' => $this->nombre_pieza,
            ':cantidad'     => $this->cantidad,
            ':estado'       => $this->estado,
        ]);

        $this->id_solicitud = (int) $this->db->lastInsertId();
        return $this->id_solicitud;
    }

    /**
     * Todas las solicitudes, para que el administrativo las revise (pendientes primero).
     */
    public function listarTodas(): array
    {
        $sql = "SELECT sr.*, e.nombre AS mecanico_nombre, e.apellido_pat AS mecanico_apellido,
                       cl.nombre AS cliente_nombre, cl.apellido_pat AS cliente_apellido,
                       a.marca, a.modelo,
                       p.nombre AS proveedor_nombre
                FROM solicitud_refaccion sr
                INNER JOIN empleado e ON e.id_empleado = sr.id_mecanico
                LEFT JOIN servicio s ON s.id_servicio = sr.id_servicio
                LEFT JOIN diagnostico d ON d.id_diagnostico = s.id_diagnostico
                LEFT JOIN cita c ON c.id_cita = d.id_cita
                LEFT JOIN cliente cl ON cl.id_cliente = c.id_cliente
                LEFT JOIN auto a ON a.id_auto = c.id_auto
                LEFT JOIN proveedor p ON p.id_proveedor = sr.id_proveedor
                ORDER BY (sr.estado = 'Pendiente') DESC, sr.creado_en DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Solicitudes hechas por un mecánico en particular (para que vea su propio estado).
     */
    public function listarDeMecanico(int $idMecanico): array
    {
        $sql = "SELECT sr.*, cl.nombre AS cliente_nombre, cl.apellido_pat AS cliente_apellido,
                       a.marca, a.modelo, p.nombre AS proveedor_nombre
                FROM solicitud_refaccion sr
                LEFT JOIN servicio s ON s.id_servicio = sr.id_servicio
                LEFT JOIN diagnostico d ON d.id_diagnostico = s.id_diagnostico
                LEFT JOIN cita c ON c.id_cita = d.id_cita
                LEFT JOIN cliente cl ON cl.id_cliente = c.id_cliente
                LEFT JOIN auto a ON a.id_auto = c.id_auto
                LEFT JOIN proveedor p ON p.id_proveedor = sr.id_proveedor
                WHERE sr.id_mecanico = :id_mecanico
                ORDER BY sr.creado_en DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id_mecanico' => $idMecanico]);
        return $stmt->fetchAll();
    }

    /**
     * El administrativo la marca Atendida (indicando a qué proveedor se le pidió)
     * o Rechazada.
     */
    public function actualizarEstado(int $id, string $estado, ?int $idProveedor): bool
    {
        $sql = "UPDATE solicitud_refaccion SET estado = :estado, id_proveedor = :id_proveedor
                WHERE id_solicitud = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':estado'      => $estado,
            ':id_proveedor' => $idProveedor,
            ':id'          => $id,
        ]);
    }
}
