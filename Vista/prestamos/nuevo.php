<?php include 'vista/plantilla/cabecera.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-plus-circle me-2"></i>Nuevo Préstamo</h2>
    <a href="index.php?accion=listar_prestamos" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i>Volver a Préstamos
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form id="form-prestamo">
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="nombre" class="form-label">Nombre del Usuario *</label>
                    <input type="text" class="form-control" id="nombre" name="nombre" required>
                    <div id="nombre-error" class="alert-validation"></div>
                </div>
                <div class="col-md-6">
                    <label for="cedula" class="form-label">Cédula/Identificación *</label>
                    <input type="text" class="form-control" id="cedula" name="cedula" required>
                    <div id="cedula-error" class="alert-validation"></div>
                </div>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Ejemplares *</label>
                <div class="input-group mb-2">
                    <input type="text" class="form-control" id="busqueda-ejemplar" placeholder="Buscar por título, autor o ISBN...">
                    <button type="button" class="btn btn-primary" id="btn-buscar-ejemplar">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
                <div id="ejemplares-error" class="alert-validation"></div>
                
                <div id="resultados-ejemplares" class="mt-2" style="display: none;">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead>
                                <tr>
                                    <th>Título</th>
                                    <th>Autor</th>
                                    <th>ISBN</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody id="tabla-ejemplares">
                                <!-- Aquí se cargarán los resultados -->
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div id="ejemplares-seleccionados" class="mt-3">
                    <h6>Ejemplares Seleccionados:</h6>
                    <ul class="list-group" id="lista-ejemplares-seleccionados">
                        <li class="list-group-item text-muted">No hay ejemplares seleccionados</li>
                    </ul>
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="fecha_prestamo" class="form-label">Fecha de Préstamo *</label>
                    <input type="date" class="form-control" id="fecha_prestamo" name="fecha_prestamo" 
                           value="<?php echo date('Y-m-d'); ?>" required>
                    <div id="fecha_prestamo-error" class="alert-validation"></div>
                </div>
                <div class="col-md-6">
                    <label for="fecha_devolucion" class="form-label">Fecha de Devolución Prevista</label>
                    <input type="date" class="form-control" id="fecha_devolucion" readonly>
                    <small class="text-muted">Se calcula automáticamente (3 días hábiles)</small>
                </div>
            </div>
            
            <div class="d-flex justify-content-between">
                <button type="button" class="btn btn-outline-secondary" onclick="window.location='index.php?accion=listar_prestamos'">
                    Cancelar
                </button>
                <button type="submit" class="btn btn-primary" id="btn-registrar">
                    <i class="fas fa-save me-1"></i>Registrar Préstamo
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal de Éxito -->
<div class="modal fade" id="modal-exito" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Préstamo Registrado</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="mensaje-exito">
                Préstamo registrado correctamente.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" onclick="window.location='index.php?accion=listar_prestamos'">
                    Ir a Préstamos
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ejemplaresSeleccionados = [];
        
        // Validación del formulario
        validarFormulario('form-prestamo', {
            nombre: {
                requerido: true,
                mensajeRequerido: 'El nombre es obligatorio',
                patron: /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/,
                mensajePatron: 'El nombre solo debe contener letras'
            },
            cedula: {
                requerido: true,
                mensajeRequerido: 'La cédula es obligatoria'
            },
            fecha_prestamo: {
                requerido: true,
                mensajeRequerido: 'La fecha de préstamo es obligatoria'
            }
        });
        
        // Calcular fecha de devolución al cargar la página
        calcularFechaDevolucion();
        
        // Calcular fecha de devolución cuando cambie la fecha de préstamo
        document.getElementById('fecha_prestamo').addEventListener('change', calcularFechaDevolucion);
        
        // Buscar ejemplares
        document.getElementById('btn-buscar-ejemplar').addEventListener('click', buscarEjemplares);
        document.getElementById('busqueda-ejemplar').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                buscarEjemplares();
            }
        });
        
        // Enviar formulario
        document.getElementById('form-prestamo').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Validar que haya ejemplares seleccionados
            if (ejemplaresSeleccionados.length === 0) {
                document.getElementById('ejemplares-error').textContent = 'Debe seleccionar al menos un ejemplar';
                document.getElementById('ejemplares-error').style.display = 'block';
                return;
            }
            
            // Preparar datos
            const formData = new FormData();
            formData.append('nombre', document.getElementById('nombre').value);
            formData.append('cedula', document.getElementById('cedula').value);
            formData.append('fecha_prestamo', document.getElementById('fecha_prestamo').value);
            
            // Agregar ejemplares
            ejemplaresSeleccionados.forEach(ejemplar => {
                formData.append('ejemplares[]', ejemplar.id);
            });
            
            // Enviar solicitud
            fetch('index.php?accion=registrar_prestamo', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Mostrar mensaje de éxito
                    document.getElementById('mensaje-exito').textContent = data.message;
                    const modal = new bootstrap.Modal(document.getElementById('modal-exito'));
                    modal.show();
                } else {
                    // Mostrar errores
                    if (data.errors) {
                        Object.keys(data.errors).forEach(key => {
                            const errorElement = document.getElementById(`${key}-error`);
                            if (errorElement) {
                                errorElement.textContent = data.errors[key];
                                errorElement.style.display = 'block';
                            }
                        });
                    } else if (data.message) {
                        alert(data.message);
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al procesar la solicitud. Intente de nuevo más tarde.');
            });
        });
        
        // Función para buscar ejemplares
        function buscarEjemplares() {
            const termino = document.getElementById('busqueda-ejemplar').value.trim();
            
            if (termino === '') {
                return;
            }
            
            fetch(`index.php?accion=buscar_ejemplares&titulo=${encodeURIComponent(termino)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        mostrarResultadosEjemplares(data.data);
                    } else {
                        alert(data.message || 'Error al buscar ejemplares');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al procesar la solicitud. Intente de nuevo más tarde.');
                });
        }
        
        // Función para mostrar resultados de ejemplares
        function mostrarResultadosEjemplares(ejemplares) {
            const tabla = document.getElementById('tabla-ejemplares');
            tabla.innerHTML = '';
            
            if (ejemplares.length === 0) {
                tabla.innerHTML = '<tr><td colspan="4" class="text-center">No se encontraron ejemplares disponibles</td></tr>';
            } else {
                ejemplares.forEach(ejemplar => {
                    const fila = document.createElement('tr');
                    
                    fila.innerHTML = `
                        <td>${ejemplar.titulo}</td>
                        <td>${ejemplar.autor}</td>
                        <td>${ejemplar.isbn}</td>
                        <td>
                            <button type="button" class="btn btn-sm btn-primary btn-agregar-ejemplar" 
                                    data-id="${ejemplar.ejemplar_id}" 
                                    data-titulo="${ejemplar.titulo}" 
                                    data-autor="${ejemplar.autor}" 
                                    data-isbn="${ejemplar.isbn}">
                                <i class="fas fa-plus"></i>
                            </button>
                        </td>
                    `;
                    
                    tabla.appendChild(fila);
                });
                
                // Agregar eventos a los botones
                document.querySelectorAll('.btn-agregar-ejemplar').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const id = this.getAttribute('data-id');
                        const titulo = this.getAttribute('data-titulo');
                        const autor = this.getAttribute('data-autor');
                        const isbn = this.getAttribute('data-isbn');
                        
                        // Verificar si ya está seleccionado
                        if (ejemplaresSeleccionados.some(e => e.id === id)) {
                            alert('Este ejemplar ya está seleccionado');
                            return;
                        }
                        
                        // Verificar límite de 3 ejemplares
                        if (ejemplaresSeleccionados.length >= 3) {
                            alert('No puede seleccionar más de 3 ejemplares');
                            return;
                        }
                        
                        // Agregar a seleccionados
                        ejemplaresSeleccionados.push({
                            id: id,
                            titulo: titulo,
                            autor: autor,
                            isbn: isbn
                        });
                        
                        actualizarEjemplaresSeleccionados();
                        
                        // Ocultar error si existe
                        document.getElementById('ejemplares-error').style.display = 'none';
                    });
                });
            }
            
            document.getElementById('resultados-ejemplares').style.display = 'block';
        }
        
        // Función para actualizar la lista de ejemplares seleccionados
        function actualizarEjemplaresSeleccionados() {
            const lista = document.getElementById('lista-ejemplares-seleccionados');
            lista.innerHTML = '';
            
            if (ejemplaresSeleccionados.length === 0) {
                lista.innerHTML = '<li class="list-group-item text-muted">No hay ejemplares seleccionados</li>';
            } else {
                ejemplaresSeleccionados.forEach((ejemplar, index) => {
                    const item = document.createElement('li');
                    item.className = 'list-group-item d-flex justify-content-between align-items-center';
                    
                    item.innerHTML = `
                        <div>
                            <strong>${ejemplar.titulo}</strong> - ${ejemplar.autor}
                            <br>
                            <small class="text-muted">ISBN: ${ejemplar.isbn}</small>
                        </div>
                        <button type="button" class="btn btn-sm btn-danger btn-quitar-ejemplar" data-index="${index}">
                            <i class="fas fa-times"></i>
                        </button>
                    `;
                    
                    lista.appendChild(item);
                });
                
                // Agregar eventos a los botones de quitar
                document.querySelectorAll('.btn-quitar-ejemplar').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const index = parseInt(this.getAttribute('data-index'));
                        ejemplaresSeleccionados.splice(index, 1);
                        actualizarEjemplaresSeleccionados();
                    });
                });
            }
        }
        
        // Función para calcular fecha de devolución
        function calcularFechaDevolucion() {
            const fechaPrestamo = document.getElementById('fecha_prestamo').value;
            
            if (fechaPrestamo) {
                // Calcular 3 días hábiles
                const fecha = new Date(fechaPrestamo);
                let diasAgregados = 0;
                let diasHabiles = 0;
                
                while (diasHabiles < 3) {
                    fecha.setDate(fecha.getDate() + 1);
                    const diaSemana = fecha.getDay();
                    
                    // Si no es fin de semana (0=domingo, 6=sábado)
                    if (diaSemana !== 0 && diaSemana !== 6) {
                        diasHabiles++;
                    }
                    
                    diasAgregados++;
                }
                
                // Formatear fecha
                const year = fecha.getFullYear();
                const month = String(fecha.getMonth() + 1).padStart(2, '0');
                const day = String(fecha.getDate()).padStart(2, '0');
                
                document.getElementById('fecha_devolucion').value = `${year}-${month}-${day}`;
            }
        }
    });
</script>

<?php include 'vista/plantilla/pie.php'; ?>