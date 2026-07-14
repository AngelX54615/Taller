<?php
require_once __DIR__ . '/../config/database.php';

class Cita
{
    private PDO $db;

    public ?int $id_cita = null;
    public string $fecha = "";
    public string $hora = "";
    public ?string $motivo = null;
    public int $id_cliente;
    public int $id_auto;
    public ?int $id_mecanico = null;

    public function __construct()
    {
        $conexion = new Database();
        $this->db = $conexion->conectar();
    }

    /**
     * Busca un mecánico que NO tenga ya una cita en esa misma fecha y hora.
     * Devuelve el id_empleado del mecánico disponible, o null si no hay ninguno.
     */
    public function buscarMecanicoDisponible(string $fecha, string $hora): ?int
    {
        $sql = "SELECT m.id_empleado
                FROM mecanico m
                INNER JOIN empleado e ON e.id_empleado = m.id_empleado
                WHERE e.activo = 1
                  AND m.id_empleado NOT IN (
                      SELECT id_mecanico FROM cita
                      WHERE fecha = :fecha AND hora = :hora
                        AND id_mecanico IS NOT NULL
                        AND estado <> 'Cancelada'
                  )
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':fecha' => $fecha, ':hora' => $hora]);
        $resultado = $stmt->fetch();

        return $resultado ? (int) $resultado['id_empleado'] : null;
    }

    /**
     * Registra la cita. Internamente busca y asigna un mecánico disponible (RF3 y RF4).
     * Lanza una excepción si la fecha/hora ya pasaron, o si no hay ningún mecánico libre.
     */
    public function guardar(): int
    {
        if ("{$this->fecha} {$this->hora}" < date('Y-m-d H:i')) {
            throw new Exception("No se pueden agendar citas en una fecha u hora que ya pasaron.");
        }

        $this->id_mecanico = $this->buscarMecanicoDisponible($this->fecha, $this->hora);

        if ($this->id_mecanico === null) {
            throw new Exception("No hay mecánicos disponibles en la fecha y hora seleccionadas.");
        }

        $sql = "INSERT INTO cita (fecha, hora, motivo, id_cliente, id_auto, id_mecanico)
                VALUES (:fecha, :hora, :motivo, :id_cliente, :id_auto, :id_mecanico)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':fecha'       => $this->fecha,
            ':hora'        => $this->hora,
            ':motivo'      => $this->motivo,
            ':id_cliente'  => $this->id_cliente,
            ':id_auto'     => $this->id_auto,
            ':id_mecanico' => $this->id_mecanico,
        ]);

        $this->id_cita = (int) $this->db->lastInsertId();
        return $this->id_cita;
    }

    /**
     * Trae una cita con los datos de cliente, auto y mecánico ya unidos (para mostrarla completa).
     */
    public function buscarPorId(int $id): array|false
    {
        $sql = "SELECT c.*, cl.nombre AS cliente_nombre, cl.apellido_pat AS cliente_apellido,
                       a.marca, a.modelo,
                       e.nombre AS mecanico_nombre, e.apellido_pat AS mecanico_apellido
                FROM cita c
                INNER JOIN cliente cl ON cl.id_cliente = c.id_cliente
                INNER JOIN auto a ON a.id_auto = c.id_auto
                LEFT JOIN empleado e ON e.id_empleado = c.id_mecanico
                WHERE c.id_cita = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }
}
