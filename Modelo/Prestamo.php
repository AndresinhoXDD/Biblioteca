<?php
require_once 'config/database.php';

class Prestamo {
    private $conexion;
    private $tabla = "prestamo";
    
    // Propiedades
    public $prestamo_id;
    public $prestamo_prestatario_id;
    public $prestamo_bibliotecario_id;
    public $prestamo_fecha_prestamo;
    public $prestamo_fecha_devolucion_prevista;
    public $prestamo_fecha_devolucion_real;
    public $ejemplares = [];
    
    public function __construct() {
        $database = new Database();
        $this->conexion = $database->getConexion();
    }
    
    // Crear nuevo préstamo
    public function crear() {
        try {
            $this->conexion->beginTransaction();
            
            // Insertar préstamo
            $consulta = "INSERT INTO " . $this->tabla . " 
                        (prestamo_prestatario_id, prestamo_bibliotecario_id, 
                         prestamo_fecha_prestamo, prestamo_fecha_devolucion_prevista) 
                        VALUES (?, ?, ?, ?)";
            
            $stmt = $this->conexion->prepare($consulta);
            $stmt->bindParam(1, $this->prestamo_prestatario_id);
            $stmt->bindParam(2, $this->prestamo_bibliotecario_id);
            $stmt->bindParam(3, $this->prestamo_fecha_prestamo);
            $stmt->bindParam(4, $this->prestamo_fecha_devolucion_prevista);
            $stmt->execute();
            
            $this->prestamo_id = $this->conexion->lastInsertId();
            
            // Insertar ejemplares del préstamo
            foreach($this->ejemplares as $ejemplarId) {
                $consulta = "INSERT INTO prestamo_ejemplar (prestamo_id, ejemplar_id) 
                            VALUES (?, ?)";
                $stmt = $this->conexion->prepare($consulta);
                $stmt->bindParam(1, $this->prestamo_id);
                $stmt->bindParam(2, $ejemplarId);
                $stmt->execute();
                
                // Actualizar estado del ejemplar a 'Prestado'
                $consulta = "UPDATE ejemplar SET ejemplar_estado = 'Prestado' WHERE ejemplar_id = ?";
                $stmt = $this->conexion->prepare($consulta);
                $stmt->bindParam(1, $ejemplarId);
                $stmt->execute();
                
                // Actualizar contador de copias disponibles
                $this->actualizarContadorCopias($ejemplarId);
            }
            
            // Registrar en bitácora
            $this->registrarBitacora("Préstamo creado");
            
            $this->conexion->commit();
            return true;
            
        } catch(PDOException $e) {
            $this->conexion->rollBack();
            error_log("Error en Prestamo::crear(): " . $e->getMessage());
            return false;
        }
    }
    
    // Registrar devolución
    public function registrarDevolucion($prestamoId, $fechaDevolucion) {
        try {
            $this->conexion->beginTransaction();
            
            // Actualizar préstamo
            $consulta = "UPDATE " . $this->tabla . " 
                        SET prestamo_fecha_devolucion_real = ? 
                        WHERE prestamo_id = ?";
            
            $stmt = $this->conexion->prepare($consulta);
            $stmt->bindParam(1, $fechaDevolucion);
            $stmt->bindParam(2, $prestamoId);
            $stmt->execute();
            
            // Obtener ejemplares del préstamo
            $consulta = "SELECT ejemplar_id FROM prestamo_ejemplar WHERE prestamo_id = ?";
            $stmt = $this->conexion->prepare($consulta);
            $stmt->bindParam(1, $prestamoId);
            $stmt->execute();
            
            while($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $ejemplarId = $fila['ejemplar_id'];
                
                // Actualizar estado del ejemplar a 'Disponible'
                $consulta = "UPDATE ejemplar SET ejemplar_estado = 'Disponible' WHERE ejemplar_id = ?";
                $stmt = $this->conexion->prepare($consulta);
                $stmt->bindParam(1, $ejemplarId);
                $stmt->execute();
                
                // Actualizar contador de copias disponibles
                $this->actualizarContadorCopias($ejemplarId);
            }
            
            // Registrar en bitácora
            $this->prestamo_id = $prestamoId;
            $this->registrarBitacora("Devolución registrada");
            
            $this->conexion->commit();
            return true;
            
        } catch(PDOException $e) {
            $this->conexion->rollBack();
            error_log("Error en Prestamo::registrarDevolucion(): " . $e->getMessage());
            return false;
        }
    }
    
    // Obtener todos los préstamos activos
    public function obtenerPrestamosActivos() {
        $consulta = "SELECT p.*, pr.prestatario_nombre, pr.prestatario_identificacion,
                    (p.prestamo_fecha_devolucion_prevista < NOW() AND p.prestamo_fecha_devolucion_real IS NULL) as en_mora 
                    FROM " . $this->tabla . " p 
                    JOIN prestatario pr ON p.prestamo_prestatario_id = pr.prestatario_id
                    WHERE p.prestamo_fecha_devolucion_real IS NULL 
                    ORDER BY en_mora DESC, p.prestamo_fecha_devolucion_prevista ASC";
        
        $stmt = $this->conexion->prepare($consulta);
        $stmt->execute();
        
        return $stmt;
    }
    
    // Obtener préstamos en mora
    public function obtenerPrestamosEnMora() {
        $consulta = "SELECT p.*, pr.prestatario_nombre, pr.prestatario_cedula
                    FROM " . $this->tabla . " p 
                    JOIN prestatario pr ON p.prestamo_prestatario_id = pr.prestatario_id
                    WHERE p.prestamo_fecha_devolucion_prevista < NOW() 
                    AND p.prestamo_fecha_devolucion_real IS NULL 
                    ORDER BY p.prestamo_fecha_devolucion_prevista ASC";
        
        $stmt = $this->conexion->prepare($consulta);
        $stmt->execute();
        
        return $stmt;
    }
    
    // Contar préstamos en mora
    public function contarPrestamosEnMora() {
        $consulta = "SELECT COUNT(*) as total FROM " . $this->tabla . " 
                    WHERE prestamo_fecha_devolucion_prevista < NOW() 
                    AND prestamo_fecha_devolucion_real IS NULL";
        
        $stmt = $this->conexion->prepare($consulta);
        $stmt->execute();
        
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);
        return $fila['total'];
    }
    
    // Verificar límite de préstamos por prestatario
    public function verificarLimitePrestamos($prestatarioId) {
        $consulta = "SELECT COUNT(*) as total FROM " . $this->tabla . " 
                    WHERE prestamo_prestatario_id = ? 
                    AND prestamo_fecha_devolucion_real IS NULL";
        
        $stmt = $this->conexion->prepare($consulta);
        $stmt->bindParam(1, $prestatarioId);
        $stmt->execute();
        
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);
        return $fila['total'];
    }
    
    // Obtener ejemplares de un préstamo
    public function obtenerEjemplaresPrestamo($prestamoId) {
        $consulta = "SELECT e.ejemplar_id, l.libro_titulo, l.libro_autor, l.libro_isbn 
                    FROM prestamo_ejemplar pe 
                    JOIN ejemplar e ON pe.ejemplar_id = e.ejemplar_id 
                    JOIN libro l ON e.libro_id = l.libro_id 
                    WHERE pe.prestamo_id = ?";
        
        $stmt = $this->conexion->prepare($consulta);
        $stmt->bindParam(1, $prestamoId);
        $stmt->execute();
        
        return $stmt;
    }
    
    // Actualizar contador de copias disponibles
    private function actualizarContadorCopias($ejemplarId) {
        // Primero obtenemos el libro_id del ejemplar
        $consulta = "SELECT libro_id FROM ejemplar WHERE ejemplar_id = ?";
        $stmt = $this->conexion->prepare($consulta);
        $stmt->bindParam(1, $ejemplarId);
        $stmt->execute();
        
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);
        $libroId = $fila['libro_id'];
        
        // Luego contamos los ejemplares disponibles
        $consulta = "SELECT COUNT(*) as disponibles FROM ejemplar 
                    WHERE libro_id = ? AND ejemplar_estado = 'Disponible'";
        $stmt = $this->conexion->prepare($consulta);
        $stmt->bindParam(1, $libroId);
        $stmt->execute();
        
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);
        $disponibles = $fila['disponibles'];
        
        // Actualizamos el contador en la tabla de libros
        $consulta = "UPDATE libro SET libro_copias_disponibles = ? WHERE libro_id = ?";
        $stmt = $this->conexion->prepare($consulta);
        $stmt->bindParam(1, $disponibles);
        $stmt->bindParam(2, $libroId);
        $stmt->execute();
    }
    
    // Registrar en bitácora
    private function registrarBitacora($accion) {
        $consulta = "INSERT INTO bitacora (usuario_id, accion, prestamo_id, fecha_hora) 
                    VALUES (?, ?, ?, NOW())";
        
        $usuarioId = isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : 0;
        
        $stmt = $this->conexion->prepare($consulta);
        $stmt->bindParam(1, $usuarioId);
        $stmt->bindParam(2, $accion);
        $stmt->bindParam(3, $this->prestamo_id);
        $stmt->execute();
    }
    
    // Calcular fecha de devolución prevista
    public function calcularFechaDevolucion($fechaPrestamo, $diasPrestamo = 3) {
        $fecha = new DateTime($fechaPrestamo);
        $diasAgregados = 0;
        $diasHabiles = 0;
        
        while($diasHabiles < $diasPrestamo) {
            $fecha->modify('+1 day');
            $diaSemana = $fecha->format('N');
            
            // Si no es fin de semana (6=sábado, 7=domingo)
            if($diaSemana < 6) {
                $diasHabiles++;
            }
            
            $diasAgregados++;
        }
        
        return $fecha->format('Y-m-d H:i:s');
    }
    
    // Obtener préstamo por ID
    public function obtenerPorId($id) {
        $consulta = "SELECT p.*, pr.prestatario_nombre, pr.prestatario_cedula 
                    FROM " . $this->tabla . " p
                    JOIN prestatario pr ON p.prestamo_prestatario_id = pr.prestatario_id
                    WHERE p.prestamo_id = ?";
        
        $stmt = $this->conexion->prepare($consulta);
        $stmt->bindParam(1, $id);
        $stmt->execute();
        
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($fila) {
            $this->prestamo_id = $fila['prestamo_id'];
            $this->prestamo_prestatario_id = $fila['prestamo_prestatario_id'];
            $this->prestamo_bibliotecario_id = $fila['prestamo_bibliotecario_id'];
            $this->prestamo_fecha_prestamo = $fila['prestamo_fecha_prestamo'];
            $this->prestamo_fecha_devolucion_prevista = $fila['prestamo_fecha_devolucion_prevista'];
            $this->prestamo_fecha_devolucion_real = $fila['prestamo_fecha_devolucion_real'];
            
            return true;
        }
        
        return false;
    }
}
?>