<?php include 'vista/plantilla/cabecera.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card border-danger">
            <div class="card-header bg-danger text-white">
                <h4><i class="fas fa-exclamation-triangle me-2"></i>Acceso Denegado</h4>
            </div>
            <div class="card-body text-center">
                <i class="fas fa-lock fa-5x text-danger mb-3"></i>
                <h5 class="card-title">No tienes permisos para acceder a esta sección</h5>
                <p class="card-text">Lo sentimos, no tienes los permisos necesarios para acceder a la página solicitada.</p>
                <a href="index.php" class="btn btn-primary">
                    <i class="fas fa-home me-1"></i>Volver al Inicio
                </a>
            </div>
        </div>
    </div>
</div>

<?php include 'vista/plantilla/pie.php'; ?>