<?php
class Database {
    private $host = "localhost";
    private $usuario = "root";
    private $password = "";
    private $base_datos = "biblioteca";
    private $conexion;

    public function getConexion() {
        $this->conexion = null;
        
        try {
            $this->conexion = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->base_datos,
                $this->usuario,
                $this->password
            );
            $this->conexion->exec("set names utf8");
            $this->conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $excepcion) {
            echo "Error de conexión: " . $excepcion->getMessage();
        }
        
        return $this->conexion;
    }
}
?>