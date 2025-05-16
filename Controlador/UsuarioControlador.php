<?php
// Controlador/UsuarioControlador.php
require_once __DIR__ . '/../Modelo/Usuario.php';

class UsuarioControlador {
    private $usuarioModel;

    public function __construct() {
        // index.php ya llamó a session_start()
        $this->usuarioModel = new Usuario();
    }

    public function iniciarSesion() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errores = [];

            // 1) Capturar y sanear el correo
            $rawCorreo = $_POST['correo'] ?? '';
            $correo    = filter_var(trim($rawCorreo), FILTER_SANITIZE_EMAIL);
            if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                $errores['correo'] = "Formato de correo electrónico inválido";
            }

            // 2) Validar contraseña
            $password = $_POST['password'] ?? '';
            if (empty($password)) {
                $errores['password'] = "La contraseña es obligatoria";
            }

            // 3) Si hay errores, volvemos al login
            if (!empty($errores)) {
                include __DIR__ . '/../Vista/login.php';
                return;
            }

            // 4) Verificar credenciales
            $check = $this->usuarioModel->verificarCredenciales($correo, $password);

            if ($check === true) {
                // 5) Asignar sesión
                $_SESSION['usuario_id'] = $this->usuarioModel->usuario_id;
                $_SESSION['nombre']     = $this->usuarioModel->nombre;
                $_SESSION['correo']     = $this->usuarioModel->correo;
                $_SESSION['rol']        = $this->usuarioModel->rol;

                // 6) Redirigir según rol
                if ($_SESSION['rol'] === 'administrador') {
                    header('Location: index.php?accion=dashboard_admin');
                } else {
                    header('Location: index.php?accion=dashboard_bibliotecario');
                }
                exit;
            } else {
                // 7) Mensaje de error genérico
                $mensaje = $check === 'user_not_found'
                    ? "El usuario no está registrado."
                    : "Contraseña incorrecta.";
                include __DIR__ . '/../Vista/login.php';
                return;
            }
        }

        // GET: mostramos formulario
        include __DIR__ . '/../Vista/login.php';
    }

    public function cerrarSesion() {
        session_destroy();
        header('Location: index.php?accion=iniciar_sesion');
        exit;
    }


    /**
     * Listar todos los usuarios (solo para administrador).
     */
    public function listarUsuarios()
    {
        // Verificar privilegios
        if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'administrador') {
            header('Location: index.php?accion=acceso_denegado');
            return;
        }

        // Aplicar filtro por rol o búsqueda si vienen en GET
        if (!empty($_GET['filtro_rol'])) {
            $resultado = $this->usuarioModel->filtrarPorRol($_GET['filtro_rol']);
        } elseif (!empty($_GET['busqueda'])) {
            $resultado = $this->usuarioModel->buscar($_GET['busqueda']);
        } else {
            $resultado = $this->usuarioModel->obtenerTodos();
        }

        include __DIR__ . '/../Vista/admin/usuarios.php';
    }

    /**
     * Actualiza el rol de un usuario (solo para administrador).
     */
    public function actualizarRol()
    {
        // Verificar privilegios
        if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'administrador') {
            echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuarioId = $_POST['usuario_id'] ?? 0;
            $nuevoRol  = $_POST['nuevo_rol']   ?? '';

            // Impedir que un admin se quite su propio rol de administrador
            if ($usuarioId == $_SESSION['usuario_id'] && $nuevoRol !== 'administrador') {
                echo json_encode([
                    'success' => false,
                    'message' => 'No puedes remover tu propio rol de administrador'
                ]);
                return;
            }

            // Intentar actualizar en el modelo
            $exito = $this->usuarioModel->actualizarRol($usuarioId, $nuevoRol);
            echo json_encode([
                'success' => $exito,
                'message' => $exito
                    ? 'Rol actualizado correctamente'
                    : 'Error al actualizar el rol'
            ]);
        }
    }
}
