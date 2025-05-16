<?php include 'Vista/plantilla/cabecera.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2>
            <i class="fas fa-exchange-alt me-2"></i>
            <?php echo isset($soloMora) && $soloMora ? 'Préstamos en Mora' : 'Gestión de Préstamos'; ?>
        </h2>
        <?php if($totalMora > 0): ?>
            <span class="badge bg-danger">
                <?php echo $totalMora; ?> préstamo<?php echo $totalMora > 1 ? 's' : ''; ?> en mora
            </span>
        <?php endif; ?>
    </div>
    <div>
        <?php if(isset($soloMora) && $soloMora): ?>
            <a href="index.php?accion=listar_prestamos" class="btn btn-outline-secondary me-2">
                <i class="fas fa-list me-1"></i>Ver Todos
            </a>
        <?php else: ?>
            <a href="index.php?accion=listar_prestamos&en_mora=1" class="btn btn-outline-danger me-2">
                <i class="fas fa-exclamation-circle me-1"></i>Ver en Mora
            </a>
        <?php endif; ?>
        <a href="index.php?accion=nuevo_prestamo" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i>Nuevo Préstamo
        </a>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th>Prestatario</th>
                <th>Cédula</th>
                <th>Fecha Préstamo</th>
                <th>Fecha Devolución Prevista</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            if($resultado->rowCount() > 0) {
                while($fila = $resultado->fetch(PDO::FETCH_ASSOC)) {
                    // Determinar si está en mora
                    $enMora = isset($fila['en_mora']) && $fila['en_mora'];
                    $claseFilaMora = $enMora ? 'mora' : '';
                    
                    echo "<tr class='{$claseFilaMora}'>";
                    echo "<td>{$fila['prestatario_nombre']}</td>";
                    echo "<td>{$fila['prestatario_identificacion']}</td>";
                    echo "<td>" . date('d/m/Y H:i', strtotime($fila['prestamo_fecha_prestamo'])) . "</td>";
                    echo "<td>" . date('d/m/Y H:i', strtotime($fila['prestamo_fecha_devolucion_prevista'])) . "</td>";
                    echo "<td>";
                    
                    if($enMora) {
                        echo "<span class='badge bg-danger'>Préstamo vencido</span>";
                    } else {
                        echo "<span class='badge bg-success'>Activo</span>";
                    }
                    
                    echo "</td>";
                    echo "<td>";
                    echo "<button type='button' class='btn btn-sm btn-info me-1 btn-ver-ejemplares' data-id='{$fila['prestamo_id']}' title='Ver Ejemplares'>";
                    echo "<i class='fas fa-book'></i>";
                    echo "</button>";
                    echo "<button type='button' class='btn btn-sm btn-success btn-devolver' data-id='{$fila['prestamo_id']}' data-nombre='{$fila['prestatario_nombre']}' title='Registrar Devolución'>";
                    echo "<i class='fas fa-undo-alt'></i>";
                    echo "</button>";
                    echo "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='6' class='text-center'>No hay préstamos activos</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<!-- Modal de Ejemplares -->
<div class="modal fade" id="modal-ejemplares" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ejemplares del Préstamo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Título</th>
                                <th>Autor</th>
                                <th>ISBN</th>
                            </tr>
                        </thead>
                        <tbody id="tabla-modal-ejemplares">
                            <!-- Aquí se cargarán los ejemplares -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Devolución -->
<div class="modal fade" id="modal-devolucion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Registrar Devolución</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Registrar devolución para el préstamo de <strong id="nombre-usuario-devolucion"></strong>.</p>
                
                <form id="form-devolucion">
                    <input type="hidden" id="prestamo-id-devolucion">
                    
                    <div class="mb-3">
                        <label for="fecha-devolucion" class="form-label">Fecha de Devolución</label>
                        <input type="datetime-local" class="form-control" id="fecha-devolucion" value="<?php echo date('Y-m-d\TH:i'); ?>">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="btn-confirmar-devolucion">Confirmar Devolución</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Modal de ejemplares
        const modalEjemplares = new bootstrap.Modal(document.getElementById('modal-ejemplares'));
        
        // Modal de devolución
        const modalDevolucion = new bootstrap.Modal(document.getElementById('modal-devolucion'));
        
        // Ver ejemplares
        document.querySelectorAll('.btn-ver-ejemplares').forEach(btn => {
            btn.addEventListener('click', function() {
                const prestamoId = this.getAttribute('data-id');
                
                fetch(`index.php?accion=obtener_ejemplares_prestamo&prestamo_id=${prestamoId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            mostrarEjemplaresPrestamo(data.data);
                            modalEjemplares.show();
                        } else {
                            alert(data.message || 'Error al obtener ejemplares');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error al procesar la solicitud. Intente de nuevo más tarde.');
                    });
            });
        });
        
        // Registrar devolución
        document.querySelectorAll('.btn-devolver').forEach(btn => {
            btn.addEventListener('click', function() {
                const prestamoId = this.getAttribute('data-id');
                const nombre = this.getAttribute('data-nombre');
                
                document.getElementById('prestamo-id-devolucion').value = prestamoId;
                document.getElementById('nombre-usuario-devolucion').textContent = nombre;
                
                modalDevolucion.show();
            });
        });
        
        // Confirmar devolución
        document.getElementById('btn-confirmar-devolucion').addEventListener('click', function() {
            const prestamoId = document.getElementById('prestamo-id-devolucion').value;
            const fechaDevolucion = document.getElementById('fecha-devolucion').value;
            
            const formData = new FormData();
            formData.append('prestamo_id', prestamoId);
            formData.append('fecha_devolucion', fechaDevolucion);
            
            fetch('index.php?accion=registrar_devolucion', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                modalDevolucion.hide();
                
                if (data.success) {
                    alert(data.message);
                    window.location.reload();
                } else {
                    alert(data.message || 'Error al registrar la devolución');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al procesar la solicitud. Intente de nuevo más tarde.');
            });
        });
        
        // Función para mostrar ejemplares del préstamo
        function mostrarEjemplaresPrestamo(ejemplares) {
            const tabla = document.getElementById('tabla-modal-ejemplares');
            tabla.innerHTML = '';
            
            if (ejemplares.length === 0) {
                tabla.innerHTML = '<tr><td colspan="3" class="text-center">No hay ejemplares asociados a este préstamo</td></tr>';
            } else {
                ejemplares.forEach(ejemplar => {
                    const fila = document.createElement('tr');
                    
                    fila.innerHTML = `
                        <td>${ejemplar.libro_titulo}</td>
                        <td>${ejemplar.libro_autor}</td>
                        <td>${ejemplar.libro_isbn}</td>
                    `;
                    
                    tabla.appendChild(fila);
                });
            }
        }
    });
</script>

<?php include 'Vista/plantilla/pie.php'; ?>