<?php
require 'conexion.php';
session_start();
if (!isset($_SESSION['usuario_activo'])) { header("Location: index.php"); exit(); }

$mensaje = "";
$estados = $conexion->query("SELECT id_estado, nombre FROM estado")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $accion = $_POST['accion'];
    $id_nuevo = $_POST['clave_municipio'];
    $nombre_nuevo = $_POST['nombre_municipio'];
    $estado_nuevo = $_POST['id_estado'];
    $oficina_nueva = $_POST['oficina'];
    $id_antiguo = $_POST['clave_antigua'] ?? '';

    try {
        if ($accion == 'guardar') {
            $stmt = $conexion->prepare("INSERT INTO municipio (id_municipio, nombre, id_estado, oficina) VALUES (?, ?, ?, ?)");
            $stmt->execute([$id_nuevo, $nombre_nuevo, $estado_nuevo, $oficina_nueva]);
            $mensaje = "<div class='mensaje_alerta alerta_exito'>Guardado correctamente.</div>";
        } elseif ($accion == 'editar' && $id_antiguo != '') {
            $stmt = $conexion->prepare("UPDATE municipio SET id_municipio=?, nombre=?, id_estado=?, oficina=? WHERE id_municipio=?");
            $stmt->execute([$id_nuevo, $nombre_nuevo, $estado_nuevo, $oficina_nueva, $id_antiguo]);
            $mensaje = "<div class='mensaje_alerta alerta_exito'>Actualizado correctamente.</div>";
        } elseif ($accion == 'eliminar' && $id_antiguo != '') {
            $stmt = $conexion->prepare("DELETE FROM municipio WHERE id_municipio=?");
            $stmt->execute([$id_antiguo]);
            $mensaje = "<div class='mensaje_alerta alerta_exito'>Eliminado correctamente.</div>";
        }
    } catch (PDOException $e) {
        $mensaje = "<div class='mensaje_alerta alerta_error'>Error: " . $e->getMessage() . "</div>";
    }
}

// Búsqueda y Paginación
$busqueda = $_GET['busqueda'] ?? '';
$where = $busqueda ? "WHERE m.nombre LIKE '%$busqueda%'" : "";
$limite = 50;
$pagina_actual = max(1, (int)($_GET['pagina'] ?? 1));
$offset = ($pagina_actual - 1) * $limite;

$total_filas = $conexion->query("SELECT COUNT(*) FROM municipio m $where")->fetchColumn();
$total_paginas = max(1, ceil($total_filas / $limite));

$sql = "SELECT m.id_municipio, m.nombre, m.id_estado, e.nombre AS estado_nombre, m.oficina 
        FROM municipio m JOIN estado e ON m.id_estado = e.id_estado $where LIMIT $limite OFFSET $offset";
$lista_municipios = $conexion->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Municipio / Ciudad</title>
    <link rel="stylesheet" href="estilos.css">
    <script>
        function seleccionarFila(fila, id_estado) {
            let clave = fila.cells[0].innerText.trim();
            let nombre = fila.cells[1].innerText.trim();
            let oficina = fila.cells[3].innerText.trim();

            document.getElementById('clave_antigua').value = clave;
            document.getElementById('nombre_antiguo').value = nombre;

            document.getElementById('clave_municipio').value = clave;
            document.getElementById('nombre_municipio').value = nombre;
            document.getElementById('oficina').value = oficina;
            document.getElementById('estado').value = id_estado;
        }
    </script>
</head>
<body>
    <nav class="barra_navegacion">
        <div class="enlaces_nav">
            <a href="inicio.php">Inicio</a>
            <a href="estado.php">Estado</a>
            <a href="municipio.php" class="activo">Municipio/Ciudad</a>
            <a href="asentamiento.php">Asentamiento</a>
            <a href="zona.php">ZONA</a>
            <a href="consulta.php">Consultas Generales</a>
        </div>
        <a href="index.php" class="boton_salir">SALIR</a>
    </nav>

    <div class="contenedor_principal">
        <h2 class="titulo_pantalla">Municipio / Ciudad</h2>
        <?php echo $mensaje; ?>

        <form action="municipio.php" method="GET" style="margin-bottom: 20px; display:flex; gap:10px;">
            <input type="text" name="busqueda" placeholder="Buscar municipio..." value="<?php echo htmlspecialchars($busqueda); ?>" style="padding:10px; border-radius:5px; border:1px solid #ccc; flex:1;">
            <button type="submit" class="boton btn_buscar">BUSCAR</button>
            <a href="municipio.php" class="boton btn_limpiar">LIMPIAR</a>
        </form>

        <form action="municipio.php" method="POST">
            <fieldset>
                <legend>Datos Antiguos (Seleccione en la tabla)</legend>
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
                    <label>Nombre Nuevo</label>
                    <input type="text" id="nombre_municipio" name="nombre_municipio" required maxlength="100">
                </div>
                <div class="grupo_formulario">
                    <label>Estado</label>
                    <select id="estado" name="id_estado" required>
                        <option value="">Seleccione...</option>
                        <?php foreach($estados as $est): ?>
                            <option value="<?php echo $est['id_estado']; ?>"><?php echo $est['nombre']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="grupo_formulario">
                    <label>Clave Municipio</label>
                    <input type="number" id="clave_municipio" name="clave_municipio" required>
                </div>
                <div class="grupo_formulario">
                    <label>Oficina</label>
                    <input type="number" id="oficina" name="oficina" required>
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
                <thead><tr><th>Clave</th><th>Nombre</th><th>Estado</th><th>Oficina</th></tr></thead>
                <tbody>
                    <?php foreach($lista_municipios as $m): ?>
                    <tr class="fila_seleccionable" onclick="seleccionarFila(this, '<?php echo $m['id_estado']; ?>')">
                        <td><?php echo htmlspecialchars($m['id_municipio']); ?></td>
                        <td><?php echo htmlspecialchars($m['nombre']); ?></td>
                        <td><?php echo htmlspecialchars($m['estado_nombre']); ?></td>
                        <td><?php echo htmlspecialchars($m['oficina']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="paginacion">
            <a href="<?php echo url_paginacion(1); ?>" class="btn_paginacion <?php if($pagina_actual<=1) echo 'deshabilitado'; ?>">&lt;&lt;</a>
            <a href="<?php echo url_paginacion(max(1, $pagina_actual-1)); ?>" class="btn_paginacion <?php if($pagina_actual<=1) echo 'deshabilitado'; ?>">&lt;</a>
            
            <select class="select_paginacion" onchange="window.location.href=this.value;">
                <?php for($i = 1; $i <= $total_paginas; $i++): ?>
                    <option value="<?php echo url_paginacion($i); ?>" <?php if($i == $pagina_actual) echo 'selected'; ?>>
                        <?php echo $i; ?>
                    </option>
                <?php endfor; ?>
            </select>
            
            <a href="<?php echo url_paginacion(min($total_paginas, $pagina_actual+1)); ?>" class="btn_paginacion <?php if($pagina_actual>=$total_paginas) echo 'deshabilitado'; ?>">&gt;</a>
            <a href="<?php echo url_paginacion($total_paginas); ?>" class="btn_paginacion <?php if($pagina_actual>=$total_paginas) echo 'deshabilitado'; ?>">&gt;&gt;</a>
        </div>
    </div>
</body>
</html>