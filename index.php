<?php
// index.php - Punto de entrada corregido
// Iniciar o reanudar sesión
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Incluir controladores (carpeta Controlador con C mayúscula)
require_once __DIR__ . '/Controlador/UsuarioControlador.php';
require_once __DIR__ . '/Controlador/LibroControlador.php';
require_once __DIR__ . '/Controlador/PrestamoControlador.php';

// Instanciación de controladores
$usuarioControlador = new UsuarioControlador();
$libroControlador   = new LibroControlador();
$prestamoControlador = new PrestamoControlador();

// Determinar acción a ejecutar
$accion = $_GET['accion'] ?? 'inicio';

switch ($accion) {
    // Ruta inicial -- redirige según estado de sesión
    case 'inicio':
        if (!empty($_SESSION['usuario_id'])) {
            header('Location: index.php?accion=dashboard_bibliotecario');
            exit;
        }
        header('Location: index.php?accion=iniciar_sesion');
        exit;

    // Login / Logout
    case 'iniciar_sesion':
        $usuarioControlador->iniciarSesion();
        break;

    case 'cerrar_sesion':
        $usuarioControlador->cerrarSesion();
        break;

    // Dashboard para bibliotecarios y administradores
    case 'dashboard_bibliotecario':
        if (empty($_SESSION['usuario_id'])) {
            header('Location: index.php?accion=iniciar_sesion');
            exit;
        }
        include __DIR__ . '/Vista/dashboard.php';
        break;

    // Gestión de libros
    case 'listar_libros':
        $libroControlador->listarLibros();
        break;

    case 'buscar_ejemplares':
        $libroControlador->buscarEjemplares();
        break;

    // Préstamos
    case 'nuevo_prestamo':
        $prestamoControlador->nuevoPrestamo();
        break;

    case 'registrar_prestamo':
        $prestamoControlador->registrarPrestamo();
        break;

    case 'listar_prestamos':
        $prestamoControlador->listarPrestamos();
        break;

    case 'registrar_devolucion':
        $prestamoControlador->registrarDevolucion();
        break;

    case 'obtener_ejemplares_prestamo':
        $prestamoControlador->obtenerEjemplaresPrestamo();
        break;

    // Dashboard para administradores
    case 'dashboard_admin':
        if (empty($_SESSION['usuario_id']) || ($_SESSION['rol'] ?? '') !== 'administrador') {
            header('Location: index.php?accion=acceso_denegado');
            exit;
        }
        include __DIR__ . '/Vista/admin/dashboard.php';
        break;

    case 'listar_usuarios':
        $usuarioControlador->listarUsuarios();
        break;

    case 'actualizar_rol':
        $usuarioControlador->actualizarRol();
        break;

    // Acceso denegado
    case 'acceso_denegado':
        include __DIR__ . '/Vista/acceso_denegado.php';
        break;

    // Acción no definida: redirigir a inicio
    default:
        header('Location: index.php');
        exit;   
}
