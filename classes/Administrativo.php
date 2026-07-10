<?php
require_once __DIR__ . '/Empleado.php';

/**
 * Administrativo hereda de Empleado (incluye turno), y agrega area.
 */
class Administrativo extends Empleado
{
    public ?string $area = null;

    /**
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
            $idEmpleado = parent::guardar();

            $sql = "INSERT INTO administrativo (id_empleado, area) VALUES (:id, :area)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':id'   => $idEmpleado,
                ':area' => $this->area,
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

    public function buscarPorId(int $id): array|false
    {
        $sql = "SELECT e.*, a.area
                FROM empleado e
                INNER JOIN administrativo a ON e.id_empleado = a.id_empleado
                WHERE e.id_empleado = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function listarTodos(): array
    {
        $sql = "SELECT e.*, a.area
                FROM empleado e
                INNER JOIN administrativo a ON e.id_empleado = a.id_empleado
                WHERE e.activo = 1";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
}
