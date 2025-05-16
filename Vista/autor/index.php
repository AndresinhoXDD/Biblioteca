<?php include 'vista/plantilla/cabecera.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Gestión de Autores</h2>
    <a href="index.php?accion=crear_autor" class="btn btn-primary">Nuevo Autor</a>
</div>

<div class="row mb-4">
    <div class="col-md-6">
        <form action="index.php" method="GET" class="d-flex">
            <input type="hidden" name="accion" value="buscar_autores">
            <input type="text" name="termino" class="form-control me-2" placeholder="Buscar autores...">
            <button type="submit" class="btn btn-outline-primary">Buscar</button>
        </form>
    </div>
    <div class="col-md-6 text-end">
        <a href="index.php" class="btn btn-outline-secondary">Volver al Inicio</a>
    </div>
</div>

<?php if(isset($mensaje)): ?>
    <div class="alert alert-danger"><?php echo $mensaje; ?></div>
<?php endif; ?>

<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Nacionalidad</th>
                <th>Fecha de Nacimiento</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            if($resultado->rowCount() > 0) {
                while($fila = $resultado->fetch(PDO::FETCH_ASSOC)) {
                    extract($fila);
                    echo "<tr>";
                    echo "<td>{$autor_id}</td>";
                    echo "<td>{$autor_nombre}</td>";
                    echo "<td>" . ($autor_nacionalidad ?? 'No disponible') . "</td>";
                    echo "<td>" . ($autor_fecha_nacimiento ? date('d/m/Y', strtotime($autor_fecha_nacimiento)) : 'No disponible') . "</td>";
                    echo "<td class='d-flex gap-2'>";
                    echo "<a href='index.php?accion=editar_autor&id={$autor_id}' class='btn btn-sm btn-outline-primary'>Editar</a>";
                    echo "<a href='index.php?accion=eliminar_autor&id={$autor_id}' class='btn btn-sm btn-outline-danger' onclick='return confirm(\"¿Estás seguro de eliminar este autor?\")'>Eliminar</a>";
                    echo "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='5' class='text-center'>No hay autores registrados</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<?php include 'vista/plantilla/pie.php'; ?>