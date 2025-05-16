<?php include 'vista/plantilla/cabecera.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Crear Nuevo Autor</h3>
            </div>
            <div class="card-body">
                <?php if(isset($mensaje)): ?>
                    <div class="alert alert-danger"><?php echo $mensaje; ?></div>
                <?php endif; ?>
                
                <form action="index.php?accion=guardar_autor" method="POST">
                    <div class="mb-3">
                        <label for="autor_nombre" class="form-label">Nombre *</label>
                        <input type="text" class="form-control" id="autor_nombre" name="autor_nombre" required 
                               value="<?php echo isset($_POST['autor_nombre']) ? htmlspecialchars($_POST['autor_nombre']) : ''; ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="autor_nacionalidad" class="form-label">Nacionalidad</label>
                        <input type="text" class="form-control" id="autor_nacionalidad" name="autor_nacionalidad"
                               value="<?php echo isset($_POST['autor_nacionalidad']) ? htmlspecialchars($_POST['autor_nacionalidad']) : ''; ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="autor_fecha_nacimiento" class="form-label">Fecha de Nacimiento</label>
                        <input type="date" class="form-control" id="autor_fecha_nacimiento" name="autor_fecha_nacimiento"
                               value="<?php echo isset($_POST['autor_fecha_nacimiento']) ? htmlspecialchars($_POST['autor_fecha_nacimiento']) : ''; ?>">
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="index.php?accion=autores" class="btn btn-outline-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'vista/plantilla/pie.php'; ?>