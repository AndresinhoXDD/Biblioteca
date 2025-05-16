<?php include 'vista/plantilla/cabecera.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-book me-2"></i>Catálogo de Libros</h2>
    <a href="index.php?accion=nuevo_prestamo" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i>Nuevo Préstamo
    </a>
</div>

<div class="card mb-4">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <form action="index.php" method="GET" class="d-flex">
                    <input type="hidden" name="accion" value="listar_libros">
                    <input type="text" name="busqueda" class="form-control me-2" placeholder="Buscar por título, autor o ISBN..." 
                           value="<?php echo isset($_GET['busqueda']) ? htmlspecialchars($_GET['busqueda']) : ''; ?>">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
            </div>
            <div class="col-md-6 text-md-end">
                <div class="d-flex justify-content-md-end">
                    <select class="form-select me-2" style="width: auto;" onchange="cambiarFilasPorPagina(this.value)">
                        <option value="10" <?php echo $porPagina == 10 ? 'selected' : ''; ?>>10 filas</option>
                        <option value="25" <?php echo $porPagina == 25 ? 'selected' : ''; ?>>25 filas</option>
                        <option value="50" <?php echo $porPagina == 50 ? 'selected' : ''; ?>>50 filas</option>
                    </select>
                    <?php if(isset($_GET['busqueda']) && !empty($_GET['busqueda'])): ?>
                        <a href="index.php?accion=listar_libros" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-1"></i>Limpiar
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th>
                    <a href="<?php echo generarUrlOrden('titulo'); ?>" class="text-decoration-none text-dark">
                        Título
                        <?php echo mostrarIconoOrden('titulo'); ?>
                    </a>
                </th>
                <th>
                    <a href="<?php echo generarUrlOrden('autor'); ?>" class="text-decoration-none text-dark">
                        Autor(es)
                        <?php echo mostrarIconoOrden('autor'); ?>
                    </a>
                </th>
                <th>
                    <a href="<?php echo generarUrlOrden('isbn'); ?>" class="text-decoration-none text-dark">
                        ISBN
                        <?php echo mostrarIconoOrden('isbn'); ?>
                    </a>
                </th>
                <th>
                    <a href="<?php echo generarUrlOrden('copias_disponibles'); ?>" class="text-decoration-none text-dark">
                        Copias Disponibles
                        <?php echo mostrarIconoOrden('copias_disponibles'); ?>
                    </a>
                </th>
            </tr>
        </thead>
        <tbody>
            <?php 
            if($resultado->rowCount() > 0) {
                while($fila = $resultado->fetch(PDO::FETCH_ASSOC)) {
                    extract($fila);
                    echo "<tr>";
                    echo "<td>{$titulo}</td>";
                    echo "<td>{$autor}</td>";
                    echo "<td>{$isbn}</td>";
                    echo "<td>{$copias_disponibles} / {$copias_totales}</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='4' class='text-center'>No se encontraron libros</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<?php if(!isset($_GET['busqueda']) || empty($_GET['busqueda'])): ?>
<nav aria-label="Paginación">
    <ul class="pagination justify-content-center">
        <li class="page-item <?php echo $pagina <= 1 ? 'disabled' : ''; ?>">
            <a class="page-link" href="<?php echo generarUrlPaginacion($pagina - 1); ?>" aria-label="Anterior">
                <span aria-hidden="true">&laquo;</span>
            </a>
        </li>
        
        <?php for($i = max(1, $pagina - 2); $i <= min($pagina + 2, $totalPaginas); $i++): ?>
            <li class="page-item <?php echo $i == $pagina ? 'active' : ''; ?>">
                <a class="page-link" href="<?php echo generarUrlPaginacion($i); ?>"><?php echo $i; ?></a>
            </li>
        <?php endfor; ?>
        
        <li class="page-item <?php echo $pagina >= $totalPaginas ? 'disabled' : ''; ?>">
            <a class="page-link" href="<?php echo generarUrlPaginacion($pagina + 1); ?>" aria-label="Siguiente">
                <span aria-hidden="true">&raquo;</span>
            </a>
        </li>
    </ul>
</nav>
<?php endif; ?>

<script>
    function cambiarFilasPorPagina(valor) {
        window.location.href = '<?php echo generarUrlBase(); ?>&por_pagina=' + valor;
    }
</script>

<?php
// Funciones auxiliares para la paginación y ordenamiento
function generarUrlBase() {
    $url = "index.php?accion=listar_libros";
    
    if(isset($_GET['busqueda']) && !empty($_GET['busqueda'])) {
        $url .= "&busqueda=" . urlencode($_GET['busqueda']);
    }
    
    return $url;
}

function generarUrlPaginacion($pagina) {
    $url = generarUrlBase();
    $url .= "&pagina=" . $pagina;
    
    if(isset($_GET['por_pagina'])) {
        $url .= "&por_pagina=" . $_GET['por_pagina'];
    }
    
    if(isset($_GET['orden'])) {
        $url .= "&orden=" . $_GET['.$_GET['por_pagina'];
    }
    
    if(isset($_GET['orden'])) {
        $url .= "&orden=" . $_GET['orden'];
        
        if(isset($_GET['direccion'])) {
            $url .= "&direccion=" . $_GET['direccion'];
        }
    }
    
    return $url;
}

function generarUrlOrden($columna) {
    $url = generarUrlBase();
    
    if(isset($_GET['pagina'])) {
        $url .= "&pagina=" . $_GET['pagina'];
    }
    
    if(isset($_GET['por_pagina'])) {
        $url .= "&por_pagina=" . $_GET['por_pagina'];
    }
    
    $direccion = 'ASC';
    if(isset($_GET['orden']) && $_GET['orden'] == $columna && isset($_GET['direccion']) && $_GET['direccion'] == 'ASC') {
        $direccion = 'DESC';
    }
    
    $url .= "&orden=" . $columna . "&direccion=" . $direccion;
    
    return $url;
}

function mostrarIconoOrden($columna) {
    if(!isset($_GET['orden']) || $_GET['orden'] != $columna) {
        return '<i class="fas fa-sort text-muted"></i>';
    }
    
    if(isset($_GET['direccion']) && $_GET['direccion'] == 'ASC') {
        return '<i class="fas fa-sort-up"></i>';
    } else {
        return '<i class="fas fa-sort-down"></i>';
    }
}
?>

<?php include 'vista/plantilla/pie.php'; ?>