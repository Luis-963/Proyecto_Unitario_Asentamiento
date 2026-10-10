<?php
require 'conexion.php';
session_start();
if (!isset($_SESSION['usuario_activo'])) { header("Location: index.php"); exit(); }

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $accion = $_POST['accion'];
    $id_nuevo = $_POST['clave_zona'];
    $nombre_nuevo = $_POST['nombre_zona'];
    $id_antiguo = $_POST['clave_antigua'] ?? '';

    try {
        if ($accion == 'guardar') {
            $stmt = $conexion->prepare("INSERT INTO zona (id_zona, nombre) VALUES (?, ?)");
            $stmt->execute([$id_nuevo, $nombre_nuevo]);
            $mensaje = "<div class='mensaje_alerta alerta_exito'>Guardado correctamente.</div>";
        } elseif ($accion == 'editar' && $id_antiguo != '') {
            $stmt = $conexion->prepare("UPDATE zona SET id_zona = ?, nombre = ? WHERE id_zona = ?");
            $stmt->execute([$id_nuevo, $nombre_nuevo, $id_antiguo]);
            $mensaje = "<div class='mensaje_alerta alerta_exito'>Actualizado correctamente.</div>";
        } elseif ($accion == 'eliminar' && $id_antiguo != '') {
            $stmt = $conexion->prepare("DELETE FROM zona WHERE id_zona = ?");
            $stmt->execute([$id_antiguo]);
            $mensaje = "<div class='mensaje_alerta alerta_exito'>Eliminado correctamente.</div>";
        }
    } catch (PDOException $e) {
        $mensaje = "<div class='mensaje_alerta alerta_error'>Error: " . $e->getMessage() . "</div>";
    }
}

// Búsqueda y Paginación
$busqueda = $_GET['busqueda'] ?? '';
$where = $busqueda ? "WHERE nombre LIKE '%$busqueda%'" : "";
$limite = 50;
$pagina_actual = max(1, (int)($_GET['pagina'] ?? 1));
$offset = ($pagina_actual - 1) * $limite;

$total_filas = $conexion->query("SELECT COUNT(*) FROM zona $where")->fetchColumn();
$total_paginas = max(1, ceil($total_filas / $limite));

$lista_zonas = $conexion->query("SELECT id_zona, nombre FROM zona $where LIMIT $limite OFFSET $offset")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Zonas</title>
    <link rel="stylesheet" href="estilos.css">
    <script>
        function seleccionarFila(fila) {
            let clave = fila.cells[0].innerText.trim();
            let nombre = fila.cells[1].innerText.trim();
            document.getElementById('clave_zona').value = clave;
            document.getElementById('nombre_zona').value = nombre;
            document.getElementById('clave_antigua').value = clave;
            document.getElementById('nombre_antiguo').value = nombre;
        }
    </script>
</head>
<body>
    <nav class="barra_navegacion">
        <div class="enlaces_nav">
            <a href="inicio.php">Inicio</a>
            <a href="estado.php">Estado</a>
            <a href="municipio.php">Municipio/Ciudad</a>
            <a href="asentamiento.php">Asentamiento</a>
            <a href="zona.php" class="activo">ZONA</a>
            <a href="consulta.php">Consultas Generales</a>
        </div>
        <a href="index.php" class="boton_salir">SALIR</a>
    </nav>

    <div class="contenedor_principal">
        <h2 class="titulo_pantalla">Zona</h2>
        <?php echo $mensaje; ?>

        <form action="zona.php" method="GET" style="margin-bottom: 20px; display:flex; gap:10px;">
            <input type="text" name="busqueda" placeholder="Buscar zona..." value="<?php echo htmlspecialchars($busqueda); ?>" style="padding:10px; border-radius:5px; border:1px solid #ccc; flex:1;">
            <button type="submit" class="boton btn_buscar">BUSCAR</button>
            <a href="zona.php" class="boton btn_limpiar">LIMPIAR</a>
        </form>

        <form action="zona.php" method="POST">
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
                    <label>Clave Nueva</label>
                    <input type="number" id="clave_zona" name="clave_zona" required>
                </div>
                <div class="grupo_formulario">
                    <label>Descripción Zona</label>
                    <input type="text" id="nombre_zona" name="nombre_zona" required maxlength="30">
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
                <thead><tr><th>Numero</th><th>Descripcion</th></tr></thead>
                <tbody>
                    <?php foreach($lista_zonas as $z): ?>
                    <tr class="fila_seleccionable" onclick="seleccionarFila(this)">
                        <td><?php echo htmlspecialchars($z['id_zona']); ?></td>
                        <td><?php echo htmlspecialchars($z['nombre']); ?></td>
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