<?php
// Vista/login.php
// No debe haber código PHP abierto al inicio que no se cierre.
// Solo abriremos bloques PHP puntuales para mostrar variables.
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Sistema de Biblioteca</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="d-flex justify-content-center align-items-center vh-100 bg-light">
    <div class="card p-4" style="width:380px;">
        <h3 class="mb-3 text-center">Biblioteca</h3>

        <?php if (!empty($mensaje)): ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($mensaje) ?>
            </div>
        <?php endif; ?>

        <form action="index.php?accion=iniciar_sesion" method="post" novalidate>
            <div class="mb-3">
                <label for="correo" class="form-label">Correo</label>
                <input
                    type="email"
                    name="correo"
                    id="correo"
                    class="form-control <?php echo isset($errores['correo']) ? 'is-invalid' : ''; ?>"
                    value="<?php echo htmlspecialchars($_POST['correo'] ?? ''); ?>"
                    required
                >
                <?php if (isset($errores['correo'])): ?>
                    <div class="invalid-feedback">
                        <?php echo htmlspecialchars($errores['correo']); ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="mb-4">
                <label for="password" class="form-label">Contraseña</label>
                <input
                    type="password"
                    name="password"
                    id="password"
                    class="form-control <?php echo isset($errores['password']) ? 'is-invalid' : ''; ?>"
                    required
                >
                <?php if (isset($errores['password'])): ?>
                    <div class="invalid-feedback">
                        <?php echo htmlspecialchars($errores['password']); ?>
                    </div>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn btn-primary w-100">
                Ingresar
            </button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
