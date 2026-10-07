<?php
session_start();
$mensaje_error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = $_POST['usuario'];
    $contrasena = $_POST['contrasena'];

    // Validación simple de ejemplo (En producción conectar a tabla usuarios)
    if (!empty($usuario) && !empty($contrasena)) {
        $_SESSION['usuario_activo'] = $usuario;
        header("Location: inicio.php");
        exit();
    } else {
        $mensaje_error = "Credenciales incorrectas. Intente nuevamente.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema de Asentamientos</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
    <div class="contenedor_principal contenedor_login">
        <h2 class="titulo_pantalla">LOGIN</h2>
        <?php if($mensaje_error != "") echo "<div class='mensaje_alerta alerta_error'>$mensaje_error</div>"; ?>
        
        <form action="index.php" method="POST">
            <div class="grupo_formulario">
                <label for="usuario">Usuario</label>
                <input type="text" id="usuario" name="usuario" required maxlength="40">
            </div>
            <br>
            <div class="grupo_formulario">
                <label for="contrasena">Contraseña</label>
                <input type="password" id="contrasena" name="contrasena" required>
            </div>
            <button type="submit" class="boton btn_guardar">Ingresar</button>
        </form>
    </div>
</body>
</html>