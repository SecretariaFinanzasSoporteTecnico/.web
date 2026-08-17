<?php
session_start();
include 'config.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

// --- PROCESAR BÚSQUEDA Y FILTROS ---
$year = isset($_GET['year']) ? $_GET['year'] : '2025';
$busqueda = isset($_GET['buscar']) ? trim($_GET['buscar']) : ''; 

$titulo = "Histórico de Servicios";

// Construcción de la consulta SQL Base
$sql = "SELECT * FROM servicios WHERE anio_archivado IS NOT NULL AND anio_archivado > 0";

// Filtro por año
if ($year != 'todos') { 
    $sql .= " AND anio_archivado = '$year'";
    $titulo = "Histórico de Servicios - Año $year";
}

// Filtro por búsqueda (Nombre o Descripción)
if (!empty($busqueda)) {
    $busqueda_segura = mysqli_real_escape_string($conn, $busqueda);
    $sql .= " AND (nombre_solicitante LIKE '%$busqueda_segura%' OR descripcion_equipo LIKE '%$busqueda_segura%')";
    $titulo .= " - Buscando: '$busqueda'";
}

$sql .= " ORDER BY id DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $titulo; ?></title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f0f2f5; min-height:100vh; padding:20px; }
        .container { max-width:1200px; margin:0 auto; background:#fff; border-radius:12px; padding:25px 30px; box-shadow:0 8px 30px rgba(0,0,0,0.1); }
        
        .header { display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:15px; margin-bottom:25px; border-bottom:3px solid #1a3a5c; padding-bottom:15px; }
        .header h1 { color:#1a3a5c; font-size:28px; }
        
        .btn { 
            padding:10px 20px; border:none; border-radius:8px; font-weight:600; cursor:pointer; 
            text-decoration:none; display:inline-block; transition:all 0.3s; 
        }
        .btn-volver { background:#6c757d; color:#fff; }
        .btn-volver:hover { transform:translateY(-2px); box-shadow:0 5px 15px rgba(108,117,125,0.3); }

        /* ESTILO DEL BOTÓN DE INVENTARIO */
        .btn-inventario { background: #20c997; color: #fff; }
        .btn-inventario:hover { background: #1aa179; transform: translateY(-2px); }

        /* ESTILOS DEL BUSCADOR */
        .buscador-container {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 10px;
            border: 1px solid #e9ecef;
        }
        .buscador-container input {
            flex: 1;
            max-width: 400px;
            padding: 10px 15px;
            border: 1px solid #ced4da;
            border-radius: 8px;
            font-size: 15px;
            outline: none;
            transition: border 0.3s;
        }
        .buscador-container input:focus {
            border-color: #1a3a5c;
        }
        .btn-buscar {
            background: #1a3a5c;
            color: #fff;
        }
        .btn-buscar:hover {
            background: #2c5f7c;
        }
        .btn-limpiar {
            background: #6c757d;
            color: #fff;
        }

        .table-responsive { overflow-x:auto; margin-top:15px; }
        table { width:100%; border-collapse:collapse; font-size:14px; }
        thead { background:#1a3a5c; color:#fff; }
        th { padding:12px 15px; text-align:left; white-space:nowrap; }
        td { padding:10px 15px; border-bottom:1px solid #e6eaef; vertical-align:middle; }
        tbody tr:hover { background:#f5f8fc; }
        .badge { display:inline-block; padding:3px 12px; border-radius:20px; font-size:12px; font-weight:600; }
        .badge-pendiente { background:#fff3cd; color:#856404; border:1px solid #ffeeba; }
        .badge-resuelto { background:#d4edda; color:#155724; border:1px solid #c3e6cb; }
        
        @media (max-width:768px) { 
            .header { flex-direction:column; align-items:stretch; } 
            .header .btn { width:100%; text-align:center; }
            .buscador-container { flex-direction: column; align-items: center; }
            .buscador-container input { width: 100%; max-width: 100%; }
            .buscador-container .btn { width: 100%; text-align: center; }
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>📜 <?php echo $titulo; ?></h1>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            
            <!-- ✅ BOTÓN DE IMPRIMIR CORREGIDO (Abre nueva pestaña y solo imprime allí) -->
            <button onclick="var w=window.open('imprimir_historico.php?year=<?php echo $year; ?>','_blank'); w.onload=function(){this.print();}" class="btn btn-imprimir" style="background:#17a2b8; color:#fff; padding:10px 20px; border-radius:8px; border:none; cursor:pointer; font-weight:600; font-size:14px; font-family:inherit;">🖨️ Imprimir</button>

            <!-- BOTÓN DE INVENTARIO -->
            <a href="inventario.php" class="btn btn-inventario">📦 Inventario</a>
            
            <!-- BOTÓN PARA VER TODOS LOS AÑOS -->
            <a href="historico_servicios.php?year=todos" class="btn btn-success" style="background: #1a3a5c; color: #fff; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 600;">📋 Ver Todos</a>

            <a href="gestion_servicio.php" class="btn btn-volver">⬅ Volver a Gestión</a>
        </div>
    </div>

    <!-- BUSCADOR SEGURO -->
    <form method="GET" action="historico_servicios.php" class="buscador-container">
        <input type="hidden" name="year" value="<?php echo $year; ?>">
        <input type="text" name="buscar" placeholder="🔍 Buscar por nombre o equipo..." value="<?php echo htmlspecialchars($busqueda); ?>">
        <button type="submit" class="btn btn-buscar">🔍 Buscar</button>
        <?php if (!empty($busqueda)): ?>
            <a href="historico_servicios.php?year=<?php echo $year; ?>" class="btn btn-limpiar">✖ Limpiar</a>
        <?php endif; ?>
    </form>

    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Folio</th>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Estado</th>
                    <th>Fecha Registro</th>
                    <th>Año Archivado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): 
                        // 👇 LÓGICA PARA GENERAR EL FOLIO BONITO 👇
                        $anio_registro = date('Y', strtotime($row['fecha_registro']));
                        $folio_mostrar = (!empty($row['folio_anual'])) ? $anio_registro . '-' . str_pad($row['folio_anual'], 4, '0', STR_PAD_LEFT) : 'SIN FOLIO';
                    ?>
                        <tr>
                            <td><strong><?php echo $folio_mostrar; ?></strong></td>
                            <td><?php echo htmlspecialchars($row['nombre_solicitante'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($row['descripcion_equipo'] ?? ''); ?></td>
                            <td>
                                <?php 
                                    $resuelto = isset($row['resuelto']) ? $row['resuelto'] : 0;
                                    if($resuelto == 1) { echo '<span class="badge badge-resuelto">✅ Resuelto</span>'; } 
                                    else { echo '<span class="badge badge-pendiente">⏳ Pendiente</span>'; }
                                ?>
                            </td>
                            <td><?php echo date('d/m/Y', strtotime($row['fecha_registro'] ?? 'now')); ?></td>
                            <td><strong><?php echo $row['anio_archivado']; ?></strong></td>
                            
                            <!-- COLUMNA DE ACCIONES -->
                            <td>
                                <div class="acciones">
                                    <a href="ver_historico.php?id=<?php echo $row['id']; ?>" class="btn btn-warning" style="background:#6f42c1; color:#fff; padding:5px 12px; font-size:12px; border-radius:6px; text-decoration:none; font-weight:600;">📄 Ver</a>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align:center; padding:40px; color:#999;">
                            📭 No hay servicios archivados que coincidan con tu búsqueda.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>