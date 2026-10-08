<?php
session_start();
if (!isset($_SESSION['usuario_activo'])) { header("Location: index.php"); exit(); }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
    <!-- ESTA BARRA DE NAVEGACIÓN DEBE INCLUIRSE EN TODAS LAS PANTALLAS (estado, municipio, etc.) -->
    <nav class="barra_navegacion">
        <div class="enlaces_nav">
            <a href="inicio.php" class="activo">Inicio</a>
            <a href="estado.php">Estado</a>
            <a href="municipio.php">Municipio/Ciudad</a>
            <a href="asentamiento.php">Asentamiento</a>
            <a href="zona.php">ZONA</a>
            <a href="consulta.php">Consultas Generales</a>
        </div>
        <a href="index.php" class="boton_salir">SALIR</a>
    </nav>

    <div class="contenedor_principal" style="text-align: center; padding: 100px;">
        <h1 style="font-size: 3em; color: var(--color-secundario);">Bienvenido</h1>
        <p>Sistema de Gestión de Códigos Postales y Asentamientos</p>
    </div>
</body>
</html>