<?php include 'vista/plantilla/cabecera.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-users me-2"></i>Gestión de Usuarios</h2>
</div>

<div class="card mb-4">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <form action="index.php" method="GET" class="d-flex">
                    <input type="hidden" name="accion" value="listar_usuarios">
                    <input type="text" name="busqueda" class="form-control me-2" placeholder="Buscar por nombre o correo..." 
                           value="<?php echo isset($_GET['busqueda']) ? htmlspecialchars($_GET['busqueda']) : ''; ?>">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
            </div>
            <div class="col-md-6">
                <div class="d-flex justify-content-md-end">
                    <div class="btn-group">
                        <a href="index.php?accion=listar_usuarios" class="btn btn-outline-secondary <?php echo !isset($_GET['filtro_rol']) ? 'active' : ''; ?>">
                            Todos
                        </a>
                        <a href="index.php?accion=listar_usuarios&filtro_rol=admin" class="btn btn-outline-secondary <?php echo isset($_GET['filtro_rol']) && $_GET['filtro_rol'] == 'admin' ? 'active' : ''; ?>">
                            Administradores
                        </a>
                        <a href="index.php?accion=listar_usuarios&filtro_rol=bibliotecario" class="btn btn-outline-secondary <?php echo isset($_GET['filtro_rol']) && $_GET['filtro_rol'] == 'bibliotecario' ? 'active' : ''; ?>">
                            Bibliotecarios
                        </a>
                        <a href="index.php?accion=listar_usuarios&filtro_rol=usuario" class="btn btn-outline-secondary <?php echo isset($_GET['filtro_rol']) && $_GET['filtro_rol'] == 'usuario' ? 'active' : ''; ?>">
                            Usuarios Estándar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Correo Electrónico</th>
                <th>Rol Actual</th>
                <th>Fecha de Registro</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            if($resultado->rowCount() > 0) {
                while($fila = $resultado->fetch(PDO::FETCH_ASSOC)) {
                    extract($fila);
                    echo "<tr>";
                    echo "<td>{$nombre}</td>";
                    echo "<td>{$correo}</td>";
                    echo "<td>";
                    
                    switch($rol) {
                        case 'admin':
                            echo "<span class='badge bg-danger'>Administrador</span>";
                            break;
                        case 'bibliotecario':
                            echo "<span class='badge bg-primary'>Bibliotecario</span>";
                            break;
                        default:
                            echo "<span class='badge bg-secondary'>Usuario Estándar</span>";
                    }
                    
                    echo "</td>";
                    echo "<td>" . date('d/m/Y', strtotime($fecha_registro)) . "</td>";
                    echo "<td>";
                    
                    // No permitir cambiar el rol propio
                    if($usuario_id != $_SESSION['usuario_id']) {
                        echo "<div class='d-flex'>";
                        echo "<select class='form-select form-select-sm me-2 selector-rol' data-id='{$usuario_id}' style='width: auto;'>";
                        echo "<option value='usuario' " . ($rol == 'usuario' ? 'selected' : '') . ">Usuario Estándar</option>";
                        echo "<option value='bibliotecario' " . ($rol == 'bibliotecario' ? 'selected' : '') . ">Bibliotecario</option>";
                        echo "<option value='admin' " . ($rol == 'admin' ? 'selected' : '') . ">Administrador</option>";
                        echo "</select>";
                        echo "<button type='button' class='btn btn-sm btn-primary btn-cambiar-rol' data-id='{$usuario_id}'>Confirmar</button>";
                        echo "</div>";
                    } else {
                        echo "<em class='text-muted'>No modificable</em>";
                    }
                    
                    echo "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='5' class='text-center'>No se encontraron usuarios</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<!-- Modal de Confirmación -->
<div class="modal fade" id="modal-confirmar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Cambio de Rol</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro de que desea cambiar el rol de este usuario?</p>
                <p>Nuevo rol: <strong id="nuevo-rol-texto"></strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btn-confirmar-cambio">Confirmar</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Modal de confirmación
        const modalConfirmar = new bootstrap.Modal(document.getElementById('modal-confirmar'));
        let usuarioIdActual = null;
        let nuevoRolActual = null;
        
        // Cambiar rol
        document.querySelectorAll('.btn-cambiar-rol').forEach(btn => {
            btn.addEventListener('click', function() {
                const usuarioId = this.getAttribute('data-id');
                const selector = document.querySelector(`.selector-rol[data-id="${usuarioId}"]`);
                const nuevoRol = selector.value;
                
                // Guardar valores actuales
                usuarioIdActual = usuarioId;
                nuevoRolActual = nuevoRol;
                
                // Mostrar texto del rol
                let rolTexto = '';
                switch(nuevoRol) {
                    case 'admin':
                        rolTexto = 'Administrador';
                        break;
                    case 'bibliotecario':
                        rolTexto = 'Bibliotecario';
                        break;
                    default:
                        rolTexto = 'Usuario Estándar';
                }
                
                document.getElementById('nuevo-rol-texto').textContent = rolTexto;
                
                // Mostrar modal
                modalConfirmar.show();
            });
        });
        
        // Confirmar cambio de rol
        document.getElementById('btn-confirmar-cambio').addEventListener('click', function() {
            if (!usuarioIdActual || !nuevoRolActual) {
                return;
            }
            
            const formData = new FormData();
            formData.append('usuario_id', usuarioIdActual);
            formData.append('nuevo_rol', nuevoRolActual);
            
            fetch('index.php?accion=actualizar_rol', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                modalConfirmar.hide();
                
                if (data.success) {
                    alert(data.message);
                    window.location.reload();
                } else {
                    alert(data.message || 'Error al actualizar el rol');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al procesar la solicitud. Intente de nuevo más tarde.');
            });
        });
    });
</script>

<?php include 'vista/plantilla/pie.php'; ?>