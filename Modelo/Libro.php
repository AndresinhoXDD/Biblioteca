<?php
require_once 'config/database.php';

class Libro {
    private $conexion;
    private $tabla = "libro";
    
    // Propiedades
    public $libro_id;
    public $titulo;
    public $autor;
    public $isbn;
    public $copias_disponibles;
    public $copias_totales;
    
    public function __construct() {
        $database = new Database();
        $this->conexion = $database->getConexion();
    }
    
    // Obtener todos los libros con paginación
    public function obtenerTodos($pagina = 1, $porPagina = 10, $orden = 'libro_titulo', $direccion = 'ASC') {
        $inicio = ($pagina - 1) * $porPagina;
        
        $consulta = "SELECT * FROM " . $this->tabla . " 
                    ORDER BY " . $orden . " " . $direccion . " 
                    LIMIT :inicio, :porPagina";
        
        $stmt = $this->conexion->prepare($consulta);
        $stmt->bindParam(':inicio', $inicio, PDO::PARAM_INT);
        $stmt->bindParam(':porPagina', $porPagina, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt;
    }
    
    // Contar total de libros (para paginación)
    public function contarTotal() {
        $consulta = "SELECT COUNT(*) as total FROM " . $this->tabla;
        $stmt = $this->conexion->prepare($consulta);
        $stmt->execute();
        
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);
        return $fila['total'];
    }
    
    // Buscar libros por título, autor o ISBN
    public function buscar($termino) {
        $consulta = "SELECT * FROM " . $this->tabla . " 
                    WHERE libro_titulo LIKE ? OR autor LIKE ? OR libro_isbn LIKE ? 
                    ORDER BY libro_titulo";
        
        $termino = "%{$termino}%";
        
        $stmt = $this->conexion->prepare($consulta);
        $stmt->bindParam(1, $termino);
        $stmt->bindParam(2, $termino);
        $stmt->bindParam(3, $termino);
        $stmt->execute();
        
        return $stmt;
    }
    
    // Obtener ejemplares disponibles para préstamo
    public function obtenerEjemplaresDisponibles($titulo = '', $autor = '', $isbn = '') {
        $consulta = "SELECT e.ejemplar_id, l.libro_titulo, l.libro_isbn 
                    FROM ejemplar e 
                    JOIN " . $this->tabla . " l ON e.ejemplar_libro_id = l.libro_id 
                    WHERE e.ejemplar_estado = 'Disponible'";
        
        $parametros = [];
        
        if(!empty($titulo)) {
            $consulta .= " AND l.libro_titulo LIKE ?";
            $parametros[] = "%{$titulo}%";
        }
        
        // if(!empty($autor)) {
        //     $consulta .= " AND l.libro_autor LIKE ?";
        //     $parametros[] = "%{$autor}%";
        // }
        
        if(!empty($isbn)) {
            $consulta .= " AND l.libro_isbn LIKE ?";
            $parametros[] = "%{$isbn}%";
        }
        
        $consulta .= " ORDER BY l.libro_titulo";
        
        $stmt = $this->conexion->prepare($consulta);
        
        for($i = 0; $i < count($parametros); $i++) {
            $stmt->bindParam($i + 1, $parametros[$i]);
        }
        
        $stmt->execute();
        
        return $stmt;
    }
    
    // Actualizar disponibilidad de ejemplar
    public function actualizarDisponibilidadEjemplar($ejemplarId, $estado) {
        $consulta = "UPDATE ejemplar SET estado = ? WHERE ejemplar_id = ?";
        $stmt = $this->conexion->prepare($consulta);
        $stmt->bindParam(1, $estado);
        $stmt->bindParam(2, $ejemplarId);
        
        if($stmt->execute()) {
            // Actualizar contador de copias disponibles
            $this->actualizarContadorCopias($ejemplarId);
            return true;
        }
        
        return false;
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
                    WHERE libro_id = ? AND estado = 'Disponible'";
        $stmt = $this->conexion->prepare($consulta);
        $stmt->bindParam(1, $libroId);
        $stmt->execute();
        
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);
        $disponibles = $fila['disponibles'];
        
        // Actualizamos el contador en la tabla de libros
        $consulta = "UPDATE " . $this->tabla . " SET copias_disponibles = ? WHERE libro_id = ?";
        $stmt = $this->conexion->prepare($consulta);
        $stmt->bindParam(1, $disponibles);
        $stmt->bindParam(2, $libroId);
        $stmt->execute();
    }
}
?>