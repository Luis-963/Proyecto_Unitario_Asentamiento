<?php
require 'conexion.php';
session_start();
if (!isset($_SESSION['usuario_activo'])) { header("Location: index.php"); exit(); }

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $accion = $_POST['accion'];
    $id_nuevo = $_POST['clave_estado'];
    $nombre_nuevo = $_POST['nombre_estado'];
    $id_antiguo = $_POST['clave_antigua'] ?? '';

    try {
        if ($accion == 'guardar') {
            $stmt = $conexion->prepare("INSERT INTO estado (id_estado, nombre) VALUES (?, ?)");
            $stmt->execute([$id_nuevo, $nombre_nuevo]);
            $mensaje = "<div class='mensaje_alerta alerta_exito'>Guardado correctamente.</div>";
        } elseif ($accion == 'editar' && $id_antiguo != '') {
            $stmt = $conexion->prepare("UPDATE estado SET id_estado = ?, nombre = ? WHERE id_estado = ?");
            $stmt->execute([$id_nuevo, $nombre_nuevo, $id_antiguo]);
            $mensaje = "<div class='mensaje_alerta alerta_exito'>Actualizado correctamente.</div>";
        } elseif ($accion == 'eliminar' && $id_antiguo != '') {
            $stmt = $conexion->prepare("DELETE FROM estado WHERE id_estado = ?");
            $stmt->execute([$id_antiguo]);
            $mensaje = "<div class='mensaje_alerta alerta_exito'>Eliminado correctamente.</div>";
        }
    } catch (PDOException $e) {
        $mensaje = "<div class='mensaje_alerta alerta_error'>Error: " . $e->getMessage() . "</div>";
    }
}

// Búsqueda
$busqueda = $_GET['busqueda'] ?? '';
$where = $busqueda ? "WHERE nombre LIKE '%$busqueda%'" : "";

// Paginación
$limite = 50;
$pagina_actual = max(1, (int)($_GET['pagina'] ?? 1));
$offset = ($pagina_actual - 1) * $limite;

$total_filas = $conexion->query("SELECT COUNT(*) FROM estado $where")->fetchColumn();
$total_paginas = max(1, ceil($total_filas / $limite));

$lista_estados = $conexion->query("SELECT id_estado, nombre FROM estado $where LIMIT $limite OFFSET $offset")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Estados</title>
    <link rel="stylesheet" href="estilos.css">
    <script>
        function seleccionarFila(fila) {
            let clave = fila.cells[0].innerText.trim();
            let nombre = fila.cells[1].innerText.trim();
            document.getElementById('clave_estado').value = clave;
            document.getElementById('nombre_estado').value = nombre;
            document.getElementById('clave_antigua').value = clave;
            document.getElementById('nombre_antiguo').value = nombre;
        }
    </script>
</head>
<body>
    <nav class="barra_navegacion">
        <div class="enlaces_nav">
            <a href="inicio.php">Inicio</a>
            <a href="estado.php" class="activo">Estado</a>
            <a href="municipio.php">Municipio/Ciudad</a>
            <a href="asentamiento.php">Asentamiento</a>
            <a href="zona.php">ZONA</a>
            <a href="consulta.php">Consultas Generales</a>
        </div>
        <a href="index.php" class="boton_salir">SALIR</a>
    </nav>

    <div class="contenedor_principal">
        <h2 class="titulo_pantalla">Estado</h2>
        <?php echo $mensaje; ?>

        <form action="estado.php" method="GET" style="margin-bottom: 20px; display:flex; gap:10px;">
            <input type="text" name="busqueda" placeholder="Buscar estado..." value="<?php echo htmlspecialchars($busqueda); ?>" style="padding:10px; border-radius:5px; border:1px solid #ccc; flex:1;">
            <button type="submit" class="boton btn_buscar">BUSCAR</button>
            <a href="estado.php" class="boton btn_limpiar">LIMPIAR</a>
        </form>

        <form action="estado.php" method="POST">
            <fieldset>
                <legend>Datos Antiguos (Seleccione en la tabla para Editar/Eliminar)</legend>
                <div class="formulario_cuadricula">
                    <div class="grupo_formulario">
                        <label>Clave Antigua</label>
                        <input type="text" id="clave_antigua" name="clave_antigua" class="input_antiguo" readonly>
                    </div>
                    <div class="grupo_formulario">
                        <label>Nombre Antiguo</label>
                        <input type="text" id="nombre_antiguo" class="input_antiguo" readonly>
                    </div>
                </div>
            </fieldset>

            <div class="formulario_cuadricula">
                <div class="grupo_formulario">
                    <label for="clave_estado">Clave Nueva</label>
                    <input type="number" id="clave_estado" name="clave_estado" required>
                </div>
                <div class="grupo_formulario">
                    <label for="nombre_estado">Nombre Nuevo</label>
                    <input type="text" id="nombre_estado" name="nombre_estado" required maxlength="50">
                </div>
            </div>
            
            <div class="contenedor_botones">
                <button type="submit" name="accion" value="guardar" class="boton btn_guardar">GUARDAR NUEVO</button>
                <button type="submit" name="accion" value="editar" class="boton btn_editar">EDITAR SELECCIONADO</button>
                <button type="submit" name="accion" value="eliminar" class="boton btn_eliminar">ELIMINAR SELECCIONADO</button>
            </div>
        </form>

        <div class="contenedor_tabla">
            <table>
                <thead><tr><th>Clave</th><th>Nombre</th></tr></thead>
                <tbody>
                    <?php foreach($lista_estados as $e): ?>
                    <tr class="fila_seleccionable" onclick="seleccionarFila(this)">
                        <td><?php echo htmlspecialchars($e['id_estado']); ?></td>
                        <td><?php echo htmlspecialchars($e['nombre']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="paginacion">
            <a href="<?php echo url_paginacion(1); ?>" class="btn_paginacion <?php if($pagina_actual<=1) echo 'deshabilitado'; ?>">Primera</a>
            <a href="<?php echo url_paginacion(max(1, $pagina_actual-1)); ?>" class="btn_paginacion <?php if($pagina_actual<=1) echo 'deshabilitado'; ?>">Anterior</a>
            <span class="texto_paginas">Página <?php echo $pagina_actual; ?> de <?php echo $total_paginas; ?></span>
            <a href="<?php echo url_paginacion(min($total_paginas, $pagina_actual+1)); ?>" class="btn_paginacion <?php if($pagina_actual>=$total_paginas) echo 'deshabilitado'; ?>">Siguiente</a>
            <a href="<?php echo url_paginacion($total_paginas); ?>" class="btn_paginacion <?php if($pagina_actual>=$total_paginas) echo 'deshabilitado'; ?>">Última</a>
        </div>
    </div>
</body>
</html>