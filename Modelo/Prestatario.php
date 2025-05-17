<?php
require_once 'config/database.php';

class Prestatario {
    private $conexion;
    private $tabla = "prestatario";
    
    // Propiedades
    public $prestatario_id;
    public $prestatario_nombre;
    public $prestatario_identificacion;
    
    public function __construct() {
        $database = new Database();
        $this->conexion = $database->getConexion();
    }
    
    // Buscar prestatario por cédula/identificación
    public function buscarPorCedula($cedula) {
        $consulta = "SELECT prestatario_id 
                     FROM " . $this->tabla . " 
                     WHERE prestatario_identificacion = ?";

        $stmt = $this->conexion->prepare($consulta);
        $stmt->execute([$cedula]);
        
        if ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
            return $fila['prestatario_id'];
        }
        
        return false;
    }
    
    // Crear nuevo prestatario
    public function crear($nombre, $cedula) {
        try {
            $consulta = "INSERT INTO " . $this->tabla . " 
                         (prestatario_nombre, prestatario_identificacion) 
                         VALUES (?, ?)";
            $stmt = $this->conexion->prepare($consulta);
            $stmt->execute([$nombre, $cedula]);
            
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
        $stmt->execute([$id]);
        
        if ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $this->prestatario_id            = $fila['prestatario_id'];
            $this->prestatario_nombre        = $fila['prestatario_nombre'];
            $this->prestatario_identificacion = $fila['prestatario_identificacion'];
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
