<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Biblioteca</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --color-primary: #3498db;
            --color-secondary: #2c3e50;
            --color-success: #2ecc71;
            --color-danger: #e74c3c;
            --color-warning: #f39c12;
            --color-info: #3498db;
        }
        
        body {
            font-family: 'Roboto', sans-serif;
        }
        
        .navbar {
            background-color: var(--color-secondary);
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
        }
        
        .content {
            margin-top: 70px;
            padding: 20px;
        }
        
        .footer {
            background-color: var(--color-secondary);
            color: white;
            text-align: center;
            padding: 10px 0;
            margin-top: 30px;
        }
        
        .btn-primary {
            background-color: var(--color-primary);
            border-color: var(--color-primary);
        }
        
        .btn-primary:hover {
            background-color: #2980b9;
            border-color: #2980b9;
        }
        
        .alert-validation {
            display: none;
            color: var(--color-danger);
            font-size: 0.8rem;
            margin-top: 5px;
        }
        
        .form-control.is-invalid {
            border-color: var(--color-danger);
        }
        
        .form-control.is-valid {
            border-color: var(--color-success);
        }
        
        .mora {
            background-color: rgba(231, 76, 60, 0.1);
        }
        
        .table-hover tbody tr:hover {
            background-color: rgba(52, 152, 219, 0.1);
        }
        
        .password-container {
            position: relative;
        }
        
        .password-toggle {
            position: absolute;
            right: 10px;
            top: 10px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <?php if(isset($_SESSION['usuario_id'])): ?>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php?accion=dashboard_bibliotecario">
                <i class="fas fa-book-reader me-2"></i>Biblioteca
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?accion=listar_libros">
                            <i class="fas fa-book me-1"></i>Catálogo
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?accion=listar_prestamos">
                            <i class="fas fa-exchange-alt me-1"></i>Préstamos
                            <?php if(isset($totalMora) && $totalMora > 0): ?>
                            <span class="badge bg-danger"><?php echo $totalMora; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <?php if(isset($_SESSION['rol']) && $_SESSION['rol'] == 'admin'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?accion=listar_usuarios">
                            <i class="fas fa-users me-1"></i>Usuarios
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
                <div class="navbar-nav">
                    <div class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-1"></i><?php echo $_SESSION['nombre']; ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="index.php?accion=cerrar_sesion">Cerrar Sesión</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </nav>
    <?php endif; ?>
    
    <div class="content">
        <div class="container">