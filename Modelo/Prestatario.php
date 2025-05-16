<?php
require_once 'config/database.php';

class Prestatario {
    private $conexion;
    private $tabla = "prestatario";
    
    // Propiedades
    public $prestatario_id;
    public $prestatario_nombre;
    public $prestatario_cedula;
    
    public function __construct() {
        $database = new Database();
        $this->conexion = $database->getConexion();
    }
    
    // Buscar prestatario por cédula
    public function buscarPorCedula($cedula) {
        $consulta = "SELECT prestatario_id FROM " . $this->tabla . " WHERE prestatario_cedula = ?";
        $stmt = $this->conexion->prepare($consulta);
        $stmt->bindParam(1, $cedula);
        $stmt->execute();
        
        if($stmt->rowCount() > 0) {
            $fila = $stmt->fetch(PDO::FETCH_ASSOC);
            return $fila['prestatario_id'];
        }
        
        return false;
    }
    
    // Crear nuevo prestatario
    public function crear($nombre, $cedula) {
        try {
            $consulta = "INSERT INTO " . $this->tabla . " (prestatario_nombre, prestatario_cedula) VALUES (?, ?)";
            $stmt = $this->conexion->prepare($consulta);
            $stmt->bindParam(1, $nombre);
            $stmt->bindParam(2, $cedula);
            $stmt->execute();
            
            return $this->conexion->lastInsertId();
        } catch(PDOException $e) {
            error_log("Error en Prestatario::crear(): " . $e->getMessage());
            return false;
        }
    }
    
    // Obtener prestatario por ID
    public function obtenerPorId($id) {
        $consulta = "SELECT * FROM " . $this->tabla . " WHERE prestatario_id = ?";
        $stmt = $this->conexion->prepare($consulta);
        $stmt->bindParam(1, $id);
        $stmt->execute();
        
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($fila) {
            $this->prestatario_id = $fila['prestatario_id'];
            $this->prestatario_nombre = $fila['prestatario_nombre'];
            $this->prestatario_cedula = $fila['prestatario_cedula'];
            
            return true;
        }
        
        return false;
    }
    
    // Obtener todos los prestatarios
    public function obtenerTodos() {
        $consulta = "SELECT * FROM " . $this->tabla . " ORDER BY prestatario_nombre";
        $stmt = $this->conexion->prepare($consulta);
        $stmt->execute();
        
        return $stmt;
    }
}
?>