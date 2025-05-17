<?php
require_once 'config/database.php';

class Prestamo
{
    private $conexion;
    private $tabla = "prestamo";

    // Propiedades
    public $prestamo_id;
    public $prestamo_prestatario_id;
    public $prestamo_bibliotecario_id;
    public $prestamo_fecha_prestamo;
    public $prestamo_fecha_devolucion_prevista;
    public $prestamo_fecha_devolucion_real;
    public $ejemplares = [];

    public function __construct()
    {
        $database = new Database();
        $this->conexion = $database->getConexion();
    }

    // Crear nuevo préstamo
    public function crear()
    {
        try {
            $this->conexion->beginTransaction();

            // Insertar préstamo
            $sql = "INSERT INTO {$this->tabla} 
                        (prestamo_prestatario_id, prestamo_bibliotecario_id, prestamo_fecha_prestamo, prestamo_fecha_devolucion_prevista) 
                    VALUES (?, ?, ?, ?)";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([
                $this->prestamo_prestatario_id,
                $this->prestamo_bibliotecario_id,
                $this->prestamo_fecha_prestamo,
                $this->prestamo_fecha_devolucion_prevista
            ]);

            $this->prestamo_id = $this->conexion->lastInsertId();

            // Insertar ejemplares del préstamo
            foreach ($this->ejemplares as $ejemplarId) {
                $sql2 = "INSERT INTO prestamoejemplar (prestamoejemplar_prestamo_id, prestamoejemplar_ejemplar_id) VALUES (?, ?)";
                $stmt2 = $this->conexion->prepare($sql2);
                $stmt2->execute([$this->prestamo_id, $ejemplarId]);

                // Actualizar estado del ejemplar a 'prestado'
                $sql3 = "UPDATE ejemplar SET ejemplar_estado = 'prestado' WHERE ejemplar_id = ?";
                $stmt3 = $this->conexion->prepare($sql3);
                $stmt3->execute([$ejemplarId]);

                // Actualizar contador de copias disponibles
                $this->actualizarContadorCopias($ejemplarId);
            }

            $this->conexion->commit();
            return true;

        } catch (PDOException $e) {
            $this->conexion->rollBack();
            error_log("Error en Prestamo::crear(): " . $e->getMessage());
            return false;
        }
    }

    // Registrar devolución
    public function registrarDevolucion($prestamoId, $fechaDevolucion)
    {
        try {
            $this->conexion->beginTransaction();

            // 1) Actualizar la fecha de devolución real
            $sql1 = "UPDATE {$this->tabla} SET prestamo_fecha_devolucion_real = ? WHERE prestamo_id = ?";
            $stmt1 = $this->conexion->prepare($sql1);
            $stmt1->execute([$fechaDevolucion, $prestamoId]);

            // 2) Obtener ejemplares asociados
            $sql2 = "SELECT prestamoejemplar_ejemplar_id AS ejemplar_id FROM prestamoejemplar WHERE prestamoejemplar_prestamo_id = ?";
            $stmt2 = $this->conexion->prepare($sql2);
            $stmt2->execute([$prestamoId]);

            // 3) Para cada ejemplar, actualizar estado y contador
            while ($fila = $stmt2->fetch(PDO::FETCH_ASSOC)) {
                $ejemplarId = $fila['ejemplar_id'];

                // Marcar ejemplar como disponible
                $sql3 = "UPDATE ejemplar SET ejemplar_estado = 'disponible' WHERE ejemplar_id = ?";
                $stmt3 = $this->conexion->prepare($sql3);
                $stmt3->execute([$ejemplarId]);

                // Actualizar contador de copias disponibles
                $this->actualizarContadorCopias($ejemplarId);
            }

            $this->conexion->commit();
            return true;

        } catch (PDOException $e) {
            $this->conexion->rollBack();
            return false;
        }
    }

    // Obtener préstamos activos
    public function obtenerPrestamosActivos()
    {
        $sql = "SELECT p.*, pr.prestatario_nombre, pr.prestatario_identificacion,
                    (p.prestamo_fecha_devolucion_prevista < NOW() AND p.prestamo_fecha_devolucion_real IS NULL) AS en_mora
                FROM {$this->tabla} p
                JOIN prestatario pr ON p.prestamo_prestatario_id = pr.prestatario_id
                WHERE p.prestamo_fecha_devolucion_real IS NULL
                ORDER BY en_mora DESC, p.prestamo_fecha_devolucion_prevista ASC";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute();
        return $stmt;
    }

    // Obtener préstamos en mora
    public function obtenerPrestamosEnMora()
    {
        $sql = "SELECT p.*, pr.prestatario_nombre, pr.prestatario_identificacion
                FROM {$this->tabla} p
                JOIN prestatario pr ON p.prestamo_prestatario_id = pr.prestatario_id
                WHERE p.prestamo_fecha_devolucion_prevista < NOW()
                  AND p.prestamo_fecha_devolucion_real IS NULL
                ORDER BY p.prestamo_fecha_devolucion_prevista ASC";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute();
        return $stmt;
    }

    // Contar préstamos en mora
    public function contarPrestamosEnMora()
    {
        $sql = "SELECT COUNT(*) AS total FROM {$this->tabla}
                WHERE prestamo_fecha_devolucion_prevista < NOW()
                  AND prestamo_fecha_devolucion_real IS NULL";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute();
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)$fila['total'];
    }

    // Verificar límite de préstamos por prestatario
    public function verificarLimitePrestamos($prestatarioId)
    {
        $sql = "SELECT COUNT(*) AS total FROM {$this->tabla}
                WHERE prestamo_prestatario_id = ?
                  AND prestamo_fecha_devolucion_real IS NULL";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([$prestatarioId]);
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)$fila['total'];
    }

    // Obtener ejemplares de un préstamo
    public function obtenerEjemplaresPrestamo($prestamoId)
    {
        $sql = "SELECT l.libro_titulo,
                       GROUP_CONCAT(a.autor_nombre SEPARATOR ', ') AS autores,
                       l.libro_isbn
                FROM prestamoejemplar pe
                JOIN ejemplar e ON pe.prestamoejemplar_ejemplar_id = e.ejemplar_id
                JOIN libro l ON e.ejemplar_libro_id = l.libro_id
                LEFT JOIN libroautor_libroautor la ON la.libroautor_libro_id = l.libro_id
                LEFT JOIN autor a ON a.autor_id = la.autor_id
                WHERE pe.prestamoejemplar_prestamo_id = ?
                GROUP BY e.ejemplar_id, l.libro_titulo, l.libro_isbn";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([$prestamoId]);
        return $stmt;
    }

    // Actualizar contador de copias disponibles
    private function actualizarContadorCopias($ejemplarId)
    {
        // 1) Obtener el libro_id desde ejemplar_libro_id
        $sql = "SELECT ejemplar_libro_id AS libro_id FROM ejemplar WHERE ejemplar_id = ?";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([$ejemplarId]);
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);
        $libroId = $fila['libro_id'];

        // 2) Contar ejemplares disponibles
        $sql2 = "SELECT COUNT(*) AS disponibles FROM ejemplar WHERE ejemplar_libro_id = ? AND ejemplar_estado = 'disponible'";
        $stmt2 = $this->conexion->prepare($sql2);
        $stmt2->execute([$libroId]);
        $fila2 = $stmt2->fetch(PDO::FETCH_ASSOC);
        $disponibles = (int)$fila2['disponibles'];

        // 3) Actualizar contador en libro
        $sql3 = "UPDATE libro SET libro_copias_disponibles = ? WHERE libro_id = ?";
        $stmt3 = $this->conexion->prepare($sql3);
        $stmt3->execute([$disponibles, $libroId]);
    }

    // Calcular fecha de devolución prevista
    public function calcularFechaDevolucion($fechaPrestamo, $diasPrestamo = 3)
    {
        $fecha = new DateTime($fechaPrestamo);
        $diasHabiles = 0;
        while ($diasHabiles < $diasPrestamo) {
            $fecha->modify('+1 day');
            if ($fecha->format('N') < 6) {
                $diasHabiles++;
            }
        }
        return $fecha->format('Y-m-d H:i:s');
    }

    // Obtener préstamo por ID
    public function obtenerPorId($id)
    {
        $sql = "SELECT p.*, pr.prestatario_nombre, pr.prestatario_identificacion
                FROM {$this->tabla} p
                JOIN prestatario pr ON p.prestamo_prestatario_id = pr.prestatario_id
                WHERE p.prestamo_id = ?";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([$id]);
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($fila) {
            $this->prestamo_id = $fila['prestamo_id'];
            $this->prestamo_prestatario_id = $fila['prestamo_prestatario_id'];
            $this->prestamo_bibliotecario_id = $fila['prestamo_bibliotecario_id'];
            $this->prestamo_fecha_prestamo = $fila['prestamo_fecha_prestamo'];
            $this->prestamo_fecha_devolucion_prevista = $fila['prestamo_fecha_devolucion_prevista'];
            $this->prestamo_fecha_devolucion_real = $fila['prestamo_fecha_devolucion_real'];
            return true;
        }
        return false;
    }
}
?>
