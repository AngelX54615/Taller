<?php
require_once __DIR__ . '/Empleado.php';

/**
 * Mecanico hereda todos los atributos y métodos de Empleado,
 * y agrega su propio atributo: especialidad.
 */
class Mecanico extends Empleado
{
    public ?string $especialidad = null;

    /**
     * Guarda el empleado (tabla padre) y luego su especialidad (tabla hija).
     * Ambos inserts van juntos porque comparten el mismo id_empleado.
     *
     * Si el llamador ya abrió una transacción en la misma conexión (por ejemplo,
     * para crear el empleado y su cuenta de acceso como una sola operación),
     * este método no abre ni cierra otra: solo participa en la del llamador.
     */
    public function guardar(): int
    {
        $transaccionPropia = !$this->db->inTransaction();
        if ($transaccionPropia) {
            $this->db->beginTransaction();
        }

        try {
            $idEmpleado = parent::guardar(); // inserta en "empleado"

            $sql = "INSERT INTO mecanico (id_empleado, especialidad) VALUES (:id, :especialidad)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':id'           => $idEmpleado,
                ':especialidad' => $this->especialidad,
            ]);

            if ($transaccionPropia) {
                $this->db->commit();
            }
            return $idEmpleado;
        } catch (Exception $e) {
            if ($transaccionPropia) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Trae un mecánico completo (datos de empleado + especialidad).
     */
    public function buscarPorId(int $id): array|false
    {
        $sql = "SELECT e.*, m.especialidad
                FROM empleado e
                INNER JOIN mecanico m ON e.id_empleado = m.id_empleado
                WHERE e.id_empleado = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Lista todos los mecánicos activos.
     */
    public function listarTodos(): array
    {
        $sql = "SELECT e.*, m.especialidad
                FROM empleado e
                INNER JOIN mecanico m ON e.id_empleado = m.id_empleado
                WHERE e.activo = 1";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function actualizarEspecialidad(int $id, string $especialidad): bool
    {
        $sql = "UPDATE mecanico SET especialidad = :especialidad WHERE id_empleado = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':especialidad' => $especialidad ?: null, ':id' => $id]);
    }
}
