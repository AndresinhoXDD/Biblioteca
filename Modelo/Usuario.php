<?php
// Modelo/Usuario.php
require_once __DIR__ . '/../config/database.php';

class Usuario {
    private $conexion;
    private $tabla = "usuario";
    
    // Propiedades públicas
    public $usuario_id;
    public $nombre;
    public $correo;
    public $rol;
    public $fecha_registro;
    
    public function __construct() {
        $db = new Database();
        $this->conexion = $db->getConexion();
    }
    
    /**
     * Verifica credenciales de login.
     * Devuelve true si coinciden, o un string de error.
     */
    public function verificarCredenciales($correo, $password) {
        $sql = "
            SELECT 
                u.usuario_id,
                u.usuario_nombre   AS nombre,
                u.usuario_email    AS correo,
                u.usuario_contrasena,
                r.rol_nombre       AS rol,
                u.usuario_fecha_registro
            FROM usuario u
            JOIN rol r ON u.usuario_rol_id = r.rol_id
            WHERE u.usuario_email = ?
        ";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(1, $correo);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $fila = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($password === $fila['usuario_contrasena']) {
                // Mapear resultados
                $this->usuario_id     = $fila['usuario_id'];
                $this->nombre         = $fila['nombre'];
                $this->correo         = $fila['correo'];
                $this->rol            = $fila['rol'];  // "administrador" o "bibliotecario"
                $this->fecha_registro = $fila['usuario_fecha_registro'];
                return true;
            }
            return "password_incorrect";
        }
        
        return "user_not_found";
    }
    
    /**
     * Obtiene todos los usuarios.
     */
    public function obtenerTodos() {
        $sql = "SELECT * FROM " . $this->tabla . " ORDER BY usuario_nombre";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute();
        return $stmt;
    }
    
    /**
     * Obtiene un usuario por su ID.
     */
    public function obtenerPorId($id) {
        $sql = "SELECT * FROM " . $this->tabla . " WHERE usuario_id = ?";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(1, $id);
        $stmt->execute();
        
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($fila) {
            $this->usuario_id     = $fila['usuario_id'];
            $this->nombre         = $fila['usuario_nombre'];
            $this->correo         = $fila['usuario_email'];
            $this->rol            = $fila['usuario_rol_id'];
            $this->fecha_registro = $fila['usuario_fecha_registro'];
            return true;
        }
        
        return false;
    }
    
    /**
     * Actualiza el rol de un usuario.
     */
    public function actualizarRol($id, $nuevoRol) {
        $sql = "UPDATE " . $this->tabla . " SET usuario_rol_id = ? WHERE usuario_id = ?";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(1, $nuevoRol);
        $stmt->bindParam(2, $id);
        return $stmt->execute();
    }
    
    /**
     * Busca usuarios por nombre o correo.
     */
    public function buscar($termino) {
        $sql = "SELECT * FROM " . $this->tabla . " 
                WHERE usuario_nombre LIKE ? 
                   OR usuario_email LIKE ?
                ORDER BY usuario_nombre";
        $param = "%{$termino}%";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(1, $param);
        $stmt->bindParam(2, $param);
        $stmt->execute();
        return $stmt;
    }
    
    /**
     * Filtra usuarios por rol (ID de rol).
     */
    public function filtrarPorRol($rolId) {
        $sql = "SELECT * FROM " . $this->tabla . " WHERE usuario_rol_id = ? ORDER BY usuario_nombre";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(1, $rolId);
        $stmt->execute();
        return $stmt;
    }
}
// ¡Asegúrate de que esta última llave cierra la clase Usuario!
