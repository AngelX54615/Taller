<?php
require_once __DIR__ . '/../config/database.php';

class Usuario
{
    private PDO $db;

    public ?int $id_usuario = null;
    public string $correo = "";
    public int $id_empleado;

    public function __construct()
    {
        $conexion = new Database();
        $this->db = $conexion->conectar();
    }

    /**
     * Verifica correo/contraseña. Si son correctos, devuelve los datos necesarios
     * para la sesión (incluyendo el rol: 'Mecanico' o 'Administrativo', según en
     * qué tabla hija está dado de alta el empleado). Devuelve false si algo falla.
     */
    public function autenticar(string $correo, string $password): array|false
    {
        $sql = "SELECT u.id_usuario, u.password_hash, e.id_empleado, e.nombre, e.apellido_pat, e.activo
                FROM usuario u
                INNER JOIN empleado e ON e.id_empleado = u.id_empleado
                WHERE u.correo = :correo";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':correo' => $correo]);
        $usuario = $stmt->fetch();

        if (!$usuario || !$usuario['activo'] || !password_verify($password, $usuario['password_hash'])) {
            return false;
        }

        $rol = $this->rolDeEmpleado((int) $usuario['id_empleado']);
        if ($rol === null) {
            return false;
        }

        return [
            'id_usuario'   => (int) $usuario['id_usuario'],
            'id_empleado'  => (int) $usuario['id_empleado'],
            'nombre'       => $usuario['nombre'],
            'apellido_pat' => $usuario['apellido_pat'],
            'rol'          => $rol,
        ];
    }

    private function rolDeEmpleado(int $idEmpleado): ?string
    {
        $stmt = $this->db->prepare("SELECT 1 FROM mecanico WHERE id_empleado = :id");
        $stmt->execute([':id' => $idEmpleado]);
        if ($stmt->fetch()) {
            return 'Mecanico';
        }

        $stmt = $this->db->prepare("SELECT 1 FROM administrativo WHERE id_empleado = :id");
        $stmt->execute([':id' => $idEmpleado]);
        if ($stmt->fetch()) {
            return 'Administrativo';
        }

        return null;
    }
}
