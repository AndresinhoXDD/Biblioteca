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
    // 👇 FUNCION DE VALIDACIÓN DE FORMULARIO
    function validarFormulario(formId, reglas) {
        const form = document.getElementById(formId);
        if (!form) return;
        const inputs = form.querySelectorAll('input, select, textarea');

        // Al enviar
        form.addEventListener('submit', function(e) {
            let valido = true;
            inputs.forEach(input => {
                if (!validarCampo(input)) valido = false;
            });
            if (!valido) e.preventDefault();
        });

        // En tiempo real
        inputs.forEach(input => {
            ['input','blur'].forEach(evt =>
                input.addEventListener(evt, () => validarCampo(input))
            );
        });

        function validarCampo(input) {
            const nombre = input.name;
            const regla  = reglas[nombre];
            if (!regla) return true;

            const valor = input.value.trim();
            let valido = true, mensaje = '';
            const errEl = document.getElementById(`${nombre}-error`);

            if (regla.requerido && valor === '') {
                valido = false;
                mensaje = regla.mensajeRequerido;
            } else if (regla.patron && !regla.patron.test(valor) && valor !== '') {
                valido = false;
                mensaje = regla.mensajePatron;
            }

            if (errEl) {
                errEl.textContent = mensaje;
                errEl.style.display = valido ? 'none' : 'block';
            }
            input.classList.toggle('is-invalid', !valido);
            input.classList.toggle('is-valid', valido);
            return valido;
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const ejemplaresSeleccionados = [];

        // ✅ Validación del formulario
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

        // Resto del JS que ya tenías:
        calcularFechaDevolucion();

        document.getElementById('fecha_prestamo').addEventListener('change', calcularFechaDevolucion);
        document.getElementById('btn-buscar-ejemplar').addEventListener('click', buscarEjemplares);
        document.getElementById('busqueda-ejemplar').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                buscarEjemplares();
            }
        });

        document.getElementById('form-prestamo').addEventListener('submit', function(e) {
            e.preventDefault();

            if (ejemplaresSeleccionados.length === 0) {
                document.getElementById('ejemplares-error').textContent = 'Debe seleccionar al menos un ejemplar';
                document.getElementById('ejemplares-error').style.display = 'block';
                return;
            }

            const formData = new FormData();
            formData.append('nombre', document.getElementById('nombre').value);
            formData.append('cedula', document.getElementById('cedula').value);
            formData.append('fecha_prestamo', document.getElementById('fecha_prestamo').value);

            ejemplaresSeleccionados.forEach(ejemplar => {
                formData.append('ejemplares[]', ejemplar.id);
            });

            fetch('index.php?accion=registrar_prestamo', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('mensaje-exito').textContent = data.message;
                    const modal = new bootstrap.Modal(document.getElementById('modal-exito'));
                    modal.show();
                } else {
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

        function buscarEjemplares() {
            const termino = document.getElementById('busqueda-ejemplar').value.trim();
            if (termino === '') return;

            fetch(`index.php?accion=buscar_ejemplares&libro_titulo=${encodeURIComponent(termino)}`)
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

        function mostrarResultadosEjemplares(ejemplares) {
            const tabla = document.getElementById('tabla-ejemplares');
            tabla.innerHTML = '';

            if (ejemplares.length === 0) {
                tabla.innerHTML = '<tr><td colspan="4" class="text-center">No se encontraron ejemplares disponibles</td></tr>';
            } else {
                ejemplares.forEach(ejemplar => {
                    const fila = document.createElement('tr');
                    fila.innerHTML = `
                        <td>${ejemplar.libro_titulo}</td>
                        <td>${ejemplar.autores}</td>
                        <td>${ejemplar.libro_isbn}</td>
                        <td>
                            <button type="button" class="btn btn-sm btn-primary btn-agregar-ejemplar"
                                data-id="${ejemplar.ejemplar_id}"
                                data-titulo="${ejemplar.libro_titulo}"
                                data-autor="${ejemplar.autores}"
                                data-isbn="${ejemplar.libro_isbn}">
                                <i class="fas fa-plus"></i>
                            </button>
                        </td>
                    `;
                    tabla.appendChild(fila);
                });

                document.querySelectorAll('.btn-agregar-ejemplar').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const id = this.getAttribute('data-id');
                        const titulo = this.getAttribute('data-titulo');
                        const autor = this.getAttribute('data-autor');
                        const isbn = this.getAttribute('data-isbn');

                        if (ejemplaresSeleccionados.some(e => e.id === id)) {
                            alert('Este ejemplar ya está seleccionado');
                            return;
                        }

                        if (ejemplaresSeleccionados.length >= 3) {
                            alert('No puede seleccionar más de 3 ejemplares');
                            return;
                        }

                        ejemplaresSeleccionados.push({ id, titulo, autor, isbn });
                        actualizarEjemplaresSeleccionados();
                        document.getElementById('ejemplares-error').style.display = 'none';
                    });
                });
            }

            document.getElementById('resultados-ejemplares').style.display = 'block';
        }

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
                            <strong>${ejemplar.titulo}</strong> - ${ejemplar.autor}<br>
                            <small class="text-muted">ISBN: ${ejemplar.isbn}</small>
                        </div>
                        <button type="button" class="btn btn-sm btn-danger btn-quitar-ejemplar" data-index="${index}">
                            <i class="fas fa-times"></i>
                        </button>
                    `;
                    lista.appendChild(item);
                });

                document.querySelectorAll('.btn-quitar-ejemplar').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const index = parseInt(this.getAttribute('data-index'));
                        ejemplaresSeleccionados.splice(index, 1);
                        actualizarEjemplaresSeleccionados();
                    });
                });
            }
        }

        function calcularFechaDevolucion() {
            const fechaPrestamo = document.getElementById('fecha_prestamo').value;
            if (fechaPrestamo) {
                const fecha = new Date(fechaPrestamo);
                let diasHabiles = 0;

                while (diasHabiles < 3) {
                    fecha.setDate(fecha.getDate() + 1);
                    const diaSemana = fecha.getDay();
                    if (diaSemana !== 0 && diaSemana !== 6) {
                        diasHabiles++;
                    }
                }

                const year = fecha.getFullYear();
                const month = String(fecha.getMonth() + 1).padStart(2, '0');
                const day = String(fecha.getDate()).padStart(2, '0');

                document.getElementById('fecha_devolucion').value = `${year}-${month}-${day}`;
            }
        }
    });
</script>

<?php include 'vista/plantilla/pie.php'; ?>