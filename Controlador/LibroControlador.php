<?php
require_once __DIR__ . '/../Modelo/Libro.php';

class LibroControlador {
    private $libro;

    public function __construct() {
        $this->libro = new Libro();
    }

    // Listar libros con paginación y ordenamiento
    public function listarLibros() {
        // Ya arrancó sesión en index.php
        if (empty($_SESSION['usuario_id'])) {
            header('Location: index.php?accion=iniciar_sesion');
            exit;
        }

        $pagina     = isset($_GET['pagina'])     ? (int) $_GET['pagina']        : 1;
        $porPagina  = isset($_GET['por_pagina']) ? (int) $_GET['por_pagina']    : 10;
        $orden      = $_GET['orden']    ?? 'libro_titulo';
        $direccion  = $_GET['direccion'] ?? 'ASC';

        // Columnas válidas según tu tabla `libro`
        $columnasValidas    = ['libro_titulo','libro_isbn','libro_copias_disponibles','libro_copias_totales'];
        $direccionesValidas = ['ASC','DESC'];

        if (!in_array($orden,     $columnasValidas,    true)) $orden     = 'libro_titulo';
        if (!in_array($direccion, $direccionesValidas, true)) $direccion = 'ASC';

        try {
            if (!empty($_GET['busqueda'])) {
                $termino     = trim($_GET['busqueda']);
                $resultado   = $this->libro->buscar($termino);
                $totalLibros = $resultado->rowCount();
            } else {
                $resultado   = $this->libro->obtenerTodos($pagina, $porPagina, $orden, $direccion);
                $totalLibros = $this->libro->contarTotal();
            }
        } catch (PDOException $e) {
            // Muestra el mensaje de error para depurar
            die("Error al consultar libros: " . $e->getMessage());
        }

        $totalPaginas = (int) ceil($totalLibros / $porPagina);

        include __DIR__ . '/../Vista/libros/index.php';
    }

    // Buscar ejemplares disponibles (JSON)
    public function buscarEjemplares() {
        if (empty($_SESSION['usuario_id'])) {
            header('Content-Type: application/json');
            echo json_encode(['success'=>false,'message'=>'Sesión no iniciada']);
            exit;
        }
    
        $titulo = $_GET['libro_titulo'] ?? '';
        $isbn   = $_GET['libro_isbn']   ?? '';
    
        $stmt = $this->libro->obtenerEjemplaresDisponibles($titulo, $isbn);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
        header('Content-Type: application/json');
        echo json_encode(['success'=>true,'data'=>$data]);
        exit;
    }
    
}
