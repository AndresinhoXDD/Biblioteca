<?php 
include 'Vista/plantilla/cabecera.php';

// Definir $totalMora si no está definida
if (!isset($totalMora)) {
    $totalMora = 0;
}
?>

<?php include 'vista/plantilla/cabecera.php'; ?>

<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h2 class="card-title"><i class="fas fa-tachometer-alt me-2"></i>Panel Principal</h2>
                <p class="card-text">Bienvenido, <?php echo $_SESSION['nombre']; ?>. Desde aquí puedes gestionar todas las funciones de la biblioteca.</p>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-6 col-lg-3">
        <div class="card h-100 border-primary">
            <div class="card-body text-center">
                <i class="fas fa-book fa-3x mb-3 text-primary"></i>
                <h5 class="card-title">Catálogo</h5>
                <p class="card-text">Gestiona el catálogo de libros de la biblioteca.</p>
                <a href="index.php?accion=listar_libros" class="btn btn-primary">
                    <i class="fas fa-arrow-right me-1"></i>Acceder
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 col-lg-3">
        <div class="card h-100 border-success">
            <div class="card-body text-center">
                <i class="fas fa-exchange-alt fa-3x mb-3 text-success"></i>
                <h5 class="card-title">Préstamos</h5>
                <p class="card-text">Gestiona los préstamos y devoluciones.</p>
                <a href="index.php?accion=listar_prestamos" class="btn btn-success">
                    <i class="fas fa-arrow-right me-1"></i>Acceder
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 col-lg-3">
        <div class="card h-100 border-info">
            <div class="card-body text-center">
                <i class="fas fa-plus-circle fa-3x mb-3 text-info"></i>
                <h5 class="card-title">Nuevo Préstamo</h5>
                <p class="card-text">Registra un nuevo préstamo de libros.</p>
                <a href="index.php?accion=nuevo_prestamo" class="btn btn-info text-white">
                    <i class="fas fa-arrow-right me-1"></i>Acceder
                </a>
            </div>
        </div>
    </div>
    
    <?php if(isset($_SESSION['rol']) && $_SESSION['rol'] == 'admin'): ?>
    <div class="col-md-6 col-lg-3">
        <div class="card h-100 border-danger">
            <div class="card-body text-center">
                <i class="fas fa-users-cog fa-3x mb-3 text-danger"></i>
                <h5 class="card-title">Usuarios</h5>
                <p class="card-text">Administra los usuarios del sistema.</p>
                <a href="index.php?accion=listar_usuarios" class="btn btn-danger">
                    <i class="fas fa-arrow-right me-1"></i>Acceder
                </a>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php if($totalMora > 0): ?>
<div class="row mt-4">
    <div class="col-12">
        <div class="card bg-danger text-white">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-exclamation-triangle me-2"></i>Préstamos en Mora</h5>
                <p class="card-text">Hay <?php echo $totalMora; ?> préstamo(s) en mora que requieren atención.</p>
                <a href="index.php?accion=listar_prestamos&en_mora=1" class="btn btn-light">
                    <i class="fas fa-eye me-1"></i>Ver Préstamos en Mora
                </a>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php include 'vista/plantilla/pie.php'; ?>