<?php
require_once __DIR__ . '/../config/database.php';

class Libro {
    private $conexion;
    private $tabla = 'libro';

    // Propiedades que coinciden con columnas
    public $libro_id;
    public $libro_titulo;
    public $libro_isbn;
    public $libro_copias_totales;
    public $libro_copias_disponibles;

    public function __construct() {
        $db = new Database();
        $this->conexion = $db->getConexion();
    }

    // Obtener todos los libros con paginación
    public function obtenerTodos($pagina=1, $porPagina=10, $orden='libro_titulo', $direccion='ASC') {
        $inicio = ($pagina - 1) * $porPagina;
        $sql = "
            SELECT libro_id, libro_titulo, libro_isbn,
                   libro_copias_totales, libro_copias_disponibles
            FROM {$this->tabla}
            ORDER BY {$orden} {$direccion}
            LIMIT :inicio, :porPagina
        ";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':inicio',    $inicio,    PDO::PARAM_INT);
        $stmt->bindParam(':porPagina', $porPagina, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt;
    }

    // Contar total de libros
    public function contarTotal() {
        $sql  = "SELECT COUNT(*) AS total FROM {$this->tabla}";
        $stmt = $this->conexion->query($sql);
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) $fila['total'];
    }

    // Buscar libros por título, ISBN o autor (JOIN con autor si lo deseas)
    public function buscar($termino) {
        $sql = "
            SELECT libro_id, libro_titulo, libro_isbn,
                   libro_copias_totales, libro_copias_disponibles
            FROM {$this->tabla}
            WHERE libro_titulo LIKE ?
               OR libro_isbn LIKE ?
            ORDER BY libro_titulo
        ";
        $like = "%{$termino}%";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([$like, $like]);
        return $stmt;

        try {
            if (!empty($_GET['busqueda'])) {
                $resultado   = $this->libro->buscar(trim($_GET['busqueda']));
                $totalLibros = $resultado->rowCount();
            } else {
                // ...
            }
        } catch (PDOException $e) {
            die("Error al buscar libros: " . $e->getMessage());
        }
        
    }

    // Obtener ejemplares disponibles para préstamo
    public function obtenerEjemplaresDisponibles($titulo = '', $isbn = '') {
        $sql = "
            SELECT 
                e.ejemplar_id,
                l.libro_titulo,
                GROUP_CONCAT(a.autor_nombre SEPARATOR ', ') AS autores,
                l.libro_isbn
            FROM ejemplar e
            JOIN {$this->tabla} l
              ON e.ejemplar_libro_id = l.libro_id
            LEFT JOIN libroautor_libroautor la
              ON la.libroautor_libro_id = l.libro_id
            LEFT JOIN autor a
              ON a.autor_id = la.autor_id
            WHERE e.ejemplar_estado = 'disponible'
        ";
        $params = [];
    
        if ($titulo !== '') {
            $sql .= " AND l.libro_titulo LIKE ?";
            $params[] = "%{$titulo}%";
        }
        if ($isbn !== '') {
            $sql .= " AND l.libro_isbn LIKE ?";
            $params[] = "%{$isbn}%";
        }
    
        $sql .= "
            GROUP BY e.ejemplar_id, l.libro_titulo, l.libro_isbn
            ORDER BY l.libro_titulo
        ";
    
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
    

    // (Si tienes otros métodos como actualizarDisponibilidadEjemplar, revisa que usen `ejemplar_estado`)
}
