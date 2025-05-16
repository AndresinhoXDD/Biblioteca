<?php
require_once __DIR__ . '/database.php'; 

$db = new Database();
$pdo = $db->getConexion();
if ($pdo) {
    echo "Conexión exitosa.";
} else {
    echo "Falló la conexión.";
}
?>
