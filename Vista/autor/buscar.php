<?php include 'vista/plantilla/cabecera.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Resultados de búsqueda: "<?php echo htmlspecialchars($_GET['termino']); ?>"</h2>
    <a href="index.php?accion=autores" class="btn btn-primary">Volver a Autores</a>
</div>

<div class="row mb-4">
    <div class="col-md-6">
        <form action="index.php" method="GET" class="d-flex">
            <input type="hidden" name="accion" value="buscar_autores">
            <input type="text" name="termino" class="form-control me-2" placeholder="Buscar autores..." 
                   value="<?php echo htmlspecialchars($_GET['termino']); ?>">
            <button type="submit" class="btn btn-outline-primary">Buscar</button>
        </form>
    </div>
</div>

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
                echo "<tr><td colspan='5' class='text-center'>No se encontraron autores que coincidan con tu búsqueda</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<?php include 'vista/plantilla/pie.php'; ?>