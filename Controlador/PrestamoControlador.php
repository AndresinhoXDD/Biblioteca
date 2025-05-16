<?php
require_once 'Modelo/Prestamo.php';
require_once 'Modelo/Libro.php';
require_once 'Modelo/Prestatario.php';

class PrestamoControlador {
    private $prestamo;
    private $libro;
    private $prestatario;
    
    public function __construct() {
        $this->prestamo = new Prestamo();
        $this->libro = new Libro();
        $this->prestatario = new Prestatario();
    }
    
    // Mostrar formulario de nuevo préstamo
    public function nuevoPrestamo() {
        // Verificar sesión
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        if(!isset($_SESSION['usuario_id'])) {
            header('Location: index.php');
            return;
        }
        
        include 'Vista/prestamos/nuevo.php';
    }
    
    // Procesar formulario de nuevo préstamo
    public function registrarPrestamo() {
        // Verificar sesión
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        if(!isset($_SESSION['usuario_id'])) {
            echo json_encode(['success' => false, 'message' => 'Sesión no iniciada']);
            return;
        }
        
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Validar datos
            $errores = [];
            
            // Validar nombre (solo letras)
            $nombre = $_POST['nombre'] ?? '';
            if(empty($nombre) || !preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/', $nombre)) {
                $errores['nombre'] = "El nombre solo debe contener letras";
            }
            
            // Validar cédula
            $cedula = $_POST['cedula'] ?? '';
            if(empty($cedula)) {
                $errores['cedula'] = "La cédula es obligatoria";
            }
            
            // Validar ejemplares
            $ejemplares = $_POST['ejemplares'] ?? [];
            if(empty($ejemplares)) {
                $errores['ejemplares'] = "Debe seleccionar al menos un ejemplar";
            }
            
            // Validar fecha de préstamo
            $fechaPrestamo = $_POST['fecha_prestamo'] ?? date('Y-m-d H:i:s');
            if(empty($fechaPrestamo)) {
                $errores['fecha_prestamo'] = "La fecha de préstamo es obligatoria";
            }
            
            // Buscar o crear prestatario
            $prestatarioId = $this->prestatario->buscarPorCedula($cedula);
            if(!$prestatarioId) {
                $prestatarioId = $this->prestatario->crear($nombre, $cedula);
                if(!$prestatarioId) {
                    $errores['prestatario'] = "Error al registrar el prestatario";
                }
            }
            
            // Verificar límite de préstamos (máximo 3 por prestatario)
            $prestamosActuales = $this->prestamo->verificarLimitePrestamos($prestatarioId);
            if($prestamosActuales + count($ejemplares) > 3) {
                $errores['limite'] = "Ha superado el límite de 3 libros simultáneos";
            }
            
            // Si hay errores, devolver respuesta con errores
            if(!empty($errores)) {
                echo json_encode(['success' => false, 'errors' => $errores]);
                return;
            }
            
            // Calcular fecha de devolución prevista
            $fechaDevolucion = $this->prestamo->calcularFechaDevolucion($fechaPrestamo);
            
            // Crear préstamo
            $this->prestamo->prestamo_prestatario_id = $prestatarioId;
            $this->prestamo->prestamo_bibliotecario_id = $_SESSION['usuario_id'];
            $this->prestamo->prestamo_fecha_prestamo = $fechaPrestamo;
            $this->prestamo->prestamo_fecha_devolucion_prevista = $fechaDevolucion;
            $this->prestamo->ejemplares = $ejemplares;
            
            if($this->prestamo->crear()) {
                echo json_encode([
                    'success' => true, 
                    'message' => "Préstamo registrado correctamente para {$nombre}. Fecha de devolución prevista: " . date('d/m/Y', strtotime($fechaDevolucion))
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => "Error al registrar el préstamo. Intente de nuevo más tarde."]);
            }
        }
    }
    
    // Listar préstamos activos y en mora
    public function listarPrestamos() {
        // Verificar sesión
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        if(!isset($_SESSION['usuario_id'])) {
            header('Location: index.php');
            return;
        }
        
        // Filtrar por préstamos en mora si se solicita
        if(isset($_GET['en_mora']) && $_GET['en_mora'] == '1') {
            $resultado = $this->prestamo->obtenerPrestamosEnMora();
            $soloMora = true;
        } else {
            $resultado = $this->prestamo->obtenerPrestamosActivos();
            $soloMora = false;
        }
        
        // Contar préstamos en mora
        $totalMora = $this->prestamo->contarPrestamosEnMora();
        
        include 'Vista/prestamos/index.php';
    }
    
    // Registrar devolución
    public function registrarDevolucion() {
        // Verificar sesión
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        if(!isset($_SESSION['usuario_id'])) {
            echo json_encode(['success' => false, 'message' => 'Sesión no iniciada']);
            return;
        }
        
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            $prestamoId = $_POST['prestamo_id'] ?? 0;
            $fechaDevolucion = $_POST['fecha_devolucion'] ?? date('Y-m-d H:i:s');
            
            // Validar fecha de devolución
            $fechaActual = new DateTime();
            $fechaActual->modify('-1 day');
            $fechaDevolucionObj = new DateTime($fechaDevolucion);
            
            if($fechaDevolucionObj < $fechaActual) {
                echo json_encode(['success' => false, 'message' => 'La fecha de devolución no puede ser anterior a ayer']);
                return;
            }
            
            if($this->prestamo->registrarDevolucion($prestamoId, $fechaDevolucion)) {
                echo json_encode(['success' => true, 'message' => 'Devolución registrada con éxito']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al registrar la devolución']);
            }
        }
    }
    
    // Obtener ejemplares de un préstamo
    public function obtenerEjemplaresPrestamo() {
        // Verificar sesión
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        if(!isset($_SESSION['usuario_id'])) {
            echo json_encode(['success' => false, 'message' => 'Sesión no iniciada']);
            return;
        }
        
        $prestamoId = $_GET['prestamo_id'] ?? 0;
        
        $resultado = $this->prestamo->obtenerEjemplaresPrestamo($prestamoId);
        
        $ejemplares = [];
        while($fila = $resultado->fetch(PDO::FETCH_ASSOC)) {
            $ejemplares[] = $fila;
        }
        
        echo json_encode(['success' => true, 'data' => $ejemplares]);
    }
}
?>