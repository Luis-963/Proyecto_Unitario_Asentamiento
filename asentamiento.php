<?php
require 'conexion.php';
session_start();
if (!isset($_SESSION['usuario_activo'])) { header("Location: index.php"); exit(); }

$mensaje = "";
$tipos = $conexion->query("SELECT * FROM tipo_asentamiento")->fetchAll();
$municipios = $conexion->query("SELECT * FROM municipio")->fetchAll();
$zonas = $conexion->query("SELECT * FROM zona")->fetchAll();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $accion = $_POST['accion'];
    $id_nuevo = $_POST['clave_asentamiento'];
    $nombre = $_POST['nombre'];
    $id_municipio = $_POST['id_municipio'];
    $id_tipo = $_POST['id_tipo_asentamiento'];
    $codigo = $_POST['d_codigo'];
    $id_zona = $_POST['id_zona'];
    $id_antiguo = $_POST['clave_antigua'] ?? '';

    try {
        if ($accion == 'guardar') {
            $stmt = $conexion->prepare("INSERT INTO asentamiento (id_asentamiento, nombre, id_tipo_asentamiento, d_codigo, id_zona, id_municipio) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$id_nuevo, $nombre, $id_tipo, $codigo, $id_zona, $id_municipio]);
            $mensaje = "<div class='mensaje_alerta alerta_exito'>Guardado correctamente.</div>";
        } elseif ($accion == 'editar' && $id_antiguo != '') {
            $stmt = $conexion->prepare("UPDATE asentamiento SET id_asentamiento=?, nombre=?, id_tipo_asentamiento=?, d_codigo=?, id_zona=?, id_municipio=? WHERE id_asentamiento=?");
            $stmt->execute([$id_nuevo, $nombre, $id_tipo, $codigo, $id_zona, $id_municipio, $id_antiguo]);
            $mensaje = "<div class='mensaje_alerta alerta_exito'>Actualizado correctamente.</div>";
        } elseif ($accion == 'eliminar' && $id_antiguo != '') {
            $stmt = $conexion->prepare("DELETE FROM asentamiento WHERE id_asentamiento=?");
            $stmt->execute([$id_antiguo]);
            $mensaje = "<div class='mensaje_alerta alerta_exito'>Eliminado correctamente.</div>";
        }
    } catch (PDOException $e) {
        $mensaje = "<div class='mensaje_alerta alerta_error'>Error: " . $e->getMessage() . "</div>";
    }
}

// Búsqueda y Paginación
$busqueda = $_GET['busqueda'] ?? '';
$where = $busqueda ? "WHERE a.nombre LIKE '%$busqueda%'" : "";
$limite = 50;
$pagina_actual = max(1, (int)($_GET['pagina'] ?? 1));
$offset = ($pagina_actual - 1) * $limite;

$total_filas = $conexion->query("SELECT COUNT(*) FROM asentamiento a $where")->fetchColumn();
$total_paginas = max(1, ceil($total_filas / $limite));

$sql = "SELECT a.id_asentamiento, a.nombre, ta.nombre AS tipo, a.id_tipo_asentamiento, a.d_codigo, m.nombre AS municipio, a.id_municipio, z.nombre AS zona, a.id_zona 
        FROM asentamiento a
        JOIN tipo_asentamiento ta ON a.id_tipo_asentamiento = ta.id_tipo_asentamiento
        JOIN municipio m ON a.id_municipio = m.id_municipio
        JOIN zona z ON a.id_zona = z.id_zona 
        $where LIMIT $limite OFFSET $offset";
$lista = $conexion->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Asentamiento</title>
    <link rel="stylesheet" href="estilos.css">
    <script>
        function seleccionarFila(fila, id_tipo, id_muni, id_zona) {
            let clave = fila.cells[0].innerText.trim();
            let nombre = fila.cells[1].innerText.trim();
            let codigo = fila.cells[3].innerText.trim();

            document.getElementById('clave_antigua').value = clave;
            document.getElementById('nombre_antiguo').value = nombre;

            document.getElementById('clave_asentamiento').value = clave;
            document.getElementById('nombre').value = nombre;
            document.getElementById('codigo_asentamiento').value = codigo;
            document.getElementById('tipo_asentamiento').value = id_tipo;
            document.getElementById('municipio').value = id_muni;
            document.getElementById('zona').value = id_zona;
        }
    </script>
</head>
<body>
    <nav class="barra_navegacion">
        <div class="enlaces_nav">
            <a href="inicio.php">Inicio</a>
            <a href="estado.php">Estado</a>
            <a href="municipio.php">Municipio/Ciudad</a>
            <a href="asentamiento.php" class="activo">Asentamiento</a>
            <a href="zona.php">ZONA</a>
            <a href="consulta.php">Consultas Generales</a>
        </div>
        <a href="index.php" class="boton_salir">SALIR</a>
    </nav>

    <div class="contenedor_principal">
        <h2 class="titulo_pantalla">Asentamiento</h2>
        <?php echo $mensaje; ?>

        <form action="asentamiento.php" method="GET" style="margin-bottom: 20px; display:flex; gap:10px;">
            <input type="text" name="busqueda" placeholder="Buscar asentamiento..." value="<?php echo htmlspecialchars($busqueda); ?>" style="padding:10px; border-radius:5px; border:1px solid #ccc; flex:1;">
            <button type="submit" class="boton btn_buscar">BUSCAR</button>
            <a href="asentamiento.php" class="boton btn_limpiar">LIMPIAR</a>
        </form>

        <form action="asentamiento.php" method="POST">
            <fieldset>
                <legend>Datos Antiguos (Seleccione en la tabla)</legend>
                <div class="formulario_cuadricula">
                    <div class="grupo_formulario">
                        <label>ID Antiguo</label>
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
                    <label>ID Asentamiento</label>
                    <input type="number" id="clave_asentamiento" name="clave_asentamiento" required>
                </div>
                <div class="grupo_formulario">
                    <label>Nombre Nuevo</label>
                    <input type="text" id="nombre" name="nombre" required maxlength="100">
                </div>
                <div class="grupo_formulario">
                    <label>Tipo asentamiento</label>
                    <select id="tipo_asentamiento" name="id_tipo_asentamiento" required>
                        <option value="">Seleccione...</option>
                        <?php foreach($tipos as $t) echo "<option value='{$t['id_tipo_asentamiento']}'>{$t['nombre']}</option>"; ?>
                    </select>
                </div>
                <div class="grupo_formulario">
                    <label>Municipio</label>
                    <select id="municipio" name="id_municipio" required>
                        <option value="">Seleccione...</option>
                        <?php foreach($municipios as $m) echo "<option value='{$m['id_municipio']}'>{$m['nombre']}</option>"; ?>
                    </select>
                </div>
                <div class="grupo_formulario">
                    <label>Código asentamiento (Postal)</label>
                    <input type="number" id="codigo_asentamiento" name="d_codigo" required>
                </div>
                <div class="grupo_formulario">
                    <label>Zona</label>
                    <select id="zona" name="id_zona" required>
                        <option value="">Seleccione...</option>
                        <?php foreach($zonas as $z) echo "<option value='{$z['id_zona']}'>{$z['nombre']}</option>"; ?>
                    </select>
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
                <thead><tr><th>Numero</th><th>Nombre</th><th>Tipo</th><th>Codigo</th><th>Municipios</th><th>Zona</th></tr></thead>
                <tbody>
                    <?php foreach($lista as $a): ?>
                    <tr class="fila_seleccionable" onclick="seleccionarFila(this, '<?php echo $a['id_tipo_asentamiento']; ?>', '<?php echo $a['id_municipio']; ?>', '<?php echo $a['id_zona']; ?>')">
                        <td><?php echo htmlspecialchars($a['id_asentamiento']); ?></td>
                        <td><?php echo htmlspecialchars($a['nombre']); ?></td>
                        <td><?php echo htmlspecialchars($a['tipo']); ?></td>
                        <td><?php echo htmlspecialchars($a['d_codigo']); ?></td>
                        <td><?php echo htmlspecialchars($a['municipio']); ?></td>
                        <td><?php echo htmlspecialchars($a['zona']); ?></td>
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