<?php
require_once 'modelo/Libro.php';

class LibroControlador {
    private $libro;
    
    public function __construct() {
        $this->libro = new Libro();
    }
    
    // Listar libros con paginación y ordenamiento
    public function listarLibros() {
        // Verificar sesión
        session_start();
        if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
        if(!isset($_SESSION['usuario_id'])) {
            header('Location: index.php');
            return;
        }
    // Reemplaza todas las llamadas a session_start() con esto:

        // Parámetros de paginación y ordenamiento
        $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
        $porPagina = isset($_GET['por_pagina']) ? (int)$_GET['por_pagina'] : 10;
        $orden = isset($_GET['orden']) ? $_GET['orden'] : 'titulo';
        $direccion = isset($_GET['direccion']) ? $_GET['direccion'] : 'ASC';
        
        // Validar parámetros
        $columnasValidas = ['libro_titulo', 'libro_isbn', 'copias_disponibles'];
        if(!in_array($orden, $columnasValidas)) {
            $orden = 'libro_titulo';
        }
        
        $direccionesValidas = ['ASC', 'DESC'];
        if(!in_array($direccion, $direccionesValidas)) {
            $direccion = 'ASC';
        }
        
        // Obtener libros
        if(isset($_GET['busqueda']) && !empty($_GET['busqueda'])) {
            $resultado = $this->libro->buscar($_GET['busqueda']);
            $totalLibros = $resultado->rowCount();
        } else {
            $resultado = $this->libro->obtenerTodos($pagina, $porPagina, $orden, $direccion);
            $totalLibros = $this->libro->contarTotal();
        }
        
        // Calcular total de páginas
        $totalPaginas = ceil($totalLibros / $porPagina);
        
        include 'vista/libros/index.php';
    }
    
    // Buscar ejemplares disponibles para préstamo
    public function buscarEjemplares() {
        // Verificar sesión
        session_start();
        if(!isset($_SESSION['usuario_id'])) {
            echo json_encode(['success' => false, 'message' => 'Sesión no iniciada']);
            return;
        }
        
        $titulo = $_GET['libro_titulo'] ?? '';
        // $autor = $_GET['autor'] ?? '';
        $isbn = $_GET['libro_isbn'] ?? '';
        
        $resultado = $this->libro->obtenerEjemplaresDisponibles($titulo, $isbn);
        
        $ejemplares = [];
        while($fila = $resultado->fetch(PDO::FETCH_ASSOC)) {
            $ejemplares[] = $fila;
        }
        
        echo json_encode(['success' => true, 'data' => $ejemplares]);
    }
}
?>