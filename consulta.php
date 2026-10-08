<?php
require 'conexion.php';
session_start();
if (!isset($_SESSION['usuario_activo'])) { header("Location: index.php"); exit(); }

function url_paginacion($pagina) {
    $parametros = $_GET;
    $parametros['pagina'] = $pagina;
    return '?' . http_build_query($parametros);
}

$busqueda_nombre = $_GET['busqueda_nombre'] ?? '';
$busqueda_estado = $_GET['busqueda_estado'] ?? '';
$busqueda_muni = $_GET['busqueda_muni'] ?? '';

$where = "WHERE 1=1";
$params = [];

if ($busqueda_nombre !== '') { $where .= " AND a.nombre LIKE ?"; $params[] = "%$busqueda_nombre%"; }
if ($busqueda_estado !== '') { $where .= " AND e.nombre LIKE ?"; $params[] = "%$busqueda_estado%"; }
if ($busqueda_muni !== '') { $where .= " AND m.nombre LIKE ?"; $params[] = "%$busqueda_muni%"; }

// Paginación
$limite = 50;
$pagina_actual = max(1, (int)($_GET['pagina'] ?? 1));
$offset = ($pagina_actual - 1) * $limite;

$sql_count = "SELECT COUNT(*) FROM asentamiento a
              JOIN municipio m ON a.id_municipio = m.id_municipio
              JOIN estado e ON m.id_estado = e.id_estado
              LEFT JOIN ciudad c ON c.id_municipio = m.id_municipio
              JOIN zona z ON a.id_zona = z.id_zona
              JOIN tipo_asentamiento ta ON a.id_tipo_asentamiento = ta.id_tipo_asentamiento $where";

$stmt_count = $conexion->prepare($sql_count);
$stmt_count->execute($params);
$total_filas = $stmt_count->fetchColumn();
$total_paginas = max(1, ceil($total_filas / $limite));

$sql_consulta = "SELECT a.nombre AS Nombre_Asentamiento, e.nombre AS Estado, m.nombre AS Municipio, c.nombre AS Ciudad, a.d_codigo AS Codigo, z.nombre AS Zona, ta.nombre AS Tipo 
                FROM asentamiento a
                JOIN municipio m ON a.id_municipio = m.id_municipio
                JOIN estado e ON m.id_estado = e.id_estado
                LEFT JOIN ciudad c ON c.id_municipio = m.id_municipio
                JOIN zona z ON a.id_zona = z.id_zona
                JOIN tipo_asentamiento ta ON a.id_tipo_asentamiento = ta.id_tipo_asentamiento 
                $where LIMIT $limite OFFSET $offset";

$stmt_consulta = $conexion->prepare($sql_consulta);
$stmt_consulta->execute($params);
$resultado = $stmt_consulta->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Consulta de Datos Generales</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
    <nav class="barra_navegacion">
        <div class="enlaces_nav">
            <a href="inicio.php">Inicio</a>
            <a href="estado.php">Estado</a>
            <a href="municipio.php">Municipio/Ciudad</a>
            <a href="asentamiento.php">Asentamiento</a>
            <a href="zona.php">ZONA</a>
            <a href="consulta.php" class="activo">Consultas Generales</a>
        </div>
        <a href="index.php" class="boton_salir">SALIR</a>
    </nav>

    <div class="contenedor_principal">
        <h2 class="titulo_pantalla">Consulta Datos Generales</h2>
        
        <form action="consulta.php" method="GET" style="background-color: var(--color-destaque); padding: 20px; border-radius: 8px; margin-bottom: 20px;">
            <div class="formulario_cuadricula">
                <div class="grupo_formulario">
                    <label>Nombre Asentamiento</label>
                    <input type="text" name="busqueda_nombre" value="<?php echo htmlspecialchars($busqueda_nombre); ?>">
                </div>
                <div class="grupo_formulario">
                    <label>Estado</label>
                    <input type="text" name="busqueda_estado" value="<?php echo htmlspecialchars($busqueda_estado); ?>">
                </div>
                <div class="grupo_formulario">
                    <label>Municipio</label>
                    <input type="text" name="busqueda_muni" value="<?php echo htmlspecialchars($busqueda_muni); ?>">
                </div>
            </div>
            <div class="contenedor_botones" style="margin-bottom: 0;">
                <button type="submit" class="boton btn_buscar">BUSCAR / FILTRAR</button>
                <a href="consulta.php" class="boton btn_eliminar" style="text-align:center;">LIMPIAR</a>
            </div>
        </form>

        <p>Total de resultados encontrados: <b><?php echo $total_filas; ?></b></p>

        <div class="contenedor_tabla">
            <table>
                <thead><tr><th>Asentamiento</th><th>Estado</th><th>Municipio</th><th>Ciudad</th><th>Codigo</th><th>Zona</th><th>Tipo</th></tr></thead>
                <tbody>
                    <?php if(count($resultado) > 0): foreach($resultado as $f): ?>
                        <tr class="fila_seleccionable">
                            <td><?php echo htmlspecialchars($f['Nombre_Asentamiento']); ?></td>
                            <td><?php echo htmlspecialchars($f['Estado']); ?></td>
                            <td><?php echo htmlspecialchars($f['Municipio']); ?></td>
                            <td><?php echo htmlspecialchars($f['Ciudad'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($f['Codigo']); ?></td>
                            <td><?php echo htmlspecialchars($f['Zona']); ?></td>
                            <td><?php echo htmlspecialchars($f['Tipo']); ?></td>
                        </tr>
                    <?php endforeach; else: ?>
                        <tr><td colspan="7" style="text-align: center;">No se encontraron registros.</td></tr>
                    <?php endif; ?>
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