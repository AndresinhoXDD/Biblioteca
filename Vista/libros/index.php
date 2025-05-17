
<?php include __DIR__ . '/../plantilla/cabecera.php'; 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-book me-2"></i>Catálogo de Libros</h2>
    <a href="index.php?accion=nuevo_prestamo" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i>Nuevo Préstamo
    </a>
</div>

<form action="index.php" method="GET" class="row g-3 mb-4">
    <input type="hidden" name="accion" value="listar_libros">
    <div class="col-md-6">
        <input type="text" name="busqueda" class="form-control" placeholder="Buscar título o ISBN..."
               value="<?php echo htmlspecialchars($_GET['busqueda'] ?? ''); ?>">
    </div>
    <div class="col-md-2">
        <select name="por_pagina" class="form-select">
            <?php foreach ([10,25,50] as $n): ?>
                <option value="<?= $n ?>" <?= ($porPagina==$n)?'selected':'' ?>>
                    <?= $n ?> filas
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-2">
        <button class="btn btn-primary w-100"><i class="fas fa-search"></i> Buscar</button>
    </div>
    <div class="col-md-2 text-end">
        <?php if (!empty($_GET['busqueda'])): ?>
            <a href="index.php?accion=listar_libros" class="btn btn-outline-secondary">
                <i class="fas fa-times"></i> Limpiar
            </a>
        <?php endif; ?>
    </div>
</form>

<div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <?php 
                $cols = [
                  'libro_titulo'          => 'Título',
                  'libro_isbn'            => 'ISBN',
                  'libro_copias_disponibles' => 'Disponibles',
                  'libro_copias_totales'  => 'Totales'
                ];
                foreach ($cols as $col => $label): ?>
                <th>
                  <a href="<?php echo generarUrlOrden($col); ?>" class="text-dark">
                    <?= $label ?> <?= mostrarIconoOrden($col) ?>
                  </a>
                </th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php if ($resultado->rowCount()): ?>
                <?php while ($row = $resultado->fetch(PDO::FETCH_ASSOC)): ?>
                <tr>
                    <td><?= htmlspecialchars($row['libro_titulo']) ?></td>
                    <td><?= htmlspecialchars($row['libro_isbn']) ?></td>
                    <td><?= (int)$row['libro_copias_disponibles'] ?></td>
                    <td><?= (int)$row['libro_copias_totales'] ?></td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="4" class="text-center">No se encontraron libros</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if (empty($_GET['busqueda'])): ?>
<nav>
  <ul class="pagination justify-content-center">
    <li class="page-item <?= $pagina<=1?'disabled':'' ?>">
      <a class="page-link" href="<?= generarUrlPaginacion($pagina-1) ?>">&laquo;</a>
    </li>
    <?php for($i=max(1,$pagina-2); $i<=min($pagina+2,$totalPaginas); $i++): ?>
    <li class="page-item <?= $i==$pagina?'active':'' ?>">
      <a class="page-link" href="<?= generarUrlPaginacion($i) ?>"><?= $i ?></a>
    </li>
    <?php endfor; ?>
    <li class="page-item <?= $pagina>=$totalPaginas?'disabled':'' ?>">
      <a class="page-link" href="<?= generarUrlPaginacion($pagina+1) ?>">&raquo;</a>
    </li>
  </ul>
</nav>
<?php endif; ?>

<script>
function cambiarFilasPorPagina(v) {
    window.location = '<?= generarUrlBase() ?>&por_pagina=' + v;
}
</script>

<?php include __DIR__ . '/../plantilla/pie.php'; ?>

<?php
// Helpers para paginación y ordenamiento

function generarUrlBase() {
    $url = "index.php?accion=listar_libros";
    if (!empty($_GET['busqueda'])) {
        $url .= "&busqueda=" . urlencode($_GET['busqueda']);
    }
    return $url;
}

function generarUrlPaginacion($pagina) {
    $url = generarUrlBase() . "&pagina=" . $pagina;
    if (!empty($_GET['por_pagina'])) {
        $url .= "&por_pagina=" . (int)$_GET['por_pagina'];
    }
    if (!empty($_GET['orden'])) {
        $url .= "&orden=" . urlencode($_GET['orden'])
             . "&direccion=" . urlencode($_GET['direccion'] ?? 'ASC');
    }
    return $url;
}

function generarUrlOrden($columna) {
    $url = generarUrlBase();
    if (!empty($_GET['pagina'])) {
        $url .= "&pagina=" . (int)$_GET['pagina'];
    }
    if (!empty($_GET['por_pagina'])) {
        $url .= "&por_pagina=" . (int)$_GET['por_pagina'];
    }
    // Alternar dirección
    $dir = 'ASC';
    if (isset($_GET['orden'], $_GET['direccion'])
        && $_GET['orden'] === $columna
        && $_GET['direccion'] === 'ASC'
    ) {
        $dir = 'DESC';
    }
    $url .= "&orden=" . urlencode($columna)
         . "&direccion=" . $dir;
    return $url;
}

function mostrarIconoOrden($columna) {
    if (!isset($_GET['orden']) || $_GET['orden'] !== $columna) {
        return '<i class="fas fa-sort text-muted"></i>';
    }
    return ($_GET['direccion'] ?? 'ASC') === 'ASC'
        ? '<i class="fas fa-sort-up"></i>'
        : '<i class="fas fa-sort-down"></i>';
}
?>

