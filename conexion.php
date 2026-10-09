<?php
$servidor = "localhost";
$usuario_bd = "root";
$contrasena_bd = ""; 
$nombre_bd = "prueba_6to_1";

try {
    $conexion = new PDO("mysql:host=$servidor;dbname=$nombre_bd;charset=utf8", $usuario_bd, $contrasena_bd);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $error) {
    die("Error de conexión: " . $error->getMessage());
}
?>